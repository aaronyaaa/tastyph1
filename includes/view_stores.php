<?php
session_start();
include('../database/config.php');
include("../database/session.php");

// Validate supplier_id
if (!isset($_GET['supplier_id']) || !is_numeric($_GET['supplier_id'])) {
    die("Invalid supplier ID.");
}

$supplier_id = intval($_GET['supplier_id']);

// Fetch supplier details
$store_sql = "SELECT * FROM apply_supplier WHERE supplier_id = ?";
$stmt = $conn->prepare($store_sql);
$stmt->bind_param("i", $supplier_id);
$stmt->execute();
$store_result = $stmt->get_result();

if ($store_result->num_rows === 0) {
    die("Supplier not found.");
}

$store = $store_result->fetch_assoc();
$business_name = htmlspecialchars($store['business_name']);
$description = htmlspecialchars($store['description']);
$address = htmlspecialchars($store['address']);
$profile_pics = !empty($store['profile_pics']) ? "../uploads/" . htmlspecialchars($store['profile_pics']) : "../assets/default-store.png";

// Fetch ingredients
// Fetch ingredients
$ingredient_stmt = $conn->prepare("SELECT ingredient_id, ingredient_name, price, quantity, image_url FROM ingredients WHERE supplier_id = ?");
$ingredient_stmt->bind_param("i", $supplier_id);
$ingredient_stmt->execute();
$ingredient_stmt->store_result();

$ingredient_stmt->bind_result($ingredient_id, $ingredient_name, $price, $quantity, $image_url);



$userType = $_SESSION['usertype'] ?? 'user';
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $business_name ?> - Ingredients</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../css/products.css">
    <link rel="stylesheet" href="../css/nav.css">
    <link rel="stylesheet" href="../css/modal.css">
    <link rel="stylesheet" href="../css/view_store.css">
</head>

<body>

    <?php include("../includes/nav_" . strtolower($userType) . ".php"); ?>
    <?php include("../includes/modal2.php")?>
    <?php include("floating.php")?>


    <div class="container mt-5">
        <!-- Supplier Profile Section -->
        <div class="store-profile">
            <img src="<?= $profile_pics ?>" alt="Supplier Profile">
            <div class="store-details">
                <h1><?= $business_name ?></h1>
                <p><strong>Description:</strong> <?= $description ?></p>
                <p><strong>Address:</strong> <?= $address ?></p>

                <!-- Buttons Row -->
                <div class="buttons-row">
                    <button type="button" class="store-info-btn" data-bs-toggle="modal" data-bs-target="#shopInfoModal">
                        Supplier Info
                    </button>
                </div>
            </div>
        </div>

        <!-- Ingredients Section -->
        <h2 class="mt-4">Ingredients Available</h2>
        <div class="row">
        <?php if ($ingredient_stmt->num_rows > 0): ?>
    <?php while ($ingredient_stmt->fetch()): ?>
        <div class="col-md-3 mb-3">
            <a href="ingredient_page.php?ingredient_id=<?= $ingredient_id; ?>" class="ingredient-link">
                <div class="card">
                    <img src="<?= htmlspecialchars($image_url); ?>" class="card-img-top" alt="<?= htmlspecialchars($ingredient_name); ?>">
                    <div class="card-body">
                        <h5 class="card-title"><?= htmlspecialchars($ingredient_name); ?></h5>
                        <p><strong>₱<?= number_format($price, 2); ?></strong></p>
                        <p><strong>Stock:</strong> <?= $quantity; ?></p>
                    </div>
                </div>
            </a>
        </div>
    <?php endwhile; ?>
<?php else: ?>
    <p class="text-muted">No ingredients available.</p>
<?php endif; ?>

        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>
