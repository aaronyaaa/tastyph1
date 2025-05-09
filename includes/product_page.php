<?php
session_start();
include("../database/config.php"); // Database connection
include("../database/session.php");

// Get product ID from URL
$product_id = $_GET['product_id'] ?? 0;

// Fetch product details
$sqlProduct = "SELECT p.*, s.business_name, s.profile_pics, s.address 
               FROM products p
               JOIN apply_seller s ON p.seller_id = s.seller_id
               WHERE p.product_id = ?";
$stmt = $conn->prepare($sqlProduct);
if (!$stmt) {
    die("Error in SQL Query (product details): " . $conn->error);
}
$stmt->bind_param("i", $product_id);
$stmt->execute();
$product = $stmt->get_result()->fetch_assoc();

if (!$product) {
    die("Product not found.");
}

// Fetch product

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
    <title><?= htmlspecialchars($product['Product_name']); ?> - Product Details</title>
    <link href="../css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../css/product_page.css">
    <link rel="stylesheet" href="../css/nav.css">
    <link rel="stylesheet" href="../css/index.css">

</head>

<body>

    <!-- Navbar -->
    <?php include("../includes/nav_" . strtolower($_SESSION['usertype'] ?? 'user') . ".php"); ?>

    <div class="container mt-4">
        <div class="row">
            <!-- Product Images Section -->
            <div class="col-md-5">
                <div class="main-image">
                    <img id="mainProductImage" src="../uploads/<?= htmlspecialchars($product['image_url']); ?>" class="img-fluid">
                </div>

            </div>

            <!-- Product Details Section -->
            <div class="col-md-7">
                <h2 class="product-title"><?= htmlspecialchars($product['Product_name']); ?></h2>
                <p class="product-price">₱<?= number_format($product['price'], 2); ?></p>
                <p class="product-description"><?= nl2br(htmlspecialchars($product['description'])); ?></p>
                <p><strong>Stock Available:</strong> <?= $product['quantity']; ?> in stock</p>

                <!-- Add to Cart Form -->
                <form action="../cart/add_to_cart.php" method="POST">
                    <input type="hidden" name="item_id" value="<?= $product_id; ?>">
                    <input type="hidden" name="item_type" value="product">
                    <input type="hidden" name="price" value="<?= $product['price']; ?>">
                    <input type="hidden" id="maxStock" value="<?= $product['quantity']; ?>"> <!-- Max stock for JS -->

                    <!-- Quantity Selector -->
                    <!-- Quantity Selector -->
                    <div class="quantity-selector mt-3">
                        <label for="quantity">Quantity:</label>
                        <div class="input-group">
                            <button class="btn btn-outline-secondary" type="button" onclick="updateQuantity(-1)">-</button>
                            <input type="number" class="form-control text-center" name="quantity" id="quantity" value="1" min="1" max="<?= $product['quantity']; ?>" oninput="validateQuantity()">
                            <button class="btn btn-outline-secondary" type="button" onclick="updateQuantity(1)">+</button>
                        </div>
                    </div>


                    <!-- Buy & Add to Cart Buttons -->
                    <div class="mt-4">
                        <button type="submit" class="btn btn-warning btn-lg">Buy Now</button>
                        <button type="submit" class="btn btn-primary btn-lg">Add to Cart</button>
                    </div>
                </form>

            </div>


        </div>

        <!-- Seller Details -->
        <!-- Seller Details -->
        <div class="mt-5 p-3 border">
            <h4>Seller Information</h4>
            <div class="d-flex align-items-center">
                <img src="../uploads/<?= htmlspecialchars($product['profile_pics'] ?? 'default-profile.png'); ?>"
                    class="rounded-circle me-3" width="50" height="50">
                <div>
                    <!-- Clickable Seller Name -->
                    <h5>
                        <a href="../includes/view_store.php?seller_id=<?= $product['seller_id']; ?>"
                            class="text-decoration-none text-primary fw-bold">
                            <?= htmlspecialchars($product['business_name']); ?>
                        </a>
                    </h5>
                    <p><?= htmlspecialchars($product['address']); ?></p>
                </div>
            </div>
        </div>

    </div>

    <script src="../js/bootstrap.bundle.min.js"></script>
    <script src="../js/product_page.js"></script>

</body>

</html>