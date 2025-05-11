<?php
include("../database/session.php");
include("../database/config.php");

// Get unread notifications count
$sql = "SELECT COUNT(*) as count FROM notifications WHERE receiver_id = ? AND is_read = 0";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $_SESSION['userId']);
$stmt->execute();
$result = $stmt->get_result();
$count = $result->fetch_assoc()['count'];

// Return the count as JSON
header('Content-Type: application/json');
echo json_encode($count);
?> 