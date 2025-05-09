<?php
session_start();
include("../database/config.php");
include("../database/data_session.php");

if (!isset($_SESSION['userId'])) {
    die("Session is not set. Please log in.");
}

$userId = $_SESSION['userId'];

$sql = "SELECT 
            o.order_id, o.status AS order_status,
            oi.quantity, oi.total_price AS item_price,
            p.product_name, p.image_url AS product_image, p.product_id,
            i.ingredient_name, i.image_url AS ingredient_image, i.ingredient_id,
            v.variant_name, v.image_url AS variant_image, v.variant_id,
            s.business_name AS seller_name, sup.business_name AS supplier_name
        FROM orders o
        JOIN order_items oi ON o.order_id = oi.order_id
        LEFT JOIN products p ON oi.product_id = p.product_id
        LEFT JOIN ingredients i ON oi.ingredient_id = i.ingredient_id
        LEFT JOIN ingredient_variants v ON oi.variant_id = v.variant_id
        LEFT JOIN apply_seller s ON p.seller_id = s.seller_id
        LEFT JOIN apply_supplier sup ON i.supplier_id = sup.supplier_id
        WHERE o.user_id = ?
        ORDER BY o.order_date DESC";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    die("No orders found.");
}

// Group by store and then by status
$groupedOrders = [];
while ($row = $result->fetch_assoc()) {
    $storeName = $row['seller_name'] ?? $row['supplier_name'];
    $status = $row['order_status'];
    $groupedOrders[$storeName][$status][] = $row;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>My Orders</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../css/index.css">
    <link rel="stylesheet" href="../css/nav.css">
</head>
<body>
<?php include("../includes/nav_" . strtolower($userType) . ".php"); ?>

<div class="container mt-5">
    <h2 class="mb-4">My Orders</h2>

    <div class="accordion" id="storeAccordion">
        <?php $storeIndex = 0; ?>
        <?php foreach ($groupedOrders as $store => $statuses): ?>
            <div class="accordion-item">
                <h2 class="accordion-header" id="heading<?= $storeIndex ?>">
                    <button class="accordion-button <?= $storeIndex > 0 ? 'collapsed' : '' ?>" type="button" data-bs-toggle="collapse" data-bs-target="#collapse<?= $storeIndex ?>" aria-expanded="<?= $storeIndex == 0 ? 'true' : 'false' ?>" aria-controls="collapse<?= $storeIndex ?>">
                        <?= htmlspecialchars($store) ?>
                    </button>
                </h2>
                <div id="collapse<?= $storeIndex ?>" class="accordion-collapse collapse <?= $storeIndex == 0 ? 'show' : '' ?>" aria-labelledby="heading<?= $storeIndex ?>" data-bs-parent="#storeAccordion">
                    <div class="accordion-body">
                        <!-- Status Tabs -->
                        <ul class="nav nav-tabs" id="statusTabs<?= $storeIndex ?>" role="tablist">
                            <?php $tabStatuses = ['Pending', 'Shipped', 'Delivered', 'Others']; ?>
                            <?php foreach ($tabStatuses as $i => $status): ?>
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link <?= $i === 0 ? 'active' : '' ?>" id="<?= $status ?>-tab-<?= $storeIndex ?>" data-bs-toggle="tab" data-bs-target="#<?= $status ?>-<?= $storeIndex ?>" type="button" role="tab">
                                        <?= $status ?>
                                    </button>
                                </li>
                            <?php endforeach; ?>
                        </ul>

                        <div class="tab-content pt-3" id="statusTabContent<?= $storeIndex ?>">
                            <?php foreach ($tabStatuses as $i => $statusLabel): ?>
                                <?php
                                $ordersToShow = [];

                                if ($statusLabel === 'Others') {
                                    foreach ($statuses as $key => $val) {
                                        if (!in_array($key, ['Pending', 'Shipped', 'Delivered'])) {
                                            $ordersToShow = array_merge($ordersToShow, $val);
                                        }
                                    }
                                } elseif (isset($statuses[$statusLabel])) {
                                    $ordersToShow = $statuses[$statusLabel];
                                }
                                ?>
                                <div class="tab-pane fade <?= $i === 0 ? 'show active' : '' ?>" id="<?= $statusLabel ?>-<?= $storeIndex ?>" role="tabpanel">
                                    <?php if (!empty($ordersToShow)): ?>
                                        <?php foreach ($ordersToShow as $row): ?>
                                            <?php
                                                $itemName = $row['variant_name'] ?? $row['product_name'] ?? $row['ingredient_name'];
                                                $itemImage = $row['variant_image'] ?? $row['product_image'] ?? $row['ingredient_image'];
                                                $itemId = $row['variant_id'] ?? $row['product_id'] ?? $row['ingredient_id'];
                                                $itemType = $row['variant_id'] ? 'variant' : ($row['product_id'] ? 'product' : 'ingredient');
                                            ?>
                                            <div class="d-flex border-top py-2">
                                                <img src="../uploads/<?= htmlspecialchars($itemImage); ?>" 
                                                    class="img-thumbnail me-3" style="width: 100px; height: 100px;">
                                                <div class="flex-grow-1">
                                                    <h6>
                                                        <a href="item_details.php?item_id=<?= $itemId ?>&type=<?= $itemType ?>&order_id=<?= $row['order_id']; ?>" 
                                                        class="text-decoration-none">
                                                            <?= htmlspecialchars($itemName); ?>
                                                        </a>
                                                    </h6>
                                                    <p class="text-muted mb-1">Qty: <?= htmlspecialchars($row['quantity']); ?></p>
                                                    <p class="mb-0">₱<?= number_format($row['item_price'], 2); ?></p>
                                                    <p class="mt-2">
                                                        <span class="badge 
                                                            <?php 
                                                                if ($row['order_status'] === 'Pending') echo 'bg-warning';
                                                                elseif ($row['order_status'] === 'Shipped') echo 'bg-primary';
                                                                elseif ($row['order_status'] === 'Delivered') echo 'bg-success';
                                                                else echo 'bg-secondary'; 
                                                            ?>">
                                                            <?= htmlspecialchars($row['order_status']); ?>
                                                        </span>
                                                    </p>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <p class="text-muted">No <?= $statusLabel ?> orders for this store.</p>
                                    <?php endif; ?>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>
            <?php $storeIndex++; ?>
        <?php endforeach; ?>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
