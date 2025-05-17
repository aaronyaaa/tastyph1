<?php

require_once("../php_logic/manage_orders_logic.php");
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
                        <form method="post" action="../php_logic/update_order_status.php" class="d-flex align-items-center justify-content-end">
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
<script>
document.addEventListener('DOMContentLoaded', function() {
    const forms = document.querySelectorAll('form[method="post"]');
    
    forms.forEach(form => {
        if (form.querySelector('select[name="update_status"]')) {
            form.addEventListener('submit', function(e) {
                e.preventDefault();
                
                const formData = new FormData(this);
                
                fetch('../php_logic/update_order_status.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.text())
                .then(result => {
                    if (result.includes('success')) {
                        window.location.reload();
                    } else {
                        alert('Error updating status: ' + result);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Error updating status. Please try again.');
                });
            });
        }
    });
});
</script>
</body>
</html>
