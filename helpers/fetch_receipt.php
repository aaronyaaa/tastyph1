<?php
session_start();
include("../database/config.php");
include("../database/data_session.php");

if (!isset($_GET['order_id']) || !is_numeric($_GET['order_id'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid order id']);
    exit;
}

$orderId = intval($_GET['order_id']);
$userId = $_SESSION['userId'] ?? null;
$userType = $_SESSION['usertype'] ?? null;

if (!$userId) {
    echo json_encode(['success' => false, 'message' => 'Not logged in']);
    exit;
}

// Optional: Verify if the user is buyer or supplier for this order here, for security

// Fetch receipt info for this order and user
$sql = "SELECT * FROM receipts WHERE order_id = ? LIMIT 1";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $orderId);
$stmt->execute();
$receipt = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$receipt) {
    echo json_encode(['success' => false, 'message' => 'Receipt not found']);
    exit;
}

// Fetch receipt items
$sqlItems = "SELECT item_name, quantity, unit_price, unit_type, subtotal FROM receipt_items WHERE receipt_id = ?";
$stmtItems = $conn->prepare($sqlItems);
$stmtItems->bind_param("i", $receipt['receipt_id']);
$stmtItems->execute();
$items = $stmtItems->get_result()->fetch_all(MYSQLI_ASSOC);
$stmtItems->close();

echo json_encode([
    'success' => true,
    'receipt' => $receipt,
    'items' => $items
]);
