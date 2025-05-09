<?php
include("../database/config.php");

$product_id = $_GET['product_id'] ?? 0;

// Fetch product details
$sqlProduct = "SELECT p.*, s.business_name, d.long_description 
               FROM products p
               JOIN apply_seller s ON p.seller_id = s.seller_id
               LEFT JOIN product_details d ON p.product_id = d.product_id
               WHERE p.product_id = ?";
$stmt = $conn->prepare($sqlProduct);
$stmt->bind_param("i", $product_id);
$stmt->execute();
$result = $stmt->get_result();
$product = $result->fetch_assoc();

// Fetch product images
$sqlImages = "SELECT image_url FROM product_images WHERE product_id = ?";
$stmtImages = $conn->prepare($sqlImages);
$stmtImages->bind_param("i", $product_id);
$stmtImages->execute();
$images = $stmtImages->get_result();

// Check if product exists
if (!$product) {
    die("<div class='container mt-5'><h3 class='text-danger'>Product not found.</h3><a href='products.php' class='btn btn-primary'>Back to Products</a></div>");
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($product['Product_name']); ?> - Product Details</title>
    <link href="../css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../css/product_details.css">
</head>
<body>

<div class="product-info">
    <h2><?= htmlspecialchars($product['Product_name']); ?></h2>

    <!-- Seller Info -->
    <p class="text-muted">Sold by: <strong><?= htmlspecialchars($product['business_name']); ?></strong></p>

    <!-- Product Price -->
    <h3 class="price">₱<?= number_format($product['price'], 2); ?></h3>

    <!-- Color & Variations -->
    <?php if ($variations->num_rows > 0): ?>
        <div class="variations">
            <h5>Available Variants:</h5>
            <?php while ($var = $variations->fetch_assoc()): ?>
                <button class="btn btn-outline-primary variant-btn"><?= htmlspecialchars($var['variant_name']); ?></button>
            <?php endwhile; ?>
        </div>
    <?php endif; ?>

    <!-- Quantity Selector -->
    <div class="quantity mt-3">
        <label for="quantity">Quantity:</label>
        <input type="number" id="quantity" class="form-control" value="1" min="1" max="<?= $product['quantity']; ?>">
    </div>

    <!-- Buttons -->
    <div class="buttons mt-4">
        <button class="btn btn-warning btn-lg">Buy Now</button>
        <button class="btn btn-primary btn-lg add-to-cart" data-product-id="<?= $product['product_id']; ?>">Add to Cart</button>
    </div>

    <!-- Long Description -->
    <?php if (!empty($product['long_description'])): ?>
        <div class="long-description mt-4">
            <h4>Product Description:</h4>
            <p><?= nl2br(htmlspecialchars($product['long_description'])); ?></p>
        </div>
    <?php endif; ?>
</div>

<script src="../js/bootstrap.bundle.min.js"></script>
</body>
</html>
