document.addEventListener("DOMContentLoaded", function () {
    document.querySelectorAll(".edit-ingredient").forEach(button => {
        button.addEventListener("click", function () {
            let modal = document.getElementById("editIngredientModal");

            document.getElementById("editIngredientId").value = this.getAttribute("data-id");
            document.getElementById("editIngredientName").value = this.getAttribute("data-name");
            document.getElementById("editIngredientDescription").value = this.getAttribute("data-description");
            document.getElementById("editIngredientPrice").value = this.getAttribute("data-price");
            document.getElementById("editIngredientQuantity").value = this.getAttribute("data-quantity");
            document.getElementById("editIngredientQuantityValue").value = this.getAttribute("data-quantity-value");

            let unitType = this.getAttribute("data-unit-type");
            document.getElementById("editIngredientUnitType").value = unitType;

            let category = this.getAttribute("data-category");
            document.getElementById("editIngredientCategory").value = category;

            let imageSrc = this.getAttribute("data-image");
            document.getElementById("currentIngredientImage").src = imageSrc ? imageSrc : "../assets/default-image.jpg";

            let modalInstance = new bootstrap.Modal(modal);
            modalInstance.show();
        });
    });
});

function setIngredientId(id) {
    const input = document.getElementById('variantIngredientId');
    if (input) {
        input.value = id;
    }
}

function showVariants(ingredientId, ingredientName) {
    const ingredientGrid = document.getElementById("ingredientGrid");
    const variantGrid = document.getElementById("variantGrid");
    const variantHeader = document.getElementById("variantHeader");
    const variantBody = document.getElementById("variantBody");

    if (!ingredientGrid || !variantGrid || !variantHeader || !variantBody) return;

    ingredientGrid.style.display = "none";
    variantGrid.style.display = "block";
    variantHeader.innerText = `Variants of "${ingredientName}"`;

    fetch(`../helpers/fetch_variants.php?ingredient_id=${ingredientId}`)
        .then(res => res.json())
        .then(data => {
            variantBody.innerHTML = "";

            if (!data.length) {
                variantBody.innerHTML = `
                    <div class="col-12 text-center">
                        <p class="text-muted">No variants available.</p>
                    </div>`;
                return;
            }

            data.forEach(variant => {
                variantBody.innerHTML += `
                    <div class="col-md-4 col-lg-3 mb-4">
                        <div class="product-card">
                            <img src="${variant.image_url}" alt="${variant.variant_name}" class="product-image">
                            <div class="product-info">
                                <h5>${variant.variant_name}</h5>
                                <div class="d-flex justify-content-between align-items-center">
                                    <span class="h5 mb-0">₱${parseFloat(variant.price).toFixed(2)}</span>
                                    <span class="badge bg-${variant.quantity > 0 ? 'success' : 'danger'}">
                                        ${variant.quantity} in stock
                                    </span>
                                </div>
                                <p class="text-muted mb-2">
                                    <i class="fas fa-box"></i> ${variant.quantity_value} ${variant.unit_type}
                                </p>
                                <div class="mt-3 d-flex gap-2">
                                    <button class="btn btn-warning btn-sm flex-grow-1" 
                                            onclick='editVariant(${JSON.stringify(variant)})'>
                                        <i class="fas fa-edit"></i> Edit
                                    </button>
                                    <button class="btn btn-danger btn-sm" 
                                            onclick="deleteVariant(${variant.variant_id})">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>`;
            });
        })
        .catch(error => {
            console.error("Error fetching variants:", error);
            variantBody.innerHTML = `
                <div class="col-12 text-center">
                    <p class="text-danger">Failed to load variants.</p>
                </div>`;
        });
}

function backToIngredients() {
    const ingredientGrid = document.getElementById("ingredientGrid");
    const variantGrid = document.getElementById("variantGrid");

    if (ingredientGrid && variantGrid) {
        variantGrid.style.display = "none";
        ingredientGrid.style.display = "grid";
    }
}

function editVariant(variantId) {
    alert("Edit logic for variant ID: " + variantId);
    // Hook up modal logic here if needed
}

function deleteVariant(variantId) {
    if (confirm("Are you sure you want to delete this variant?")) {
        fetch(`../helpers/delete_variant.php?id=${variantId}`, {
            method: 'POST'
        })
        .then(response => response.text())
        .then(result => {
            alert("Variant deleted.");
            location.reload(); // or re-call showVariants(...) for dynamic update
        })
        .catch(error => {
            console.error("Error deleting variant:", error);
        });
    }
}
function editVariant(variant) {
    document.getElementById('editVariantId').value = variant.variant_id;
    document.getElementById('editVariantName').value = variant.variant_name;
    document.getElementById('editVariantPrice').value = variant.price;
    document.getElementById('editVariantQuantity').value = variant.quantity;
    document.getElementById('editVariantQuantityValue').value = variant.quantity_value;
    document.getElementById('editVariantUnitType').value = variant.unit_type;
    document.getElementById('currentVariantImage').src = variant.image_url || '../assets/default-image.jpg';

    const modal = new bootstrap.Modal(document.getElementById('editVariantModal'));
    modal.show();
}
