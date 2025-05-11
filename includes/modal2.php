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
        <div class="modal-header">
          <h5 class="modal-title" id="addRecipeModalLabel">Add New Recipe</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>

        <div class="modal-body">
          <div class="mb-3">
            <label for="title" class="form-label">Recipe Title</label>
            <input type="text" class="form-control" name="title" required>
          </div>

          <div class="row mb-3">
            <div class="col">
              <label for="servings" class="form-label">Servings</label>
              <input type="text" class="form-control" name="servings">
            </div>
            <div class="col">
              <label for="prep_time" class="form-label">Prep Time</label>
              <input type="text" class="form-control" name="prep_time">
            </div>
            <div class="col">
              <label for="cook_time" class="form-label">Cook Time</label>
              <input type="text" class="form-control" name="cook_time">
            </div>
          </div>

          <div class="mb-3">
            <label for="ingredients" class="form-label">Ingredients (separate by comma)</label>
            <textarea class="form-control" name="ingredients" rows="3" required></textarea>
          </div>

          <div class="mb-3">
            <label for="directions" class="form-label">Directions</label>
            <textarea class="form-control" name="directions" rows="5" required></textarea>
          </div>

          <div class="mb-3">
            <label for="notes" class="form-label">Notes</label>
            <textarea class="form-control" name="notes" rows="2"></textarea>
          </div>

          <div class="mb-3">
            <label for="recipe_image" class="form-label">Recipe Image</label>
            <input type="file" class="form-control" name="recipe_image" accept="image/*">
          </div>
        </div>

        <div class="modal-footer">
          <button type="submit" class="btn btn-success">Save Recipe</button>
        </div>
      </div>
    </form>
  </div>
</div>

<!-- View Recipe Modal -->
<div class="modal fade" id="viewRecipeModal<?= $row['recipe_id'] ?>" tabindex="-1" aria-labelledby="viewRecipeModalLabel<?= $row['recipe_id'] ?>" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
    
      <div class="modal-header">
        <h5 class="modal-title" id="viewRecipeModalLabel<?= $row['recipe_id'] ?>"><?= htmlspecialchars($row['title']) ?></h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>

      <div class="modal-body">
        <?php if (!empty($row['recipe_image'])): ?>
          <img src="<?= htmlspecialchars($row['recipe_image']) ?>" alt="Recipe Image" class="img-fluid rounded mb-3">
        <?php endif; ?>

        <p><strong>Servings:</strong> <?= htmlspecialchars($row['servings']) ?: 'N/A' ?></p>
        <p><strong>Prep Time:</strong> <?= htmlspecialchars($row['prep_time']) ?: 'N/A' ?></p>
        <p><strong>Cook Time:</strong> <?= htmlspecialchars($row['cook_time']) ?: 'N/A' ?></p>

        <h6>Ingredients:</h6>
        <ul>
          <?php
            $ingredients = explode(',', $row['ingredients']);
            foreach ($ingredients as $ingredient):
          ?>
            <li><?= htmlspecialchars(trim($ingredient)) ?></li>
          <?php endforeach; ?>
        </ul>

        <h6>Directions:</h6>
        <p><?= nl2br(htmlspecialchars($row['directions'])) ?></p>

        <?php if (!empty($row['notes'])): ?>
          <h6>Notes:</h6>
          <p><?= nl2br(htmlspecialchars($row['notes'])) ?></p>
        <?php endif; ?>
      </div>

      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
      </div>

    </div>
  </div>
</div>


<!-- Edit Recipe Modal -->
<div class="modal fade" id="editRecipeModal<?= $row['recipe_id'] ?>" tabindex="-1" aria-labelledby="editRecipeModalLabel<?= $row['recipe_id'] ?>" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <form method="POST" action="../helpers/edit_recipe.php" enctype="multipart/form-data">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="editRecipeModalLabel<?= $row['recipe_id'] ?>">Edit Recipe: <?= htmlspecialchars($row['title']) ?></h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>

        <div class="modal-body">
          <input type="hidden" name="recipe_id" value="<?= $row['recipe_id'] ?>">

          <div class="mb-3">
            <label class="form-label">Title</label>
            <input type="text" name="title" class="form-control" value="<?= htmlspecialchars($row['title']) ?>" required>
          </div>

          <div class="row mb-3">
            <div class="col">
              <label>Servings</label>
              <input type="text" name="servings" class="form-control" value="<?= htmlspecialchars($row['servings']) ?>">
            </div>
            <div class="col">
              <label>Prep Time</label>
              <input type="text" name="prep_time" class="form-control" value="<?= htmlspecialchars($row['prep_time']) ?>">
            </div>
            <div class="col">
              <label>Cook Time</label>
              <input type="text" name="cook_time" class="form-control" value="<?= htmlspecialchars($row['cook_time']) ?>">
            </div>
          </div>

          <div class="mb-3">
            <label>Ingredients (comma-separated)</label>
            <textarea name="ingredients" class="form-control" required><?= htmlspecialchars($row['ingredients']) ?></textarea>
          </div>

          <div class="mb-3">
            <label>Directions</label>
            <textarea name="directions" class="form-control" required><?= htmlspecialchars($row['directions']) ?></textarea>
          </div>

          <div class="mb-3">
            <label>Notes</label>
            <textarea name="notes" class="form-control"><?= htmlspecialchars($row['notes']) ?></textarea>
          </div>

          <div class="mb-3">
            <label>Change Image (optional)</label>
            <input type="file" name="recipe_image" class="form-control">
          </div>
        </div>

        <div class="modal-footer">
          <button type="submit" class="btn btn-success">Save Changes</button>
        </div>
      </div>
    </form>
  </div>
</div>

