$(document).ready(function () {
    $(".edit-product").click(function () {
        let productId = $(this).data("id");
        let productName = $(this).data("name");
        let description = $(this).data("description");
        let price = $(this).data("price");
        let quantity = $(this).data("quantity");
        let categoryId = $(this).data("category");
        let imageUrl = $(this).data("image");

        console.log("Editing Product ID:", productId); // Debugging output

        // Populate the modal form fields
        $("#editProductId").val(productId);
        $("#editProductName").val(productName);
        $("#editDescription").val(description);
        $("#editPrice").val(price);
        $("#editQuantity").val(quantity);
        $("#editCategory").val(categoryId);

        // Show image preview if an image exists
        if (imageUrl) {
            $("#editImagePreview").attr("src", imageUrl).show();
        } else {
            $("#editImagePreview").hide();
        }
    });
});


$(document).ready(function () {
    $(".delete-product").click(function () {
        let productId = $(this).data("id");

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


document.addEventListener("DOMContentLoaded", function () {
    document.querySelectorAll(".availability-toggle").forEach(toggle => {
        toggle.addEventListener("change", function () {
            let targetId = this.getAttribute("data-target");
            let timeInputs = document.getElementById(targetId);
            let timeFields = timeInputs.querySelectorAll("input");
            let hiddenInput = this.closest(".form-check").querySelector("input[type='hidden']"); 

            if (this.checked) {
                timeInputs.classList.remove("d-none");
                timeFields.forEach(input => input.removeAttribute("disabled"));
                hiddenInput.value = "1";
            } else {
                timeInputs.classList.add("d-none");
                timeFields.forEach(input => input.setAttribute("disabled", "disabled"));
                hiddenInput.value = "0";
            }
        });
    });

    // Ensure all schedules update correctly
    document.querySelector("#businessHoursForm").addEventListener("submit", function () {
        document.querySelectorAll(".availability-toggle").forEach(toggle => {
            let hiddenInput = toggle.closest(".form-check").querySelector("input[type='hidden']");
            if (!toggle.checked) {
                hiddenInput.value = "0"; 
            }
        });
    });
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
