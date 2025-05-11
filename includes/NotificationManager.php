<?php
class NotificationManager {
    private $conn;
    
    public function __construct($conn) {
        $this->conn = $conn;
    }
    
    // Create a new notification
    public function createNotification($senderId, $receiverId, $type, $title, $message, $referenceId = null) {
        $sql = "INSERT INTO notifications (sender_id, receiver_id, type, title, message, reference_id) 
                VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("iisssi", $senderId, $receiverId, $type, $title, $message, $referenceId);
        return $stmt->execute();
    }
    
    // Get unread notifications count for a user
    public function getUnreadCount($userId) {
        $sql = "SELECT COUNT(*) as count FROM notifications WHERE receiver_id = ? AND is_read = 0";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_assoc()['count'];
    }
    
    // Get notifications for a user with pagination
    public function getNotifications($userId, $page = 1, $limit = 10) {
        $offset = ($page - 1) * $limit;
        
        $sql = "SELECT n.*, u.first_name, u.last_name, 
                CASE WHEN n.is_read = 0 THEN 'unread' ELSE 'read' END as status
                FROM notifications n 
                JOIN users u ON n.sender_id = u.id 
                WHERE n.receiver_id = ? 
                ORDER BY n.created_at DESC 
                LIMIT ? OFFSET ?";
                
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("iii", $userId, $limit, $offset);
        $stmt->execute();
        return $stmt->get_result();
    }
    
    // Mark a notification as read
    public function markAsRead($notificationId, $userId) {
        $sql = "UPDATE notifications SET is_read = 1 
                WHERE notification_id = ? AND receiver_id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("ii", $notificationId, $userId);
        return $stmt->execute();
    }
    
    // Mark all notifications as read for a user
    public function markAllAsRead($userId) {
        $sql = "UPDATE notifications SET is_read = 1 
                WHERE receiver_id = ? AND is_read = 0";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $userId);
        return $stmt->execute();
    }
    
    // Delete old notifications (optional, can be used for cleanup)
    public function deleteOldNotifications($days = 30) {
        $sql = "DELETE FROM notifications 
                WHERE created_at < DATE_SUB(NOW(), INTERVAL ? DAY) 
                AND is_read = 1";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $days);
        return $stmt->execute();
    }
}
?> 