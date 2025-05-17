<?php
session_start();
include("../database/config.php");
include("../database/data_session.php");

// Ensure user is logged in as supplier
$userId = $_SESSION['userId'] ?? null;
$userType = $_SESSION['usertype'] ?? null;

if (!$userId || $userType !== 'supplier') {
    die("Unauthorized access. Please log in as a supplier.");
}

$supplierId = $userId; // Assuming supplierId = userId

// Fetch supplier info from apply_supplier and contact number from users table
$stmtSupplier = $conn->prepare("
    SELECT s.supplier_id, s.business_name, s.address,
           u.contact_number
    FROM apply_supplier s
    LEFT JOIN users u ON u.id = s.supplier_id AND u.usertype = 'supplier'
    WHERE s.supplier_id = ? LIMIT 1
");
if (!$stmtSupplier) {
    die("Prepare failed (supplier): " . $conn->error);
}
$stmtSupplier->bind_param("i", $supplierId);
$stmtSupplier->execute();
$supplierResult = $stmtSupplier->get_result();
$supplier = $supplierResult->fetch_assoc();
$stmtSupplier->close();

if (!$supplier) {
    die("Supplier account not found.");
}

// Insert receipt without receipt_number
$sql = "INSERT INTO receipts (order_id, user_id, seller_id, supplier_id, payment_date, payment_method, subtotal, tax_rate, tax_amount, total_paid, amount_paid, authorized_by) VALUES (?, ?, ?, ?, NOW(), ?, ?, ?, ?, ?, ?, ?)";
$stmt = $conn->prepare($sql);
// bind params, execute, etc...

$receiptId = $conn->insert_id;

// Use $receiptId as the receipt number wherever needed


if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'], $_POST['order_id'])) {
    $newStatus = $_POST['update_status'];
    $orderId = intval($_POST['order_id']);

    $conn->begin_transaction();

    try {
        $verifySql = "
            SELECT o.status, o.user_id AS buyer_id, oi.ingredient_id, oi.variant_id, oi.quantity, 
                   i.ingredient_name, i.quantity_value, i.unit_type, oi.total_price
            FROM orders o
            JOIN order_items oi ON o.order_id = oi.order_id
            JOIN ingredients i ON oi.ingredient_id = i.ingredient_id
            WHERE o.order_id = ? 
              AND i.supplier_id = ?
        ";
        $stmtVerify = $conn->prepare($verifySql);
        if (!$stmtVerify) {
            throw new Exception("Prepare failed (verify): " . $conn->error);
        }
        $stmtVerify->bind_param("ii", $orderId, $supplierId);
        if (!$stmtVerify->execute()) {
            throw new Exception("Execute failed (verify): " . $stmtVerify->error);
        }
        $verifyResult = $stmtVerify->get_result();
        $orderItems = $verifyResult->fetch_all(MYSQLI_ASSOC);
        $stmtVerify->close();

        if (empty($orderItems)) {
            throw new Exception("No items found for this order and supplier.");
        }

        $currentStatus = $orderItems[0]['status'];
        $buyerId = $orderItems[0]['buyer_id'];

        if (in_array($currentStatus, ['Delivered', 'Cancelled'])) {
            throw new Exception("Order status cannot be updated.");
        }

        $stmtUpdate = $conn->prepare("UPDATE orders SET status = ? WHERE order_id = ?");
        if (!$stmtUpdate) {
            throw new Exception("Prepare update failed: " . $conn->error);
        }
        $stmtUpdate->bind_param("si", $newStatus, $orderId);
        if (!$stmtUpdate->execute()) {
            throw new Exception("Execute update failed: " . $stmtUpdate->error);
        }
        $stmtUpdate->close();

        if ($newStatus === 'Delivered') {
            foreach ($orderItems as $item) {
                $variantId = $item['variant_id'] ?? null;

                if ($variantId) {
                    $stmtUpdateVariant = $conn->prepare("
                        UPDATE ingredient_variants
                        SET quantity = quantity - ?
                        WHERE variant_id = ? AND ingredient_id = ?
                    ");
                    if (!$stmtUpdateVariant) {
                        throw new Exception("Prepare update variant quantity failed: " . $conn->error);
                    }
                    $stmtUpdateVariant->bind_param("iii", $item['quantity'], $variantId, $item['ingredient_id']);
                    $stmtUpdateVariant->execute();
                    $stmtUpdateVariant->close();
                } else {
                    $stmtUpdateIngredient = $conn->prepare("
                        UPDATE ingredients
                        SET quantity = quantity - ?
                        WHERE ingredient_id = ?
                    ");
                    if (!$stmtUpdateIngredient) {
                        throw new Exception("Prepare update ingredient quantity failed: " . $conn->error);
                    }
                    $stmtUpdateIngredient->bind_param("ii", $item['quantity'], $item['ingredient_id']);
                    $stmtUpdateIngredient->execute();
                    $stmtUpdateIngredient->close();
                }

                $stmtCheckSupplierInv = $conn->prepare("
                    SELECT inventory_id, quantity 
                    FROM ingredients_inventory
                    WHERE ingredient_id = ? AND (variant_id = ? OR (variant_id IS NULL AND ? IS NULL)) AND supplier_id = ?
                ");
                if (!$stmtCheckSupplierInv) {
                    throw new Exception("Prepare failed (check supplier inv): " . $conn->error);
                }
                $stmtCheckSupplierInv->bind_param("iiii", $item['ingredient_id'], $variantId, $variantId, $supplierId);
                $stmtCheckSupplierInv->execute();
                $supplierInventory = $stmtCheckSupplierInv->get_result()->fetch_assoc();
                $stmtCheckSupplierInv->close();

                if ($supplierInventory) {
                    $newSupplierQuantity = $supplierInventory['quantity'] - $item['quantity'];
                    $stmtUpdateSupplier = $conn->prepare("
                        UPDATE ingredients_inventory 
                        SET quantity = ? 
                        WHERE inventory_id = ?
                    ");
                    if (!$stmtUpdateSupplier) {
                        throw new Exception("Prepare failed (update supplier inv): " . $conn->error);
                    }
                    $stmtUpdateSupplier->bind_param("ii", $newSupplierQuantity, $supplierInventory['inventory_id']);
                    $stmtUpdateSupplier->execute();
                    $stmtUpdateSupplier->close();
                }

                $stmtCheckBuyerInv = $conn->prepare("
                    SELECT inventory_id, quantity 
                    FROM ingredients_inventory 
                    WHERE ingredient_id = ? 
                      AND (variant_id = ? OR (variant_id IS NULL AND ? IS NULL))
                      AND user_id = ? 
                      AND supplier_id = ?
                ");
                if (!$stmtCheckBuyerInv) {
                    throw new Exception("Prepare failed (check buyer inv): " . $conn->error);
                }
                $stmtCheckBuyerInv->bind_param("iiiis", $item['ingredient_id'], $variantId, $variantId, $buyerId, $supplierId);
                $stmtCheckBuyerInv->execute();
                $buyerInventory = $stmtCheckBuyerInv->get_result()->fetch_assoc();
                $stmtCheckBuyerInv->close();

                if ($buyerInventory) {
                    $newBuyerQuantity = $buyerInventory['quantity'] + $item['quantity'];
                    $stmtUpdateBuyer = $conn->prepare("
                        UPDATE ingredients_inventory 
                        SET quantity = ?
                        WHERE inventory_id = ?
                    ");
                    if (!$stmtUpdateBuyer) {
                        throw new Exception("Prepare failed (update buyer inv): " . $conn->error);
                    }
                    $stmtUpdateBuyer->bind_param("ii", $newBuyerQuantity, $buyerInventory['inventory_id']);
                    $stmtUpdateBuyer->execute();
                    $stmtUpdateBuyer->close();
                } else {
                    $stmtInsertBuyer = $conn->prepare("
                        INSERT INTO ingredients_inventory 
                        (ingredient_id, ingredient_name, quantity, quantity_value, unit_type, supplier_id, variant_id, user_id)
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?)
                    ");
                    if (!$stmtInsertBuyer) {
                        throw new Exception("Prepare failed (insert buyer inv): " . $conn->error);
                    }
                    $stmtInsertBuyer->bind_param(
                        "isdissii",
                        $item['ingredient_id'],
                        $item['ingredient_name'],
                        $item['quantity'],
                        $item['quantity_value'],
                        $item['unit_type'],
                        $supplierId,
                        $variantId,
                        $buyerId
                    );
                    $stmtInsertBuyer->execute();
                    $stmtInsertBuyer->close();
                }
            }

            // Receipt generation start

            $orderSql = "
            SELECT 
                o.*, 
                u.first_name, 
                u.last_name, 
                u.email, 
                u.contact_number,
                CONCAT_WS(', ', u.streetname, u.barangay, u.city, u.province, u.country) AS address
            FROM orders o 
            JOIN users u ON o.user_id = u.id 
            WHERE o.order_id = ?
        ";
        $stmt = $conn->prepare($orderSql);
        if (!$stmt) {
            error_log("Prepare failed (order): " . $conn->error);
            throw new Exception("Prepare failed (order): " . $conn->error);
        }
        
            $stmt->bind_param("i", $orderId);
            if (!$stmt->execute()) {
                throw new Exception("Execute failed (order): " . $stmt->error);
            }
            $order = $stmt->get_result()->fetch_assoc();
            $stmt->close();

            $itemsSql = "SELECT oi.*, i.ingredient_name, v.variant_name, i.quantity_value, i.unit_type 
                         FROM order_items oi
                         JOIN ingredients i ON oi.ingredient_id = i.ingredient_id
                         LEFT JOIN ingredient_variants v ON oi.variant_id = v.variant_id
                         WHERE oi.order_id = ?";
            $stmt = $conn->prepare($itemsSql);
            if (!$stmt) {
                throw new Exception("Prepare failed (items for receipt): " . $conn->error);
            }
            $stmt->bind_param("i", $orderId);
            $stmt->execute();
            $itemsForReceipt = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
            $stmt->close();


           // Insert receipt (without receipt_number)
$receiptSql = "INSERT INTO receipts (
    order_id, user_id, seller_id, supplier_id, payment_date, payment_method,
    subtotal, tax_rate, tax_amount, total_paid, amount_paid, authorized_by
) VALUES (?, ?, ?, ?, NOW(), ?, ?, ?, ?, ?, ?, ?)";

$stmt = $conn->prepare($receiptSql);
if (!$stmt) {
    throw new Exception("Prepare failed (insert receipt): " . $conn->error);
}

// For example, get these variables from $order or elsewhere:
$user_id = $buyerId;
$seller_id = null; // or your logic to get seller_id
$payment_date = date('Y-m-d H:i:s');
$payment_method = $order['payment_method'];
$subtotal = $order['total_price'];  // adjust if needed
$tax_rate = 0;    // or your tax rate
$tax_amount = 0;  // or calculate tax
$total_paid = $order['total_price'];
$amount_paid = $order['total_price'];
$authorized_by = $supplierId;  // or whoever authorizes

$stmt->bind_param(
    "iiiissddddd", 
    $orderId,
    $user_id,
    $seller_id,
    $supplierId,
    $payment_method,
    $subtotal,
    $tax_rate,
    $tax_amount,
    $total_paid,
    $amount_paid,
    $authorized_by
);
$stmt->execute();
$receiptId = $conn->insert_id;
$stmt->close();


$itemSql = "INSERT INTO receipt_items (
    receipt_id, ingredient_id, variant_id, description,
    quantity, unit_price, total_price
) VALUES (?, ?, ?, ?, ?, ?, ?)";

$stmt = $conn->prepare($itemSql);
if (!$stmt) {
    throw new Exception("Prepare failed (insert receipt items): " . $conn->error);
}

foreach ($itemsForReceipt as $item) {
    $description = $item['variant_name'] 
        ? $item['ingredient_name'] . ' - ' . $item['variant_name']
        : $item['ingredient_name'];

    $unit_price = $item['unit_price'] ?? 0;
    $total_price = $item['total_price'] ?? 0;

    $variantId = $item['variant_id'] ?? null;

    $stmt->bind_param(
        "iiisidd",
        $receiptId,
        $item['ingredient_id'],
        $variantId,
        $description,
        $item['quantity'],
        $unit_price,
        $total_price
    );
    $stmt->execute();
}
$stmt->close();

        }

        $conn->commit();
        echo "success";
        exit();

    } catch (Exception $e) {
        $conn->rollback();
        error_log("Error updating order status: " . $e->getMessage());
        echo "error: " . $e->getMessage();
        exit();
    }
} else {
    echo "error: Invalid request";
    exit();
}
