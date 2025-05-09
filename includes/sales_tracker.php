<?php
session_start();
include("../database/config.php");
include("../database/data_session.php");

// Ensure the user is logged in and is a supplier
if (!isset($_SESSION['userId']) || $_SESSION['usertype'] !== 'supplier') {
    echo "<script>alert('Unauthorized access.'); window.location.href = 'login.php';</script>";
    exit;
}
if (!$userId || $userType !== 'supplier') {
    die("Unauthorized access. Please log in as a supplier.");
}
// Use the logged-in user's ID as the supplier's ID
$supplierId = $_SESSION['userId'];  // Use the session user ID as the supplier ID

// Now proceed with fetching sales data based on the supplier's ID
$sql = "
    SELECT 
        o.order_id, o.order_date, o.total_price, o.status,
        oi.quantity, oi.total_price AS item_price,
        p.product_name, i.ingredient_name, v.variant_name, 
        p.image_url AS product_image, v.image_url AS variant_image,
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
    WHERE i.supplier_id = ?  -- Only fetch data for the current supplier
    ORDER BY o.order_date DESC
";

// Prepare the statement
$stmt = $conn->prepare($sql);
if ($stmt === false) {
    die('Error preparing SQL statement: ' . $conn->error);  // Output the MySQL error if preparation fails
}

// Bind the supplier_id from the session
$stmt->bind_param("i", $supplierId);
$stmt->execute();
$result = $stmt->get_result();

$sales = [];
$totalRevenue = 0;
$totalOrders = [];
$bestSellers = [];

// Loop through the result set to process sales data
while ($row = $result->fetch_assoc()) {
    $date = date("Y-m", strtotime($row['order_date']));
    $store = $row['seller_name'] ?? $row['supplier_name'];

    // Decide which item name to use: variant name, product name, or ingredient name
    // If a variant exists, prefer it, otherwise fallback to ingredient name
    $item = $row['variant_name'] ?? $row['product_name'] ?? $row['ingredient_name'];

    // Track daily, weekly, monthly, and yearly sales
    $day = date("Y-m-d", strtotime($row['order_date']));
    $week = date("Y-W", strtotime($row['order_date']));
    $year = date("Y", strtotime($row['order_date'])) ;

    // Add to daily, weekly, monthly, and yearly sales
    $sales['daily'][$day]['revenue'] = ($sales['daily'][$day]['revenue'] ?? 0) + $row['item_price'];
    $sales['weekly'][$week]['revenue'] = ($sales['weekly'][$week]['revenue'] ?? 0) + $row['item_price'];
    $sales[$date]['revenue'] = ($sales[$date]['revenue'] ?? 0) + $row['item_price'];
    $sales[$year]['revenue'] = ($sales[$year]['revenue'] ?? 0) + $row['item_price'];

    // Best sellers
    if (!isset($bestSellers[$item])) {
        $bestSellers[$item] = 0;
    }
    $bestSellers[$item] += $row['quantity'];

    // Total revenue and orders count
    $totalRevenue += $row['item_price'];
    $totalOrders[$row['order_id']] = true;
}

// Prepare data for the chart
$months = [];
$revenues = [];
foreach ($sales as $month => $data) {
    if ($month != 'daily' && $month != 'weekly' && $month != 'yearly') {
        $months[] = $month;
        $revenues[] = $data['revenue'];
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Sales Tracker</title>
    <link rel="stylesheet" href="../css/index.css">
    <link rel="stylesheet" href="../css/nav.css">
    <link rel="stylesheet" href="../css/sales.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
<?php include("../includes/nav_" . strtolower($userType) . ".php"); ?>

<div class="container mt-5">
    <h2 class="mb-4 text-center text-light">📊 Sales Tracker</h2>

    <!-- Sales Overview Card -->
    <div class="card mb-4 shadow-sm futuristic-card">
        <div class="card-header">
            <h5 class="card-title">Sales Overview</h5>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <h6>Total Revenue:</h6>
                    <h3>₱<?= number_format($totalRevenue, 2) ?></h3>
                </div>
                <div class="col-md-6">
                    <h6>Total Orders:</h6>
                    <h3><?= count($totalOrders) ?></h3>
                </div>
            </div>
        </div>
    </div>

    <!-- Dropdown for selecting time period with an icon -->
    <div class="mb-4">
        <label for="chartType" class="form-label"><i class="bi bi-gear"></i> Select Time Period:</label>
        <select class="form-select" id="chartType">
            <option value="monthly">Monthly</option>
            <option value="daily">Daily</option>
            <option value="weekly">Weekly</option>
            <option value="yearly">Yearly</option>
        </select>
    </div>

    <!-- Chart for Sales -->
    <div class="card mb-4 shadow-sm futuristic-card">
        <div class="card-header">
            <h5 class="card-title">📈 Sales Chart</h5>
        </div>
        <div class="card-body">
            <canvas id="salesChart"></canvas>
        </div>
    </div>

    <!-- Best-Selling Items with Images -->
    <div class="card mb-4 shadow-sm futuristic-card">
        <div class="card-header">
            <h5 class="card-title">🏆 Best-Selling Items</h5>
        </div>
        <div class="card-body">
            <table class="table table-sm table-hover">
                <thead><tr><th>Item</th><th>Total Sold</th></tr></thead>
                <tbody>
                    <?php arsort($bestSellers); foreach ($bestSellers as $name => $qty): ?>
                        <tr>
                            <td><?= htmlspecialchars($name) ?></td>
                            <td><?= $qty ?></td>

                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
<script src="../js/sales.js"></script>

</body>
</html>
