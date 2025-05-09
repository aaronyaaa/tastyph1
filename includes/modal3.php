<div class="modal fade" id="editProfileModal" tabindex="-1" aria-labelledby="editProfileModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editProfileModalLabel">Edit Supplier Profile</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form method="post" action="../helpers/update_supplier_store.php" enctype="multipart/form-data">
                    <div class="form-group mb-3">
                        <label for="profile_picture">Profile Picture</label>
                        <div>
                            <img src="<?php echo !empty($row['profile_pics']) ? htmlspecialchars($row['profile_pics']) : '../assets/default-profile.jpg'; ?>" 
                                 alt="Current Profile Picture" style="width: 100px; height: 100px; object-fit: cover;">
                        </div>
                        <input type="file" class="form-control" id="profile_picture" name="profile_pics" accept="image/*">
                    </div>
                    <div class="form-group mb-3">
                        <label for="business_name">Business Name</label>
                        <input type="text" class="form-control" id="business_name" name="business_name" value="<?php echo htmlspecialchars($row['business_name'] ?? ''); ?>" required>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-4">
                            <label for="streetname" class="form-label">Street Name</label>
                            <input type="text" class="form-control" id="streetname" name="streetname" value="<?php echo htmlspecialchars($streetname ?? ''); ?>">
                        </div>
                        <div class="col-md-4">
                            <label for="barangay" class="form-label">Barangay</label>
                            <input type="text" class="form-control" id="barangay" name="barangay" value="<?php echo htmlspecialchars($barangay ?? ''); ?>">
                        </div>
                        <div class="col-md-4">
                            <label for="province" class="form-label">Province</label>
                            <input type="text" class="form-control" id="province" name="province" value="<?php echo htmlspecialchars($province ?? ''); ?>">
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







<!-- Add Category Modal -->
<div class="modal fade" id="addCategoryModal" tabindex="-1" aria-labelledby="addCategoryModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="addCategoryForm" method="POST" action="add_category.php">
                <div class="modal-header">
                    <h5 class="modal-title" id="addCategoryModalLabel">Add Category</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="form-group mb-3">
                        <label for="category_name" class="form-label">Category Name</label>
                        <input type="text" class="form-control" id="category_name" name="name" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">Add Category</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Category Modal (for updating categories) -->
<div class="modal fade" id="editCategoryModal" tabindex="-1" aria-labelledby="editCategoryModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="editCategoryForm" method="POST" action="edit_category.php">
                <div class="modal-header">
                    <h5 class="modal-title" id="editCategoryModalLabel">Edit Category</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" id="editCategoryId" name="category_id">
                    <div class="form-group mb-3">
                        <label for="editCategoryName" class="form-label">Category Name</label>
                        <input type="text" class="form-control" id="editCategoryName" name="name" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">Update Category</button>
                </div>
            </form>
        </div>
    </div>
</div>



