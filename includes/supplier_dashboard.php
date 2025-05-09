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

    <!-- Main Container for Sellers and Suppliers -->
    <div class="container mt-5">
        <!-- Filter Section -->
        <section class="filter-section text-center mb-5">
            <h3>Browse by Category</h3>
            <div class="btn-group">
                <button class="btn btn-primary active" id="stores-filter">Stores</button>
                <button class="btn btn-primary" id="suppliers-filter">Suppliers</button>
            </div>
        </section>

        <!-- Store Listings -->
        <section class="store-section mb-5" id="store-section">
            <h2 class="section-title text-center mb-4">Traditional Stores</h2>
            <div class="store-list">
                <?php while ($row = $resultSellers->fetch_assoc()): ?>
                    <div class="store-card" style="background-image: url('<?= !empty($row['profile_pics']) ? '../uploads/' . htmlspecialchars($row['profile_pics']) : '../assets/default-image.jpg'; ?>');">
                        <div class="store-info">
                            <h3 class="store-name"><?= htmlspecialchars($row['business_name']); ?></h3>
                        </div>
                        <div class="overlay">
                            <a href="../includes/view_store.php?seller_id=<?= $row['seller_id']; ?>" class="view-btn">View Store</a>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>
            <!-- Scroll Buttons -->
            <div class="scroll-button scroll-button-left">
                <ion-icon name="arrow-back-circle-outline"></ion-icon>
            </div>
            <div class="scroll-button scroll-button-right">
                <ion-icon name="arrow-forward-circle-outline"></ion-icon>
            </div>
        </section>

        <!-- Supplier Listings -->
        <section class="supplier-section mb-5" id="supplier-section" style="display: none;">
            <h2 class="section-title text-center mb-4">Palengke (Suppliers)</h2>
            <div class="supplier-list">
                <?php while ($row = $resultSuppliers->fetch_assoc()): ?>
                    <div class="supplier-card" style="background-image: url('<?= !empty($row['profile_pics']) ? '../uploads/' . htmlspecialchars($row['profile_pics']) : '../assets/default-image.jpg'; ?>');">
                        <div class="supplier-info">
                            <h3 class="supplier-name"><?= htmlspecialchars($row['business_name']); ?></h3>
                        </div>
                        <div class="overlay">
                            <a href="../includes/view_stores.php?supplier_id=<?= $row['supplier_id']; ?>" class="view-btn">View Supplier</a>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>
            <!-- Scroll Buttons -->
            <div class="scroll-button scroll-button-left">
                <ion-icon name="arrow-back-circle-outline"></ion-icon>
            </div>
            <div class="scroll-button scroll-button-right">
                <ion-icon name="arrow-forward-circle-outline"></ion-icon>
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

