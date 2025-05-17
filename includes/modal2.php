<div class="modal fade" id="editProfileModal" tabindex="-1" aria-labelledby="editProfileModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editSellerProfileModalLabel">Edit Store</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form method="post" action="../helpers/update_store_profile.php" enctype="multipart/form-data">
                    <div class="form-group mb-3">
                        <label for="profile_pics">Store Profile Picture</label>
                        <div>
                            <!-- Display current profile picture or default image -->
                            <img src="<?php echo !empty($row['profile_pics']) ? htmlspecialchars($row['profile_pics']) : 'path/to/default-profile.jpg'; ?>"
                                alt="Current Profile Picture" style="width: 100px; height: 100px; object-fit: cover;">
                        </div>
                        <input type="file" class="form-control" id="profile_pics" name="profile_pics" accept="image/*">
                    </div>
                    <div class="form-group mb-3">
                        <label for="business_name">Business Name</label>
                        <input type="text" class="form-control" id="business_name" name="business_name" value="<?php echo htmlspecialchars($row['business_name'] ?? ''); ?>" required>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-4">
                            <label for="streetname" class="form-label">Street Name</label>
                            <input type="text" class="form-control" id="streetname" name="streetname" value="<?php echo htmlspecialchars($streetname); ?>">
                        </div>
                        <div class="col-md-4">
                            <label for="barangay" class="form-label">Barangay</label>
                            <input type="text" class="form-control" id="barangay" name="barangay" value="<?php echo htmlspecialchars($barangay); ?>">
                        </div>
                        <div class="col-md-4">
                            <label for="province" class="form-label">Province</label>
                            <input type="text" class="form-control" id="province" name="province" value="<?php echo htmlspecialchars($province); ?>">
                        </div>
                    </div>
                    <div class="form-group mb-3">
                        <label for="description">Description</label>
                        <textarea class="form-control" id="description" name="description" rows="3" required><?php echo htmlspecialchars($row['description'] ?? ''); ?></textarea>
                    </div>
                    <button type="submit" class="btn btn-primary">Save Changes</button>
                </form>
            </div>
        </div>
    </div>
</div>


