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
</head>
<body>
<?php include("../includes/nav_" . strtolower($userType) . ".php"); ?>
<?php include("../includes/modal3.php"); ?>

<div id="content" class="mt-4">
    <?php if ($supplier): ?>
        <div class="profile-container">
            <img src="<?= !empty($supplier['profile_pics']) ? htmlspecialchars($supplier['profile_pics']) : '../assets/default-profile.jpg'; ?>"
                 alt="Profile Picture" class="rounded-circle" style="width: 100px; height: 100px;">
            <div class="profile-info">
                <h1><?= htmlspecialchars($supplier['business_name']); ?></h1>
                <p><strong>Description:</strong> <?= htmlspecialchars($supplier['description']); ?></p>
                <p><strong>Address:</strong> <?= htmlspecialchars($supplier['address']); ?></p>
                <p><strong>Joined:</strong> <?= htmlspecialchars($supplier['application_date']); ?></p>
            </div>
            <a href="#" class="btn btn-primary edit-profile-btn" data-bs-toggle="modal" data-bs-target="#editProfileModal">Edit Profile</a>
        </div>
    <?php endif; ?>
</div>

<div class="container mt-5">
    <h1 class="text-center">Ingredients Inventory</h1>
    <button class="btn btn-primary my-3" data-bs-toggle="modal" data-bs-target="#addIngredientModal">+ Add Ingredient</button>

    <!-- INGREDIENT TABLE -->
    <div id="ingredientTable">
        <table class="table table-bordered text-center">
            <thead class="table-dark">
            <tr>
                <th>Name</th>
                <th>Description</th>
                <th>Price</th>
                <th>Quantity</th>
                <th>Created At</th>
                <th>Category</th>
                <th>Image</th>
                <th>Actions</th>
                <th>Variant</th>
            </tr>
            </thead>
            <tbody>
            <?php if ($ingredientsResult->num_rows > 0): ?>
                <?php while ($row = $ingredientsResult->fetch_assoc()): ?>
                    <tr>
                        <td>
                            <a href="#" onclick="showVariants(<?= $row['ingredient_id']; ?>, '<?= htmlspecialchars($row['ingredient_name']); ?>')">
                                <?= htmlspecialchars($row['ingredient_name']); ?>
                            </a>
                        </td>
                        <td><?= htmlspecialchars($row['description']); ?></td>
                        <td>₱<?= number_format($row['price'], 2); ?></td>
                        <td><?= $row['quantity']; ?></td>
                        <td><?= $row['created_at']; ?></td>
                        <td><?= htmlspecialchars($row['category_name']); ?></td>
                        <td><img src="<?= htmlspecialchars($row['image_url']); ?>" width="50"></td>
                        <td>
                            <button class="btn btn-warning btn-sm edit-ingredient"
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
                                Edit
                            </button>
                            <button class="btn btn-danger btn-sm delete-ingredient" data-id="<?= $row['ingredient_id']; ?>">Delete</button>
                        </td>
                        <td>
                            <button class="btn btn-success btn-sm" data-bs-toggle="modal" data-bs-target="#addVariantModal" onclick="setIngredientId(<?= $row['ingredient_id']; ?>)">Add Variant</button>
                        </td>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr><td colspan="9">No Ingredients Found</td></tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>

    <!-- VARIANT TABLE -->
    <div id="variantTable" style="display: none;">
        <h3 id="variantHeader"></h3>
        <button class="btn btn-secondary mb-3" onclick="backToIngredients()">← Back to Ingredients</button>
        <table class="table table-bordered text-center">
            <thead class="table-dark">
                <tr>
                    <th>Variant Name</th>
                    <th>Price</th>
                    <th>Quantity</th>
                    <th>Measurement</th>
                    <th>Image</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody id="variantBody">
                <!-- JS will populate this -->
            </tbody>
        </table>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../js/bootstrap.bundle.min.js"></script>
<script src="../js/manage_ingredient.js"></script>
</body>
</html>
