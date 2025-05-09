<?php
session_start();
include('../database/config.php');

header("Content-Type: application/json");

$data = json_decode(file_get_contents("php://input"), true);
$message_id = $data['message_id'] ?? null;
$reaction = $data['reactions'] ?? "";

if (!$message_id || !$reaction) {
    echo json_encode(["success" => false, "error" => "Invalid input."]);
    exit;
}

// Update the message with reaction
$sql = "UPDATE messages SET reactions = ? WHERE message_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("si", $reaction, $message_id);

if ($stmt->execute()) {
    echo json_encode(["success" => true]);
} else {
    echo json_encode(["success" => false, "error" => "Failed to react."]);
}

$stmt->close();
$conn->close();
?>
