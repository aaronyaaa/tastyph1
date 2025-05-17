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

            case 'get_receipt':
                if (isset($_POST['receipt_id'])) {
                    $receiptId = (int)$_POST['receipt_id'];
                    
                    // Get receipt details
                    $sql = "SELECT r.*, ri.* 
                           FROM receipts r 
                           JOIN receipt_items ri ON r.receipt_id = ri.receipt_id 
                           WHERE r.receipt_id = ?";
                    $stmt = $conn->prepare($sql);
                    $stmt->bind_param("i", $receiptId);
                    $stmt->execute();
                    $result = $stmt->get_result();
                    
                    $receipt = null;
                    $items = [];
                    
                    while ($row = $result->fetch_assoc()) {
                        if (!$receipt) {
                            $receipt = [
                                'receipt_number' => $row['receipt_number'],
                                'issue_date' => $row['issue_date'],
                                'total_amount' => $row['total_amount'],
                                'payment_method' => $row['payment_method'],
                                'payment_status' => $row['payment_status'],
                                'buyer_name' => $row['buyer_name'],
                                'buyer_address' => $row['buyer_address'],
                                'buyer_contact' => $row['buyer_contact'],
                                'supplier_name' => $row['supplier_name'],
                                'supplier_address' => $row['supplier_address'],
                                'supplier_contact' => $row['supplier_contact'],
                                'supplier_tin' => $row['supplier_tin']
                            ];
                        }
                        $items[] = [
                            'item_name' => $row['item_name'],
                            'quantity' => $row['quantity'],
                            'unit_price' => $row['unit_price'],
                            'unit_type' => $row['unit_type'],
                            'quantity_value' => $row['quantity_value'],
                            'subtotal' => $row['subtotal']
                        ];
                    }
                    
                    if ($receipt) {
                        $receipt['items'] = $items;
                        echo json_encode(['success' => true, 'receipt' => $receipt]);
                    } else {
                        echo json_encode(['success' => false, 'message' => 'Receipt not found']);
                    }
                }
                break;
        }
    }
    exit;
}

