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

// Fetch supplier ID
$stmt = $conn->prepare("
    SELECT supplier_id 
    FROM apply_supplier 
    WHERE business_name = (SELECT business_name FROM users WHERE id = ?)
");
$stmt->bind_param("i", $userId);
$stmt->execute();
$supplierResult = $stmt->get_result();
$supplier = $supplierResult->fetch_assoc();
$stmt->close();

$supplierId = $supplier['supplier_id'] ?? null;
if (!$supplierId) {
    die("Supplier account not found.");
}

// Handle status filter
$statusFilter = $_GET['status'] ?? '';
$dateRange = $_GET['date_range'] ?? '';
$dateCondition = '';
$params = [$supplierId, $supplierId];
$types = 'ii';

if ($dateRange) {
    if ($dateRange == 'today') {
        $dateCondition = " AND DATE(o.order_date) = CURDATE()";
    } elseif ($dateRange == 'last_week') {
        $dateCondition = " AND o.order_date >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)";
    } elseif ($dateRange == 'last_month') {
        $dateCondition = " AND o.order_date >= DATE_SUB(CURDATE(), INTERVAL 1 MONTH)";
    } elseif ($dateRange == 'last_year') {
        $dateCondition = " AND o.order_date >= DATE_SUB(CURDATE(), INTERVAL 1 YEAR)";
    }
}

// Prepare query based on filter
$sql = "
    SELECT o.order_id, o.status AS order_status, o.order_date,
       oi.quantity, oi.total_price AS item_price,
       p.product_name, p.image_url AS product_image, p.product_id,
       i.ingredient_name, i.image_url AS ingredient_image, i.ingredient_id,
       v.variant_name, v.image_url AS variant_image, v.variant_id,
       s.business_name AS seller_name, 
       sup.business_name AS supplier_name,
       u.first_name, u.last_name
    FROM orders o
    JOIN order_items oi ON o.order_id = oi.order_id
    LEFT JOIN products p ON oi.product_id = p.product_id
    LEFT JOIN ingredients i ON oi.ingredient_id = i.ingredient_id
    LEFT JOIN ingredient_variants v ON oi.variant_id = v.variant_id
    LEFT JOIN apply_seller s ON p.seller_id = s.seller_id
    LEFT JOIN apply_supplier sup ON i.supplier_id = sup.supplier_id
    LEFT JOIN users u ON o.user_id = u.id
    WHERE (i.supplier_id = ? OR v.ingredient_id IN (SELECT ingredient_id FROM ingredients WHERE supplier_id = ?))";

if ($statusFilter) {
    $sql .= " AND o.status = ?";
    $params[] = $statusFilter;
    $types .= 's';
}
$sql .= $dateCondition;

// Prepare SQL statement
$stmt = $conn->prepare($sql);
if (!$stmt) {
    die("SQL Error: " . $conn->error);
}

$stmt->bind_param($types, ...$params);

$stmt->execute();
$result = $stmt->get_result();


// Group orders by order_id and buyer
$orders = [];
while ($row = $result->fetch_assoc()) {
    $orderId = $row['order_id'];
    $buyerName = $row['first_name'] . ' ' . $row['last_name'];
    if (!isset($orders[$orderId])) {
        $orders[$orderId] = [
            'buyer' => $buyerName,
            'status' => $row['order_status'],
            'order_date' => $row['order_date'],
            'items' => [],
            'total_amount' => 0
        ];
    }
    $orders[$orderId]['items'][] = $row;
    $orders[$orderId]['total_amount'] += $row['item_price'];
}

// Handle status update submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'], $_POST['order_id'])) {
    $newStatus = $_POST['update_status'];
    $orderId = intval($_POST['order_id']);

    // Check current status
    $stmtCheck = $conn->prepare("SELECT status FROM orders WHERE order_id = ?");
    $stmtCheck->bind_param("i", $orderId);
    $stmtCheck->execute();
    $currentStatus = $stmtCheck->get_result()->fetch_assoc()['status'];
    $stmtCheck->close();

    if (!in_array($currentStatus, ['Delivered', 'Cancelled'])) {
        // Update the order status
        $stmtUpdate = $conn->prepare("UPDATE orders SET status = ? WHERE order_id = ?");
        $stmtUpdate->bind_param("si", $newStatus, $orderId);
        $stmtUpdate->execute();
        $stmtUpdate->close();
    }

    header("Location: manage_orders.php?updated=1");
    exit();
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Supplier Orders Management</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../css/manage_orders.css">
    <link rel="stylesheet" href="../css/nav.css">
    <link rel="stylesheet" href="../css/index.css">
</head>
<body>
<?php include("../includes/nav_supplier.php"); ?>

<div class="container mt-5">
    <h2 class="mb-4">Supplier Orders Management</h2>

    <!-- Status Filter Form -->
    <form method="GET" class="d-flex">
        <select class="form-select me-2" name="status">
            <option value="">All Statuses</option>
            <?php foreach (['Pending', 'Order Confirmed', 'Packed', 'Delivered', 'Cancelled'] as $status): ?>
                <option value="<?= $status ?>" <?= isset($_GET['status']) && $_GET['status'] == $status ? 'selected' : '' ?>>
                    <?= htmlspecialchars($status) ?>
                </option>
            <?php endforeach; ?>
        </select>
        <select class="form-select me-2" name="date_range">
            <option value="">All Dates</option>
            <option value="today" <?= (isset($_GET['date_range']) && $_GET['date_range'] == 'today') ? 'selected' : '' ?>>Today</option>
            <option value="last_week" <?= (isset($_GET['date_range']) && $_GET['date_range'] == 'last_week') ? 'selected' : '' ?>>Last Week</option>
            <option value="last_month" <?= (isset($_GET['date_range']) && $_GET['date_range'] == 'last_month') ? 'selected' : '' ?>>Last Month</option>
            <option value="last_year" <?= (isset($_GET['date_range']) && $_GET['date_range'] == 'last_year') ? 'selected' : '' ?>>Last Year</option>
        </select>
        <button type="submit" class="btn btn-primary">Filter</button>
    </form>

    <?php if (isset($_GET['updated'])): ?>
        <div class="alert alert-success mt-3">Order status updated successfully!</div>
    <?php endif; ?>

    <?php if (empty($orders)): ?>
        <div class="alert alert-info mt-3">No orders found for selected status.</div>
    <?php endif; ?>

    <?php foreach ($orders as $orderId => $orderData): ?>
        <div class="order-group p-4 mb-4 border rounded shadow-sm">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <div>
                    <h5 class="mb-1">Order #<?= $orderId ?></h5>
                    <p class="mb-1">Buyer: <?= htmlspecialchars($orderData['buyer']) ?></p>
                    <p class="mb-1">Order Date: <?= date('M d, Y h:i A', strtotime($orderData['order_date'])) ?></p>
                    <p class="mb-1">Total Amount: ₱<?= number_format($orderData['total_amount'], 2) ?></p>
                </div>
                <div class="text-end">
                    <!-- Status Badge -->
                    <span class="badge 
                        <?php 
                            if ($orderData['status'] === 'Pending') echo 'bg-warning';
                            elseif ($orderData['status'] === 'Order Confirmed') echo 'bg-primary';
                            elseif ($orderData['status'] === 'Packed') echo 'bg-info';
                            elseif ($orderData['status'] === 'Delivered') echo 'bg-success';
                            elseif ($orderData['status'] === 'Cancelled') echo 'bg-danger';
                            else echo 'bg-secondary';
                        ?> fs-6 mb-2">
                        <?= htmlspecialchars($orderData['status']) ?>
                    </span>

                    <!-- Bulk Status Update Form -->
                    <?php if (!in_array($orderData['status'], ['Delivered', 'Cancelled'])): ?>
                        <form method="post" class="d-flex align-items-center justify-content-end">
                            <input type="hidden" name="order_id" value="<?= $orderId ?>">
                            <select class="form-select me-2" name="update_status" style="width: auto;">
                                <?php foreach (['Pending', 'Order Confirmed', 'Packed', 'Delivered', 'Cancelled'] as $status): ?>
                                    <option value="<?= $status ?>" <?= $orderData['status'] == $status ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($status) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <button class="btn btn-primary" type="submit">Update Order Status</button>
                        </form>
                    <?php else: ?>
                        <div class="text-muted">No further changes allowed</div>
                    <?php endif; ?>
                </div>
            </div>

            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Item</th>
                            <th>Quantity</th>
                            <th>Price</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($orderData['items'] as $item): ?>
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <?php if ($item['product_image']): ?>
                                            <img src="../uploads/<?= htmlspecialchars($item['product_image']); ?>" class="img-thumbnail me-2" style="width: 40px; height: 40px;">
                                        <?php elseif ($item['ingredient_image']): ?>
                                            <img src="../uploads/<?= htmlspecialchars($item['ingredient_image']); ?>" class="img-thumbnail me-2" style="width: 40px; height: 40px;">
                                        <?php elseif ($item['variant_image']): ?>
                                            <img src="../uploads/<?= htmlspecialchars($item['variant_image']); ?>" class="img-thumbnail me-2" style="width: 40px; height: 40px;">
                                        <?php else: ?>
                                            <img src="../uploads/default_image.png" class="img-thumbnail me-2" style="width: 40px; height: 40px;">
                                        <?php endif; ?>
                                        <div>
                                            <?= htmlspecialchars($item['product_name'] ?? $item['ingredient_name']); ?>
                                            <?= $item['variant_name'] ? "<br><small class='text-muted'>" . htmlspecialchars($item['variant_name']) . "</small>" : "" ?>
                                        </div>
                                    </div>
                                </td>
                                <td><?= htmlspecialchars($item['quantity']); ?></td>
                                <td>₱<?= number_format($item['item_price'], 2); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    <?php endforeach; ?>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
