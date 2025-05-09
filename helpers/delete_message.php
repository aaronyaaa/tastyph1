<?php
session_start();
include('../database/config.php');

header("Content-Type: application/json");

$data = json_decode(file_get_contents("php://input"), true);
$message_id = $data['message_id'] ?? null;
$sender_id = $_SESSION['userId'];

if (!$message_id) {
    echo json_encode(["success" => false, "error" => "Message ID missing."]);
    exit;
}

// Ensure the sender owns the message before deleting
$sql = "DELETE FROM messages WHERE message_id = ? AND sender_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $message_id, $sender_id);

if ($stmt->execute()) {
    echo json_encode(["success" => true]);
} else {
    echo json_encode(["success" => false, "error" => "Failed to delete message."]);
}

$stmt->close();
$conn->close();
?>