<!-- Edit Product Modal -->
<div class="modal fade" id="editProductModal" tabindex="-1" aria-labelledby="editProductModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form method="POST" action="../helpers/edit_product.php" enctype="multipart/form-data">
                <div class="modal-header">
                    <h5 class="modal-title" id="editProductModalLabel">Edit Product</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3">
                        <!-- Hidden product ID field -->
                        <input type="hidden" name="product_id" id="editProductId">

                        <div class="col-md-12">
                            <label for="editProductName" class="form-label">Product Name</label>
                            <input type="text" class="form-control" id="editProductName" name="Product_name" required>
                        </div>
                        <div class="col-md-12">
                            <label for="editDescription" class="form-label">Description</label>
                            <textarea class="form-control" id="editDescription" name="description" rows="3" required></textarea>
                        </div>
                        <div class="col-md-6">
                            <label for="editPrice" class="form-label">Price</label>
                            <div class="input-group">
                                <span class="input-group-text">₱</span>
                                <input type="number" class="form-control" id="editPrice" name="price" step="0.01" min="0" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label for="editQuantity" class="form-label">Quantity</label>
                            <input type="number" class="form-control" id="editQuantity" name="quantity" min="0" required>
                        </div>
                        <div class="col-md-12">
                            <label for="editCategory" class="form-label">Category</label>
                            <select class="form-select" id="editCategory" name="category_id" required>
                                <option value="">Select a category</option>
                                <?php
                                // Fetch categories
                                $categoriesQuery = "SELECT category_id, name FROM categories ORDER BY name";
                                $categoriesResult = $conn->query($categoriesQuery);

                                if ($categoriesResult && $categoriesResult->num_rows > 0) {
                                    while ($category = $categoriesResult->fetch_assoc()) {
                                        echo "<option value='" . htmlspecialchars($category['category_id']) . "'>" . 
                                             htmlspecialchars($category['name']) . "</option>";
                                    }
                                }
                                ?>
                            </select>
                        </div>
                        <div class="col-md-12">
                            <label for="editImage" class="form-label">Product Image</label>
                            <input type="file" class="form-control" id="editImage" name="image_url" accept="image/*">
                            <div id="editImagePreview" class="mt-2" style="display: none;">
                                <img src="" alt="Product Preview" class="img-thumbnail" style="max-height: 200px;">
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-2"></i>Update Product
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="editCategoryModal" tabindex="-1" aria-labelledby="editCategoryModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="editCategoryForm" method="POST" action="../helpers/edit_category.php">
                <div class="modal-header">
                    <h5 class="modal-title" id="editCategoryModalLabel">Edit Category</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" id="editCategoryId" name="category_id">
                    <div class="form-group mb-3">
                        <label for="editCategoryName" class="form-label">Category Name</label>
                        <input type="text" class="form-control" id="editCategoryName" name="category_name" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">Save Changes</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="shopInfoModal" tabindex="-1" aria-labelledby="shopInfoModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="shopInfoModalLabel">Shop Information</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <?php
                include("../database/config.php");

                // Ensure seller_id is correctly retrieved
                $seller_id = $_GET['seller_id'] ?? $_SESSION['userId'] ?? null;

                if (!$seller_id) {
                    echo "<p class='text-danger'>Invalid seller ID.</p>";
                    exit;
                }

                // Check user type (seller or supplier)
                $user_type = $_SESSION['usertype'] ?? '';

                // Fetch store details based on user type
                $sql = ($user_type === 'seller')
                    ? "SELECT u.first_name, u.last_name, u.email, s.business_name, s.address 
                       FROM users u
                       INNER JOIN apply_seller s ON u.id = s.seller_id
                       WHERE s.seller_id = ?"
                    : "SELECT u.first_name, u.last_name, u.email, sp.business_name, sp.address
                       FROM users u
                       INNER JOIN apply_supplier sp ON u.id = sp.supplier_id
                       WHERE sp.supplier_id = ?";

                // Execute query for store details
                if ($stmt = $conn->prepare($sql)) {
                    $stmt->bind_param("i", $seller_id);
                    $stmt->execute();
                    $store = $stmt->get_result()->fetch_assoc();
                    $stmt->close();
                }

                // Fetch business hours specific to this seller
                $hours_sql = "SELECT day_of_week, open_time, close_time, is_available 
                              FROM business_hours 
                              WHERE user_id = ? AND business_type = ?";
                if ($stmt = $conn->prepare($hours_sql)) {
                    $stmt->bind_param("is", $seller_id, $user_type);
                    $stmt->execute();
                    $hours_result = $stmt->get_result();

                    // Store business hours in an array
                    $business_hours = [];
                    while ($row = $hours_result->fetch_assoc()) {
                        $business_hours[$row['day_of_week']] = $row;
                    }
                    $stmt->close();
                }

                // Define days of the week for ordered display
                $days_of_week = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
                ?>

                <!-- Store Details -->
                <h3 class="fw-bold"><?= htmlspecialchars($store['business_name'] ?? 'N/A') ?></h3>
                <p class="mb-1"><strong>Owner:</strong> <?= htmlspecialchars($store['first_name'] . ' ' . $store['last_name']) ?></p>
                <p class="mb-1"><strong>Address:</strong> <?= htmlspecialchars($store['address'] ?? 'N/A') ?></p>

                <!-- Business Hours -->
                <div class="mt-3">
                    <h5><i class="bi bi-clock"></i> Business Hours</h5>
                    <ul class="list-group">
                        <?php
                        $has_hours = false;
                        foreach ($days_of_week as $day):
                            $hours = $business_hours[$day] ?? null;
                            if ($hours) {
                                $has_hours = true; ?>
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    <span><?= htmlspecialchars($day) ?></span>
                                    <?php if ($hours['is_available']): ?>
                                        <span class="text-success"><?= date("h:i A", strtotime($hours['open_time'])) ?> - <?= date("h:i A", strtotime($hours['close_time'])) ?></span>
                                    <?php else: ?>
                                        <span class="text-danger">Closed</span>
                                    <?php endif; ?>
                                </li>
                            <?php }
                        endforeach;

                        if (!$has_hours): ?>
                            <li class="list-group-item text-muted">Business hours not set.</li>
                        <?php endif; ?>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>



