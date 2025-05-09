<?php
session_start();
include('../database/config.php');


// Check if user is logged in
if (!isset($_SESSION['userId'])) {
    die("Error: User not logged in.");
}

// Retrieve form data
$userId = $_SESSION['userId']; // Buyer (sender)
$seller_id = intval($_POST['seller_id']); // Seller (receiver)
$product_name = isset($_POST['product_name']) ? htmlspecialchars(trim($_POST['product_name'])) : '';
$quantity = isset($_POST['quantity']) ? intval($_POST['quantity']) : 1;
$note = isset($_POST['note']) ? htmlspecialchars($_POST['note']) : '';

// Validate input
if (empty($product_name)) {
    die("Error: Product name is required.");
}

// Insert request into the database
$sql = "INSERT INTO requests (user_id, product_name, seller_id, quantity, additional_notes, status) 
        VALUES (?, ?, ?, ?, ?, 'pending')";
$stmt = $conn->prepare($sql);
if (!$stmt) {
    die("SQL Error: " . $conn->error);
}
$stmt->bind_param("issis", $userId, $product_name, $seller_id, $quantity, $note);
$stmt->execute();

// Create a notification for the seller
$notification_message = "New pre-order request for: $product_name (Qty: $quantity).";
$notification_sql = "INSERT INTO notifications (sender_id, receiver_id, message) VALUES (?, ?, ?)";
$stmt_notification = $conn->prepare($notification_sql);
if (!$stmt_notification) {
    die("SQL Error (Notification): " . $conn->error);
}
$stmt_notification->bind_param("iis", $userId, $seller_id, $notification_message);
$stmt_notification->execute();

header("Location: ../includes/view_store.php?seller_id=$seller_id&status=success");
exit();
?>
