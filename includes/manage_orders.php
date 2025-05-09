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
    WHERE (i.supplier_id = ? OR v.ingredient_id IN (SELECT ingredient_id FROM ingredients WHERE supplier_id = ?))
";

if ($statusFilter) {
    $sql .= " AND o.status = ?";  // Optional filter by status
}

// Prepare SQL statement
$stmt = $conn->prepare($sql);
if (!$stmt) {
    die("SQL Error: " . $conn->error);
}

// Check if statusFilter exists and bind parameters accordingly
if ($statusFilter) {
    // Bind three parameters: two integers for supplierId, one string for statusFilter
    $stmt->bind_param("iis", $supplierId, $supplierId, $statusFilter);
} else {
    // Bind only two integers for supplierId
    $stmt->bind_param("ii", $supplierId, $supplierId);
}

$stmt->execute();
$result = $stmt->get_result();


// Group orders by store and buyer
$orders = [];
while ($row = $result->fetch_assoc()) {
    $buyerName = $row['first_name'] . ' ' . $row['last_name'];
    $orders[$buyerName][] = $row;
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
        <button type="submit" class="btn btn-primary">Filter</button>
    </form>

    <?php if (isset($_GET['updated'])): ?>
        <div class="alert alert-success mt-3">Order status updated successfully!</div>
    <?php endif; ?>

    <?php if (empty($orders)): ?>
        <div class="alert alert-info mt-3">No orders found for selected status.</div>
    <?php endif; ?>

    <?php foreach ($orders as $buyer => $items): ?>
        <div class="order-group p-3 mb-4 border rounded shadow-sm">
            <h5 class="mb-3">Buyer: <?= htmlspecialchars($buyer) ?></h5>

            <?php foreach ($items as $order): ?>
                <div class="d-flex border-top py-3">
                    <!-- Display appropriate image: product, ingredient, or variant -->
                    <?php if ($order['product_image']): ?>
                        <img src="../uploads/<?= htmlspecialchars($order['product_image']); ?>" class="img-thumbnail me-3 order-img" style="width: 50px; height: 50px;">
                    <?php elseif ($order['ingredient_image']): ?>
                        <img src="../uploads/<?= htmlspecialchars($order['ingredient_image']); ?>" class="img-thumbnail me-3 order-img" style="width: 50px; height: 50px;">
                    <?php elseif ($order['variant_image']): ?>
                        <img src="../uploads/<?= htmlspecialchars($order['variant_image']); ?>" class="img-thumbnail me-3 order-img" style="width: 50px; height: 50px;">
                    <?php else: ?>
                        <img src="../uploads/default_image.png" class="img-thumbnail me-3 order-img" style="width: 50px; height: 50px;">
                    <?php endif; ?>

                    <div class="flex-grow-1">
                        <h6>
                            <?= htmlspecialchars($order['product_name']); ?> 
                            <?= $order['variant_name'] ? " - " . htmlspecialchars($order['variant_name']) : "" ?>
                        </h6>
                        <p class="mb-1">Quantity: <?= htmlspecialchars($order['quantity']); ?></p>
                        <p class="mb-1">₱<?= number_format($order['item_price'], 2); ?></p>
                        <p class="mb-1">Order Date: <?= date('M d, Y h:i A', strtotime($order['order_date'])); ?></p>

                        <!-- Status Badge Display (Including Cancelled) -->
                        <span class="badge 
                            <?php 
                                if ($order['order_status'] === 'Pending') echo 'bg-warning';
                                elseif ($order['order_status'] === 'Order Confirmed') echo 'bg-primary';
                                elseif ($order['order_status'] === 'Packed') echo 'bg-info';
                                elseif ($order['order_status'] === 'Delivered') echo 'bg-success';
                                elseif ($order['order_status'] === 'Cancelled') echo 'bg-danger';
                                else echo 'bg-secondary';
                            ?>">
                            <?= htmlspecialchars($order['order_status']); ?>
                        </span>

                        <!-- Status Update Form -->
                        <?php if (!in_array($order['order_status'], ['Delivered', 'Cancelled'])): ?>
                            <form method="post" class="d-flex align-items-center mt-2">
                                <input type="hidden" name="order_id" value="<?= $order['order_id'] ?>">
                                <select class="form-select me-2" name="update_status">
                                    <?php foreach (['Pending', 'Order Confirmed', 'Packed', 'Delivered', 'Cancelled'] as $status): ?>
                                        <option value="<?= $status ?>" <?= $order['order_status'] == $status ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($status) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <button class="btn btn-primary" type="submit">Update</button>
                            </form>
                        <?php else: ?>
                            <span class="badge bg-secondary mt-2">No further changes allowed.</span>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endforeach; ?>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
