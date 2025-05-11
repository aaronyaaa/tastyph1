<?php
session_start();
include('../database/config.php');
include('../database/data_session.php');
include('../database/session.php');

$supplierId = $_SESSION['supplier_id'] ?? '';
$userType = $_SESSION['usertype'] ?? '';

if ($userType !== 'supplier') {
    header("Location: ../index.php");
    exit();
}

$sqlProfile = "SELECT business_name, description, address, profile_pics, application_date FROM apply_supplier WHERE supplier_id = ?";
$stmtProfile = $conn->prepare($sqlProfile);
$stmtProfile->bind_param("i", $userId);
$stmtProfile->execute();
$profileResult = $stmtProfile->get_result();
$supplier = $profileResult->fetch_assoc();

$sqlIngredients = "SELECT i.*, c.name AS category_name
                   FROM ingredients i
                   LEFT JOIN categories c ON i.category_id = c.category_id
                   WHERE i.supplier_id = ?";
$stmtIngredients = $conn->prepare($sqlIngredients);
$stmtIngredients->bind_param("i", $userId);
$stmtIngredients->execute();
$ingredientsResult = $stmtIngredients->get_result();

$categoriesResult = $conn->query("SELECT category_id, name FROM categories");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Supplier Dashboard - Manage Ingredients</title>
    <link href="../css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../css/store.css">
    <link rel="stylesheet" href="../css/nav.css">
    <link rel="stylesheet" href="../css/modal.css">
    <link rel="stylesheet" href="../css/manage_ingredient.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
<?php include("../includes/nav_" . strtolower($userType) . ".php"); ?>
<?php include("../includes/modal3.php"); ?>

<div class="store-container">
    <!-- Store Header -->
    <div class="store-header">
        <div class="row align-items-center">
            <div class="col-md-2">
                <img src="<?= !empty($supplier['profile_pics']) ? htmlspecialchars($supplier['profile_pics']) : '../assets/default-profile.jpg'; ?>" 
                     alt="Profile Picture" 
                     class="rounded-circle" 
                     style="width: 120px; height: 120px; object-fit: cover; border: 3px solid white;">
            </div>
            <div class="col-md-10">
                <h1><?= htmlspecialchars($supplier['business_name']); ?></h1>
                <p class="mb-2">
                    <i class="fas fa-map-marker-alt"></i> 
                    <?= htmlspecialchars($supplier['address']); ?>
                </p>
                <p class="mb-2">
                    <i class="fas fa-info-circle"></i> 
                    <?= htmlspecialchars($supplier['description']); ?>
                </p>
                <p class="mb-2">
                    <i class="fas fa-calendar"></i> 
                    Joined: <?= htmlspecialchars($supplier['application_date']); ?>
                </p>
                <div class="store-stats">
                    <div class="stat-card">
                        <h3><?= $ingredientsResult->num_rows; ?></h3>
                        <p>Total Ingredients</p>
                    </div>
                    <div class="stat-card">
                        <h3>4.8</h3>
                        <p>Rating</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="store-content">
        <!-- Sidebar -->
        <div class="store-sidebar">
            <div class="quick-actions">
                <h4>Quick Actions</h4>
                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addIngredientModal">
                    <i class="fas fa-plus"></i> Add Ingredient
                </button>
                <button class="btn btn-warning" data-bs-toggle="modal" data-bs-target="#editProfileModal">
                    <i class="fas fa-edit"></i> Edit Profile
                </button>
                <button class="btn btn-secondary" data-bs-toggle="modal" data-bs-target="#addCategoryModal">
                    <i class="fas fa-tags"></i> Add Category
                </button>
            </div>
        </div>

        <!-- Main Content -->
        <div class="store-main">
            <!-- Ingredients Section -->
            <div class="section-title">
                <h2>Ingredients Inventory</h2>
                <button class="btn btn-add" data-bs-toggle="modal" data-bs-target="#addIngredientModal">
                    <i class="fas fa-plus"></i> Add Ingredient
                </button>
            </div>

            <!-- INGREDIENT GRID -->
            <div id="ingredientGrid" class="product-grid">
                <?php if ($ingredientsResult->num_rows > 0): ?>
                    <?php while ($row = $ingredientsResult->fetch_assoc()): ?>
                        <div class="product-card">
                            <img src="<?= htmlspecialchars($row['image_url']); ?>" alt="<?= htmlspecialchars($row['ingredient_name']); ?>" class="product-image">
                            <div class="product-info">
                                <h5><?= htmlspecialchars($row['ingredient_name']); ?></h5>
                                <p class="text-muted"><?= htmlspecialchars(substr($row['description'], 0, 100)) . '...'; ?></p>
                                <div class="d-flex justify-content-between align-items-center">
                                    <span class="h5 mb-0">₱<?= number_format($row['price'], 2); ?></span>
                                    <span class="badge bg-<?= $row['quantity'] > 0 ? 'success' : 'danger'; ?>">
                                        <?= $row['quantity']; ?> in stock
                                    </span>
                                </div>
                                <p class="text-muted mb-2">
                                    <i class="fas fa-tag"></i> <?= htmlspecialchars($row['category_name']); ?>
                                </p>
                                <div class="mt-3 d-flex gap-2">
                                    <button class="btn btn-warning btn-sm flex-grow-1 edit-ingredient"
                                            data-bs-toggle="modal"
                                            data-bs-target="#editIngredientModal"
                                            data-id="<?= $row['ingredient_id']; ?>"
                                            data-name="<?= htmlspecialchars($row['ingredient_name']); ?>"
                                            data-description="<?= htmlspecialchars($row['description']); ?>"
                                            data-price="<?= $row['price']; ?>"
                                            data-quantity="<?= $row['quantity']; ?>"
                                            data-quantity-value="<?= $row['quantity_value']; ?>"
                                            data-unit-type="<?= $row['unit_type']; ?>"
                                            data-category="<?= $row['category_id']; ?>"
                                            data-image="<?= htmlspecialchars($row['image_url']); ?>">
                                        <i class="fas fa-edit"></i> Edit
                                    </button>
                                    <button class="btn btn-danger btn-sm delete-ingredient" data-id="<?= $row['ingredient_id']; ?>">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                                <div class="mt-2 d-flex gap-2">
                                    <button class="btn btn-info btn-sm flex-grow-1" 
                                            onclick="showVariants(<?= $row['ingredient_id']; ?>, '<?= htmlspecialchars($row['ingredient_name']); ?>')">
                                        <i class="fas fa-boxes"></i> View Variants
                                    </button>
                                    <button class="btn btn-primary btn-sm" 
                                            onclick="setIngredientId(<?= $row['ingredient_id']; ?>)" 
                                            data-bs-toggle="modal" 
                                            data-bs-target="#addVariantModal">
                                        <i class="fas fa-plus"></i> Add Variant
                                    </button>
                                </div>
                            </div>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <div class="col-12 text-center">
                        <p class="text-muted">No ingredients available.</p>
                    </div>
                <?php endif; ?>
            </div>

            <!-- VARIANTS SECTION -->
            <div id="variantGrid" class="mt-4" style="display: none;">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h3 id="variantHeader" class="mb-0">Variants</h3>
                    <button class="btn btn-secondary" onclick="backToIngredients()">
                        <i class="fas fa-arrow-left"></i> Back to Ingredients
                    </button>
                </div>
                <div class="row" id="variantBody">
                    <!-- Variants will be loaded here dynamically -->
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="../js/manage_ingredient.js"></script>
</body>
</html>
