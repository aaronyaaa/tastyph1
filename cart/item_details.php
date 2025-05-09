<?php
session_start();
include("../database/config.php");
include("../database/data_session.php");


// Get item details
$itemId = $_GET['item_id'] ?? null;
$type = $_GET['type'] ?? null;
$orderId = $_GET['order_id'] ?? null;

if (!$itemId || !$orderId || !in_array($type, ['product', 'ingredient'])) {
    die("Invalid item selection.");
}

// Fetch order details
$orderSql = "SELECT o.order_id, o.payment_method, o.total_price, o.order_date, 
                    o.status, u.first_name, u.middle_name, u.last_name, 
                    u.contact_number, u.country, u.city, u.streetname, 
                    u.barangay, u.province, u.email, u.profile_pics
             FROM orders o
             JOIN users u ON o.user_id = u.id
             WHERE o.order_id = ?";
$orderStmt = $conn->prepare($orderSql);
$orderStmt->bind_param("i", $orderId);
$orderStmt->execute();
$orderResult = $orderStmt->get_result();

if ($orderResult->num_rows == 0) {
    die("Order not found.");
}

$orderDetails = $orderResult->fetch_assoc();

// Fetch item details from order_items
if ($type === 'product') {
    $itemSql = "SELECT oi.quantity, oi.total_price AS item_price, 
                       p.product_name, p.image_url, p.price, p.description, 
                       s.business_name AS seller_name
                FROM order_items oi
                JOIN products p ON oi.product_id = p.product_id
                JOIN apply_seller s ON p.seller_id = s.seller_id
                WHERE oi.order_id = ? AND oi.product_id = ?";
} else {
    $itemSql = "SELECT oi.quantity, oi.total_price AS item_price, 
                       i.ingredient_name, i.image_url, i.price, i.description, 
                       sup.business_name AS supplier_name
                FROM order_items oi
                JOIN ingredients i ON oi.ingredient_id = i.ingredient_id
                JOIN apply_supplier sup ON i.supplier_id = sup.supplier_id
                WHERE oi.order_id = ? AND oi.ingredient_id = ?";
}

$itemStmt = $conn->prepare($itemSql);
$itemStmt->bind_param("ii", $orderId, $itemId);
$itemStmt->execute();
$itemResult = $itemStmt->get_result();

if ($itemResult->num_rows == 0) {
    die("Item not found in this order.");
}

$itemDetails = $itemResult->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Item Details</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../css/nav.css">
    <link rel="stylesheet" href="../css/index.css">

</head>
<body>
<?php include("../includes/nav_" . strtolower($userType) . ".php"); ?>

<div class="container mt-5">
    <h2>Item Details</h2>

    <!-- Order Information -->
    <div class="card mb-4">
        <div class="card-body">
            <h5 class="card-title">Order Information</h5>
            <p><strong>Order ID:</strong> <?= htmlspecialchars($orderDetails['order_id']); ?></p>
            <p><strong>Order Date:</strong> <?= htmlspecialchars($orderDetails['order_date']); ?></p>
            <p><strong>Payment Method:</strong> <?= htmlspecialchars($orderDetails['payment_method']); ?></p>
            <p><strong>Total Price:</strong> ₱<?= number_format($orderDetails['total_price'], 2); ?></p>
            <p><strong>Status:</strong> <?= htmlspecialchars($orderDetails['status']); ?></p>
        </div>
    </div>

    <!-- Item Details -->
    <div class="card mb-4">
        <div class="card-body">
            <h5 class="card-title">Item Information</h5>
            <div class="row">
                <div class="col-md-5">
                    <img src="../uploads/<?= htmlspecialchars($itemDetails['image_url']); ?>" 
                         class="img-fluid rounded" alt="Item Image">
                </div>
                <div class="col-md-7">
                    <h3><?= htmlspecialchars($itemDetails['product_name'] ?? $itemDetails['ingredient_name']); ?></h3>
                    <p><strong>Price:</strong> ₱<?= number_format($itemDetails['price'], 2); ?></p>
                    <p><strong>Quantity:</strong> <?= htmlspecialchars($itemDetails['quantity']); ?></p>
                    <p><strong>Total:</strong> ₱<?= number_format($itemDetails['item_price'], 2); ?></p>
                    <p><strong>Seller:</strong> <?= htmlspecialchars($itemDetails['seller_name'] ?? $itemDetails['supplier_name']); ?></p>
                    <p><?= nl2br(htmlspecialchars($itemDetails['description'])); ?></p>
                </div>
            </div>
        </div>
    </div>

    <!-- Buyer Information -->
    <div class="card">
        <div class="card-body">
            <h5 class="card-title">Buyer Information</h5>
            <div class="d-flex align-items-center">
                <img src="../uploads/<?= htmlspecialchars($orderDetails['profile_pics']); ?>" 
                     class="rounded-circle me-3" style="width: 80px; height: 80px;" alt="Buyer Profile">
                <div>
                    <h6><?= htmlspecialchars($orderDetails['first_name'] . ' ' . ($orderDetails['middle_name'] ? $orderDetails['middle_name'] . ' ' : '') . $orderDetails['last_name']); ?></h6>
                    <p><strong>Contact:</strong> <?= htmlspecialchars($orderDetails['contact_number']); ?></p>
                    <p><strong>Email:</strong> <?= htmlspecialchars($orderDetails['email']); ?></p>
                    <p><strong>Address:</strong> <?= htmlspecialchars($orderDetails['streetname'] . ', ' . $orderDetails['barangay'] . ', ' . $orderDetails['city'] . ', ' . $orderDetails['province'] . ', ' . $orderDetails['country']); ?></p>
                </div>
            </div>
        </div>
    </div>

    <a href="order_confirmation.php?order_id=<?= $orderId ?>" class="btn btn-primary mt-3">Back to Orders</a>
</div>

</body>
</html>
