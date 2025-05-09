<?php
session_start();
include("../database/config.php");

// Ensure user is logged in and request is valid
if (!isset($_SESSION['userId']) || !isset($_POST['order_id']) || !isset($_POST['order_status'])) {
    die("Unauthorized access.");
}

$orderId = $_POST['order_id'];
$newStatus = $_POST['order_status'];

// Validate status
$validStatuses = ['pending', 'confirmed', 'packed', 'delivered'];
if (!in_array($newStatus, $validStatuses)) {
    die("Invalid status.");
}

// Update the order status
$stmt = $conn->prepare("UPDATE orders SET status = ? WHERE order_id = ?");
$stmt->bind_param("si", $newStatus, $orderId);

if ($stmt->execute()) {
    echo "Success";
} else {
    echo "Error updating status.";
}
?>
