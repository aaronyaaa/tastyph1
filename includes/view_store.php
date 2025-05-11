<?php
session_start();
include('../database/config.php');
include("../database/session.php");

// Get the seller_id from the query string
if (!isset($_GET['seller_id'])) {
    echo "<p>Invalid store ID.</p>";
    exit;
}

$seller_id = intval($_GET['seller_id']);

// Fetch store details
$store_sql = "SELECT * FROM apply_seller WHERE seller_id = ?";
$stmt = $conn->prepare($store_sql);
$stmt->bind_param("i", $seller_id);
$stmt->execute();
$store_result = $stmt->get_result();

if ($store_result->num_rows === 0) {
    echo "<p>Store not found.</p>";
    exit;
}

$store = $store_result->fetch_assoc();
$business_name = htmlspecialchars($store['business_name']);
$description = htmlspecialchars($store['description']);
$address = htmlspecialchars($store['address']);
$profile_pics = !empty($store['profile_pics']) ? "../uploads/" . htmlspecialchars($store['profile_pics']) : "../assets/default-store.png";

// Fetch products for the store
$product_sql = "SELECT * FROM products WHERE seller_id = ?";
$stmt = $conn->prepare($product_sql);
$stmt->bind_param("i", $seller_id);
$stmt->execute();
$product_result = $stmt->get_result();

$userType = $_SESSION['usertype'] ?? 'user';
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $business_name; ?> - Store</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../css/products.css">
    <link rel="stylesheet" href="../css/nav.css">
    <link rel="stylesheet" href="../css/modal.css">
    <link rel="stylesheet" href="../css/view_store.css">

</head>

<body>
    <?php include("../includes/nav_" . strtolower($userType) . ".php"); ?>
    <?php include("modal2.php"); // Ensure user is authenticated and session is active
    ?>
        <?php include("floating.php"); // Ensure user is authenticated and session is active
    ?>

    <div class="container mt-5">
        <!-- Store Profile Section -->
        <div class="store-profile">
            <img src="<?= $profile_pics ?>" alt="Store Profile">
            <div class="store-details">
                <h1><?= $business_name ?></h1>
                <p><strong>Description:</strong> <?= $description ?></p>
                <p><strong>Address:</strong> <?= $address ?></p>

                <!-- Buttons Row -->
                <div class="buttons-row">
                    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#preOrderModal">
                        Place a Pre-Order
                    </button>
                    <button type="button" class="store-info-btn" data-bs-toggle="modal" data-bs-target="#shopInfoModal">
                        Shop Info
                    </button>
                    <a href="chat.php?seller_id=<?= $seller_id ?>" class="btn btn-outline-primary message-btn">
                        <i class="fas fa-envelope"></i> Message Seller
                    </a>
                </div>
            </div>
        </div>

        <!-- Products Section -->
        <h2 class="mt-4">Products in this Store</h2>
        <div class="row">
            <?php while ($product = $product_result->fetch_assoc()): ?>
                <div class="col-md-3 mb-3">
                    <a href="product_page.php?product_id=<?= $product['product_id']; ?>" class="product-link">
                        <div class="card">
                            <img src="<?= htmlspecialchars($product['image_url']); ?>" class="card-img-top" alt="Product Image">
                            <div class="card-body">
                                <h5 class="card-title"><?= htmlspecialchars($product['Product_name']); ?></h5>
                                <p><strong>₱<?= number_format($product['price'], 2); ?></strong></p>
                            </div>
                        </div>
                    </a>
                </div>
            <?php endwhile; ?>

        </div>
    </div>



    <!-- Bootstrap & jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="../js/view_store.js"></script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>