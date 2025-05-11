<?php
include("../database/session.php");
include("../database/config.php");

// Get the current user type and ID from the session
$userType = $_SESSION['usertype'] ?? 'user'; // Default to 'user' if not set
$userId = $_SESSION['userId'] ?? 0; // Get user ID from session

// Fetch all sellers (stores)
$sqlSellers = "SELECT seller_id, business_name, profile_pics FROM apply_seller";
$resultSellers = $conn->query($sqlSellers);

// Fetch all suppliers
$sqlSuppliers = "SELECT supplier_id, business_name, profile_pics FROM apply_supplier";
$resultSuppliers = $conn->query($sqlSuppliers);

// Fetch best selling kakanin from sellers
$sqlBestKakanin = "SELECT 
    p.product_id, 
    p.Product_name as product_name, 
    p.price, 
    p.image_url as product_image,
    p.seller_id,
    p.category_id,
    s.business_name,
    'seller' as product_type
FROM products p
JOIN apply_seller s ON p.seller_id = s.seller_id
WHERE p.status = 'active' 
AND s.status = 'approved'
ORDER BY p.quantity DESC
LIMIT 10";

$resultBestKakanin = $conn->query($sqlBestKakanin);

// Check if query was successful
if (!$resultBestKakanin) {
    $resultBestKakanin = new stdClass();
    $resultBestKakanin->num_rows = 0;
    $resultBestKakanin->fetch_assoc = function() { return false; };
}

// Fetch best selling ingredients from suppliers
$sqlBestIngredients = "SELECT 
    i.ingredient_id,
    i.ingredient_name as product_name,
    i.price,
    i.image_url as product_image,
    i.supplier_id,
    i.category_id,
    s.business_name,
    'supplier' as product_type
FROM ingredients i
JOIN apply_supplier s ON i.supplier_id = s.supplier_id
WHERE s.status = 'approved'
ORDER BY i.quantity DESC
LIMIT 10";

$resultBestIngredients = $conn->query($sqlBestIngredients);

// Check if query was successful
if (!$resultBestIngredients) {
    $resultBestIngredients = new stdClass();
    $resultBestIngredients->num_rows = 0;
    $resultBestIngredients->fetch_assoc = function() { return false; };
}

// Fetch top sellers based on total products
$sqlTopSellers = "SELECT 
    s.seller_id, 
    s.business_name, 
    s.profile_pics,
    COUNT(p.product_id) as total_products
FROM apply_seller s
LEFT JOIN products p ON s.seller_id = p.seller_id 
WHERE s.status = 'approved'
GROUP BY s.seller_id
ORDER BY COUNT(p.product_id) DESC
LIMIT 5";

$resultTopSellers = $conn->query($sqlTopSellers);

// Check if query was successful
if (!$resultTopSellers) {
    // If query fails, create an empty result set
    $resultTopSellers = new stdClass();
    $resultTopSellers->num_rows = 0;
    $resultTopSellers->fetch_assoc = function() { return false; };
}

// Fetch top suppliers based on total products
$sqlTopSuppliers = "SELECT 
    s.supplier_id, 
    s.business_name, 
    s.profile_pics,
    COUNT(i.ingredient_id) as total_products
FROM apply_supplier s
LEFT JOIN ingredients i ON s.supplier_id = i.supplier_id 
WHERE s.status = 'approved'
GROUP BY s.supplier_id
ORDER BY COUNT(i.ingredient_id) DESC
LIMIT 5";

$resultTopSuppliers = $conn->query($sqlTopSuppliers);

