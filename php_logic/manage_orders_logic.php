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

// Get supplier info from ingredients & apply_supplier join
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

// Filters from GET
$statusFilter = $_GET['status'] ?? '';
$dateRange = $_GET['date_range'] ?? '';

// Date filtering (no placeholders)
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

// Main query including variant filtering by supplier/seller
$sql = "
    SELECT 
        o.order_id,
        o.status AS order_status,
        o.order_date,
        u.first_name,
        u.last_name,
        oi.quantity,
        oi.total_price AS item_price,
        p.product_name,
        p.image_url AS product_image,
        i.ingredient_name,
        i.image_url AS ingredient_image,
        v.variant_name,
        v.image_url AS variant_image,
        i.ingredient_id,
        v.variant_id,
        p.product_id,
        o.user_id
    FROM orders o
    JOIN order_items oi ON o.order_id = oi.order_id
    LEFT JOIN products p ON oi.product_id = p.product_id
    LEFT JOIN ingredients i ON oi.ingredient_id = i.ingredient_id
    LEFT JOIN ingredient_variants v ON oi.variant_id = v.variant_id
    JOIN users u ON o.user_id = u.id
    WHERE (
        (i.supplier_id = ?)
        OR (p.seller_id = ?)
        OR (v.supplier_id = ? OR v.seller_id = ?)
    )
    $dateCondition
";

// Parameters and types
$params = [$supplierId, $supplierId, $supplierId, $supplierId];
$types = "iiii";

// Status filter if set
if ($statusFilter) {
    $sql .= " AND o.status = ?";
    $params[] = $statusFilter;
    $types .= "s";
}

$sql .= " ORDER BY o.order_date DESC";

$stmt = $conn->prepare($sql);
if (!$stmt) {
    die("SQL prepare error: " . $conn->error);
}

// Bind parameters dynamically
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
?>
