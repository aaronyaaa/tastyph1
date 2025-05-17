<?php
session_start();
include("../database/config.php");
include("../database/data_session.php");

// Ensure user is logged in as supplier
$userId = $_SESSION['userId'] ?? null;
$userType = $_SESSION['usertype'] ?? null;

if (!$userId || $userType !== 'supplier') {
    die("Unauthorized access. Please log in as a supplier.");
}

// Get supplier ID from ingredients table
$stmtSupplier = $conn->prepare("
    SELECT DISTINCT i.supplier_id, s.business_name
    FROM ingredients i
    JOIN apply_supplier s ON i.supplier_id = s.supplier_id
    WHERE i.supplier_id = ?
    LIMIT 1
");
if (!$stmtSupplier) {
    die("Prepare failed: (" . $conn->errno . ") " . $conn->error);
}
$stmtSupplier->bind_param("i", $userId);
$stmtSupplier->execute();
$supplierResult = $stmtSupplier->get_result();
$supplier = $supplierResult->fetch_assoc();
$stmtSupplier->close();

$supplierId = $supplier['supplier_id'] ?? null;
if (!$supplierId) {
    die("Supplier account not found.");
}

// Get filters
$statusFilter = $_GET['status'] ?? '';
$dateRange = $_GET['date_range'] ?? '';

// Build date condition
$dateCondition = '';
if ($dateRange) {
    switch ($dateRange) {
        case 'today':
            $dateCondition = " AND DATE(o.order_date) = CURDATE()";
            break;
        case 'last_week':
            $dateCondition = " AND o.order_date >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)";
            break;
        case 'last_month':
            $dateCondition = " AND o.order_date >= DATE_SUB(CURDATE(), INTERVAL 1 MONTH)";
            break;
        case 'last_year':
            $dateCondition = " AND o.order_date >= DATE_SUB(CURDATE(), INTERVAL 1 YEAR)";
            break;
    }
}

// Main query to get orders with supplier's items only
$sql = "
    SELECT DISTINCT
        o.order_id,
        o.status AS order_status,
        o.order_date,
        u.first_name,
        u.last_name,
        oi.quantity,
        oi.total_price AS item_price,
        i.ingredient_name,
        CASE 
            WHEN v.variant_id IS NOT NULL THEN v.image_url
            ELSE i.image_url
        END as product_image,
        v.variant_name,
        i.ingredient_id,
        v.variant_id,
        o.user_id
    FROM orders o
    JOIN order_items oi ON o.order_id = oi.order_id
    JOIN ingredients i ON oi.ingredient_id = i.ingredient_id
    LEFT JOIN ingredient_variants v ON oi.variant_id = v.variant_id
    JOIN users u ON o.user_id = u.id
    WHERE i.supplier_id = ?  -- Only get ingredients owned by this supplier
    AND (v.variant_id IS NULL OR v.ingredient_id IN (  -- Only get variants of supplier's ingredients
        SELECT ingredient_id 
        FROM ingredients 
        WHERE supplier_id = ?
    ))";

$params = [$supplierId, $supplierId];
$types = "ii";

if ($statusFilter) {
    $sql .= " AND o.status = ?";
    $params[] = $statusFilter;
    $types .= "s";
}

$sql .= $dateCondition;
$sql .= " ORDER BY o.order_date DESC";

$stmt = $conn->prepare($sql);
if (!$stmt) {
    die("SQL prepare error: " . $conn->error);
}

$stmt->bind_param($types, ...$params);
$stmt->execute();
$result = $stmt->get_result();

// Group orders by order_id
$orders = [];
while ($row = $result->fetch_assoc()) {
    $orderId = $row['order_id'];
    $buyerName = $row['first_name'] . ' ' . $row['last_name'];
    
    if (!isset($orders[$orderId])) {
        $orders[$orderId] = [
            'buyer' => $buyerName,
            'status' => $row['order_status'],
            'order_date' => $row['order_date'],
            'total_amount' => 0,
            'items' => []
        ];
    }
    
    $orders[$orderId]['items'][] = $row;
    $orders[$orderId]['total_amount'] += (float)$row['item_price'];
}
$stmt->close();

// Handle status update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'], $_POST['order_id'])) {
    error_log("POST data received: " . print_r($_POST, true));
    $newStatus = $_POST['update_status'];
    $orderId = intval($_POST['order_id']);
    
    error_log("Attempting to update order $orderId to status: $newStatus");
    
    // Start transaction
    $conn->begin_transaction();
    
    try {
        // Verify the order contains supplier's items before updating
        $verifySql = "
            SELECT o.status, o.user_id as buyer_id, oi.ingredient_id, oi.variant_id, oi.quantity, 
                   i.ingredient_name, i.quantity_value, i.unit_type, oi.total_price
            FROM orders o
            JOIN order_items oi ON o.order_id = oi.order_id
            JOIN ingredients i ON oi.ingredient_id = i.ingredient_id
            WHERE o.order_id = ? 
            AND i.supplier_id = ?
        ";
        
        $stmtVerify = $conn->prepare($verifySql);
        if (!$stmtVerify) {
            throw new Exception("Prepare failed: " . $conn->error);
        }
        
        $stmtVerify->bind_param("ii", $orderId, $supplierId);
        if (!$stmtVerify->execute()) {
            throw new Exception("Execute failed: " . $stmtVerify->error);
        }
        
        $verifyResult = $stmtVerify->get_result();
        $orderItems = $verifyResult->fetch_all(MYSQLI_ASSOC);
        $stmtVerify->close();
        
        if (!empty($orderItems)) {
            $currentStatus = $orderItems[0]['status'];
            
            // Only proceed if order is not already delivered or cancelled
            if (!in_array($currentStatus, ['Delivered', 'Cancelled'])) {
                // Update order status
                $stmtUpdate = $conn->prepare("UPDATE orders SET status = ? WHERE order_id = ?");
                $stmtUpdate->bind_param("si", $newStatus, $orderId);
                $stmtUpdate->execute();
                $stmtUpdate->close();
                
                // If status is being changed to 'Delivered', update inventory
                if ($newStatus === 'Delivered') {
                    foreach ($orderItems as $item) {
                        // Check if inventory record exists
                        $checkInventorySql = "
                            SELECT inventory_id, quantity 
                            FROM ingredients_inventory 
                            WHERE ingredient_id = ? 
                            AND (variant_id = ? OR (variant_id IS NULL AND ? IS NULL))
                            AND supplier_id = ?
                        ";
                        $stmtCheck = $conn->prepare($checkInventorySql);
                        $stmtCheck->bind_param("iiii", 
                            $item['ingredient_id'], 
                            $item['variant_id'], 
                            $item['variant_id'],
                            $supplierId
                        );
                        $stmtCheck->execute();
                        $inventoryResult = $stmtCheck->get_result();
                        $inventory = $inventoryResult->fetch_assoc();
                        $stmtCheck->close();
                        
                        if ($inventory) {
                            // Update existing inventory
                            $newQuantity = $inventory['quantity'] - $item['quantity'];
                            $updateInventorySql = "
                                UPDATE ingredients_inventory 
                                SET quantity = ? 
                                WHERE inventory_id = ?
                            ";
                            $stmtUpdate = $conn->prepare($updateInventorySql);
                            $stmtUpdate->bind_param("ii", $newQuantity, $inventory['inventory_id']);
                            $stmtUpdate->execute();
                            $stmtUpdate->close();
                        } else {
                            // Create new inventory record
                            $insertInventorySql = "
                                INSERT INTO ingredients_inventory 
                                (ingredient_id, ingredient_name, quantity, quantity_value, unit_type, supplier_id, variant_id) 
                                VALUES (?, ?, ?, ?, ?, ?, ?)
                            ";
                            $stmtInsert = $conn->prepare($insertInventorySql);
                            $variantId = $item['variant_id'] ?? null;
                            $stmtInsert->bind_param("isiiisi", 
                                $item['ingredient_id'],
                                $item['ingredient_name'],
                                $item['quantity'],
                                $item['quantity_value'],
                                $item['unit_type'],
                                $supplierId,
                                $variantId
                            );
                            $stmtInsert->execute();
                            $stmtInsert->close();
                        }
                    }
                }
                
                // Commit transaction
                $conn->commit();
                header("Location: manage_orders.php?updated=1");
                exit();
            }
        }
        
        // If we get here, something went wrong
        $conn->rollback();
        header("Location: manage_orders.php?error=1");
        exit();
        
    } catch (Exception $e) {
        // Rollback transaction on error
        $conn->rollback();
        error_log("Error updating order status: " . $e->getMessage());
        header("Location: ../includes/manage_orders.php?error=" . urlencode($e->getMessage()));
        exit();
    }
}
?>
