$(document).ready(function () {
    // Initialize Bootstrap tooltips (vanilla JS, but fine here)
    const tooltipTriggerList = document.querySelectorAll('[data-bs-toggle="tooltip"]');
    const tooltipList = [...tooltipTriggerList].map(el => new bootstrap.Tooltip(el));

    // --- Edit Product ---
    $(document).on('click', '.edit-product', function() {
        const productId = $(this).data('id');
        const productName = $(this).data('name');
        const description = $(this).data('description');
        const price = $(this).data('price');
        const quantity = $(this).data('quantity');
        const categoryId = $(this).data('category');
        const imageUrl = $(this).data('image');

        // Fill form fields - make sure IDs match your modal inputs
        $('#editProductId').val(productId);
        $('#editProductName').val(productName);
        $('#editDescription').val(description);
        $('#editPrice').val(price);
        $('#editQuantity').val(quantity);
        $('#editCategory').val(categoryId);

        // Image preview
        if (imageUrl) {
            $('#editImagePreview img').attr('src', imageUrl);
            $('#editImagePreview').show();
        } else {
            $('#editImagePreview').hide();
        }

        // Show the edit product modal
        const editModal = new bootstrap.Modal(document.getElementById('editProductModal'));
        editModal.show();
    });

    // --- Image preview for edit product ---
    $(document).on('change', '#editImage', function(e) {
        const file = e.target.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                $('#editImagePreview img').attr('src', e.target.result);
                $('#editImagePreview').show();
            }
            reader.readAsDataURL(file);
        }
    });

    // --- Delete Product ---
    $(document).on('click', '.delete-product', function() {
        const productId = $(this).data('id');
        if (confirm('Are you sure you want to delete this product?')) {
            $.ajax({
                url: '../helpers/delete_product.php',
                type: 'POST',
                data: { product_id: productId },
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        alert('Product deleted successfully!');
                        location.reload();
                    } else {
                        alert('Error: ' + response.message);
                    }
                },
                error: function(xhr, status, error) {
                    alert('An error occurred while deleting the product.');
                    console.error('AJAX error:', status, error);
                }
            });
        }
    });

    // --- Edit Category ---
    $(document).on('click', '.edit-category', function() {
        const categoryId = $(this).data('id');
        const categoryName = $(this).data('name');
        $('#editCategoryId').val(categoryId);
        $('#editCategoryName').val(categoryName);
        const editCategoryModal = new bootstrap.Modal(document.getElementById('editCategoryModal'));
        editCategoryModal.show();
    });

    // --- Delete Category ---
    $(document).on('click', '.delete-category', function() {
        const categoryId = $(this).data('id');
        if (confirm('Are you sure you want to delete this category? This action cannot be undone.')) {
            $.ajax({
                url: '../helpers/delete_category.php',
                type: 'POST',
                data: { category_id: categoryId },
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        alert('Category deleted successfully!');
                        location.reload();
                    } else {
                        alert('Error: ' + response.message);
                    }
                },
                error: function(xhr, status, error) {
                    alert('An error occurred while deleting the category.');
                    console.error('AJAX error:', status, error);
                }
            });
        }
    });

    // --- Submit Edit Category Form via AJAX ---
    $(document).on('submit', '#editCategoryForm', function(e) {
        e.preventDefault();
        const formData = new FormData(this);
        $.ajax({
            url: '../helpers/edit_category.php',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function() {
                location.reload();
            },
            error: function(xhr, status, error) {
                alert('An error occurred while updating the category.');
                console.error('AJAX error:', status, error);
            }
        });
    });

    // --- Business Hours toggles ---
    $(document).on('change', '.availability-toggle', function() {
        const targetId = $(this).data('target');
        const timeInputs = $('#' + targetId);
        const timeFields = timeInputs.find('input');
        const hiddenInput = $(this).closest('.form-check').find('input[type="hidden"]');

        if (this.checked) {
            timeInputs.removeClass('d-none');
            timeFields.prop('disabled', false);
            hiddenInput.val('1');
        } else {
            timeInputs.addClass('d-none');
            timeFields.prop('disabled', true);
            hiddenInput.val('0');
        }
    });

    // --- Variant edit/delete placeholders ---
    $(document).on('click', '.edit-variant', function() {
        const variantId = $(this).data('variant-id');
        console.log('Edit variant:', variantId);
        // Add your variant edit modal logic here
    });
    $(document).on('click', '.delete-variant', function() {
        const variantId = $(this).data('variant-id');
        if (confirm('Are you sure you want to delete this variant?')) {
            console.log('Delete variant:', variantId);
            // Add your delete logic here (AJAX call)
        }
    });

    // --- View Variants tab switch ---
    $(document).on('click', '.view-variants', function() {
        $('#variants-tab').tab('show');
    });

    // --- Programmatically open add modals if needed ---
    // (Optional, as data-bs-toggle="modal" works automatically)
    $(document).on('click', '[data-bs-target="#addProductModal"]', function() {
        const addModal = new bootstrap.Modal(document.getElementById('addProductModal'));
        addModal.show();
    });
    $(document).on('click', '[data-bs-target="#addRecipeModal"]', function() {
        const addModal = new bootstrap.Modal(document.getElementById('addRecipeModal'));
        addModal.show();
    });
    $(document).on('click', '[data-bs-target="#ingredientsInventoryModal"]', function() {
        const inventoryModal = new bootstrap.Modal(document.getElementById('ingredientsInventoryModal'));
        inventoryModal.show();
    });
    $(document).on('click', '[data-bs-target="#businessHoursModal"]', function() {
        const hoursModal = new bootstrap.Modal(document.getElementById('businessHoursModal'));
        hoursModal.show();
    });
    $(document).on('click', '[data-bs-target="#editProfileModal"]', function() {
        const profileModal = new bootstrap.Modal(document.getElementById('editProfileModal'));
        profileModal.show();
    });
});
