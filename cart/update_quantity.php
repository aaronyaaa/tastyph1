<?php
session_start();
include("../database/config.php"); // Database connection

header("Content-Type: application/json");

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    echo json_encode(["success" => false, "message" => "Invalid request"]);
    exit;
}

// Ensure user is logged in
if (!isset($_SESSION['userId'])) {
    echo json_encode(["success" => false, "message" => "User not logged in"]);
    exit;
}

$userId = $_SESSION['userId'];
$data = json_decode(file_get_contents("php://input"), true);

$cartId = $data['cart_id'] ?? 0;
$newQuantity = $data['quantity'] ?? 1;

if ($cartId <= 0 || $newQuantity < 1) {
    echo json_encode(["success" => false, "message" => "Invalid cart item or quantity"]);
    exit;
}

// Fetch the product/ingredient price to recalculate total price
$sql = "SELECT product_id, ingredient_id FROM cart WHERE cart_id = ? AND user_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $cartId, $userId);
$stmt->execute();
$result = $stmt->get_result();
$cartItem = $result->fetch_assoc();

if (!$cartItem) {
    echo json_encode(["success" => false, "message" => "Cart item not found"]);
    exit;
}

$productId = $cartItem['product_id'] ?? null;
$ingredientId = $cartItem['ingredient_id'] ?? null;

// Determine item price (Product or Ingredient)
if ($productId) {
    $priceQuery = "SELECT price FROM products WHERE product_id = ?";
    $stmt = $conn->prepare($priceQuery);
    $stmt->bind_param("i", $productId);
} elseif ($ingredientId) {
    $priceQuery = "SELECT price FROM ingredients WHERE ingredient_id = ?";
    $stmt = $conn->prepare($priceQuery);
    $stmt->bind_param("i", $ingredientId);
} else {
    echo json_encode(["success" => false, "message" => "Item not found"]);
    exit;
}

$stmt->execute();
$priceResult = $stmt->get_result();
$item = $priceResult->fetch_assoc();

if (!$item) {
    echo json_encode(["success" => false, "message" => "Item price not found"]);
    exit;
}

$itemPrice = $item['price'];
$newTotalPrice = $itemPrice * $newQuantity;

// Update the cart with new quantity and total price
$updateQuery = "UPDATE cart SET quantity = ?, total_price = ? WHERE cart_id = ? AND user_id = ?";
$stmt = $conn->prepare($updateQuery);
$stmt->bind_param("idii", $newQuantity, $newTotalPrice, $cartId, $userId);

if ($stmt->execute()) {
    echo json_encode([
        "success" => true,
        "newSubtotal" => $newTotalPrice,
        "message" => "Cart updated successfully"
    ]);
} else {
    echo json_encode(["success" => false, "message" => "Failed to update cart"]);
}

exit;
