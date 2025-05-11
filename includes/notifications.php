<?php
include("../database/session.php");
include("../database/config.php");
require_once("NotificationManager.php");

$notificationManager = new NotificationManager($conn);
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$notifications = $notificationManager->getNotifications($_SESSION['userId'], $page);
$unreadCount = $notificationManager->getUnreadCount($_SESSION['userId']);

// Handle AJAX requests
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json');
    
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'mark_read':
                if (isset($_POST['notification_id'])) {
                    $success = $notificationManager->markAsRead($_POST['notification_id'], $_SESSION['userId']);
                    echo json_encode(['success' => $success]);
                }
                break;
                
            case 'mark_all_read':
                $success = $notificationManager->markAllAsRead($_SESSION['userId']);
                echo json_encode(['success' => $success]);
                break;
        }
    }
    exit;
}

// Function to determine notification type and title
function getNotificationInfo($message) {
    $type = 'order';
    $title = 'New Notification';
    
    if (strpos($message, 'pre-order request') !== false) {
        $type = 'order';
        $title = 'New Pre-order Request';
    } elseif (strpos($message, 'message') !== false) {
        $type = 'message';
        $title = 'New Message';
    } else {
        $type = 'system';
        $title = 'System Notification';
    }
    
    return ['type' => $type, 'title' => $title];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Notifications</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="../css/nav.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .notification-item {
            border-left: 4px solid #0d6efd;
            margin-bottom: 1rem;
            padding: 1rem;
            background-color: #f8f9fa;
            border-radius: 0.25rem;
            transition: all 0.3s ease;
        }
        .notification-item:hover {
            background-color: #e9ecef;
            transform: translateX(5px);
        }
        .notification-item.unread {
            background-color: #e3f2fd;
            border-left-color: #2196f3;
        }
        .notification-time {
            font-size: 0.875rem;
            color: #6c757d;
        }
        .notification-sender {
            font-weight: bold;
            color: #0d6efd;
        }
        .notification-type {
            font-size: 0.75rem;
            padding: 0.25rem 0.5rem;
            border-radius: 0.25rem;
            margin-left: 0.5rem;
        }
        .type-order { background-color: #28a745; color: white; }
        .type-message { background-color: #17a2b8; color: white; }
        .type-system { background-color: #6c757d; color: white; }
        .notification-actions {
            display: flex;
            gap: 0.5rem;
            margin-top: 0.5rem;
        }
        .notification-badge {
            background-color: #dc3545;
            color: white;
            padding: 0.25rem 0.5rem;
            border-radius: 0.25rem;
            font-size: 0.75rem;
        }
        .pagination {
            margin-top: 2rem;
        }
    </style>
</head>
<body>
    <?php include("../includes/nav_" . strtolower($_SESSION['usertype']) . ".php"); ?>

    <div class="container mt-5">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>Notifications</h2>
            <?php if ($unreadCount > 0): ?>
                <button id="markAllRead" class="btn btn-primary">
                    <i class="fas fa-check-double"></i> Mark All as Read
                </button>
            <?php endif; ?>
        </div>
        
        <div id="notificationsList">
            <?php if ($notifications->num_rows > 0): ?>
                <?php while ($notification = $notifications->fetch_assoc()): 
                    // Get notification type and title based on message content
                    $notificationInfo = getNotificationInfo($notification['message']);
                    $type = $notification['type'] ?? $notificationInfo['type'];
                    $title = $notification['title'] ?? $notificationInfo['title'];
                ?>
                    <div class="notification-item <?= $notification['status'] ?>" 
                         data-notification-id="<?= $notification['notification_id'] ?>">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <div class="d-flex align-items-center">
                                    <span class="notification-sender">
                                        <?= htmlspecialchars($notification['first_name'] . ' ' . $notification['last_name']) ?>
                                    </span>
                                    <span class="notification-type type-<?= $type ?>">
                                        <?= ucfirst($type) ?>
                                    </span>
                                    <?php if ($notification['status'] === 'unread'): ?>
                                        <span class="notification-badge ms-2">New</span>
                                    <?php endif; ?>
                                </div>
                                <h5 class="mt-2"><?= htmlspecialchars($title) ?></h5>
                                <p class="mb-1"><?= htmlspecialchars($notification['message']) ?></p>
                                <span class="notification-time">
                                    <?= date('M d, Y h:i A', strtotime($notification['created_at'])) ?>
                                </span>
                            </div>
                            <?php if ($notification['status'] === 'unread'): ?>
                                <button class="btn btn-sm btn-outline-primary mark-read" 
                                        data-notification-id="<?= $notification['notification_id'] ?>">
                                    <i class="fas fa-check"></i> Mark as Read
                                </button>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div class="alert alert-info">
                    <i class="fas fa-info-circle"></i> No notifications yet.
                </div>
            <?php endif; ?>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        $(document).ready(function() {
            // Mark single notification as read
            $('.mark-read').click(function() {
                const button = $(this);
                const notificationId = button.data('notification-id');
                const notificationItem = button.closest('.notification-item');
                
                $.post('notifications.php', {
                    action: 'mark_read',
                    notification_id: notificationId
                }, function(response) {
                    if (response.success) {
                        notificationItem.removeClass('unread');
                        notificationItem.find('.notification-badge').remove();
                        button.remove();
                        updateNotificationCount();
                    }
                });
            });

            // Mark all notifications as read
            $('#markAllRead').click(function() {
                const button = $(this);
                $.post('notifications.php', {
                    action: 'mark_all_read'
                }, function(response) {
                    if (response.success) {
                        $('.notification-item.unread').removeClass('unread');
                        $('.notification-badge').remove();
                        $('.mark-read').remove();
                        button.remove();
                        updateNotificationCount();
                    }
                });
            });

            // Update notification count in navbar
            function updateNotificationCount() {
                $.get('get_unread_count.php', function(count) {
                    const badge = $('.notification-badge');
                    if (count > 0) {
                        badge.text(count).show();
                    } else {
                        badge.hide();
                    }
                });
            }
        });
    </script>
</body>
</html> 