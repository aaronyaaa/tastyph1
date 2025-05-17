<?php
// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Include required files
require_once("../database/config.php");
require_once("../database/session.php");

// Ensure user is authenticated and session is active
if (!isset($_SESSION['userId']) || !isset($_SESSION['usertype'])) {
    header("Location: ../index.php");
    exit();
}

// Fetch user details from session
$userId = $_SESSION['userId'];
$userType = $_SESSION['usertype'];

// Redirect if user is not a seller
if ($userType !== 'seller') {
    header("Location: ../index.php");
    exit();
}

// Initialize user data
$userData = null;

// Fetch user and business details
$sql = "SELECT u.*, a.business_name, a.description, a.address 
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
    $stmt->close();
}

// Fetch products for the seller
$products = [];
$sql = "SELECT p.*, c.name AS category_name 
        FROM products p
        LEFT JOIN categories c ON p.category_id = c.category_id
        WHERE p.seller_id = ?";
$stmt = $conn->prepare($sql);
if ($stmt) {
    $stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $products[] = $row;
    }
    $stmt->close();
}

// Count products with stock
$availableProducts = 0;
$stockCountSql = "SELECT COUNT(*) as available_products FROM products WHERE seller_id = ? AND quantity > 0";
$stockCountStmt = $conn->prepare($stockCountSql);
if ($stockCountStmt) {
$stockCountStmt->bind_param("i", $userId);
$stockCountStmt->execute();
$stockCountResult = $stockCountStmt->get_result();
$availableProducts = $stockCountResult->fetch_assoc()['available_products'];
$stockCountStmt->close();
}

// Fetch business hours
$business_hours = [];
$hours_sql = "SELECT day_of_week, open_time, close_time, is_available 
              FROM business_hours 
              WHERE user_id = ? AND business_type = ?";
$hours_stmt = $conn->prepare($hours_sql);
if ($hours_stmt) {
    $hours_stmt->bind_param("is", $userId, $userType);
    $hours_stmt->execute();
    $hours_result = $hours_stmt->get_result();
    while ($row = $hours_result->fetch_assoc()) {
    $business_hours[$row['day_of_week']] = $row;
}
    $hours_stmt->close();
}

// Fetch recipes
$recipes = [];
$recipe_sql = "SELECT * FROM recipes WHERE user_id = ? ORDER BY created_at DESC";
$recipe_stmt = $conn->prepare($recipe_sql);
if ($recipe_stmt) {
    $recipe_stmt->bind_param("i", $userId);
    $recipe_stmt->execute();
    $recipe_result = $recipe_stmt->get_result();
while ($row = $recipe_result->fetch_assoc()) {
    $recipes[] = $row;
}
    $recipe_stmt->close();
}

// Fetch categories for dropdowns
$categories = [];
$categoriesQuery = "SELECT category_id, name FROM categories ORDER BY name";
$categoriesResult = $conn->query($categoriesQuery);
if ($categoriesResult) {
    while ($row = $categoriesResult->fetch_assoc()) {
        $categories[] = $row;
    }
}

// Fetch base ingredients inventory for current user (no variant)
$baseIngredients = [];
$sqlBase = "SELECT * FROM ingredients_inventory WHERE user_id = ? AND (variant_id IS NULL OR variant_id = 0) ORDER BY created_at DESC";
$stmtBase = $conn->prepare($sqlBase);
if ($stmtBase) {
    $stmtBase->bind_param("i", $userId);
    $stmtBase->execute();
    $resultBase = $stmtBase->get_result();
    while ($row = $resultBase->fetch_assoc()) {
        $baseIngredients[] = $row;
    }
    $stmtBase->close();
}

// Fetch ingredient variants inventory for current user
$ingredientVariants = [];
$sqlVariants = "SELECT ii.*, v.variant_name, i.ingredient_name 
                FROM ingredients_inventory ii 
                INNER JOIN ingredient_variants v ON ii.variant_id = v.variant_id 
                INNER JOIN ingredients i ON v.ingredient_id = i.ingredient_id 
                WHERE ii.user_id = ? AND ii.variant_id IS NOT NULL AND ii.variant_id != 0
                ORDER BY ii.created_at DESC";
