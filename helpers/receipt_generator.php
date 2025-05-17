<?php
require_once("../database/config.php");

class ReceiptGenerator {
    private $conn;
    private $notificationManager;

    public function __construct($conn) {
        $this->conn = $conn;
        require_once("../includes/NotificationManager.php");
        $this->notificationManager = new NotificationManager($conn);
    }

    public function generateReceiptNumber() {
        $year = date('Y');
        $month = date('m');
        
        // Get the last receipt number for current month
        $sql = "SELECT receipt_number FROM receipts 
                WHERE receipt_number LIKE ? 
                ORDER BY receipt_id DESC LIMIT 1";
        
        $stmt = $this->conn->prepare($sql);
        $prefix = "RCPT-{$year}{$month}-";
        $stmt->bind_param("s", $prefix . "%");
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $lastNumber = $result->fetch_assoc()['receipt_number'];
            $sequence = intval(substr($lastNumber, -4)) + 1;
        } else {
            $sequence = 1;
        }
        
        return $prefix . str_pad($sequence, 4, '0', STR_PAD_LEFT);
    }

    public function createReceipt($orderId, $supplierId, $buyerId) {
        try {
            $this->conn->begin_transaction();

            // Get order details
            $orderSql = "SELECT o.*, u.first_name, u.last_name, u.email, u.phone, u.address 
                        FROM orders o 
                        JOIN users u ON o.user_id = u.id 
                        WHERE o.order_id = ?";
            $stmt = $this->conn->prepare($orderSql);
            if (!$stmt) {
                throw new Exception("Prepare failed: " . $this->conn->error);
            }
            $stmt->bind_param("i", $orderId);
            $stmt->execute();
            $order = $stmt->get_result()->fetch_assoc();

            // Get supplier details
            $supplierSql = "SELECT * FROM apply_supplier WHERE supplier_id = ?";
            $stmt = $this->conn->prepare($supplierSql);
            $stmt->bind_param("i", $supplierId);
            $stmt->execute();
            $supplier = $stmt->get_result()->fetch_assoc();

            // Get order items
            $itemsSql = "SELECT oi.*, i.ingredient_name, v.variant_name, i.quantity_value, i.unit_type 
                        FROM order_items oi 
                        JOIN ingredients i ON oi.ingredient_id = i.ingredient_id 
                        LEFT JOIN ingredient_variants v ON oi.variant_id = v.variant_id 
                        WHERE oi.order_id = ?";
            $stmt = $this->conn->prepare($itemsSql);
            $stmt->bind_param("i", $orderId);
            $stmt->execute();
            $items = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

            // Generate receipt number
            $receiptNumber = $this->generateReceiptNumber();

            // Insert receipt
            $receiptSql = "INSERT INTO receipts (
                order_id, supplier_id, buyer_id, receipt_number, issue_date,
                total_amount, payment_method, payment_status,
                buyer_name, buyer_address, buyer_contact,
                supplier_name, supplier_address, supplier_contact, supplier_tin
            ) VALUES (?, ?, ?, ?, NOW(), ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

            $buyerName = $order['first_name'] . ' ' . $order['last_name'];
            $stmt = $this->conn->prepare($receiptSql);
            $stmt->bind_param(
                "iiisssssssssss",
                $orderId,
                $supplierId,
                $buyerId,
                $receiptNumber,
                $order['total_amount'],
                $order['payment_method'],
                $order['payment_status'],
                $buyerName,
                $order['address'],
                $order['phone'],
                $supplier['business_name'],
                $supplier['address'],
                $supplier['contact_number'],
                $supplier['tin_number']
            );
            $stmt->execute();
            $receiptId = $this->conn->insert_id;

            // Insert receipt items
            $itemSql = "INSERT INTO receipt_items (
                receipt_id, ingredient_id, variant_id, item_name,
                quantity, unit_price, unit_type, quantity_value, subtotal
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";

            $stmt = $this->conn->prepare($itemSql);
            foreach ($items as $item) {
                $itemName = $item['variant_name'] 
                    ? $item['ingredient_name'] . ' - ' . $item['variant_name']
                    : $item['ingredient_name'];
                
                $stmt->bind_param(
                    "iiisidisd",
                    $receiptId,
                    $item['ingredient_id'],
                    $item['variant_id'],
                    $itemName,
                    $item['quantity'],
                    $item['unit_price'],
                    $item['unit_type'],
                    $item['quantity_value'],
                    $item['total_price']
                );
                $stmt->execute();
            }

            // Create notifications for both buyer and supplier
            $buyerMessage = "Your order #{$orderId} has been delivered. Receipt #{$receiptNumber} has been generated.";
            $supplierMessage = "Order #{$orderId} has been delivered. Receipt #{$receiptNumber} has been generated.";

            $this->notificationManager->createNotification(
                $buyerId,
                'order',
                $buyerMessage,
                "Receipt generated for Order #{$orderId}"
            );

            $this->notificationManager->createNotification(
                $supplierId,
                'order',
                $supplierMessage,
                "Receipt generated for Order #{$orderId}"
            );

            $this->conn->commit();
            return [
                'success' => true,
                'receipt_id' => $receiptId,
                'receipt_number' => $receiptNumber
            ];

        } catch (Exception $e) {
            $this->conn->rollback();
            error_log("Error generating receipt: " . $e->getMessage());
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
}
?> 