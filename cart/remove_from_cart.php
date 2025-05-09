<?php
session_start();
include("../database/config.php");

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $cart_id = $_POST['cart_id'] ?? null;

    if (!$cart_id) {
        $_SESSION['error_message'] = "Invalid cart item.";
        header("Location: ../cart/cart.php");
        exit();
    }

    $stmt = $conn->prepare("DELETE FROM cart WHERE cart_id = ?");
    $stmt->bind_param("i", $cart_id);

    if ($stmt->execute()) {
        $_SESSION['success_message'] = "Item removed from cart.";
    } else {
        $_SESSION['error_message'] = "Failed to remove item.";
    }

    header("Location: ../cart/cart.php");
    exit();
}
?>