// Check if query was successful
if (!$resultTopSuppliers) {
    // If query fails, create an empty result set
    $resultTopSuppliers = new stdClass();
    $resultTopSuppliers->num_rows = 0;
    $resultTopSuppliers->fetch_assoc = function() { return false; };
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Online Marketplace - Kakanin</title>
    <link href="https://cdn.jsdelivr.net/npm/ionicons@5.5.2/dist/ionicons/ionicons.min.css" rel="stylesheet">
    <link href="../css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../css/nav.css">
    <link rel="stylesheet" href="../css/modal.css">
    <link rel="stylesheet" href="../css/index.css">
</head>

<body>

    <!-- Modal Inclusion -->
    <?php include("../includes/modal.php"); ?>

    <!-- Navbar -->
    <?php include("../includes/nav_" . strtolower($userType) . ".php"); ?>

    <!-- Hero Section with Carousel -->
    <section class="hero-container mb-4">
        <div id="imageCarousel" class="carousel slide" data-bs-ride="carousel">
            <div class="carousel-inner rounded shadow-sm">
                <div class="carousel-item active">
                    <img src="../uploads/1.png" class="d-block carousel-img" alt="Kakanin Delights">
                </div>
                <div class="carousel-item">
                    <img src="../uploads/2.png" class="d-block carousel-img" alt="Kakanin Delights">
                </div>
                <div class="carousel-item">
                    <img src="../uploads/3.png" class="d-block carousel-img" alt="Kakanin Delights">
                </div>

            </div>
            <button class="carousel-control-prev" type="button" data-bs-target="#imageCarousel" data-bs-slide="prev">
                <span class="carousel-control-prev-icon"></span>
            </button>
            <button class="carousel-control-next" type="button" data-bs-target="#imageCarousel" data-bs-slide="next">
                <span class="carousel-control-next-icon"></span>
            </button>
        </div>
    </section>

    <!-- Best Selling Sections -->
    <div class="container mt-4">
        <!-- Kakanin Flash Deals -->
        <section class="kakanin-deals-section mb-4">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <h2 class="section-title mb-0">🍡 Best Selling Kakanin</h2>
                <a href="#" class="see-all-link">See All &gt;</a>
            </div>
            <div class="deals-scroll-container">
                <div class="deals-scroll-wrapper">
                    <?php if ($resultBestKakanin && $resultBestKakanin->num_rows > 0):
                        while ($product = $resultBestKakanin->fetch_assoc()): ?>
                        <div class="kakanin-card">
                            <div class="deal-img-wrap">
                                <img src="<?= !empty($product['product_image']) ? htmlspecialchars($product['product_image']) : '../assets/default-product.jpg'; ?>" 
                                     alt="<?= htmlspecialchars($product['product_name']); ?>">
                            </div>
                            <div class="deal-info">
                                <div class="deal-title" title="<?= htmlspecialchars($product['product_name']); ?>">
                                    <?= htmlspecialchars($product['product_name']); ?>
                                </div>
                                <div class="deal-price">₱<?= number_format($product['price'], 2); ?></div>
                                <div class="deal-badges">
                                    <?php $qty = isset($product['quantity']) ? $product['quantity'] : 0; ?>
                                    <?php if ($qty > 10): ?>
                                        <span class="badge selling-fast">SELLING FAST</span>
                                    <?php else: ?>
                                        <span class="badge sold-count"><?= $qty; ?> SOLD</span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    <?php endwhile; else: ?>
                        <div class="no-items-message">No kakanin available</div>
                    <?php endif; ?>
                </div>
                <button class="scroll-btn prev-btn"><i class="fas fa-chevron-left"></i></button>
                <button class="scroll-btn next-btn"><i class="fas fa-chevron-right"></i></button>
            </div>
        </section>

        <!-- Ingredients Flash Deals -->
        <section class="ingredients-deals-section mb-4">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <h2 class="section-title mb-0">🥥 Best Selling Ingredients</h2>
                <a href="#" class="see-all-link">See All &gt;</a>
            </div>
            <div class="deals-scroll-container">
                <div class="deals-scroll-wrapper">
                    <?php if ($resultBestIngredients && $resultBestIngredients->num_rows > 0):
                        while ($ingredient = $resultBestIngredients->fetch_assoc()): ?>
                        <div class="ingredient-card">
                            <div class="deal-img-wrap">
                                <img src="<?= !empty($ingredient['product_image']) ? htmlspecialchars($ingredient['product_image']) : '../assets/default-product.jpg'; ?>" 
                                     alt="<?= htmlspecialchars($ingredient['product_name']); ?>">
                            </div>
                            <div class="deal-info">
                                <div class="deal-title" title="<?= htmlspecialchars($ingredient['product_name']); ?>">
                                    <?= htmlspecialchars($ingredient['product_name']); ?>
                                </div>
                                <div class="deal-price">₱<?= number_format($ingredient['price'], 2); ?></div>
                                <a href="../includes/ingredient_page.php?ingredient_id=<?= $ingredient['ingredient_id']; ?>&type=supplier" class="btn btn-outline-primary btn-sm mt-2">View Ingredient</a>
                            </div>
                        </div>
                    <?php endwhile; else: ?>
                        <div class="no-items-message">No ingredients available</div>
                    <?php endif; ?>
                </div>
                <button class="scroll-btn prev-btn"><i class="fas fa-chevron-left"></i></button>
                <button class="scroll-btn next-btn"><i class="fas fa-chevron-right"></i></button>
            </div>
        </section>
    </div>

    <!-- Vendors Sections -->
    <div class="container mt-4">
        <!-- Sellers Section -->
        <section class="sellers-section mb-4">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <h2 class="section-title mb-0">🏪 Featured Sellers</h2>
                <a href="#" class="see-all-link">See All &gt;</a>
            </div>
            <div class="vendors-scroll-container">
                <div class="vendors-scroll-wrapper">
                    <?php if ($resultSellers && $resultSellers->num_rows > 0):
                        while ($seller = $resultSellers->fetch_assoc()): ?>
                        <div class="seller-card">
                            <div class="vendor-img-wrap">
                                <img src="<?= !empty($seller['profile_pics']) ? '../uploads/' . htmlspecialchars($seller['profile_pics']) : '../assets/default-image.jpg'; ?>" 
                                     alt="<?= htmlspecialchars($seller['business_name']); ?>">
                            </div>
                            <div class="vendor-info">
                                <div class="vendor-name"><?= htmlspecialchars($seller['business_name']); ?></div>
                                <a href="../includes/view_store.php?seller_id=<?= $seller['seller_id']; ?>" class="btn btn-outline-primary btn-sm">View Store</a>
                            </div>
                        </div>
                    <?php endwhile; else: ?>
                        <div class="no-items-message">No sellers available</div>
                    <?php endif; ?>
                </div>
                <button class="scroll-btn prev-btn"><i class="fas fa-chevron-left"></i></button>
                <button class="scroll-btn next-btn"><i class="fas fa-chevron-right"></i></button>
            </div>
        </section>

        <!-- Suppliers Section -->
        <section class="suppliers-section mb-4">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <h2 class="section-title mb-0">🏭 Featured Suppliers</h2>
                <a href="#" class="see-all-link">See All &gt;</a>
            </div>
            <div class="vendors-scroll-container">
                <div class="vendors-scroll-wrapper">
                    <?php if ($resultSuppliers && $resultSuppliers->num_rows > 0):
                        while ($supplier = $resultSuppliers->fetch_assoc()): ?>
                        <div class="supplier-card">
                            <div class="vendor-img-wrap">
                                <img src="<?= !empty($supplier['profile_pics']) ? '../uploads/' . htmlspecialchars($supplier['profile_pics']) : '../assets/default-image.jpg'; ?>" 
                                     alt="<?= htmlspecialchars($supplier['business_name']); ?>">
                            </div>
                            <div class="vendor-info">
                                <div class="vendor-name"><?= htmlspecialchars($supplier['business_name']); ?></div>
                                <a href="../includes/view_stores.php?supplier_id=<?= $supplier['supplier_id']; ?>" class="btn btn-outline-primary btn-sm">View Store</a>
                            </div>
                        </div>
                    <?php endwhile; else: ?>
                        <div class="no-items-message">No suppliers available</div>
                    <?php endif; ?>
                </div>
                <button class="scroll-btn prev-btn"><i class="fas fa-chevron-left"></i></button>
                <button class="scroll-btn next-btn"><i class="fas fa-chevron-right"></i></button>
            </div>
        </section>
    </div>

    <!-- Footer Inclusion -->
    <?php include("../includes/footer.php"); ?>

    <!-- JS Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../js/scripts.js"></script>
    <script src="../js/custom-scroll.js"></script>

</body>

</html>
