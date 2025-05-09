<?php
session_start();
include("../database/config.php");
include("../database/data_session.php");


// Ensure user is logged in
if (!isset($_SESSION['userId'])) {
    die("Session is not set. Please log in.");
}

// Get order ID
$orderId = $_GET['order_id'] ?? null;
if (!$orderId) {
    die("Invalid order ID.");
}

// Fetch order details grouped by store
$sql = "SELECT 
            o.order_id, o.status,
            oi.quantity, oi.total_price AS item_price,
            p.product_name, p.image_url AS product_image, p.product_id,
            i.ingredient_name, i.image_url AS ingredient_image, i.ingredient_id,
            v.variant_name, v.image_url AS variant_image, v.variant_id,
            s.business_name AS seller_name, 
            sup.business_name AS supplier_name
        FROM orders o
        JOIN order_items oi ON o.order_id = oi.order_id
        LEFT JOIN products p ON oi.product_id = p.product_id
        LEFT JOIN ingredients i ON oi.ingredient_id = i.ingredient_id
        LEFT JOIN ingredient_variants v ON oi.variant_id = v.variant_id
        LEFT JOIN apply_seller s ON p.seller_id = s.seller_id
        LEFT JOIN apply_supplier sup ON COALESCE(i.supplier_id, (
            SELECT i2.supplier_id FROM ingredients i2 WHERE i2.ingredient_id = v.ingredient_id
        )) = sup.supplier_id
        WHERE o.order_id = ? AND o.user_id = ?
        ORDER BY s.business_name, sup.business_name";


$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $orderId, $_SESSION['userId']);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    die("Order not found or unauthorized access.");
}

// Organize order items by store
$orderItems = [];
while ($row = $result->fetch_assoc()) {
    $storeName = $row['seller_name'] ?? $row['supplier_name'];
    $orderItems[$storeName][] = $row;
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Order Details</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../css/index.css">
    <link rel="stylesheet" href="../css/nav.css">

</head>
<body>
<?php include("../includes/nav_" . strtolower($userType) . ".php"); ?>

<div class="container mt-5">
    <h2 class="mb-4">Order Details</h2>

    <?php foreach ($orderItems as $store => $items): ?>
        <div class="store-container p-3 mb-4 border rounded">
            <div class="d-flex justify-content-between align-items-center">
                <h5><?= htmlspecialchars($store) ?></h5>
                <span class="badge bg-info text-dark">Status: <?= htmlspecialchars($items[0]['status']) ?></span>
            </div>
            
            <?php foreach ($items as $row): ?>
                <div class="d-flex border-top py-3">
                <img src="../uploads/<?= htmlspecialchars($row['variant_image'] ?? $row['product_image'] ?? $row['ingredient_image']); ?>"
                class="img-thumbnail me-3" style="width: 100px; height: 100px;">
                    <div class="flex-grow-1">
                        <h6>
                        <a href="../cart/item_details.php?item_id=<?= $row['product_id'] ?? $row['ingredient_id']; ?>&type=<?= $row['product_id'] ? 'product' : 'ingredient'; ?>&order_id=<?= $orderId; ?>" 
                        class="text-decoration-none">
                        <?= htmlspecialchars($row['variant_name'] ?? $row['product_name'] ?? $row['ingredient_name']); ?>
                        </a>
                        </h6>
                        <p class="text-muted mb-1">Qty: <?= htmlspecialchars($row['quantity']); ?></p>
                        <p class="mb-0">₱<?= number_format($row['item_price'], 2); ?></p>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endforeach; ?>

</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>
