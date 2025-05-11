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
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../css/store.css">
    <link rel="stylesheet" href="../css/nav.css">
    <link rel="stylesheet" href="../css/modal.css">
    <link rel="stylesheet" href="../css/recipe_cards.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

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

<?php
// Initialize user data
$userData = null;
if (isset($_SESSION['usertype']) && $_SESSION['usertype'] === 'seller' && isset($_SESSION['userId'])) {
    $userId = $_SESSION['userId'];
    include("../database/config.php");

    $sql = "SELECT u.*, a.business_name 
            FROM users u
            LEFT JOIN apply_seller a ON u.id = a.seller_id 
            WHERE u.id = ?";

    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result && $result->num_rows > 0) {
            $userData = $result->fetch_assoc();
        }
    }
}
?>

    <div class="store-container">
        <!-- Store Header -->
        <div class="store-header">
            <div class="row align-items-center">
                <div class="col-md-2">
                    <img src="<?php echo !empty($userData['profile_pics']) ? htmlspecialchars($userData['profile_pics']) : '../images/default-profile.jpg'; ?>" 
                         alt="Store Logo" 
                         class="rounded-circle" 
                         style="width: 120px; height: 120px; object-fit: cover; border: 3px solid white;">
                </div>
                <div class="col-md-10">
                    <h1><?php echo !empty($userData['business_name']) ? htmlspecialchars($userData['business_name']) : 'My Store'; ?></h1>
                    <p class="mb-2">
                        <i class="fas fa-map-marker-alt"></i> 
                        <?php 
                        if (!empty($userData)) {
                            echo htmlspecialchars(
                                implode(', ', array_filter([
                                    $userData['streetname'] ?? '',
                                    $userData['barangay'] ?? '',
                                    $userData['city'] ?? ''
                                ]))
                            );
                        } else {
                            echo 'Address not set';
                        }
                        ?>
                    </p>
                    <p class="mb-2">
                        <i class="fas fa-envelope"></i> 
                        <?php echo !empty($userData['email']) ? htmlspecialchars($userData['email']) : 'Email not set'; ?>
                    </p>
                    <div class="store-stats">
                        <div class="stat-card">
                            <h3><?php echo isset($result) ? $result->num_rows : 0; ?></h3>
                            <p>Products</p>
                        </div>
                        <div class="stat-card">
                            <h3><?php echo isset($recipes) ? count($recipes) : 0; ?></h3>
                            <p>Recipes</p>
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
                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addProductModal">
                        <i class="fas fa-plus"></i> Add Product
                    </button>
                    <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#addRecipeModal">
                        <i class="fas fa-utensils"></i> Add Recipe
                    </button>
                    <button class="btn btn-info" data-bs-toggle="modal" data-bs-target="#businessHoursModal">
                        <i class="fas fa-clock"></i> Business Hours
                    </button>
                    <button class="btn btn-warning" data-bs-toggle="modal" data-bs-target="#editProfileModal">
                        <i class="fas fa-edit"></i> Edit Profile
                    </button>
                </div>

                <div class="store-hours">
                    <h4 class="mb-3 d-flex align-items-center">
                        <i class="fas fa-clock me-2"></i>
                        Business Hours
                    </h4>
                    <div class="business-hours-grid">
                        <?php 
                        $days = [
                            'sunday' => ['icon' => 'fas fa-sun'],
                            'monday' => ['icon' => 'fas fa-moon'],
                            'tuesday' => ['icon' => 'fas fa-moon'],
                            'wednesday' => ['icon' => 'fas fa-moon'],
                            'thursday' => ['icon' => 'fas fa-moon'],
                            'friday' => ['icon' => 'fas fa-moon'],
                            'saturday' => ['icon' => 'fas fa-sun']
                        ];
                        
                        foreach ($days as $day => $info): 
                            $hours = $business_hours[$day] ?? null;
                            $isOpen = $hours && $hours['is_available'];
                            $openTime = $isOpen ? date('h:i A', strtotime($hours['open_time'])) : null;
                            $closeTime = $isOpen ? date('h:i A', strtotime($hours['close_time'])) : null;
                        ?>
                            <div class="hours-card <?php echo $isOpen ? 'open' : 'closed'; ?>">
                                <div class="day-name">
                                    <i class="<?php echo $info['icon']; ?>"></i>
                                    <?php echo ucfirst($day); ?>
                                </div>
                                <div class="hours-time">
                                    <?php if ($isOpen): ?>
                                        <div class="d-flex flex-column">
                                            <span><?php echo $openTime . ' - ' . $closeTime; ?></span>
                                            <span class="hours-status status-open mt-1">Open</span>
                                        </div>
                                    <?php else: ?>
                                        <div class="d-flex flex-column">
                                            <span>Closed</span>
                                            <span class="hours-status status-closed mt-1">Closed</span>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>

            <!-- Main Content -->
            <div class="store-main">
                <!-- Products Section -->
                <div class="section-title">
                    <h2>Products</h2>
                    <button class="btn btn-add" data-bs-toggle="modal" data-bs-target="#addProductModal">
                        <i class="fas fa-plus"></i> Add Product
                    </button>
                </div>
                <div class="product-grid">
                    <?php
                    if (isset($_SESSION['userId']) && isset($_SESSION['usertype']) && $_SESSION['usertype'] === 'seller') {
                        $userId = $_SESSION['userId'];
                        include("../database/config.php");
                        $sql = "SELECT * FROM products WHERE seller_id = ?";
                        if ($stmt = $conn->prepare($sql)) {
                            $stmt->bind_param("i", $userId);
                            $stmt->execute();
                            $result = $stmt->get_result();

                            while ($row = $result->fetch_assoc()):
                    ?>
                        <div class="product-card">
                            <img src="<?php echo htmlspecialchars($row['image_url']); ?>" alt="<?php echo htmlspecialchars($row['Product_name']); ?>" class="product-image">
                            <div class="product-info">
                                <h5><?php echo htmlspecialchars($row['Product_name']); ?></h5>
                                <p class="text-muted"><?php echo htmlspecialchars(substr($row['description'], 0, 100)) . '...'; ?></p>
                                <div class="d-flex justify-content-between align-items-center">
                                    <span class="h5 mb-0">₱<?php echo number_format($row['price'], 2); ?></span>
                                    <span class="badge bg-<?php echo $row['quantity'] > 0 ? 'success' : 'danger'; ?>">
                                        <?php echo $row['quantity']; ?> in stock
                                    </span>
                                </div>
                                <div class="mt-3 d-flex gap-2">
                                    <button class="btn btn-warning btn-sm flex-grow-1 edit-product" 
                                        data-bs-toggle="modal" 
                                        data-bs-target="#editProductModal"
                                        data-id="<?php echo $row['product_id']; ?>"
                                        data-name="<?php echo htmlspecialchars($row['Product_name']); ?>"
                                        data-description="<?php echo htmlspecialchars($row['description']); ?>"
                                        data-price="<?php echo $row['price']; ?>"
                                        data-quantity="<?php echo $row['quantity']; ?>"
                                        data-category="<?php echo $row['category_id']; ?>"
                                        data-image="<?php echo $row['image_url']; ?>">
                                        <i class="fas fa-edit"></i> Edit
                                    </button>
                                    <button class="btn btn-danger btn-sm delete-product" data-id="<?php echo $row['product_id']; ?>">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    <?php 
                            endwhile;
                        }
                    }
                    ?>
                </div>

                <!-- Recipes Section -->
                <div class="section-title mt-5">
                    <h2>Recipes</h2>
                    <button class="btn btn-add" data-bs-toggle="modal" data-bs-target="#addRecipeModal">
                        <i class="fas fa-plus"></i> Add Recipe
                    </button>
                </div>
                <div class="product-grid">
                    <?php foreach ($recipes as $recipe): ?>
                        <div class="product-card">
                            <?php if (!empty($recipe['recipe_image'])): ?>
                                <img src="<?php echo htmlspecialchars($recipe['recipe_image']); ?>" class="product-image" alt="<?php echo htmlspecialchars($recipe['title']); ?>">
                            <?php endif; ?>
                            <div class="product-info">
                                <h5><?php echo htmlspecialchars($recipe['title']); ?></h5>
                                <p class="text-muted">
                                    <i class="fas fa-clock"></i> Prep: <?php echo htmlspecialchars($recipe['prep_time']); ?> | 
                                    Cook: <?php echo htmlspecialchars($recipe['cook_time']); ?>
                                </p>
                                <p class="text-muted">
                                    <i class="fas fa-users"></i> Serves: <?php echo htmlspecialchars($recipe['servings']); ?>
                                </p>
                                <div class="mt-3 d-flex gap-2">
                                    <button class="btn btn-info btn-sm flex-grow-1" data-bs-toggle="modal" data-bs-target="#viewRecipeModal<?php echo $recipe['recipe_id']; ?>">
                                        <i class="fas fa-eye"></i> View
                                    </button>
                                    <button class="btn btn-warning btn-sm" data-bs-toggle="modal" data-bs-target="#editRecipeModal<?php echo $recipe['recipe_id']; ?>">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <form method="POST" action="../helpers/delete_recipe.php" onsubmit="return confirm('Are you sure you want to delete this recipe?');" class="d-inline">
                                        <input type="hidden" name="recipe_id" value="<?php echo $recipe['recipe_id']; ?>">
                                        <button type="submit" class="btn btn-danger btn-sm">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Recipe Modals -->
    <?php foreach ($recipes as $recipe): ?>
        <!-- View Recipe Modal -->
        <div class="modal fade" id="viewRecipeModal<?php echo $recipe['recipe_id']; ?>" tabindex="-1" aria-labelledby="viewRecipeModalLabel<?php echo $recipe['recipe_id']; ?>" aria-hidden="true">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="viewRecipeModalLabel<?php echo $recipe['recipe_id']; ?>">
                            <?php echo htmlspecialchars($recipe['title']); ?>
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>

                    <div class="modal-body">
                        <?php if (!empty($recipe['recipe_image'])): ?>
                            <img src="<?php echo htmlspecialchars($recipe['recipe_image']); ?>" 
                                 alt="Recipe Image" 
                                 class="img-fluid rounded mb-3" 
                                 style="max-height: 300px; width: 100%; object-fit: cover;">
                        <?php endif; ?>

                        <div class="row mb-4">
                            <div class="col-md-4">
                                <div class="card bg-light">
                                    <div class="card-body text-center">
                                        <i class="fas fa-users mb-2"></i>
                                        <h6 class="card-title">Servings</h6>
                                        <p class="card-text"><?php echo htmlspecialchars($recipe['servings']) ?: 'N/A'; ?></p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="card bg-light">
                                    <div class="card-body text-center">
                                        <i class="fas fa-clock mb-2"></i>
                                        <h6 class="card-title">Prep Time</h6>
                                        <p class="card-text"><?php echo htmlspecialchars($recipe['prep_time']) ?: 'N/A'; ?></p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="card bg-light">
                                    <div class="card-body text-center">
                                        <i class="fas fa-fire mb-2"></i>
                                        <h6 class="card-title">Cook Time</h6>
                                        <p class="card-text"><?php echo htmlspecialchars($recipe['cook_time']) ?: 'N/A'; ?></p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="card mb-3">
                                    <div class="card-header bg-primary text-white">
                                        <h6 class="mb-0"><i class="fas fa-list-ul me-2"></i>Ingredients</h6>
                                    </div>
                                    <div class="card-body">
                                        <ul class="list-group list-group-flush">
                                            <?php
                                            $ingredients = explode(',', $recipe['ingredients']);
                                            foreach ($ingredients as $ingredient):
                                                $ingredient = trim($ingredient);
                                                if (!empty($ingredient)):
                                            ?>
                                                <li class="list-group-item">
                                                    <i class="fas fa-check-circle text-success me-2"></i>
                                                    <?php echo htmlspecialchars($ingredient); ?>
                                                </li>
                                            <?php 
                                                endif;
                                            endforeach; 
                                            ?>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="card mb-3">
                                    <div class="card-header bg-primary text-white">
                                        <h6 class="mb-0"><i class="fas fa-utensils me-2"></i>Directions</h6>
                                    </div>
                                    <div class="card-body">
                                        <p class="card-text"><?php echo nl2br(htmlspecialchars($recipe['directions'])); ?></p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <?php if (!empty($recipe['notes'])): ?>
                            <div class="card mt-3">
                                <div class="card-header bg-info text-white">
                                    <h6 class="mb-0"><i class="fas fa-sticky-note me-2"></i>Notes</h6>
                                </div>
                                <div class="card-body">
                                    <p class="card-text"><?php echo nl2br(htmlspecialchars($recipe['notes'])); ?></p>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Edit Recipe Modal -->
        <div class="modal fade" id="editRecipeModal<?php echo $recipe['recipe_id']; ?>" tabindex="-1" aria-labelledby="editRecipeModalLabel<?php echo $recipe['recipe_id']; ?>" aria-hidden="true">
            <div class="modal-dialog modal-lg">
                <form method="POST" action="../helpers/edit_recipe.php" enctype="multipart/form-data">
                    <div class="modal-content">
                        <div class="modal-header bg-warning">
                            <h5 class="modal-title" id="editRecipeModalLabel<?php echo $recipe['recipe_id']; ?>">
                                <i class="fas fa-edit me-2"></i>Edit Recipe: <?php echo htmlspecialchars($recipe['title']); ?>
                            </h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>

                        <div class="modal-body">
                            <input type="hidden" name="recipe_id" value="<?php echo $recipe['recipe_id']; ?>">

                            <div class="mb-3">
                                <label class="form-label">Title</label>
                                <input type="text" name="title" class="form-control" value="<?php echo htmlspecialchars($recipe['title']); ?>" required>
                            </div>

                            <div class="row mb-3">
                                <div class="col-md-4">
                                    <label class="form-label">Servings</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="fas fa-users"></i></span>
                                        <input type="text" name="servings" class="form-control" value="<?php echo htmlspecialchars($recipe['servings']); ?>">
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Prep Time</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="fas fa-clock"></i></span>
                                        <input type="text" name="prep_time" class="form-control" value="<?php echo htmlspecialchars($recipe['prep_time']); ?>">
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Cook Time</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="fas fa-fire"></i></span>
                                        <input type="text" name="cook_time" class="form-control" value="<?php echo htmlspecialchars($recipe['cook_time']); ?>">
                                    </div>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Ingredients (comma-separated)</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-list-ul"></i></span>
                                    <textarea name="ingredients" class="form-control" rows="3" required><?php echo htmlspecialchars($recipe['ingredients']); ?></textarea>
                                </div>
                                <small class="text-muted">Separate ingredients with commas</small>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Directions</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-utensils"></i></span>
                                    <textarea name="directions" class="form-control" rows="5" required><?php echo htmlspecialchars($recipe['directions']); ?></textarea>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Notes</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-sticky-note"></i></span>
                                    <textarea name="notes" class="form-control" rows="3"><?php echo htmlspecialchars($recipe['notes']); ?></textarea>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Change Image (optional)</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-image"></i></span>
                                    <input type="file" name="recipe_image" class="form-control" accept="image/*">
                                </div>
                                <?php if (!empty($recipe['recipe_image'])): ?>
                                    <div class="mt-2">
                                        <small class="text-muted">Current image:</small>
                                        <img src="<?php echo htmlspecialchars($recipe['recipe_image']); ?>" 
                                             alt="Current Recipe Image" 
                                             class="img-thumbnail mt-1" 
                                             style="max-height: 100px;">
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>

                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-success">
                                <i class="fas fa-save me-2"></i>Save Changes
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    <?php endforeach; ?>

    <!-- Scripts -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../js/manage_product.js"></script>

</body>

</html>