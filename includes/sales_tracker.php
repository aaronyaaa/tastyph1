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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Supplier Dashboard - Sales Analytics</title>
    <link rel="stylesheet" href="../css/index.css">
    <link rel="stylesheet" href="../css/nav.css">
    <link rel="stylesheet" href="../css/sales.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        :root {
            --sidebar-width: 250px;
            --header-height: 60px;
            --primary-color: #2c3e50;
            --secondary-color: #34495e;
            --accent-color: #3498db;
            --success-color: #2ecc71;
            --warning-color: #f1c40f;
            --danger-color: #e74c3c;
        }

        body {
            background-color: #f5f6fa;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        .dashboard-container {
            display: flex;
            justify-content: center;   /* Center horizontally */
            align-items: flex-start;   /* Align to top, or use center for vertical centering */
            min-height: calc(100vh - 60px);
            margin-top: 60px;
            background: #f5f6fa;
        }

        .sidebar {
            width: var(--sidebar-width);
            background: var(--primary-color);
            color: white;
            position: fixed;
            height: calc(100vh - 60px); /* Adjust for navbar */
            padding: 1rem;
            box-shadow: 2px 0 5px rgba(0,0,0,0.1);
            top: 60px; /* Position below navbar */
        }

        .sidebar-header {
            padding: 1rem 0;
            border-bottom: 1px solid rgba(255,255,255,0.1);
            margin-bottom: 1rem;
        }

        .sidebar-menu {
            list-style: none;
            padding: 0;
        }

        .sidebar-menu li {
            margin-bottom: 0.5rem;
        }

        .sidebar-menu a {
            color: white;
            text-decoration: none;
            display: flex;
            align-items: center;
            padding: 0.75rem 1rem;
            border-radius: 5px;
            transition: all 0.3s;
        }

        .sidebar-menu a:hover {
            background: var(--secondary-color);
        }

        .sidebar-menu i {
            margin-right: 10px;
        }

        .main-content {
            width: 100%;
            max-width: 100%;
            margin: 0 auto;
            padding: 2rem 1rem 3rem 1rem;
        }

        .dashboard-header {
            background: white;
            padding: 1rem 2rem;
            border-radius: 10px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            margin-bottom: 2rem;
        }

        .stat-card {
            background: white;
            border-radius: 10px;
            padding: 1.5rem;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            transition: transform 0.3s;
        }

        .stat-card:hover {
            transform: translateY(-5px);
        }

        .stat-card .icon {
            font-size: 2rem;
            margin-bottom: 1rem;
        }

        .stat-card .value {
            font-size: 1.8rem;
            font-weight: bold;
            margin: 0.5rem 0;
        }

        .stat-card .label {
            color: #666;
            font-size: 0.9rem;
        }

        .chart-container {
            background: white;
            border-radius: 10px;
            padding: 1.5rem;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            margin-bottom: 2rem;
        }

        .best-sellers-table {
            background: white;
            border-radius: 10px;
            padding: 1.5rem;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        .best-sellers-table th {
            background: var(--primary-color);
            color: white;
        }

        .period-selector {
            background: white;
            padding: 1rem;
            border-radius: 10px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            margin-bottom: 1rem;
        }
    </style>
</head>
<body>
    <?php include("../includes/nav_" . strtolower($userType) . ".php"); ?>

    <div class="dashboard-container">
        <!-- Sidebar -->
       

        <!-- Main Content -->
        <div class="main-content">
            <div class="dashboard-header">
                <h2><i class="bi bi-graph-up"></i> Sales Analytics Dashboard</h2>
                <p class="text-muted">Welcome back, <?php echo htmlspecialchars($_SESSION['business_name'] ?? 'Supplier'); ?></p>
            </div>

            <!-- Stats Cards -->
            <div class="row mb-4">
                <div class="col-md-4">
                    <div class="stat-card">
                        <div class="icon text-primary">
                            <i class="bi bi-currency-dollar"></i>
                        </div>
                        <div class="value">₱<?= number_format($totalRevenue, 2) ?></div>
                        <div class="label">Total Revenue</div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="stat-card">
                        <div class="icon text-success">
                            <i class="bi bi-cart-check"></i>
                        </div>
                        <div class="value"><?= count($totalOrders) ?></div>
                        <div class="label">Total Orders</div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="stat-card">
                        <div class="icon text-warning">
                            <i class="bi bi-box-seam"></i>
                        </div>
                        <div class="value"><?= array_sum($bestSellers) ?></div>
                        <div class="label">Total Items Sold</div>
                    </div>
                </div>
            </div>

            <!-- Period Selector -->
            <div class="period-selector">
                <div class="row align-items-center">
                    <div class="col-md-6">
                        <h5 class="mb-0"><i class="bi bi-calendar"></i> Sales Period</h5>
                    </div>
                    <div class="col-md-6">
                        <select class="form-select" id="chartType">
                            <option value="monthly">Monthly Overview</option>
                            <option value="daily">Daily Analysis</option>
                            <option value="weekly">Weekly Trends</option>
                            <option value="yearly">Yearly Summary</option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- Sales Chart -->
            <div class="chart-container">
                <h5 class="mb-4"><i class="bi bi-bar-chart"></i> Sales Performance</h5>
                <canvas id="salesChart" height="300"></canvas>
            </div>

            <!-- Best Sellers Table -->
            <div class="best-sellers-table">
                <h5 class="mb-4"><i class="bi bi-trophy"></i> Best-Selling Products</h5>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Product</th>
                                <th>Units Sold</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            arsort($bestSellers); 
                            $counter = 0;
                            foreach ($bestSellers as $name => $qty): 
                                $counter++;
                                $statusClass = $counter <= 3 ? 'success' : 'primary';
                            ?>
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <span class="badge bg-<?= $statusClass ?> me-2">#<?= $counter ?></span>
                                            <?= htmlspecialchars($name) ?>
                                        </div>
                                    </td>
                                    <td><?= $qty ?> units</td>
                                    <td>
                                        <span class="badge bg-<?= $statusClass ?>">
                                            <?= $counter <= 3 ? 'Top Seller' : 'Regular' ?>
                                        </span>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../js/sales.js"></script>
</body>
</html>