<div class="modal fade" id="businessHoursModal" tabindex="-1" aria-labelledby="businessHoursModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="businessHoursModalLabel">Business Hours</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="businessHoursForm" method="post" action="../helpers/update_business_hours.php">
                    <input type="hidden" name="user_id" value="<?= $_SESSION['userId'] ?>">
                    <input type="hidden" name="business_type" value="<?= $_SESSION['usertype'] ?>">

                    <div class="container">
                        <div class="row">
                            <?php
                            $days = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
                            foreach ($days as $day):
                                $open_time = $business_hours[$day]['open_time'] ?? '';
                                $close_time = $business_hours[$day]['close_time'] ?? '';
                                $is_available = isset($business_hours[$day]['is_available']) && $business_hours[$day]['is_available'] ? 1 : 0;
                                $checked = $is_available ? 'checked' : '';
                                $disabled = $is_available ? '' : 'disabled';
                                $hidden_class = $is_available ? '' : 'd-none';
                            ?>
                                <div class="col-md-6 mb-3">
                                    <div class="card p-3 shadow-sm">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <label class="form-label mb-0"><strong><?= $day ?></strong></label>
                                            <div class="form-check form-switch">
                                                <input class="form-check-input availability-toggle" type="checkbox" name="is_available[<?= $day ?>]" value="1" <?= $checked ?> data-target="timeInputs-<?= $day ?>">
                                                <input type="hidden" name="is_available[<?= $day ?>]" value="0">
                                            </div>
                                        </div>
                                        <div id="timeInputs-<?= $day ?>" class="row time-inputs <?= $hidden_class ?> mt-2">
                                            <div class="col-6">
                                                <label for="open_time_<?= $day ?>" class="small">From</label>
                                                <input type="time" class="form-control form-control-sm" name="open_time[<?= $day ?>]" value="<?= $open_time ?>" <?= $disabled ?>>
                                            </div>
                                            <div class="col-6">
                                                <label for="close_time_<?= $day ?>" class="small">To</label>
                                                <input type="time" class="form-control form-control-sm" name="close_time[<?= $day ?>]" value="<?= $close_time ?>" <?= $disabled ?>>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <div class="text-end mt-3">
                        <button type="submit" class="btn btn-primary px-4">Save Hours</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>


<div class="modal fade" id="preOrderModal" tabindex="-1" aria-labelledby="preOrderModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="preOrderModalLabel">Pre-Order Request</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="preOrderForm" action="../helpers/submit_request.php" method="POST">
                    <input type="hidden" name="seller_id" value="<?= $seller_id ?>">

                    <!-- Product Search Field -->
                    <div class="mb-3 position-relative">
                        <label for="product_name" class="form-label">Search Product</label>
                        <input type="hidden" name="seller_id" value="<?= $seller_id ?>">
                        <input type="text" class="form-control" id="product_name" name="product_name" placeholder="Start typing..." autocomplete="off">
                        <div id="product_suggestions" class="autocomplete-results"></div>

                    </div>

                    <div class="mb-3">
                        <label for="quantity" class="form-label">Quantity</label>
                        <input type="number" class="form-control" name="quantity" min="1" required>
                    </div>

                    <div class="mb-3">
                        <label for="note" class="form-label">Additional Notes</label>
                        <textarea
                            name="note"
                            id="note"
                            rows="3"
                            class="form-control"
                            placeholder="Add any special requests (optional)">
                        </textarea>
                    </div>

                    <button type="submit" class="btn btn-primary">Submit Request</button>
                </form>
            </div>
        </div>
    </div>
</div>



<!-- Availability Modal -->
<div class="modal fade" id="availabilityModal" tabindex="-1" aria-labelledby="availabilityModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="availabilityModalLabel">Product Inventory</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <table class="table table-bordered text-center">
                    <thead class="table-dark">
                        <tr>
                            <th>Name</th>
                            <th>Description</th>
                            <th>Price</th>
                            <th>Quantity</th>
                            <th>Category ID</th>
                            <th>Image</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $prod_sql = "SELECT * FROM products WHERE seller_id = ?";
                        $prod_stmt = $conn->prepare($prod_sql);
                        $prod_stmt->bind_param("i", $seller_id);
                        $prod_stmt->execute();
                        $products = $prod_stmt->get_result();

                        if ($products->num_rows > 0):
                            while ($prod = $products->fetch_assoc()): ?>
                                <tr>
                                    <td><?= htmlspecialchars($prod['Product_name']) ?></td>
                                    <td><?= htmlspecialchars($prod['description']) ?></td>
                                    <td><?= htmlspecialchars($prod['price']) ?></td>
                                    <td><?= htmlspecialchars($prod['quantity']) ?></td>
                                    <td><?= htmlspecialchars($prod['category_id']) ?></td>
                                    <td><img src="<?= htmlspecialchars($prod['image_url']) ?>" width="50"></td>
                                </tr>
                            <?php endwhile;
                        else: ?>
                            <tr><td colspan="8">No products found.</td></tr>
                        <?php endif;
                        $prod_stmt->close(); ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>


