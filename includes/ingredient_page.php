<?php
session_start();
include("../database/config.php"); // Database connection
include("../database/session.php");

// Get ingredient ID from URL
$ingredient_id = $_GET['ingredient_id'] ?? 0;

// Fetch ingredient details
$sqlIngredient = "SELECT i.*, s.business_name, s.profile_pics, s.address 
                  FROM ingredients i
                  JOIN apply_supplier s ON i.supplier_id = s.supplier_id
                  WHERE i.ingredient_id = ?";
$stmt = $conn->prepare($sqlIngredient);
if (!$stmt) {
    die("Error in SQL Query (ingredient details): " . $conn->error);
}
$stmt->bind_param("i", $ingredient_id);
$stmt->execute();
$ingredient = $stmt->get_result()->fetch_assoc();

if (!$ingredient) {
    die("Ingredient not found.");
}

$userType = $_SESSION['usertype'] ?? 'user';

// Generate dynamic quantity description (e.g., `1 can = 300g`, `1 pack = 30 pcs`)
$quantityDescription = "{$ingredient['quantity_value']} " . strtoupper($ingredient['unit_type']);
?>

<?php
$variant_sql = "SELECT * FROM ingredient_variants WHERE ingredient_id = ?";
$variant_stmt = $conn->prepare($variant_sql);
$variant_stmt->bind_param("i", $ingredient_id);
$variant_stmt->execute();
$variant_result = $variant_stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($ingredient['ingredient_name']); ?> - Ingredient Details</title>
    <link href="../css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../css/ingredient_page.css">
    <link rel="stylesheet" href="../css/nav.css">
    <link rel="stylesheet" href="../css/index.css">
</head>

<body>

    <!-- Navbar -->
    <?php include("../includes/nav_" . strtolower($userType) . ".php"); ?>

    <div class="container mt-4">
        <div class="row">
            <!-- Ingredient Image Section -->
            <div class="col-md-5">
                <div class="main-image">
                    <img id="mainIngredientImage" src="../uploads/<?= htmlspecialchars($ingredient['image_url']); ?>" class="img-fluid">
                </div>
            </div>

            <!-- Ingredient Details Section -->
            <div class="col-md-7">
                <h2 class="ingredient-title"><?= htmlspecialchars($ingredient['ingredient_name']); ?></h2>
                <p class="ingredient-price">₱<?= number_format($ingredient['price'], 2); ?></p>
                <p class="ingredient-description"><?= nl2br(htmlspecialchars($ingredient['description'])); ?></p>

                <!-- Stock Display -->
                <p id="stockDisplay">
                    <strong>Stock Available:</strong>
                    <?= number_format($ingredient['quantity']) . " " . strtoupper($ingredient['unit_type']); ?>
                    (<?= $quantityDescription; ?> per unit)
                </p>


                <?php if ($variant_result->num_rows > 0): ?>
                    <div class="mt-5">
                        <h4>Available Variants</h4>
                        <div class="d-flex flex-wrap gap-3" id="variantSelector">
                            <!-- Original ingredient card (default) -->
                            <div class="card variant-card p-2 active-variant" style="width: 120px; cursor: pointer;"
                                onclick='selectOriginalIngredient(this)'>
                                <img src="../uploads/<?= htmlspecialchars($ingredient['image_url']) ?>" class="img-fluid mb-2" style="height: 80px; object-fit: cover;">
                                <p class="text-center m-0 small"><?= htmlspecialchars($ingredient['ingredient_name']) ?></p>
                            </div>


                            <?php while ($variant = $variant_result->fetch_assoc()): ?>
                                <div class="card variant-card p-2" style="width: 120px; cursor: pointer;"
                                    onclick='selectVariant(this, <?= json_encode($variant) ?>)'>
                                    <img src="<?= htmlspecialchars($variant['image_url']) ?>" class="img-fluid mb-2" style="height: 80px; object-fit: cover;">
                                    <p class="text-center m-0 small"><?= htmlspecialchars($variant['variant_name']) ?></p>
                                </div>
                            <?php endwhile; ?>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- Add to Cart Form -->
                <form action="../cart/add_to_cart.php" method="POST">
                    <input type="hidden" name="item_id" value="<?= $ingredient_id; ?>">
                    <input type="hidden" name="item_type" value="ingredient">
                    <input type="hidden" name="price" id="selectedPrice" value="<?= $ingredient['price']; ?>">
                    <input type="hidden" name="variant_id" id="variantId" value=""> <!-- This will be updated via JS -->
                    <input type="hidden" id="maxStock" value="<?= $ingredient['quantity']; ?>">

                    <!-- Quantity selector -->
                    <div class="quantity-selector mt-3">
                        <label for="quantity">Quantity (<?= strtoupper($ingredient['unit_type']); ?>):</label>
                        <div class="input-group">
                            <button class="btn btn-outline-secondary" type="button" onclick="updateQuantity(-1)">-</button>
                            <input type="number" class="form-control text-center" name="quantity" id="quantity" value="1" min="1" max="<?= $ingredient['quantity']; ?>" oninput="validateQuantity()">
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

        <!-- Supplier Details -->
        <!-- Supplier Details -->
        <div class="mt-5 p-3 border">
            <h4>Supplier Information</h4>
            <div class="d-flex align-items-center">
                <img src="../uploads/<?= htmlspecialchars($ingredient['profile_pics'] ?? 'default-profile.png'); ?>"
                    class="rounded-circle me-3" width="50" height="50">
                <div>
                    <!-- Clickable Supplier Name -->
                    <h5>
                        <a href="../includes/view_stores.php?supplier_id=<?= $ingredient['supplier_id']; ?>"
                            class="text-decoration-none text-primary fw-bold">
                            <?= htmlspecialchars($ingredient['business_name']); ?>
                        </a>
                    </h5>
                    <p><?= htmlspecialchars($ingredient['address']); ?></p>
                </div>
            </div>
        </div>


    </div>
    <script>
        const originalData = {
            name: "<?= htmlspecialchars($ingredient['ingredient_name']); ?>",
            price: <?= $ingredient['price']; ?>,
            description: `<?= nl2br(htmlspecialchars($ingredient['description'])); ?>`.replace(/<br\s*\/?>/g, "\n"),
            image_url: "../uploads/<?= htmlspecialchars($ingredient['image_url']); ?>",
            quantity: <?= $ingredient['quantity']; ?>,
            quantity_value: <?= $ingredient['quantity_value']; ?>,
            unit_type: "<?= $ingredient['unit_type']; ?>"
        };
    </script>
    <script src="../js/ingredient_page.js"></script>

    <script src="../js/bootstrap.bundle.min.js"></script>
    <script src="../js/ingredient_page.js"></script>
    <script src="../js/add_to_cart.js"></script>

</body>

</html>