$stmtVariants = $conn->prepare($sqlVariants);
if ($stmtVariants) {
    $stmtVariants->bind_param("i", $userId);
    $stmtVariants->execute();
    $resultVariants = $stmtVariants->get_result();
    while ($row = $resultVariants->fetch_assoc()) {
        $ingredientVariants[] = $row;
    }
    $stmtVariants->close();
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Store Dashboard</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="../css/store.css">
    <link rel="stylesheet" href="../css/nav.css">
    <link rel="stylesheet" href="../css/modal.css">
    <link rel="stylesheet" href="../css/recipe_cards.css">
</head>
<body>
    <?php
    // Include navigation and modals
    include("../includes/nav_" . strtolower($userType) . ".php");
    include("../includes/modal2.php");
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
                            <h3><?php echo $availableProducts; ?></h3>
                            <p>Available Products</p>
                        </div>
                        <div class="stat-card">
                            <h3><?php echo count($recipes); ?></h3>
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
                    <button class="btn btn-info" data-bs-toggle="modal" data-bs-target="#ingredientsInventoryModal">
    <i class="fas fa-box"></i> Ingredients Inventory
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
                    <button class="btn btn-secondary" data-bs-toggle="modal" data-bs-target="#addCategoryModal">
                        <i class="fas fa-tags"></i> Add Category
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
                    <?php if (!empty($products)): ?>
                        <?php foreach ($products as $product): ?>
                        <div class="product-card">
                                <img src="<?php echo htmlspecialchars($product['image_url']); ?>" 
                                     alt="<?php echo htmlspecialchars($product['Product_name']); ?>" 
                                     class="product-image">
                            <div class="product-info">
                                    <h5><?php echo htmlspecialchars($product['Product_name']); ?></h5>
                                    <p class="text-muted"><?php echo htmlspecialchars(substr($product['description'], 0, 100)) . '...'; ?></p>
                                <div class="d-flex justify-content-between align-items-center">
                                        <span class="h5 mb-0">₱<?php echo number_format($product['price'], 2); ?></span>
                                        <span class="badge bg-<?php echo $product['quantity'] > 0 ? 'success' : 'danger'; ?>">
                                            <?php echo $product['quantity']; ?> in stock
                                    </span>
                                </div>
                                <div class="mt-3 d-flex gap-2">
                                    <button class="btn btn-warning btn-sm flex-grow-1 edit-product" 
                                        data-bs-toggle="modal" 
                                        data-bs-target="#editProductModal"
                                                data-id="<?php echo $product['product_id']; ?>"
                                                data-name="<?php echo htmlspecialchars($product['Product_name']); ?>"
                                                data-description="<?php echo htmlspecialchars($product['description']); ?>"
                                                data-price="<?php echo $product['price']; ?>"
                                                data-quantity="<?php echo $product['quantity']; ?>"
                                                data-category="<?php echo $product['category_id']; ?>"
                                                data-image="<?php echo $product['image_url']; ?>">
                                        <i class="fas fa-edit"></i> Edit
                                    </button>
                                        <button class="btn btn-danger btn-sm delete-product" 
                                                data-id="<?php echo $product['product_id']; ?>">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="col-12 text-center">
                            <p class="text-muted">No products found. Add your first product!</p>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Recipes Section -->
                <div class="section-title mt-5">
                    <h2>Recipes</h2>
                    <button class="btn btn-add" data-bs-toggle="modal" data-bs-target="#addRecipeModal">
                        <i class="fas fa-plus"></i> Add Recipe
                    </button>
                </div>
                <div class="product-grid">
                    <?php if (!empty($recipes)): ?>
                    <?php foreach ($recipes as $recipe): ?>
                        <div class="product-card">
                            <?php if (!empty($recipe['recipe_image'])): ?>
                                    <img src="<?php echo htmlspecialchars($recipe['recipe_image']); ?>" 
                                         class="product-image" 
                                         alt="<?php echo htmlspecialchars($recipe['title']); ?>">
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
                                        <button class="btn btn-info btn-sm flex-grow-1" 
                                                data-bs-toggle="modal" 
                                                data-bs-target="#viewRecipeModal<?php echo $recipe['recipe_id']; ?>">
                                        <i class="fas fa-eye"></i> View
                                    </button>
                                        <button class="btn btn-warning btn-sm" 
                                                data-bs-toggle="modal" 
                                                data-bs-target="#editRecipeModal<?php echo $recipe['recipe_id']; ?>">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                        <form method="POST" 
                                              action="../helpers/delete_recipe.php" 
                                              onsubmit="return confirm('Are you sure you want to delete this recipe?');" 
                                              class="d-inline">
                                        <input type="hidden" name="recipe_id" value="<?php echo $recipe['recipe_id']; ?>">
                                        <button type="submit" class="btn btn-danger btn-sm">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                    <?php else: ?>
                        <div class="col-12 text-center">
                            <p class="text-muted">No recipes found. Add your first recipe!</p>
                                    </div>
                                <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../js/manage_product.js"></script>

    <!-- Include all modals from modal2.php -->
    <?php include("../includes/modal2.php"); ?>

    <script>
        // Initialize all tooltips
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });

        // Initialize all modals
        var modalTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="modal"]'));
        modalTriggerList.forEach(function (modalTriggerEl) {
            modalTriggerEl.addEventListener('click', function (event) {
                event.preventDefault();
                var targetModal = document.querySelector(this.getAttribute('data-bs-target'));
                if (targetModal) {
                    var modal = new bootstrap.Modal(targetModal);
                    modal.show();
                }
            });
        });

        // Handle image preview for add product modal
        document.getElementById('productImage')?.addEventListener('change', function(e) {
            const preview = document.getElementById('imagePreview');
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    preview.querySelector('img').src = e.target.result;
                    preview.style.display = 'block';
                }
                reader.readAsDataURL(file);
            }
        });

        // Handle image preview for edit product modal
        document.getElementById('editImage')?.addEventListener('change', function(e) {
            const preview = document.getElementById('editImagePreview');
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    preview.querySelector('img').src = e.target.result;
                    preview.style.display = 'block';
                }
                reader.readAsDataURL(file);
            }
    });
    </script>

    <!-- Ingredients Inventory Modal -->
