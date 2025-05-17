<?php
session_start();
include("../database/config.php");
include("../database/data_session.php");

if (!isset($_SESSION['userId']) || !isset($_POST['order_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized or invalid request']);
    exit;
}

$orderId = intval($_POST['order_id']);
$supplierId = $_SESSION['userId']; // Supplier updating status (must be supplier)
$userType = $_SESSION['usertype'] ?? '';

if ($userType !== 'supplier') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized user']);
    exit;
}

// Generate unique receipt number
function generateReceiptNumber($conn) {
    $year = date('Y');
    $month = date('m');
    $prefix = "RCPT-{$year}{$month}-";

    $sql = "SELECT receipt_number FROM receipts WHERE receipt_number LIKE ? ORDER BY receipt_id DESC LIMIT 1";
    $stmt = $conn->prepare($sql);
    $likeParam = $prefix . '%';
    $stmt->bind_param("s", $likeParam);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $lastReceipt = $result->fetch_assoc()['receipt_number'];
        $lastSeq = intval(substr($lastReceipt, -4));
        $newSeq = $lastSeq + 1;
    } else {
        $newSeq = 1;
    }

    return $prefix . str_pad($newSeq, 4, '0', STR_PAD_LEFT);
}

$conn->begin_transaction();

try {
    // Fetch order & buyer details
    $orderSql = "SELECT o.*, u.first_name, u.last_name, u.email, u.phone, u.address 
                 FROM orders o JOIN users u ON o.user_id = u.id WHERE o.order_id = ?";
    $stmt = $conn->prepare($orderSql);
    $stmt->bind_param("i", $orderId);
    $stmt->execute();
    $order = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if (!$order) throw new Exception("Order not found");

    $buyerId = $order['user_id'];
    $buyerName = $order['first_name'] . ' ' . $order['last_name'];

    // Fetch supplier details
    $supplierSql = "SELECT * FROM apply_supplier WHERE supplier_id = ?";
    $stmt = $conn->prepare($supplierSql);
    $stmt->bind_param("i", $supplierId);
    $stmt->execute();
    $supplier = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if (!$supplier) throw new Exception("Supplier not found");

    // Fetch order items
    $itemsSql = "SELECT oi.*, i.ingredient_name, v.variant_name, i.quantity_value, i.unit_type 
                 FROM order_items oi
                 JOIN ingredients i ON oi.ingredient_id = i.ingredient_id
                 LEFT JOIN ingredient_variants v ON oi.variant_id = v.variant_id
                 WHERE oi.order_id = ?";
    $stmt = $conn->prepare($itemsSql);
    $stmt->bind_param("i", $orderId);
    $stmt->execute();
    $items = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();

    if (empty($items)) throw new Exception("No items in order");

    // Generate receipt number
    $receiptNumber = generateReceiptNumber($conn);

    // Insert receipt
    $receiptSql = "INSERT INTO receipts (
        order_id, supplier_id, buyer_id, receipt_number, issue_date,
        total_amount, payment_method, payment_status,
        buyer_name, buyer_address, buyer_contact,
        supplier_name, supplier_address, supplier_contact, supplier_tin
    ) VALUES (?, ?, ?, ?, NOW(), ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

    $stmt = $conn->prepare($receiptSql);
    $stmt->bind_param(
        "iiissssssssssss",
        $orderId,
        $supplierId,
        $buyerId,
        $receiptNumber,
        $order['total_amount'],
        $order['payment_method'],
        $order['payment_status'],
        $buyerName,
        $order['address'],
        $order['phone'],
        $supplier['business_name'],
        $supplier['address'],
        $supplier['contact_number'],
        $supplier['tin_number']
    );
    $stmt->execute();
    $receiptId = $conn->insert_id;
    $stmt->close();

    // Insert receipt items
    $itemSql = "INSERT INTO receipt_items (
        receipt_id, ingredient_id, variant_id, item_name,
        quantity, unit_price, unit_type, quantity_value, subtotal
    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($itemSql);

    foreach ($items as $item) {
        $itemName = $item['variant_name'] ? $item['ingredient_name'] . ' - ' . $item['variant_name'] : $item['ingredient_name'];

        $stmt->bind_param(
            "iiisidisd",
            $receiptId,
            $item['ingredient_id'],
            $item['variant_id'] ?? null,
            $itemName,
            $item['quantity'],
            $item['unit_price'] ?? 0,
            $item['unit_type'],
            $item['quantity_value'],
            $item['total_price'] ?? 0
        );
        $stmt->execute();
    }
    $stmt->close();

    $conn->commit();

    echo json_encode(['success' => true, 'receipt_number' => $receiptNumber, 'receipt_id' => $receiptId]);
} catch (Exception $e) {
    $conn->rollback();
    error_log("Error generating receipt: " . $e->getMessage());
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
?>