<!-- Add Ingredient Modal -->
<div class="modal fade" id="addIngredientModal" tabindex="-1" aria-labelledby="addIngredientModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add New Ingredient</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="addIngredientForm" method="POST" action="../helpers/add_ingredient.php" enctype="multipart/form-data">
                    
                    <!-- Ingredient Name -->
                    <div class="mb-3">
                        <label class="form-label">Ingredient Name</label>
                        <input type="text" class="form-control" name="ingredient_name" required>
                    </div>

                    <!-- Description -->
                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea class="form-control" name="description" rows="3" required></textarea>
                    </div>

                    <!-- Price -->
                    <div class="mb-3">
                        <label class="form-label">Price (₱)</label>
                        <input type="number" class="form-control" name="price" step="0.01" required>
                    </div>

                    <div class="row">
                        <!-- Stock Quantity (Availability) -->
                        <div class="col-md-6">
                            <label class="form-label">Available Stock</label>
                            <input type="number" class="form-control" name="quantity" required>
                        </div>

                        <!-- Quantity Value (Measurement Per Unit) -->
                        <div class="col-md-3">
                            <label class="form-label">Measurement</label>
                            <input type="number" class="form-control" name="quantity_value" placeholder="e.g., 500, 1, 2, 30" required>
                        </div>

                        <!-- Unit Type Dropdown -->
                        <div class="col-md-3">
                            <label class="form-label">Unit Type</label>
                            <select class="form-select" name="unit_type" required>
                                <option value="g">Grams (g)</option>
                                <option value="kg">Kilograms (kg)</option>
                                <option value="ml">Milliliters (ml)</option>
                                <option value="l">Liters (L)</option>
                                <option value="pcs">Pieces (pcs)</option>
                                <option value="pack">Pack</option>
                                <option value="bottle">Bottle</option>
                                <option value="can">Can</option>
                            </select>
                        </div>
                    </div>

                    <!-- Category Dropdown -->
                    <div class="mb-3">
                        <label class="form-label">Category</label>
                        <select class="form-select" name="category_id" required>
                            <?php
                            $conn = new mysqli('localhost', 'root', '', 'tastyph1');
                            $sql = "SELECT category_id, name FROM categories";
                            $result = $conn->query($sql);

                            if ($result->num_rows > 0) {
                                while ($row = $result->fetch_assoc()) {
                                    echo "<option value='" . $row['category_id'] . "'>" . $row['name'] . "</option>";
                                }
                            } else {
                                echo "<option value=''>No categories available</option>";
                            }
                            $conn->close();
                            ?>
                        </select>
                    </div>

                    <!-- Image Upload -->
                    <div class="mb-3">
                        <label class="form-label">Image</label>
                        <input type="file" class="form-control" name="image_url" accept="image/*" required>
                    </div>

                    <button type="submit" class="btn btn-primary w-100">Add Ingredient</button>
                </form>
            </div>
        </div>
    </div>
</div>


<!-- Edit Ingredient Modal -->
<div class="modal fade" id="editIngredientModal" tabindex="-1" aria-labelledby="editIngredientModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit Ingredient</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form action="../helpers/edit_ingredient.php" method="POST" enctype="multipart/form-data">
                    <input type="hidden" id="editIngredientId" name="ingredient_id">

                    <!-- Ingredient Name -->
                    <div class="mb-3">
                        <label class="form-label">Ingredient Name</label>
                        <input type="text" class="form-control" id="editIngredientName" name="ingredient_name" required>
                    </div>

                    <!-- Description -->
                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea class="form-control" id="editIngredientDescription" name="description" rows="3" required></textarea>
                    </div>

                    <!-- Price -->
                    <div class="mb-3">
                        <label class="form-label">Price (₱)</label>
                        <input type="number" class="form-control" id="editIngredientPrice" name="price" step="0.01" required>
                    </div>

                    <div class="row">
                        <!-- Stock Availability (Quantity) -->
                        <div class="col-md-4">
                            <label class="form-label">Stock Availability</label>
                            <input type="number" class="form-control" id="editIngredientQuantity" name="quantity" required>
                        </div>

                        <!-- Measurement Value -->
                        <div class="col-md-4">
                            <label class="form-label">Measurement Value</label>
                            <input type="number" class="form-control" id="editIngredientQuantityValue" name="quantity_value" required>
                        </div>

                        <!-- Unit Type Dropdown -->
                        <div class="col-md-4">
                            <label class="form-label">Unit Type</label>
                            <select class="form-select" id="editIngredientUnitType" name="unit_type" required>
                                <option value="g">Grams (g)</option>
                                <option value="kg">Kilograms (kg)</option>
                                <option value="ml">Milliliters (ml)</option>
                                <option value="l">Liters (L)</option>
                                <option value="pcs">Pieces (pcs)</option>
                                <option value="pack">Pack</option>
                                <option value="bottle">Bottle</option>
                                <option value="can">Can</option>
                            </select>
                        </div>
                    </div>

                    <!-- Category Dropdown -->
                    <div class="mb-3">
                        <label class="form-label">Category</label>
                        <select class="form-select" id="editIngredientCategory" name="category_id" required>
                            <?php
                            $conn = new mysqli('localhost', 'root', '', 'tastyph1');
                            $sql = "SELECT category_id, name FROM categories";
                            $result = $conn->query($sql);

                            if ($result->num_rows > 0) {
                                while ($row = $result->fetch_assoc()) {
                                    echo "<option value='" . $row['category_id'] . "'>" . $row['name'] . "</option>";
                                }
                            } else {
                                echo "<option value=''>No categories available</option>";
                            }
                            $conn->close();
                            ?>
                        </select>
                    </div>

                    <!-- Image Upload -->
                    <div class="mb-3">
                        <label class="form-label">Image</label>
                        <input type="file" class="form-control" id="editIngredientImage" name="image_url">
                        <img id="currentIngredientImage" src="" width="100" class="mt-2">
                    </div>

                    <button type="submit" class="btn btn-primary w-100">Save Changes</button>
                </form>
            </div>
        </div>
    </div>