<!-- Add Recipe Modal -->
<div class="modal fade" id="addRecipeModal" tabindex="-1" aria-labelledby="addRecipeModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <form action="../helpers/save_recipe.php" method="POST" enctype="multipart/form-data">
      <div class="modal-content">
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title" id="addRecipeModalLabel">
                        <i class="fas fa-utensils me-2"></i>Add New Recipe
                    </h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>

        <div class="modal-body">
          <div class="mb-3">
            <label for="title" class="form-label">Recipe Title</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-heading"></i></span>
            <input type="text" class="form-control" name="title" required>
                        </div>
          </div>

          <div class="row mb-3">
                        <div class="col-md-4">
              <label for="servings" class="form-label">Servings</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-users"></i></span>
                                <input type="text" class="form-control" name="servings" placeholder="e.g., 4 servings">
                            </div>
            </div>
                        <div class="col-md-4">
              <label for="prep_time" class="form-label">Prep Time</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-clock"></i></span>
                                <input type="text" class="form-control" name="prep_time" placeholder="e.g., 30 mins">
                            </div>
            </div>
                        <div class="col-md-4">
              <label for="cook_time" class="form-label">Cook Time</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-fire"></i></span>
                                <input type="text" class="form-control" name="cook_time" placeholder="e.g., 1 hour">
                            </div>
            </div>
          </div>

          <div class="mb-3">
                        <label for="ingredients" class="form-label">Ingredients</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-list-ul"></i></span>
                            <textarea class="form-control" name="ingredients" rows="3" required 
                                    placeholder="Enter ingredients, separated by commas (e.g., 2 cups flour, 1 cup sugar)"></textarea>
                        </div>
                        <small class="text-muted">Separate ingredients with commas</small>
          </div>

          <div class="mb-3">
            <label for="directions" class="form-label">Directions</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-utensils"></i></span>
                            <textarea class="form-control" name="directions" rows="5" required 
                                    placeholder="Enter step-by-step cooking instructions"></textarea>
                        </div>
          </div>

          <div class="mb-3">
            <label for="notes" class="form-label">Notes</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-sticky-note"></i></span>
                            <textarea class="form-control" name="notes" rows="2" 
                                    placeholder="Optional: Add any additional notes or tips"></textarea>
                        </div>
          </div>

          <div class="mb-3">
            <label for="recipe_image" class="form-label">Recipe Image</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-image"></i></span>
            <input type="file" class="form-control" name="recipe_image" accept="image/*">
                        </div>
                        <div id="recipeImagePreview" class="mt-2" style="display: none;">
                            <img src="" alt="Recipe Preview" class="img-thumbnail" style="max-height: 200px;">
                        </div>
          </div>
        </div>

        <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-save me-2"></i>Save Recipe
                    </button>
        </div>
      </div>
    </form>
  </div>
</div>

<!-- Recipe Modals -->
<?php if (isset($recipes) && !empty($recipes)): ?>
    <?php foreach ($recipes as $recipe): ?>
<!-- View Recipe Modal -->
        <div class="modal fade" id="viewRecipeModal<?php echo $recipe['recipe_id']; ?>" tabindex="-1" aria-labelledby="viewRecipeModalLabel<?php echo $recipe['recipe_id']; ?>" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
                    <div class="modal-header bg-primary text-white">
                        <h5 class="modal-title" id="viewRecipeModalLabel<?php echo $recipe['recipe_id']; ?>">
                            <i class="fas fa-utensils me-2"></i><?php echo htmlspecialchars($recipe['title']); ?>
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
                        <button type="button" class="btn btn-warning" 
                                data-bs-toggle="modal" 
                                data-bs-target="#editRecipeModal<?php echo $recipe['recipe_id']; ?>">
                            <i class="fas fa-edit me-2"></i>Edit Recipe
                        </button>
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
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-heading"></i></span>
                                    <input type="text" name="title" class="form-control" value="<?php echo htmlspecialchars($recipe['title']); ?>" required>
                                </div>
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
                                <label class="form-label">Ingredients</label>
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
<?php endif; ?>

