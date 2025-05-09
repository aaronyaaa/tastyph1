<?php
session_start();
include("../database/config.php");

if (!isset($_SESSION['userId'])) {
    echo json_encode(["success" => false, "message" => "Unauthorized access."]);
    exit;
}


// Get the current user type from the session
$userType = $_SESSION['usertype'] ?? 'user'; // Default to 'user' if not set

// Query to get store applications from the 'apply_seller' table
$sql = "SELECT seller_id, id, business_name, description, address, business_permit, health_permit, application_date, status FROM apply_seller";
$result = $conn->query($sql);

$sql = "SELECT supplier_id, business_name, description, address, business_permit, health_permit, application_date, status FROM apply_supplier";
$result = $conn->query($sql);

$seller_id = $_SESSION['seller_id'] ?? $_GET['seller_id'] ?? 0;

$sql = "SELECT DISTINCT seller_id FROM products";
$stmt = $conn->prepare($sql);
$stmt->execute();
$result = $stmt->get_result();


$userId = $_SESSION['userId'];

// Recipe Requirements (per batch, 30 pcs per batch)
$recipe_requirements = [
    "malagkit" => 500,
    "ube" => 300,
    "condense milk" => 390,
    "evaporated milk" => 430,
    "cheese" => 430,
    "lumpia wrapper" => 30,
    "oil" => 1000
];

// Fetch available ingredients
$sql = "SELECT inv.ingredients_inventory_id, inv.ingredient_id, 
               TRIM(LOWER(inv.ingredient_name)) AS ingredient_name, 
               inv.quantity, ing.unit_type
        FROM ingredients_inventory inv
        INNER JOIN ingredients ing ON inv.ingredient_id = ing.ingredient_id
        WHERE inv.seller_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();

$available_ingredients = [];
$max_batches = PHP_INT_MAX; // Start with a high number

while ($row = $result->fetch_assoc()) {
    $ingredient_name = strtolower($row['ingredient_name']);
    
    if (isset($recipe_requirements[$ingredient_name])) {
        $quantity_value = $row['quantity'] * $recipe_requirements[$ingredient_name];
        $possible_batches = ($row['quantity'] > 0) ? intdiv($quantity_value, $recipe_requirements[$ingredient_name]) : 0;
        $max_batches = min($max_batches, $possible_batches);
    }

    $available_ingredients[$ingredient_name] = [
        'inventory_id' => $row['ingredients_inventory_id'],
        'ingredient_id' => $row['ingredient_id'],
        'quantity' => $row['quantity'],
        'unit_type' => $row['unit_type']
    ];
}

// Ensure max_batches is not negative or zero
$max_batches = max(0, $max_batches);

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['batch_count'])) {
    $batch_count = intval($_POST['batch_count']);
    if ($batch_count < 1 || $batch_count > $max_batches) {
        echo json_encode(["success" => false, "message" => "Invalid number of batches to cook."]);
        exit;
    }

    // Deduct ingredients from inventory
    foreach ($recipe_requirements as $ingredient => $needed) {
        $ingredient_lower = strtolower(trim($ingredient));
        if (isset($available_ingredients[$ingredient_lower])) {
            $inventory_id = $available_ingredients[$ingredient_lower]['inventory_id'];
            $required_quantity = $needed * $batch_count;

            // Deduct from Quantity Value
            $new_quantity_value = max(0, $available_ingredients[$ingredient_lower]['quantity_value'] - $required_quantity);

            // Deduct Available Quantity based on batch count
            $new_quantity = max(0, $available_ingredients[$ingredient_lower]['quantity'] - $batch_count);

            $update_sql = "UPDATE ingredients_inventory 
                           SET quantity = ?, quantity_value = ? 
                           WHERE ingredients_inventory_id = ? AND seller_id = ?";
            $update_stmt = $conn->prepare($update_sql);
            $update_stmt->bind_param("iiii", $new_quantity, $new_quantity_value, $inventory_id, $userId);
            $update_stmt->execute();
        }
    }

    // Insert cooked Turon into 'products'
    $product_name = "Ube Cheese Turon";
    $product_desc = "Delicious Ube Turon with cheese";
    $product_price = 10.00;
    $product_category = 1;
    $product_image = "Product Image";
    $total_quantity = $batch_count * 30;

    $check_product_sql = "SELECT product_id, quantity FROM products WHERE product_name = ? AND seller_id = ?";
    $check_stmt = $conn->prepare($check_product_sql);
    $check_stmt->bind_param("si", $product_name, $userId);
    $check_stmt->execute();
    $product_result = $check_stmt->get_result();

    if ($product_result->num_rows > 0) {
        $row = $product_result->fetch_assoc();
        $new_quantity = $row['quantity'] + $total_quantity;
        $update_product_sql = "UPDATE products SET quantity = ? WHERE product_id = ? AND seller_id = ?";
        $update_product_stmt = $conn->prepare($update_product_sql);
        $update_product_stmt->bind_param("iii", $new_quantity, $row['product_id'], $userId);
        $update_product_stmt->execute();
    } else {
        $insert_product_sql = "INSERT INTO products (product_name, description, price, quantity, created_at, category_id, image_url, seller_id)
                               VALUES (?, ?, ?, ?, NOW(), ?, ?, ?)";
        $insert_product_stmt = $conn->prepare($insert_product_sql);
        $insert_product_stmt->bind_param("ssdiisi", $product_name, $product_desc, $product_price, $total_quantity, $product_category, $product_image, $userId);
        $insert_product_stmt->execute();
    }

    echo json_encode(["success" => true, "message" => "Successfully cooked $total_quantity Ube Cheese Turon!"]);
    exit;
}
?>



