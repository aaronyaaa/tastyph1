<?php 
session_start();
include("../database/config.php");
include("../database/session.php");

// Ensure the user is logged in and is a supplier
if (!isset($_SESSION['userId']) || $_SESSION['usertype'] !== 'supplier') {
    echo "<script>alert('Unauthorized access.'); window.location.href = 'login.php';</script>";
    exit;
}

$supplier_id = $_SESSION['userId']; // Supplier ID from session

// Fetch orders for the supplier's ingredients and products
$sql = "
    SELECT 
        o.order_id, 
        o.user_id, 
        o.payment_method, 
        o.total_price, 
        o.order_date, 
        o.status,
        u.first_name, 
        u.last_name,
        oi.quantity, 
        i.ingredient_name, 
        i.description AS ingredient_description,
        p.product_name,
        p.description AS product_description
    FROM orders o
    JOIN users u ON o.user_id = u.id
    JOIN order_items oi ON o.order_id = oi.order_id
    LEFT JOIN ingredients i ON oi.ingredient_id = i.ingredient_id AND i.supplier_id = ?
    LEFT JOIN products p ON oi.product_id = p.product_id AND p.seller_id = ?
    WHERE (i.supplier_id = ? OR p.seller_id = ?) -- Filter orders specific to this supplier
    ORDER BY o.order_date DESC
";
$stmt = $conn->prepare($sql);
$stmt->bind_param("iiii", $supplier_id, $supplier_id, $supplier_id, $supplier_id);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Supplier Orders</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../css/nav.css">
</head>
<body>
<?php include("../includes/nav_" . strtolower($_SESSION['usertype']) . ".php"); ?>

<div class="container mt-5">
    <h2 class="mb-4">Supplier Orders</h2>

    <!-- Deliver Orders Form -->
    <form action="process_selected_orders.php" method="POST" class="mb-3">
        <div class="d-flex align-items-center mb-3">
            <!-- Dropdown to select status -->
            <label for="bulk_status" class="me-2">Change Status:</label>
            <select name="bulk_status" id="bulk_status" class="form-select w-auto me-3">
                <option value="pending">Pending</option>
                <option value="confirmed">Order Confirmed</option>
                <option value="packed">Packed</option>
                <option value="delivered">Delivered</option>
            </select>
            <button type="submit" class="btn btn-primary">Confirm</button>
        </div>

        <!-- Orders Table for selecting orders -->
        <table class="table table-bordered mt-3">
            <thead class="table-dark">
                <tr>
                    <th><input type="checkbox" id="select_all"> Select All</th>
                    <th>Order ID</th>
                    <th>Buyer Name</th>
                    <th>Item Name</th>
                    <th>Description</th>
                    <th>Quantity</th>
                    <th>Payment Method</th>
                    <th>Total Price</th>
                    <th>Order Date</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($result->num_rows > 0): ?>
                    <?php while ($order = $result->fetch_assoc()): ?>
                        <tr>
                            <td><input type="checkbox" name="selected_orders[]" value="<?= $order['order_id'] ?>"></td>
                            <td><?= $order['order_id'] ?></td>
                            <td><?= htmlspecialchars($order['first_name'] . ' ' . $order['last_name']) ?></td>
                            <td>
                                <?php 
                                    echo !empty($order['product_name']) ? htmlspecialchars($order['product_name']) : htmlspecialchars($order['ingredient_name']);
                                ?>
                            </td>
                            <td>
                                <?php 
                                    echo !empty($order['product_description']) ? htmlspecialchars($order['product_description']) : htmlspecialchars($order['ingredient_description']);
                                ?>
                            </td>
                            <td><?= htmlspecialchars($order['quantity']) ?></td>
                            <td><?= htmlspecialchars($order['payment_method']) ?></td>
                            <td><?= number_format($order['total_price'], 2) ?></td>
                            <td><?= htmlspecialchars($order['order_date']) ?></td>
                            <td>
                                <span class="badge 
                                    <?php 
                                        if ($order['status'] === 'Pending') echo 'bg-warning';
                                        elseif ($order['status'] === 'Confirmed') echo 'bg-primary';
                                        elseif ($order['status'] === 'Packed') echo 'bg-info';
                                        elseif ($order['status'] === 'Delivered') echo 'bg-success';
                                        else echo 'bg-secondary'; 
                                    ?>">
                                    <?= htmlspecialchars(ucfirst($order['status'])) ?>
                                </span>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="10" class="text-center">No orders found.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </form>
</div>

<!-- JavaScript to toggle select all checkboxes -->
<script>
    document.getElementById('select_all').addEventListener('click', function () {
        const checkboxes = document.querySelectorAll('input[name="selected_orders[]"]');
        checkboxes.forEach(checkbox => checkbox.checked = this.checked);
    });
</script>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>
