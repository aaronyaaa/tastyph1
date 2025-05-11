$(document).ready(function () {
    // Handle edit product button clicks
    $(".edit-product").click(function () {
        const productId = $(this).data("id");
        const productName = $(this).data("name");
        const description = $(this).data("description");
        const price = $(this).data("price");
        const quantity = $(this).data("quantity");
        const categoryId = $(this).data("category");
        const imageUrl = $(this).data("image");

        console.log("Editing Product:", {
            id: productId,
            name: productName,
            category: categoryId
        }); // Debugging output

        // Set form values
        $("#editProductId").val(productId);
        $("#editProductName").val(productName);
        $("#editDescription").val(description);
        $("#editPrice").val(price);
        $("#editQuantity").val(quantity);
        
        // Handle category selection
        const categorySelect = $("#editCategory");
        if (categoryId) {
            categorySelect.val(categoryId);
            console.log("Setting category to:", categoryId); // Debugging output
        } else {
            categorySelect.val(""); // Reset to default if no category
            console.log("No category selected"); // Debugging output
        }

        // Show image preview if an image exists
        const imagePreview = $("#editImagePreview");
        if (imageUrl) {
            imagePreview.find("img").attr("src", imageUrl);
            imagePreview.show();
        } else {
            imagePreview.hide();
        }
    });

    // Handle image preview on file selection
    $("#editImage").change(function(e) {
        const file = e.target.files[0];
        if (file) {
            const reader = new FileReader();
            const previewImg = $("#editImagePreview img");
            const imagePreview = $("#editImagePreview");

            reader.onload = function(e) {
                previewImg.attr("src", e.target.result);
                imagePreview.show();
            }

            reader.readAsDataURL(file);
        }
    });

    // Handle delete product
    $(".delete-product").click(function () {
        const productId = $(this).data("id");

        if (confirm("Are you sure you want to delete this product?")) {
            $.ajax({
                url: "../helpers/delete_product.php",
                type: "POST",
                data: { product_id: productId },
                dataType: "json",
                success: function (response) {
                    console.log("Server Response:", response); // Debugging

                    if (response.success) {
                        alert("Product deleted successfully!");
                        $("#action-" + productId).closest("tr").remove();
                    } else {
                        alert("Error: " + response.message);
                    }
                },
                error: function (xhr, status, error) {
                    console.error("AJAX Error:", status, error, xhr.responseText); // Debugging
                    alert("An error occurred while deleting the product.");
                }
            });
        }
    });

    // Handle edit category button clicks
    $(".edit-category").click(function() {
        const categoryId = $(this).data("id");
        const categoryName = $(this).data("name");
        
        // Set the form values
        $("#editCategoryId").val(categoryId);
        $("#editCategoryName").val(categoryName);
        
        // Show the modal
        const editModal = new bootstrap.Modal(document.getElementById("editCategoryModal"));
        editModal.show();
    });

    // Handle delete category button clicks
    $(".delete-category").click(function() {
        const categoryId = $(this).data("id");
        
        if (confirm("Are you sure you want to delete this category? This action cannot be undone.")) {
            $.ajax({
                url: "../helpers/delete_category.php",
                type: "POST",
                data: { category_id: categoryId },
                dataType: "json",
                success: function(response) {
                    if (response.success) {
                        alert("Category deleted successfully!");
                        location.reload();
                    } else {
                        alert("Error: " + response.message);
                    }
                },
                error: function(xhr, status, error) {
                    console.error("Error:", error);
                    alert("An error occurred while deleting the category");
                }
            });
        }
    });

    // Handle business hours toggles
    $(".availability-toggle").change(function() {
        const targetId = $(this).data("target");
        const timeInputs = $("#" + targetId);
        const timeFields = timeInputs.find("input");
        const hiddenInput = $(this).closest(".form-check").find("input[type='hidden']");

        if (this.checked) {
            timeInputs.removeClass("d-none");
            timeFields.prop("disabled", false);
            hiddenInput.val("1");
        } else {
            timeInputs.addClass("d-none");
            timeFields.prop("disabled", true);
            hiddenInput.val("0");
        }
    });

    // Ensure all schedules update correctly on form submit
    $("#businessHoursForm").submit(function() {
        $(".availability-toggle").each(function() {
            const hiddenInput = $(this).closest(".form-check").find("input[type='hidden']");
            if (!this.checked) {
                hiddenInput.val("0");
            }
        });
    });
});

// Function that will be triggered when the "Start Cooking" button is clicked
function startCooking() {
    // Custom action, such as redirecting to a cooking page or processing ingredients
    alert("Cooking has started!"); // Placeholder action
}

// Ensure the function is available after the page loads
document.addEventListener("DOMContentLoaded", function () {
    const startCookingButton = document.getElementById("startCookingButton");
    
    if (startCookingButton) {
        startCookingButton.addEventListener("click", startCooking);
    }
});

// When the Edit button is clicked, populate the modal with the product details
document.querySelectorAll('.edit-product').forEach(button => {
    button.addEventListener('click', function () {
        const productId = this.getAttribute('data-id');
        const name = this.getAttribute('data-name');
        const description = this.getAttribute('data-description');
        const price = this.getAttribute('data-price');
        const quantity = this.getAttribute('data-quantity');
        const category = this.getAttribute('data-category');
        const imageUrl = this.getAttribute('data-image');

        // Fill the modal fields with the current product data
        document.getElementById('editProductId').value = productId;
        document.getElementById('editProductName').value = name;
        document.getElementById('editProductDescription').value = description;
        document.getElementById('editProductPrice').value = price;
        document.getElementById('editProductQuantity').value = quantity;
        document.getElementById('editProductCategory').value = category;
        document.getElementById('editProductImage').src = "../uploads/" + imageUrl;
    });
});

// Handle Delete Product
document.querySelectorAll('.delete-product').forEach(button => {
    button.addEventListener('click', function () {
        const productId = this.getAttribute('data-id');

        if (confirm("Are you sure you want to delete this product?")) {
            window.location.href = `delete_product.php?product_id=${productId}`;
        }
    });
});
