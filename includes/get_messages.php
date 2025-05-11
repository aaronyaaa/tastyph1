<?php
session_start();
include('database/config.php');
include("database/session.php");

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    exit('Unauthorized');
}

// Get the seller_id from the URL
if (!isset($_GET['seller_id'])) {
    exit('Invalid request');
}

$seller_id = intval($_GET['seller_id']);
$user_id = $_SESSION['user_id'];

// Fetch messages
$messages_sql = "SELECT m.*, 
                CASE 
                    WHEN m.is_seller = 0 THEN u.username 
                    ELSE s.business_name 
                END as sender_name
                FROM messages m 
                LEFT JOIN users u ON m.sender_id = u.user_id AND m.is_seller = 0
                LEFT JOIN apply_seller s ON m.sender_id = s.seller_id AND m.is_seller = 1
                WHERE (m.sender_id = ? AND m.receiver_id = ? AND m.is_seller = 0)
                   OR (m.sender_id = ? AND m.receiver_id = ? AND m.is_seller = 1)
                ORDER BY m.created_at ASC";
$stmt = $conn->prepare($messages_sql);
$stmt->bind_param("iiii", $user_id, $seller_id, $seller_id, $user_id);
$stmt->execute();
$messages_result = $stmt->get_result();

// Output messages
while ($message = $messages_result->fetch_assoc()): ?>
    <div class="message <?php echo ($message['sender_id'] == $user_id && $message['is_seller'] == 0) ? 'sent' : 'received'; ?>">
        <div class="message-content">
            <div class="message-text"><?php echo htmlspecialchars($message['message']); ?></div>
            <div class="message-time"><?php echo date('M d, Y h:i A', strtotime($message['created_at'])); ?></div>
        </div>
    </div>
<?php endwhile; ?>