<div class="modal fade" id="ingredientsInventoryModal" tabindex="-1" aria-labelledby="ingredientsInventoryModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-xl modal-dialog-scrollable">
    <div class="modal-content">
      <div class="modal-header bg-info text-white">
        <h5 class="modal-title" id="ingredientsInventoryModalLabel">
          <i class="fas fa-box me-2"></i>Your Purchased Ingredients Inventory
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">

        <ul class="nav nav-tabs mb-3" id="ingredientsTabs" role="tablist">
          <li class="nav-item" role="presentation">
            <button class="nav-link active" id="base-tab" data-bs-toggle="tab" data-bs-target="#baseIngredients" type="button" role="tab">
              <i class="fas fa-box me-2"></i>Base Ingredients
            </button>
          </li>
          <li class="nav-item" role="presentation">
            <button class="nav-link" id="variants-tab" data-bs-toggle="tab" data-bs-target="#ingredientVariants" type="button" role="tab">
              <i class="fas fa-layer-group me-2"></i>Ingredient Variants
            </button>
          </li>
        </ul>

        <div class="tab-content" id="ingredientsTabContent">
          <!-- Base Ingredients Tab -->
          <div class="tab-pane fade show active" id="baseIngredients" role="tabpanel" aria-labelledby="base-tab">
            <div class="table-responsive">
              <table class="table table-striped table-hover">
                <thead class="table-info">
                  <tr>
                    <th>Ingredient Name</th>
                    <th>Quantity</th>
                    <th>Quantity Value</th>
                    <th>Unit Type</th>
                    <th>Date Added</th>
                  </tr>
                </thead>
                <tbody>
                  <?php
                  $sqlBaseIngredients = "SELECT * FROM ingredients_inventory WHERE user_id = ? AND (variant_id IS NULL OR variant_id = 0) ORDER BY created_at DESC";
                  $stmtBase = $conn->prepare($sqlBaseIngredients);
                  if ($stmtBase) {
                      $stmtBase->bind_param("i", $userId);
                      $stmtBase->execute();
                      $resultBase = $stmtBase->get_result();
                      if ($resultBase->num_rows > 0) {
                          while ($row = $resultBase->fetch_assoc()) {
                              echo "<tr>";
                              echo "<td>" . htmlspecialchars($row['ingredient_name']) . "</td>";
                              echo "<td>" . htmlspecialchars($row['quantity']) . "</td>";
                              echo "<td>" . htmlspecialchars($row['quantity_value']) . "</td>";
                              echo "<td>" . htmlspecialchars($row['unit_type']) . "</td>";
                              echo "<td>" . date('M d, Y', strtotime($row['created_at'])) . "</td>";
                              echo "</tr>";
                          }
                      } else {
                          echo "<tr><td colspan='6' class='text-center'>No base ingredients found in your inventory.</td></tr>";
                      }
                      $stmtBase->close();
                  }
                  ?>
                </tbody>
              </table>
            </div>
          </div>

          <!-- Ingredient Variants Tab -->
          <div class="tab-pane fade" id="ingredientVariants" role="tabpanel" aria-labelledby="variants-tab">
            <div class="table-responsive">
              <table class="table table-striped table-hover">
                <thead class="table-info">
                  <tr>
                    <th>Base Ingredient</th>
                    <th>Variant Name</th>
                    <th>Quantity</th>
                    <th>Quantity Value</th>
                    <th>Unit Type</th>
                    <th>Supplier ID</th>
                    <th>Date Added</th>
                  </tr>
                </thead>
                <tbody>
                  <?php
                  $sqlVariants = "SELECT ii.*, v.variant_name, i.ingredient_name 
                                  FROM ingredients_inventory ii 
                                  INNER JOIN ingredient_variants v ON ii.variant_id = v.variant_id 
                                  INNER JOIN ingredients i ON v.ingredient_id = i.ingredient_id 
                                  WHERE ii.user_id = ? AND ii.variant_id IS NOT NULL AND ii.variant_id != 0
                                  ORDER BY ii.created_at DESC";
                  $stmtVariants = $conn->prepare($sqlVariants);
                  if ($stmtVariants) {
                      $stmtVariants->bind_param("i", $userId);
                      $stmtVariants->execute();
                      $resultVariants = $stmtVariants->get_result();
                      if ($resultVariants->num_rows > 0) {
                          while ($row = $resultVariants->fetch_assoc()) {
                              echo "<tr>";
                              echo "<td>" . htmlspecialchars($row['ingredient_name']) . "</td>";
                              echo "<td>" . htmlspecialchars($row['variant_name']) . "</td>";
                              echo "<td>" . htmlspecialchars($row['quantity']) . "</td>";
                              echo "<td>" . htmlspecialchars($row['quantity_value']) . "</td>";
                              echo "<td>" . htmlspecialchars($row['unit_type']) . "</td>";
                              echo "<td>" . htmlspecialchars($row['supplier_id']) . "</td>";
                              echo "<td>" . date('M d, Y', strtotime($row['created_at'])) . "</td>";
                              echo "</tr>";
                          }
                      } else {
                          echo "<tr><td colspan='7' class='text-center'>No ingredient variants found in your inventory.</td></tr>";
                      }
                      $stmtVariants->close();
                  }
                  ?>
                </tbody>
              </table>
            </div>
          </div>
        </div>

      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>

</body>
</html>
</html>