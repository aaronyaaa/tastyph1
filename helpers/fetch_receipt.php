<?php
session_start();
include("../database/config.php");
include("../database/data_session.php");

if (!isset($_GET['order_id'])) {
    echo json_encode(['success' => false, 'message' => 'Order ID is required']);
    exit();
}

$orderId = intval($_GET['order_id']);

// Log the order ID being passed
error_log("Order ID received: " . $orderId);

// Retrieve the receipt based on order_id
$receiptSql = "
    SELECT r.receipt_id, r.order_id, r.user_id AS buyer_id, r.supplier_id, r.payment_date, 
           r.payment_method, r.subtotal, r.tax_rate, r.tax_amount, r.total_paid, r.amount_paid, 
           r.authorized_by, r.created_at, 
           u.first_name AS buyer_first_name, u.last_name AS buyer_last_name, 
           u.contact_number AS buyer_contact, 
           CONCAT_WS(', ', u.streetname, u.barangay, u.city, u.province, u.country) AS buyer_address, 
           sup.business_name AS supplier_name, 
           sup.address AS supplier_address, sup.contact_number AS supplier_contact, sup.tin_number AS supplier_tin
    FROM receipts r
    JOIN users u ON r.user_id = u.id
    LEFT JOIN apply_supplier sup ON r.supplier_id = sup.supplier_id
    WHERE r.order_id = ?
";

$stmt = $conn->prepare($receiptSql);
if (!$stmt) {
    error_log("Prepare failed (receipt query): " . $conn->error);
    echo json_encode(['success' => false, 'message' => 'Error preparing SQL query: ' . $conn->error]);
    exit();
}

$stmt->bind_param("i", $orderId);
$stmt->execute();
$receiptResult = $stmt->get_result();
$receipt = $receiptResult->fetch_assoc();
$stmt->close();

if (!$receipt) {
    error_log("No receipt found for Order ID: " . $orderId);
    echo json_encode(['success' => false, 'message' => 'Receipt not found for this order.']);
    exit();
}

// Retrieve receipt items
$itemsSql = "
    SELECT ri.receipt_item_id, ri.product_id, ri.ingredient_id, ri.variant_id, 
           IF(ri.variant_id IS NOT NULL, CONCAT(i.ingredient_name, ' - ', v.variant_name), i.ingredient_name) AS description,
           ri.quantity, ri.unit_price, ri.total_price, i.unit_type
    FROM receipt_items ri
    LEFT JOIN ingredients i ON ri.ingredient_id = i.ingredient_id
    LEFT JOIN ingredient_variants v ON ri.variant_id = v.variant_id
    WHERE ri.receipt_id = ?
";

$stmt = $conn->prepare($itemsSql);
if (!$stmt) {
    error_log("Prepare failed (receipt items query): " . $conn->error);
    echo json_encode(['success' => false, 'message' => 'Error preparing SQL query for items: ' . $conn->error]);
    exit();
}

$stmt->bind_param("i", $receipt['receipt_id']);
$stmt->execute();
$itemsResult = $stmt->get_result();
$items = $itemsResult->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// Log the items retrieved
error_log("Items retrieved for receipt ID: " . $receipt['receipt_id'] . " - " . count($items) . " items found.");

// Prepare the response
$response = [
    'success' => true,
    'receipt' => [
        'receipt_id' => $receipt['receipt_id'],
        'payment_date' => $receipt['payment_date'],
        'payment_method' => $receipt['payment_method'],
        'subtotal' => $receipt['subtotal'],
        'tax_rate' => $receipt['tax_rate'],
        'tax_amount' => $receipt['tax_amount'],
        'total_paid' => $receipt['total_paid'],
        'amount_paid' => $receipt['amount_paid'],
        'buyer_name' => $receipt['buyer_first_name'] . ' ' . $receipt['buyer_last_name'],
        'buyer_contact' => $receipt['buyer_contact'],
        'buyer_address' => $receipt['buyer_address'],
        'supplier_name' => $receipt['supplier_name'],
        'supplier_address' => $receipt['supplier_address'],
        'supplier_contact' => $receipt['supplier_contact'],
        'supplier_tin' => $receipt['supplier_tin'],
    ],
    'items' => $items
];

echo json_encode($response);
exit();
?>