</div>


<!-- Add Variant Modal -->
<div class="modal fade" id="addVariantModal" tabindex="-1" aria-labelledby="addVariantModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add Variant to Ingredient</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="addVariantForm" method="POST" action="../helpers/add_ingredient_variant.php" enctype="multipart/form-data">
                    <input type="hidden" name="ingredient_id" id="variantIngredientId">

                    <div class="mb-3">
                        <label class="form-label">Variant Name</label>
                        <input type="text" class="form-control" name="variant_name" required>
                    </div>

                    <div class="row">
                        <div class="col-md-4">
                            <label class="form-label">Price (₱)</label>
                            <input type="number" class="form-control" name="price" step="0.01" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Quantity</label>
                            <input type="number" class="form-control" name="quantity" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Measurement</label>
                            <input type="number" class="form-control" name="quantity_value" required>
                        </div>
                    </div>

                    <div class="mb-3 mt-3">
                        <label class="form-label">Unit Type</label>
                        <select class="form-select" name="unit_type" required>
                            <option value="g">Grams (g)</option>
                            <option value="kg">Kilograms (kg)</option>
                            <option value="ml">Milliliters (ml)</option>
                            <option value="l">Liters (L)</option>
                            <option value="pcs">Pieces (pcs)</option>
                            <option value="pack">Pack</option>
                            <option value="bottle">Bottle</option>
                            <option value="can">Can</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Variant Image</label>
                        <input type="file" class="form-control" name="variant_image" accept="image/*" required>
                    </div>

                    <button type="submit" class="btn btn-primary w-100">Add Variant</button>
                </form>
            </div>
        </div>
    </div>
</div>


<!-- Edit Variant Modal -->
<div class="modal fade" id="editVariantModal" tabindex="-1" aria-labelledby="editVariantModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <form id="editVariantForm" method="POST" action="../helpers/update_variant.php" enctype="multipart/form-data">
        <div class="modal-header">
          <h5 class="modal-title">Edit Variant</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">

          <input type="hidden" name="variant_id" id="editVariantId">

          <div class="mb-3">
            <label class="form-label">Variant Name</label>
            <input type="text" class="form-control" name="variant_name" id="editVariantName" required>
          </div>

          <div class="mb-3">
            <label class="form-label">Price (₱)</label>
            <input type="number" step="0.01" class="form-control" name="price" id="editVariantPrice" required>
          </div>

          <div class="mb-3">
            <label class="form-label">Quantity</label>
            <input type="number" class="form-control" name="quantity" id="editVariantQuantity" required>
          </div>

          <div class="row">
            <div class="col-md-6">
              <label class="form-label">Measurement (Value)</label>
              <input type="number" class="form-control" name="quantity_value" id="editVariantQuantityValue" required>
            </div>

            <div class="col-md-6">
              <label class="form-label">Unit Type</label>
              <select class="form-select" name="unit_type" id="editVariantUnitType" required>
                <option value="g">Grams (g)</option>
                <option value="kg">Kilograms (kg)</option>
                <option value="ml">Milliliters (ml)</option>
                <option value="l">Liters (L)</option>
                <option value="pcs">Pieces (pcs)</option>
                <option value="pack">Pack</option>
                <option value="bottle">Bottle</option>
                <option value="can">Can</option>
              </select>
            </div>
          </div>

          <div class="mb-3 mt-3">
            <label class="form-label">Image</label>
            <input type="file" class="form-control" name="image_url">
            <img id="currentVariantImage" src="" alt="Current Image" width="100" class="mt-2">
          </div>
        </div>

        <div class="modal-footer">
          <button type="submit" class="btn btn-primary">Save Changes</button>
        </div>
      </form>
    </div>
  </div>
</div>
