<?php
session_start();
include("../database/config.php");

header("Content-Type: application/json");

if (!isset($_SESSION['userId'])) {
    echo json_encode(["success" => false, "message" => "Not logged in."]);
    exit;
}

$data = json_decode(file_get_contents("php://input"), true);
$cart_id = $data['cart_id'] ?? null;
$new_quantity = intval($data['quantity'] ?? 1);

if (!$cart_id || $new_quantity < 1) {
    echo json_encode(["success" => false, "message" => "Invalid input."]);
    exit;
}

// Get price to calculate new total
$priceQuery = "SELECT 
    COALESCE(v.price, p.price, i.price) AS price
 FROM cart c
 LEFT JOIN products p ON c.product_id = p.product_id
 LEFT JOIN ingredients i ON c.ingredient_id = i.ingredient_id
 LEFT JOIN ingredient_variants v ON c.variant_id = v.variant_id
 WHERE c.cart_id = ?";

$stmt = $conn->prepare($priceQuery);
$stmt->bind_param("i", $cart_id);
$stmt->execute();
$priceResult = $stmt->get_result();
$row = $priceResult->fetch_assoc();

if (!$row) {
    echo json_encode(["success" => false, "message" => "Item not found."]);
    exit;
}

$unit_price = floatval($row['price']);
$new_total = $unit_price * $new_quantity;

// Update quantity in cart
$updateQuery = "UPDATE cart SET quantity = ?, total_price = ? WHERE cart_id = ?";
$updateStmt = $conn->prepare($updateQuery);
$updateStmt->bind_param("idi", $new_quantity, $new_total, $cart_id);
$updateStmt->execute();

echo json_encode(["success" => true, "new_subtotal" => $new_total]);