// Function to determine notification type and title
function getNotificationInfo($message) {
    $type = 'order';
    $title = 'New Notification';
    $showReceiptButton = false;
    $receiptId = null;
    
    if (strpos($message, 'receipt') !== false) {
        $type = 'receipt';
        $title = 'Receipt Generated';
        $showReceiptButton = true;
        // Extract receipt number from message
        if (preg_match('/Receipt #([A-Z0-9-]+)/', $message, $matches)) {
            $receiptNumber = $matches[1];
            // Get receipt ID from database
            global $conn;
            $stmt = $conn->prepare("SELECT receipt_id FROM receipts WHERE receipt_number = ?");
            $stmt->bind_param("s", $receiptNumber);
            $stmt->execute();
            $result = $stmt->get_result();
            if ($row = $result->fetch_assoc()) {
                $receiptId = $row['receipt_id'];
            }
        }
    } elseif (strpos($message, 'pre-order request') !== false) {
        $type = 'order';
        $title = 'New Pre-order Request';
    } elseif (strpos($message, 'message') !== false) {
        $type = 'message';
        $title = 'New Message';
    } else {
        $type = 'system';
        $title = 'System Notification';
    }
    
    return [
        'type' => $type, 
        'title' => $title,
        'showReceiptButton' => $showReceiptButton,
        'receiptId' => $receiptId
    ];
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
        .type-receipt { background-color: #6f42c1; color: white; }
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
        /* Receipt Modal Styles */
        .receipt-modal .modal-content {
            border-radius: 0.5rem;
        }
        .receipt-header {
            border-bottom: 2px solid #dee2e6;
            padding-bottom: 1rem;
            margin-bottom: 1rem;
        }
        .receipt-items {
            margin: 1.5rem 0;
        }
        .receipt-items th {
            background-color: #f8f9fa;
        }
        .receipt-total {
            border-top: 2px solid #dee2e6;
            padding-top: 1rem;
            margin-top: 1rem;
            font-weight: bold;
        }
        .receipt-footer {
            margin-top: 2rem;
            padding-top: 1rem;
            border-top: 1px solid #dee2e6;
            font-size: 0.875rem;
        }
        @media print {
            .no-print {
                display: none !important;
            }
            .receipt-modal .modal-content {
                border: none;
                box-shadow: none;
            }
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
                            <div class="notification-actions">
                                <?php if ($notificationInfo['showReceiptButton'] && $notificationInfo['receiptId']): ?>
                                    <button class="btn btn-sm btn-primary view-receipt" 
                                            data-receipt-id="<?= $notificationInfo['receiptId'] ?>">
                                        <i class="fas fa-receipt"></i> View Receipt
                                    </button>
                                <?php endif; ?>
                                <?php if ($notification['status'] === 'unread'): ?>
                                    <button class="btn btn-sm btn-outline-primary mark-read" 
                                            data-notification-id="<?= $notification['notification_id'] ?>">
                                        <i class="fas fa-check"></i> Mark as Read
                                    </button>
                                <?php endif; ?>
                            </div>
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

    <!-- Receipt Modal -->
    <div class="modal fade receipt-modal" id="receiptModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Receipt Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="receipt-header">
                        <div class="row">
                            <div class="col-6">
                                <h4 class="supplier-name mb-2"></h4>
                                <p class="supplier-address mb-1"></p>
                                <p class="supplier-contact mb-1"></p>
                                <p class="supplier-tin mb-0"></p>
                            </div>
                            <div class="col-6 text-end">
                                <h5 class="receipt-number mb-2"></h5>
                                <p class="issue-date mb-1"></p>
                                <p class="payment-method mb-1"></p>
                                <p class="payment-status mb-0"></p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="buyer-details mb-4">
                        <h6>Bill To:</h6>
                        <p class="buyer-name mb-1"></p>
                        <p class="buyer-address mb-1"></p>
                        <p class="buyer-contact mb-0"></p>
                    </div>

                    <div class="receipt-items">
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>Item</th>
                                    <th class="text-center">Quantity</th>
                                    <th class="text-end">Unit Price</th>
                                    <th class="text-end">Subtotal</th>
                                </tr>
                            </thead>
                            <tbody class="items-list">
                            </tbody>
                            <tfoot>
                                <tr class="receipt-total">
                                    <td colspan="3" class="text-end">Total Amount:</td>
                                    <td class="text-end total-amount"></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>

                    <div class="receipt-footer">
                        <p class="text-center mb-0">Thank you for your business!</p>
                    </div>
                </div>
                <div class="modal-footer no-print">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-primary" onclick="window.print()">
                        <i class="fas fa-print"></i> Print Receipt
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        $(document).ready(function() {
            const receiptModal = new bootstrap.Modal(document.getElementById('receiptModal'));

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

            // View receipt
            $('.view-receipt').click(function() {
                const receiptId = $(this).data('receipt-id');
                
                $.post('notifications.php', {
                    action: 'get_receipt',
                    receipt_id: receiptId
                }, function(response) {
                    if (response.success) {
                        const receipt = response.receipt;
                        
                        // Update modal content
                        $('.supplier-name').text(receipt.supplier_name);
                        $('.supplier-address').text(receipt.supplier_address);
                        $('.supplier-contact').text(receipt.supplier_contact);
                        $('.supplier-tin').text('TIN: ' + receipt.supplier_tin);
                        
                        $('.receipt-number').text('Receipt #' + receipt.receipt_number);
                        $('.issue-date').text('Date: ' + new Date(receipt.issue_date).toLocaleDateString());
                        $('.payment-method').text('Payment Method: ' + receipt.payment_method);
                        $('.payment-status').text('Status: ' + receipt.payment_status);
                        
                        $('.buyer-name').text(receipt.buyer_name);
                        $('.buyer-address').text(receipt.buyer_address);
                        $('.buyer-contact').text(receipt.buyer_contact);
                        
                        // Clear and populate items
                        $('.items-list').empty();
                        receipt.items.forEach(item => {
                            $('.items-list').append(`
                                <tr>
                                    <td>${item.item_name}</td>
                                    <td class="text-center">${item.quantity} ${item.unit_type}</td>
                                    <td class="text-end">₱${parseFloat(item.unit_price).toFixed(2)}</td>
                                    <td class="text-end">₱${parseFloat(item.subtotal).toFixed(2)}</td>
                                </tr>
                            `);
                        });
                        
                        $('.total-amount').text('₱' + parseFloat(receipt.total_amount).toFixed(2));
                        
                        receiptModal.show();
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