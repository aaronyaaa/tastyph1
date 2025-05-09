<?php
include("../database/session.php"); // Ensure user is authenticated and session is active
include("../database/config.php");
// Fetch user details from session
$userId = $_SESSION['id'] ?? ''; // Assuming 'id' is set in session
$userType = $_SESSION['usertype'] ?? '';
// Redirect if user is not a seller
if ($userType !== 'seller') {
    header("Location: ../index.php");
    exit();
}
// Fetch products for the seller, join with users table
$sql = "SELECT p.*, c.name AS category_name, u.first_name, u.last_name, u.email
        FROM products p
        LEFT JOIN categories c ON p.category_id = c.category_id
        INNER JOIN users u ON p.seller_id = u.id
        WHERE p.seller_id = ?"; // Join products and users table based on seller_id and user id
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $userId); // Use userId (seller's id)
$stmt->execute();
$result = $stmt->get_result();
// Fetch categories for the dropdown in the form
$categoriesQuery = "SELECT category_id, name FROM categories";
$categoriesResult = $conn->query($categoriesQuery);
if (!$categoriesResult) {
    die("Error fetching categories: " . $conn->error);
}
?>
<?php if (isset($message)): ?>
    <div class="alert alert-info"><?php echo htmlspecialchars($message); ?></div>
<?php endif; ?>
<?php
// Database connection (assumes $conn is defined earlier)

// Fetch data from the `apply_seller` table
$sql = "SELECT seller_id, id, business_name, description, address, business_permit, health_permit, application_date, status FROM apply_seller";
$result = $conn->query($sql);
?>

<?php
include('../database/config.php');
$user_id = $_SESSION['userId'] ?? '';
$user_type = $_SESSION['usertype'] ?? 'seller';
$sql = "SELECT day_of_week, open_time, close_time, is_available FROM business_hours WHERE user_id = ? AND business_type = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("is", $user_id, $user_type);
$stmt->execute();
$result = $stmt->get_result();
$business_hours = [];
while ($row = $result->fetch_assoc()) {
    $business_hours[$row['day_of_week']] = $row;
}
$sql = "SELECT * FROM recipes WHERE user_id = ? ORDER BY created_at DESC";
$stmt = $conn->prepare($sql);
if (!$stmt) {
    die("Query preparation failed (recipes): " . $conn->error);
}
$stmt->bind_param("i", $userId); // reuse existing $userId from session
$stmt->execute();
$recipe_result = $stmt->get_result();
$recipes = [];
while ($row = $recipe_result->fetch_assoc()) {
    $recipes[] = $row;
}
$stmt->close();
?>
<?php $userId = $_SESSION['userId'];

// Fetch recipes for current seller
$sql = "SELECT * FROM recipes WHERE user_id = ? ORDER BY created_at DESC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $userId);
$stmt->execute();
$recipe_result = $stmt->get_result();
$recipes = [];
while ($row = $recipe_result->fetch_assoc()) {
    $recipes[] = $row;
}
$stmt->close();
?>?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Store Dashboard</title>
    <link href="css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../css/store.css">
    <link href="../css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../css/nav.css">
    <link rel="stylesheet" href="../css/modal.css">
    <link rel="stylesheet" href="../css/recipe_cards.css">

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../js/bootstrap.bundle.min.js"></script>
</head>

