<?php
session_start();
include('../database/config.php');

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $sender_id = $_SESSION['userId'];
    $receiver_id = $_POST['receiver_id'] ?? '';
    $message = trim($_POST['message'] ?? '');
    $image_urls = []; // Store multiple images

    if (empty($receiver_id)) {
        die("Error: Receiver ID is missing.");
    }

    // Ensure uploads folder exists
    $targetDir = "../uploads/";
    if (!file_exists($targetDir)) {
        mkdir($targetDir, 0777, true);
    }

    // Process multiple image uploads
    if (!empty($_FILES['images']['name'][0])) {
        foreach ($_FILES['images']['name'] as $key => $imageName) {
            $fileName = time() . "_" . basename($imageName);
            $targetFilePath = $targetDir . $fileName;
            $fileType = strtolower(pathinfo($targetFilePath, PATHINFO_EXTENSION));
            $allowedTypes = ['jpg', 'png', 'jpeg', 'gif'];

            if (in_array($fileType, $allowedTypes)) {
                if (move_uploaded_file($_FILES["images"]["tmp_name"][$key], $targetFilePath)) {
                    $image_urls[] = "uploads/" . $fileName; // Save relative path
                } else {
                    die("Error: Failed to upload " . $imageName);
                }
            } else {
                die("Error: Invalid file type for " . $imageName);
            }
        }
    }

    // Convert image URLs to a comma-separated string
    $image_urls_string = !empty($image_urls) ? implode(",", $image_urls) : null;

    // Save message & images
    if (!empty($message) || !empty($image_urls_string)) {
        $sql = "INSERT INTO messages (sender_id, receiver_id, message_text, image_url, timestamp, is_read, reply_to, pinned, reactions) 
                VALUES (?, ?, ?, ?, NOW(), 0, NULL, 0, '')";
        $stmt = $conn->prepare($sql);
        if (!$stmt) {
            die("SQL Error: " . $conn->error); // Debugging: Check for SQL errors
        }
        $stmt->bind_param("iiss", $sender_id, $receiver_id, $message, $image_urls_string);
    
        if ($stmt->execute()) {
            error_log("Message Sent: Sender $sender_id -> Receiver $receiver_id | Text: $message | Images: $image_urls_string");
            header("Location: ../includes/chat.php?receiver_id=$receiver_id");
            exit;
        } else {
            die("Error: Could not send the message. " . $stmt->error);
        }
    }
    
}
?>