<!-- Add Category Modal -->
<div class="modal fade" id="addCategoryModal" tabindex="-1" aria-labelledby="addCategoryModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="addCategoryModalLabel">
                    <i class="fas fa-tags me-2"></i>Add New Category
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="../helpers/add_category.php" method="POST">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="categoryName" class="form-label">Category Name</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-tag"></i></span>
                            <input type="text" class="form-control" id="categoryName" name="category_name" required 
                                   placeholder="Enter category name">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-2"></i>Save Category
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Category List Modal -->
<div class="modal fade" id="categoryListModal" tabindex="-1" aria-labelledby="categoryListModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="categoryListModalLabel">
                    <i class="fas fa-list me-2"></i>Categories
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="list-group">
                    <?php
                    $categoriesQuery = "SELECT category_id, name FROM categories ORDER BY name";
                    $categoriesResult = $conn->query($categoriesQuery);
                    if ($categoriesResult && $categoriesResult->num_rows > 0) {
                        while ($category = $categoriesResult->fetch_assoc()) {
                            echo '<div class="list-group-item d-flex justify-content-between align-items-center">';
                            echo '<span>' . htmlspecialchars($category['name']) . '</span>';
                            echo '<div class="btn-group">';
                            echo '<button type="button" class="btn btn-sm btn-warning edit-category" 
                                    data-id="' . $category['category_id'] . '" 
                                    data-name="' . htmlspecialchars($category['name']) . '"
                                    data-bs-toggle="modal" 
                                    data-bs-target="#editCategoryModal">
                                    <i class="fas fa-edit"></i>
                                </button>';
                            echo '<button type="button" class="btn btn-sm btn-danger delete-category" 
                                    data-id="' . $category['category_id'] . '">
                                    <i class="fas fa-trash"></i>
                                </button>';
                            echo '</div>';
                            echo '</div>';
                        }
                    } else {
                        echo '<div class="list-group-item text-center text-muted">No categories found</div>';
                    }
                    ?>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addCategoryModal">
                    <i class="fas fa-plus me-2"></i>Add New Category
                </button>
            </div>
        </div>
    </div>
</div>




<!-- Add Ingredient Modal -->
<div class="modal fade" id="addIngredientModal" tabindex="-1" aria-labelledby="addIngredientModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title" id="addIngredientModalLabel">
                    <i class="fas fa-plus me-2"></i>Add New Ingredient
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="../helpers/add_ingredient.php" method="POST">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="ingredientName" class="form-label">Ingredient Name</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-box"></i></span>
                            <input type="text" class="form-control" id="ingredientName" name="ingredient_name" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="description" class="form-label">Description</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-align-left"></i></span>
                            <textarea class="form-control" id="description" name="description" rows="2"></textarea>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="quantity" class="form-label">Quantity</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-balance-scale"></i></span>
                                <input type="number" class="form-control" id="quantity" name="quantity" step="0.01" min="0" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label for="unitType" class="form-label">Unit Type</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-ruler"></i></span>
                                <select class="form-select" id="unitType" name="unit_type" required>
                                    <option value="">Select unit</option>
                                    <option value="kg">Kilogram (kg)</option>
                                    <option value="g">Gram (g)</option>
                                    <option value="l">Liter (L)</option>
                                    <option value="ml">Milliliter (ml)</option>
                                    <option value="pcs">Pieces (pcs)</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="price" class="form-label">Price</label>
                        <div class="input-group">
                            <span class="input-group-text">₱</span>
                            <input type="number" class="form-control" id="price" name="price" step="0.01" min="0" required>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-save me-2"></i>Save Ingredient
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Ingredient Modal -->
<div class="modal fade" id="editIngredientModal" tabindex="-1" aria-labelledby="editIngredientModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-warning">
                <h5 class="modal-title" id="editIngredientModalLabel">
                    <i class="fas fa-edit me-2"></i>Edit Ingredient
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="../helpers/edit_ingredient.php" method="POST">
                <div class="modal-body">
                    <input type="hidden" id="editIngredientId" name="ingredient_id">
                    <div class="mb-3">
                        <label for="editIngredientName" class="form-label">Ingredient Name</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-box"></i></span>
                            <input type="text" class="form-control" id="editIngredientName" name="ingredient_name" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="editDescription" class="form-label">Description</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-align-left"></i></span>
                            <textarea class="form-control" id="editDescription" name="description" rows="2"></textarea>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="editQuantity" class="form-label">Quantity</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-balance-scale"></i></span>
                                <input type="number" class="form-control" id="editQuantity" name="quantity" step="0.01" min="0" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label for="editUnitType" class="form-label">Unit Type</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-ruler"></i></span>
                                <select class="form-select" id="editUnitType" name="unit_type" required>
                                    <option value="">Select unit</option>
                                    <option value="kg">Kilogram (kg)</option>
                                    <option value="g">Gram (g)</option>
                                    <option value="l">Liter (L)</option>
                                    <option value="ml">Milliliter (ml)</option>
                                    <option value="pcs">Pieces (pcs)</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="editPrice" class="form-label">Price</label>
                        <div class="input-group">
                            <span class="input-group-text">₱</span>
                            <input type="number" class="form-control" id="editPrice" name="price" step="0.01" min="0" required>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-warning">
                        <i class="fas fa-save me-2"></i>Save Changes
                    </button>
                </div>
            </form>
        </div>
  </div>
</div>

