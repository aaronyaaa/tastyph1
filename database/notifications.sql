-- Drop existing notifications table if it exists
DROP TABLE IF EXISTS notifications;

-- Create notifications table with improved structure
CREATE TABLE notifications (
    notification_id INT PRIMARY KEY AUTO_INCREMENT,
    sender_id INT NOT NULL,
    receiver_id INT NOT NULL,
    type ENUM('order', 'message', 'system') NOT NULL,
    title VARCHAR(255) NOT NULL,
    message TEXT NOT NULL,
    reference_id INT, -- For linking to orders, messages, etc.
    is_read BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (sender_id) REFERENCES users(id),
    FOREIGN KEY (receiver_id) REFERENCES users(id)
);

-- Create index for faster queries
CREATE INDEX idx_notifications_receiver ON notifications(receiver_id, is_read, created_at); 