<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cook Ube Cheese Turon</title>
    <link href="https://cdn.jsdelivr.net/npm/ionicons@5.5.2/dist/ionicons/ionicons.min.css" rel="stylesheet">
    <link href="../css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../css/nav.css">
    <link rel="stylesheet" href="../css/modal.css">

</head>
<body>
<?php include("../includes/nav_" . strtolower($userType) . ".php"); ?>

    <div class="container mt-5">
        
        <h1 class="text-center">Cook Ube Cheese Turon</h1>

        <div class="alert alert-info text-center">
            <strong>You can make up to: <span id="maxBatches"><?= $max_batches ?></span> batches (30 pcs per batch)</strong>
        </div>

        <form id="cookForm">
            <table class="table table-bordered text-center">
                <thead>
                    <tr>
                        <th>Ingredient</th>
                        <th>Available Quantity</th>
                        <th>Quantity Value</th>
                        <th>Unit Type</th>
                        <th>Quantity Needed per Batch</th>
                        <th>Quantity to Use</th>
                    </tr>
                </thead>
                <tbody id="ingredient-table">
                    <?php foreach ($recipe_requirements as $ingredient => $needed): 
                        $ingredient_lower = strtolower(trim($ingredient));
                        $available = $available_ingredients[$ingredient_lower]['quantity'] ?? 0;
                        $unit_type = $available_ingredients[$ingredient_lower]['unit_type'] ?? "pcs";
                        $quantity_value = $available * $needed; // Calculate quantity value
                    ?>
                        <tr data-ingredient="<?= $available_ingredients[$ingredient_lower]['ingredient_id'] ?>" data-needed="<?= $needed ?>">
                            <td><?= htmlspecialchars($ingredient) ?></td>
                            <td class="available-qty"> <?= htmlspecialchars($available) ?></td>
                            <td class="quantity-value"> <?= htmlspecialchars($quantity_value) ?></td>
                            <td><?= strtoupper($unit_type) ?></td>
                            <td><?= $needed ?></td>
                            <td>
                                <input type="number" 
                                       class="form-control quantity-use-input" 
                                       readonly 
                                       value="<?= $needed ?>">
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

            <label for="batch_count"><strong>Select How Many Batches to Cook:</strong></label>
            <input type="number" id="batch_count" name="batch_count" min="1" max="<?= $max_batches ?>" class="form-control" value="1" required>

            <button type="submit" id="cookButton" class="btn btn-success w-100 mt-3" <?= $max_batches > 0 ? "" : "disabled" ?>>Cook</button>
        </form>
    </div>

    <!-- Cooking Modal -->
    <div class="modal fade" id="cookingModal" tabindex="-1" aria-labelledby="cookingModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content text-center">
                <div class="modal-header">
                    <h5 class="modal-title" id="cookingModalLabel">COOKING...</h5>
                </div>
                <div class="modal-body">
                    <img src="image/cooking.gif" class="img-fluid" alt="Cooking...">
                    <p id="cookingStatus" class="mt-3">Please wait while we cook your Turon...</p>
                </div>
            </div>
        </div>
    </div>

   <!-- Ensure jQuery is loaded before Bootstrap -->
   <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../js/cook.js"></script>
</body>
</html>
