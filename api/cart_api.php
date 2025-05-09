<?php
session_start();
header("Content-Type: application/json");
include("../database/config.php"); // Ensure correct DB connection

$method = $_SERVER['REQUEST_METHOD'];

if ($method === "POST") {
    $userId = $_SESSION['userId'] ?? null;
    $productId = $_POST['product_id'] ?? 0;
    $ingredientId = $_POST['ingredient_id'] ?? 0;
    $quantity = $_POST['quantity'] ?? 1;

    if (!$userId) {
        echo json_encode(["status" => "error", "message" => "User not logged in"]);
        exit;
    }

    if ($productId > 0 || $ingredientId > 0) {
        $column = $productId > 0 ? "product_id" : "ingredient_id";
        $itemId = $productId > 0 ? $productId : $ingredientId;

        // Check if item already exists in cart
        $checkQuery = "SELECT cart_id FROM cart WHERE user_id = ? AND $column = ?";
        $stmt = $conn->prepare($checkQuery);
        $stmt->bind_param("ii", $userId, $itemId);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            // Update existing quantity
            $updateQuery = "UPDATE cart SET quantity = quantity + ? WHERE user_id = ? AND $column = ?";
            $stmt = $conn->prepare($updateQuery);
            $stmt->bind_param("iii", $quantity, $userId, $itemId);
        } else {
            // Insert new cart entry
            $insertQuery = "INSERT INTO cart (user_id, $column, quantity) VALUES (?, ?, ?)";
            $stmt = $conn->prepare($insertQuery);
            $stmt->bind_param("iii", $userId, $itemId, $quantity);
        }

        if ($stmt->execute()) {
            echo json_encode(["status" => "success", "message" => "Added to cart"]);
        } else {
            echo json_encode(["status" => "error", "message" => "Failed to add to cart"]);
        }
        exit;
    }
}

// Handle fetching cart count from the database
if (isset($_GET['getCartCount'])) {
    $userId = $_SESSION['userId'] ?? null;

    if (!$userId) {
        echo json_encode(["count" => 0]); // User not logged in, return 0
        exit;
    }

    // Fetch the count of unique items in the user's cart from the database
    $sql = "SELECT COUNT(*) AS total_items FROM cart WHERE user_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    $cartData = $result->fetch_assoc();
    $cartCount = $cartData['total_items'] ?? 0;

    echo json_encode(["count" => $cartCount]);
    exit;
}

echo json_encode(["status" => "error", "message" => "Invalid request"]);
exit;
?>
