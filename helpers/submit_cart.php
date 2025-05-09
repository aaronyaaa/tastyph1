<?php
session_start();
include("../database/config.php");

if (!isset($_SESSION['userId'])) {
    die("Session expired or not set. Please log in again.");
}

$userId = $_SESSION['userId'];
$selectedItems = $_POST['selected_items'] ?? '';
$paymentMethod = $_POST['payment_method'] ?? '';
$cashAmount = floatval($_POST['cash_amount'] ?? 0);
$gcashReceipt = $_FILES['gcash_receipt'] ?? null;

if (empty($selectedItems)) {
    die("No items selected.");
}

$itemIds = array_map('intval', explode(",", $selectedItems));
if (empty($itemIds)) {
    die("Invalid item selection.");
}

$totalPrice = 0.0;
$conn->begin_transaction();

// Prepare cart query
$placeholders = implode(",", array_fill(0, count($itemIds), "?"));
$sql = "SELECT cart_id, total_price, product_id, ingredient_id, variant_id, quantity 
        FROM cart WHERE cart_id IN ($placeholders) AND user_id = ?";
$stmt = $conn->prepare($sql);
$types = str_repeat("i", count($itemIds)) . "i";
$params = array_merge($itemIds, [$userId]);
$stmt->bind_param($types, ...$params);
$stmt->execute();
$result = $stmt->get_result();

$cartData = [];
while ($row = $result->fetch_assoc()) {
    $totalPrice += floatval($row['total_price']);
    $cartData[$row['cart_id']] = $row;
}

// GCash upload (if selected)
$paymentProof = null;
if ($paymentMethod === 'gcash') {
    if (!$gcashReceipt || $gcashReceipt['error'] !== UPLOAD_ERR_OK) {
        $conn->rollback();
        die("GCash receipt upload error.");
    }

    $targetDir = "../uploads/";
    $fileName = time() . "_" . basename($gcashReceipt["name"]);
    $targetFilePath = $targetDir . $fileName;

    if (!move_uploaded_file($gcashReceipt["tmp_name"], $targetFilePath)) {
        $conn->rollback();
        die("Failed uploading GCash receipt.");
    }

    $paymentProof = $fileName;
}

// Insert order
$stmt = $conn->prepare("INSERT INTO orders (user_id, payment_method, payment_proof, total_price, status) VALUES (?, ?, ?, ?, 'Pending')");
$stmt->bind_param("issd", $userId, $paymentMethod, $paymentProof, $totalPrice);
if (!$stmt->execute()) {
    $conn->rollback();
    die("Order insert error: " . $stmt->error);
}
$orderId = $stmt->insert_id;

// Insert order_items (including variants)
foreach ($itemIds as $cartId) {
    $item = $cartData[$cartId];
    $productId = $item['product_id'] ?? null;
    $ingredientId = $item['ingredient_id'] ?? null;
    $variantId = $item['variant_id'] ?? null;
    $quantity = intval($item['quantity']);
    $itemTotalPrice = floatval($item['total_price']);

    // Insert into order_items table
    $stmtInsert = $conn->prepare("INSERT INTO order_items (order_id, product_id, ingredient_id, variant_id, quantity, total_price)
                                  VALUES (?, ?, ?, ?, ?, ?)");
    $stmtInsert->bind_param("iiiiid", $orderId, $productId, $ingredientId, $variantId, $quantity, $itemTotalPrice);

    if (!$stmtInsert->execute()) {
        $conn->rollback();
        die("Insert error in order_items: " . $stmtInsert->error);
    }

    // Deduct stock for product/ingredient/variant
    if ($productId) {
        $update = $conn->prepare("UPDATE products SET quantity = quantity - ? WHERE product_id = ?");
        $update->bind_param("ii", $quantity, $productId);
        $update->execute();
    }

    if ($variantId) {
        $update = $conn->prepare("UPDATE ingredient_variants SET quantity = quantity - ? WHERE variant_id = ?");
        $update->bind_param("ii", $quantity, $variantId);
        $update->execute();
    } elseif ($ingredientId) {
        $update = $conn->prepare("UPDATE ingredients SET quantity = quantity - ? WHERE ingredient_id = ?");
        $update->bind_param("ii", $quantity, $ingredientId);
        $update->execute();
    }
}

// Delete cart items
$deletePlaceholders = implode(",", array_fill(0, count($itemIds), "?"));
$typesDelete = str_repeat("i", count($itemIds)) . "i";
$paramsDelete = array_merge($itemIds, [$userId]);

$stmtDelete = $conn->prepare("DELETE FROM cart WHERE cart_id IN ($deletePlaceholders) AND user_id = ?");
$stmtDelete->bind_param($typesDelete, ...$paramsDelete);
if (!$stmtDelete->execute()) {
    $conn->rollback();
    die("Failed to delete from cart: " . $stmtDelete->error);
}

// Commit the transaction
$conn->commit();
header("Location: ../cart/order_confirmation.php?order_id=" . $orderId);
exit();
?>
