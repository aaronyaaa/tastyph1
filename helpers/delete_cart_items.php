<?php
session_start();
include("../database/config.php");

header("Content-Type: application/json");

if (!isset($_SESSION['userId'])) {
    echo json_encode(["success" => false, "message" => "Unauthorized"]);
    exit;
}

$data = json_decode(file_get_contents("php://input"), true);
$cartIds = $data['cart_ids'] ?? [];

if (empty($cartIds)) {
    echo json_encode(["success" => false, "message" => "No items selected"]);
    exit;
}

$placeholders = implode(',', array_fill(0, count($cartIds), '?'));
$types = str_repeat('i', count($cartIds));

$sql = "DELETE FROM cart WHERE cart_id IN ($placeholders)";
$stmt = $conn->prepare($sql);

if ($stmt) {
    $stmt->bind_param($types, ...$cartIds);
    $stmt->execute();
    echo json_encode(["success" => true]);
} else {
    echo json_encode(["success" => false, "message" => "Failed to prepare delete statement"]);
}