<body>
    <?php include("modal2.php"); // Ensure user is authenticated and session is active
    ?>
    <?php
    // Fetch profile picture or use a default if it's not available
    $profilePic = !empty($row['profile_pics']) ? $row['profile_pics'] : 'path/to/default-profile.jpg';
    ?>
    <?php include("../includes/nav_" . strtolower($userType) . ".php"); ?>
    <?php include("../includes/modal.php"); ?>

    <!-- Profile Section -->
    <div id="content" class="mt-4 ">
        <?php
        // Check if the user is logged in and is a seller
        if (isset($_SESSION['usertype']) && $_SESSION['usertype'] === 'seller' && isset($_SESSION['userId'])):
            // Get the current user ID from the session
            $userId = $_SESSION['userId'];  // Use 'userId' as per your session dump
            include("../database/config.php");


            $sql = "SELECT u.*, a.business_name 
            FROM users u
            LEFT JOIN apply_seller a ON u.id = a.seller_id 
            WHERE u.id = ?";

            if ($stmt = $conn->prepare($sql)) {
                $stmt->bind_param("i", $userId);  // Bind the userId to the query
                $stmt->execute();
                $result = $stmt->get_result();

                if ($result && $result->num_rows > 0):
                    $row = $result->fetch_assoc();
        ?>
                    <div class="profile-container">
                        <img src="<?php echo htmlspecialchars($row['profile_pics']); ?>" alt="Profile Picture" class="rounded-circle" style="width: 100px; height: 100px;">
                        <div class="profile-info">
                            <h1><?php echo htmlspecialchars($row['business_name']); ?></h1>
                            <p><strong>Email:</strong> <?php echo htmlspecialchars($row['email']); ?></p>
                            <p><strong>Address:</strong> <?php echo htmlspecialchars($row['streetname'] . ', ' . $row['barangay'] . ', ' . $row['city'] . ', ' . $row['province'] . ', ' . $row['country']); ?></p>
                            <p><strong>Email:</strong> <?php echo htmlspecialchars($row['email']); ?></p>
                        </div>
                        <a href="#" class="btn btn-primary edit-profile-btn" data-bs-toggle="modal" data-bs-target="#editProfileModal">Edit Profile</a>
                        <a href="#" class="btn btn-secondary mt-2" data-bs-toggle="modal" data-bs-target="#businessHoursModal">
                            Manage Business Hours
                        </a>

                    </div>
                <?php else: ?>
                    <p class="text-center">No profile found for this user.</p>
            <?php endif;
            } else {
                echo "<p class='text-center'>Error fetching user data.</p>";
            }
            $conn->close();
        else: ?>
            <p class="text-center">Access denied. You are not authorized to view this page.</p>
        <?php endif; ?>
    </div>


    <div class="container mt-5">
        <h1 class="text-center">Product Inventory</h1>
        <!-- Category Button -->
        <button class="btn btn-primary my-3" data-bs-toggle="modal" data-bs-target="#addCategoryModal">Add Category</button>
        <!-- Button to trigger modal -->
        <a href="../includes/cook_turon.php" class="btn btn-primary my-3">
            View Ingredients Inventory
        </a>
        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addRecipeModal">
            + Add Recipe
        </button>
        <!-- Example View Button for each recipe -->

        <table class="table table-bordered text-center" id="productTable">
            <thead class="table-dark">
                <tr>
                    <th>Name</th>
                    <th>Description</th>
                    <th>Price</th>
                    <th>Quantity</th>
                    <th>Category ID</th>
                    <th>Image</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php
                // Check if user is logged in and is a seller
                if (isset($_SESSION['userId']) && isset($_SESSION['usertype']) && $_SESSION['usertype'] === 'seller') {
                    $userId = $_SESSION['userId']; // Get the user ID from the session

                    // Database connection
                    include("../database/config.php");

                    // Query to get products for the logged-in user (seller)
                    $sql = "SELECT * FROM products WHERE seller_id = ?"; // Assuming 'seller_id' relates products to sellers
                    if ($stmt = $conn->prepare($sql)) {
                        $stmt->bind_param("i", $userId); // Bind the logged-in user's ID
                        $stmt->execute();
                        $result = $stmt->get_result();

                        if ($result->num_rows > 0) {
                            while ($row = $result->fetch_assoc()) {
                                echo "<tr>
                                <td>{$row['Product_name']}</td>
                                <td>{$row['description']}</td>
                                <td>{$row['price']}</td>
                                <td>{$row['quantity']}</td>
                                <td>{$row['category_id']}</td>
                                <td><img src='{$row['image_url']}' alt='Product Image' width='50'></td>
                                <td id='action-{$row['product_id']}'>
                                    <!-- Edit button -->
                                    <button class='btn btn-warning btn-sm edit-product' 
                                        data-bs-toggle='modal' 
                                        data-bs-target='#editProductModal'
                                        data-id='{$row['product_id']}'
                                        data-name='" . htmlspecialchars($row["Product_name"]) . "'
                                        data-description='" . htmlspecialchars($row["description"]) . "'
                                        data-price='{$row["price"]}'
                                        data-quantity='{$row["quantity"]}'
                                        data-category='{$row["category_id"]}'
                                        data-image='{$row["image_url"]}'>Edit</button>

                                    <!-- Delete button -->
                                    <button class='btn btn-danger btn-sm delete-product' data-id='{$row['product_id']}'>Delete</button>
                                </td>
                            </tr>";
                            }
                        } else {
                            echo "<tr><td colspan='9'>No Products Found</td></tr>";
                        }
                        $stmt->close();
                    } else {
                        echo "<tr><td colspan='9'>Error retrieving products.</td></tr>";
                    }

                    $conn->close();
                } else {
                    echo "<tr><td colspan='9'>Access denied. You are not authorized to view these products.</td></tr>";
                }
                ?>
            </tbody>
        </table>


        <div class="container mt-5">
            <h2 class="text-center">My Recipes</h2>
            <div class="row">
                <?php foreach ($recipes as $row): ?>
                    <div class="col-md-4 mb-4">
                        <div class="card h-100">
                            <?php if (!empty($row['recipe_image'])): ?>
                                <img src="<?php echo htmlspecialchars($row['recipe_image']); ?>" class="card-img-top" alt="Recipe Image">
                            <?php endif; ?>
                            <div class="card-body">
                                <h5 class="card-title"><?php echo htmlspecialchars($row['title']); ?></h5>
                                <p><strong>Prep:</strong> <?php echo htmlspecialchars($row['prep_time']); ?> | <strong>Cook:</strong> <?php echo htmlspecialchars($row['cook_time']); ?></p>
                                <p><strong>Servings:</strong> <?php echo htmlspecialchars($row['servings']); ?></p>

                                <!-- Row 1: View Buttons -->
                                <div class="button-row d-flex gap-2 mb-2">
                                    <button type="button" class="btn btn-info btn-sm flex-fill" data-bs-toggle="modal" data-bs-target="#viewRecipeModal<?php echo $row['recipe_id']; ?>">
                                        View Recipe
                                    </button>

                                </div>

                                <!-- Row 2: Edit + Delete -->
                                <div class="button-row d-flex gap-2">
                                    <button type="button" class="btn btn-warning btn-sm flex-fill" data-bs-toggle="modal" data-bs-target="#editRecipeModal<?php echo $row['recipe_id']; ?>">
                                        Edit
                                    </button>

                                    <form method="POST" action="../helpers/delete_recipe.php" onsubmit="return confirm('Are you sure you want to delete this recipe?');" class="flex-fill">
                                        <input type="hidden" name="recipe_id" value="<?php echo $row['recipe_id']; ?>">
                                        <button type="submit" class="btn btn-danger btn-sm w-100">Delete</button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- View Recipe Modal with Tabs -->
                    <div class="modal fade" id="viewRecipeModal<?php echo $row['recipe_id']; ?>" tabindex="-1" aria-labelledby="viewRecipeModalLabel<?php echo $row['recipe_id']; ?>" aria-hidden="true">
                        <div class="modal-dialog modal-lg">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title" id="viewRecipeModalLabel<?php echo $row['recipe_id']; ?>">
                                        <?php echo htmlspecialchars($row['title']); ?>
                                    </h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                                <div class="modal-body">
                                    <ul class="nav nav-tabs" id="recipeTab<?php echo $row['recipe_id']; ?>" role="tablist">
                                        <li class="nav-item" role="presentation">
                                            <a class="nav-link active" id="ingredients-tab<?php echo $row['recipe_id']; ?>" data-bs-toggle="tab" href="#ingredients<?php echo $row['recipe_id']; ?>" role="tab" aria-controls="ingredients" aria-selected="true">Ingredients</a>
                                        </li>
                                        <li class="nav-item" role="presentation">
                                            <a class="nav-link" id="directions-tab<?php echo $row['recipe_id']; ?>" data-bs-toggle="tab" href="#directions<?php echo $row['recipe_id']; ?>" role="tab" aria-controls="directions" aria-selected="false">Directions</a>
                                        </li>
                                        <?php if (!empty($row['notes'])): ?>
                                            <li class="nav-item" role="presentation">
                                                <a class="nav-link" id="notes-tab<?php echo $row['recipe_id']; ?>" data-bs-toggle="tab" href="#notes<?php echo $row['recipe_id']; ?>" role="tab" aria-controls="notes" aria-selected="false">Notes</a>
                                            </li>
                                        <?php endif; ?>
                                    </ul>
                                    <div class="tab-content" id="recipeTabContent<?php echo $row['recipe_id']; ?>">
                                        <div class="tab-pane fade show active" id="ingredients<?php echo $row['recipe_id']; ?>" role="tabpanel" aria-labelledby="ingredients-tab<?php echo $row['recipe_id']; ?>">
                                            <ul>
                                                <?php
                                                // Assume $row['ingredients'] contains the ingredients string
                                                $ingredients = explode("\n", $row['ingredients']); // Split ingredients by newline

                                                foreach ($ingredients as $ingredient):
                                                    $ingredient = trim($ingredient); // Trim spaces for cleaner links
                                                    if (!empty($ingredient)): // Avoid empty ingredients (e.g., extra newlines)
                                                ?>
                                                        <li>
                                                            <!-- Use JavaScript to handle the click event -->
                                                            <a href="javascript:void(0);" class="ingredient-link" data-ingredient="<?php echo urlencode($ingredient); ?>">
                                                                <?php echo htmlspecialchars($ingredient); ?>
                                                            </a>
                                                        </li>
                                                    <?php endif; ?>
                                                <?php endforeach; ?>
                                            </ul>

                                        </div>
                                        <div class="tab-pane fade" id="directions<?php echo $row['recipe_id']; ?>" role="tabpanel" aria-labelledby="directions-tab<?php echo $row['recipe_id']; ?>">
                                            <p><?php echo nl2br(htmlspecialchars($row['directions'])); ?></p>
                                        </div>
                                        <?php if (!empty($row['notes'])): ?>
                                            <div class="tab-pane fade" id="notes<?php echo $row['recipe_id']; ?>" role="tabpanel" aria-labelledby="notes-tab<?php echo $row['recipe_id']; ?>">
                                                <p><?php echo nl2br(htmlspecialchars($row['notes'])); ?></p>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                </div>
                            </div>
                        </div>
                    </div>

                <?php endforeach; ?>
            </div>
        </div>

    </div>


    <!-- Ensure jQuery is loaded before Bootstrap -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>

    <!-- External JS -->
    <script src="../js/manage_product.js"></script>


</body>



</html>