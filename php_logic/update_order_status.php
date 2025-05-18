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

$supplierId = $userId; // Assuming supplierId == userId

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'], $_POST['order_id'])) {
    $newStatus = $_POST['update_status'];
    $orderId = intval($_POST['order_id']);

    $conn->begin_transaction();

    try {
        // Verify order items belong to this supplier (ingredient or variant supplier)
        $verifySql = "
            SELECT 
                o.status, 
                o.user_id AS buyer_id, 
                oi.ingredient_id, 
                oi.variant_id, 
                oi.quantity, 
                i.ingredient_name, 
                i.quantity_value, 
                i.unit_type, 
                oi.total_price
            FROM orders o
            JOIN order_items oi ON o.order_id = oi.order_id
            JOIN ingredients i ON oi.ingredient_id = i.ingredient_id
            LEFT JOIN ingredient_variants v ON oi.variant_id = v.variant_id
            WHERE o.order_id = ? 
            AND (
                i.supplier_id = ? 
                OR (oi.variant_id IS NOT NULL AND v.supplier_id = ?)
            )
        ";

        $stmtVerify = $conn->prepare($verifySql);
        if (!$stmtVerify) throw new Exception("Prepare failed (verify): " . $conn->error);

        $stmtVerify->bind_param("iii", $orderId, $supplierId, $supplierId);
        if (!$stmtVerify->execute()) throw new Exception("Execute failed (verify): " . $stmtVerify->error);

        $verifyResult = $stmtVerify->get_result();
        $orderItems = $verifyResult->fetch_all(MYSQLI_ASSOC);
        $stmtVerify->close();

        if (empty($orderItems)) {
            throw new Exception("No items found for this order and supplier.");
        }

        // Current status and buyer ID from first row
        $currentStatus = $orderItems[0]['status'];
        $buyerId = $orderItems[0]['buyer_id'];

        if (in_array($currentStatus, ['Delivered', 'Cancelled'])) {
            throw new Exception("Order status cannot be updated.");
        }

        // Update order status
        $stmtUpdate = $conn->prepare("UPDATE orders SET status = ? WHERE order_id = ?");
        if (!$stmtUpdate) throw new Exception("Prepare update failed: " . $conn->error);

        $stmtUpdate->bind_param("si", $newStatus, $orderId);
        if (!$stmtUpdate->execute()) throw new Exception("Execute update failed: " . $stmtUpdate->error);
        $stmtUpdate->close();

        // If delivered, update inventory and create receipt
        if ($newStatus === 'Delivered') {
            foreach ($orderItems as $item) {
                $variantId = isset($item['variant_id']) && $item['variant_id'] !== null ? (int)$item['variant_id'] : null;

                // Deduct quantity from variants or ingredients
                if ($variantId !== null) {
                    $stmtUpdateVariant = $conn->prepare("
                        UPDATE ingredient_variants
                        SET quantity = quantity - ?
                        WHERE variant_id = ? AND ingredient_id = ?
                    ");
                    if (!$stmtUpdateVariant) throw new Exception("Prepare update variant failed: " . $conn->error);

                    $stmtUpdateVariant->bind_param("iii", $item['quantity'], $variantId, $item['ingredient_id']);
                    $stmtUpdateVariant->execute();
                    $stmtUpdateVariant->close();
                } else {
                    $stmtUpdateIngredient = $conn->prepare("
                        UPDATE ingredients
                        SET quantity = quantity - ?
                        WHERE ingredient_id = ?
                    ");
                    if (!$stmtUpdateIngredient) throw new Exception("Prepare update ingredient failed: " . $conn->error);

                    $stmtUpdateIngredient->bind_param("ii", $item['quantity'], $item['ingredient_id']);
                    $stmtUpdateIngredient->execute();
                    $stmtUpdateIngredient->close();
                }

                // Update supplier inventory
                $stmtCheckSupplierInv = $conn->prepare("
                    SELECT inventory_id, quantity 
                    FROM ingredients_inventory
                    WHERE ingredient_id = ? AND (variant_id = ? OR (variant_id IS NULL AND ? IS NULL)) AND supplier_id = ?
                ");
                if (!$stmtCheckSupplierInv) throw new Exception("Prepare failed (check supplier inv): " . $conn->error);

                $null = null;
                if ($variantId === null) {
                    $stmtCheckSupplierInv->bind_param("iiii", $item['ingredient_id'], $null, $null, $supplierId);
                } else {
                    $stmtCheckSupplierInv->bind_param("iiii", $item['ingredient_id'], $variantId, $variantId, $supplierId);
                }

                $stmtCheckSupplierInv->execute();
                $supplierInventory = $stmtCheckSupplierInv->get_result()->fetch_assoc();
                $stmtCheckSupplierInv->close();

                if ($supplierInventory) {
                    $newSupplierQuantity = max(0, $supplierInventory['quantity'] - $item['quantity']);

                    $stmtUpdateSupplier = $conn->prepare("
                        UPDATE ingredients_inventory 
                        SET quantity = ? 
                        WHERE inventory_id = ?
                    ");
                    if (!$stmtUpdateSupplier) throw new Exception("Prepare failed (update supplier inv): " . $conn->error);

                    $stmtUpdateSupplier->bind_param("ii", $newSupplierQuantity, $supplierInventory['inventory_id']);
                    $stmtUpdateSupplier->execute();
                    $stmtUpdateSupplier->close();
                } else {
                    // Insert new supplier inventory if not exist
                    $stmtInsertSupplier = $conn->prepare("
                        INSERT INTO ingredients_inventory 
                        (ingredient_id, ingredient_name, quantity, quantity_value, unit_type, supplier_id, variant_id, user_id)
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?)
                    ");
                    if (!$stmtInsertSupplier) throw new Exception("Prepare failed (insert supplier inv): " . $conn->error);

                    $variantIdInsert = $variantId !== null ? $variantId : null;

                    $stmtInsertSupplier->bind_param(
                        "isdisiii",
                        $item['ingredient_id'],
                        $item['ingredient_name'],
                        $item['quantity'],
                        $item['quantity_value'],
                        $item['unit_type'],
                        $supplierId,
                        $variantIdInsert,
                        $supplierId
                    );
                    $stmtInsertSupplier->execute();
                    $stmtInsertSupplier->close();
                }

                // Update buyer inventory
                $stmtCheckBuyerInv = $conn->prepare("
                    SELECT inventory_id, quantity 
                    FROM ingredients_inventory 
                    WHERE ingredient_id = ? 
                      AND (variant_id = ? OR (variant_id IS NULL AND ? IS NULL))
                      AND user_id = ? 
                      AND supplier_id = ?
                ");
                if (!$stmtCheckBuyerInv) throw new Exception("Prepare failed (check buyer inv): " . $conn->error);

                if ($variantId === null) {
                    $stmtCheckBuyerInv->bind_param("iiiii", $item['ingredient_id'], $null, $null, $buyerId, $supplierId);
                } else {
                    $stmtCheckBuyerInv->bind_param("iiiii", $item['ingredient_id'], $variantId, $variantId, $buyerId, $supplierId);
                }

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
                    if (!$stmtUpdateBuyer) throw new Exception("Prepare failed (update buyer inv): " . $conn->error);

                    $stmtUpdateBuyer->bind_param("ii", $newBuyerQuantity, $buyerInventory['inventory_id']);
                    $stmtUpdateBuyer->execute();
                    $stmtUpdateBuyer->close();
                } else {
                    $stmtInsertBuyer = $conn->prepare("
                        INSERT INTO ingredients_inventory 
                        (ingredient_id, ingredient_name, quantity, quantity_value, unit_type, supplier_id, variant_id, user_id)
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?)
                    ");
                    if (!$stmtInsertBuyer) throw new Exception("Prepare failed (insert buyer inv): " . $conn->error);

                    $stmtInsertBuyer->bind_param(
                        "isdisiii",
                        $item['ingredient_id'],
                        $item['ingredient_name'],
                        $item['quantity'],
                        $item['quantity_value'],
                        $item['unit_type'],
                        $supplierId,
                        $variantIdInsert,
                        $buyerId
                    );
                    $stmtInsertBuyer->execute();
                    $stmtInsertBuyer->close();
                }
            }

            // Fetch order and user info for receipt
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
            if (!$stmt) throw new Exception("Prepare failed (order): " . $conn->error);

            $stmt->bind_param("i", $orderId);
            $stmt->execute();
            $order = $stmt->get_result()->fetch_assoc();
            $stmt->close();

            $itemsSql = "
                SELECT oi.*, i.ingredient_name, v.variant_name, i.quantity_value, i.unit_type 
                FROM order_items oi
                JOIN ingredients i ON oi.ingredient_id = i.ingredient_id
                LEFT JOIN ingredient_variants v ON oi.variant_id = v.variant_id
                WHERE oi.order_id = ?
            ";
            $stmt = $conn->prepare($itemsSql);
            if (!$stmt) throw new Exception("Prepare failed (items for receipt): " . $conn->error);

            $stmt->bind_param("i", $orderId);
            $stmt->execute();
            $itemsForReceipt = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
            $stmt->close();

            // Insert receipt record
            $receiptSql = "INSERT INTO receipts (
                order_id, user_id, seller_id, supplier_id, payment_date, payment_method,
                subtotal, tax_rate, tax_amount, total_paid, amount_paid, authorized_by
            ) VALUES (?, ?, ?, ?, NOW(), ?, ?, ?, ?, ?, ?, ?)";

            $stmt = $conn->prepare($receiptSql);
            if (!$stmt) throw new Exception("Prepare failed (insert receipt): " . $conn->error);

            $user_id = $buyerId;
            $seller_id = null; // set accordingly if you have seller id info
            $payment_method = $order['payment_method'] ?? 'N/A';
            $subtotal = $order['total_price'] ?? 0;
            $tax_rate = 0;
            $tax_amount = 0;
            $total_paid = $order['total_price'] ?? 0;
            $amount_paid = $order['total_price'] ?? 0;
            $authorized_by = $supplierId;

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

            // Insert receipt items
            $itemSql = "INSERT INTO receipt_items (
                receipt_id, ingredient_id, variant_id, description,
                quantity, unit_price, total_price
            ) VALUES (?, ?, ?, ?, ?, ?, ?)";

            $stmt = $conn->prepare($itemSql);
            if (!$stmt) throw new Exception("Prepare failed (insert receipt items): " . $conn->error);

            foreach ($itemsForReceipt as $item) {
                $description = $item['variant_name'] 
                    ? $item['ingredient_name'] . ' - ' . $item['variant_name']
                    : $item['ingredient_name'];

                $unit_price = $item['unit_price'] ?? 0;
                $total_price = $item['total_price'] ?? 0;

                $variantId = isset($item['variant_id']) && $item['variant_id'] !== null ? (int)$item['variant_id'] : null;

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
