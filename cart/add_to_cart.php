<?php
session_start();
include("../database/config.php");

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    if (!isset($_SESSION['userId'])) {
        $_SESSION['error_message'] = "You must log in to add items to the cart.";
        header("Location: ../login.php");
        exit();
    }

    $userId = $_SESSION['userId'];
    $item_id = $_POST['item_id'] ?? null;
    $item_type = $_POST['item_type'] ?? null;
    $quantity = intval($_POST['quantity'] ?? 1);
    $price = floatval($_POST['price'] ?? 0);
    $variant_id = $_POST['variant_id'] ?? null;

    if (!$item_id || !$item_type || $quantity < 1 || $price <= 0) {
        $_SESSION['error_message'] = "Invalid item data.";
        header("Location: ../cart/cart.php");
        exit();
    }

    $total_price = $quantity * $price;

    // ✅ If a variant is selected
    if ($item_type === "ingredient" && !empty($variant_id)) {
        $query = "SELECT cart_id, quantity FROM cart WHERE user_id = ? AND variant_id = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("ii", $userId, $variant_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $existingItem = $result->fetch_assoc();

        if ($existingItem) {
            $newQuantity = $existingItem['quantity'] + $quantity;
            $newTotalPrice = $newQuantity * $price;

            $updateQuery = "UPDATE cart SET quantity = ?, total_price = ? WHERE cart_id = ?";
            $updateStmt = $conn->prepare($updateQuery);
            $updateStmt->bind_param("idi", $newQuantity, $newTotalPrice, $existingItem['cart_id']);
            $updateStmt->execute();
        } else {
            $insertQuery = "INSERT INTO cart (user_id, variant_id, quantity, total_price) VALUES (?, ?, ?, ?)";
            $insertStmt = $conn->prepare($insertQuery);
            $insertStmt->bind_param("iiid", $userId, $variant_id, $quantity, $total_price);
            $insertStmt->execute();
        }

    } else {
        // ✅ Original logic (product or ingredient without variant)
        if ($item_type === "product") {
            $query = "SELECT cart_id, quantity FROM cart WHERE user_id = ? AND product_id = ?";
        } else {
            $query = "SELECT cart_id, quantity FROM cart WHERE user_id = ? AND ingredient_id = ?";
        }

        $stmt = $conn->prepare($query);
        $stmt->bind_param("ii", $userId, $item_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $existingItem = $result->fetch_assoc();

        if ($existingItem) {
            $newQuantity = $existingItem['quantity'] + $quantity;
            $newTotalPrice = $newQuantity * $price;

            $updateQuery = "UPDATE cart SET quantity = ?, total_price = ? WHERE cart_id = ?";
            $updateStmt = $conn->prepare($updateQuery);
            $updateStmt->bind_param("idi", $newQuantity, $newTotalPrice, $existingItem['cart_id']);
            $updateStmt->execute();
        } else {
            if ($item_type === "product") {
                $insertQuery = "INSERT INTO cart (user_id, product_id, quantity, total_price) VALUES (?, ?, ?, ?)";
            } else {
                $insertQuery = "INSERT INTO cart (user_id, ingredient_id, quantity, total_price) VALUES (?, ?, ?, ?)";
            }

            $insertStmt = $conn->prepare($insertQuery);
            $insertStmt->bind_param("iiid", $userId, $item_id, $quantity, $total_price);
            $insertStmt->execute();
        }
    }

    $_SESSION['success_message'] = "Item added to cart!";
    header("Location: ../cart/cart.php");
    exit();
}
