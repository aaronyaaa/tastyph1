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
    const ingredientTable = document.getElementById("ingredientTable");
    const variantTable = document.getElementById("variantTable");
    const variantHeader = document.getElementById("variantHeader");
    const variantBody = document.getElementById("variantBody");

    if (!ingredientTable || !variantTable || !variantHeader || !variantBody) return;

    ingredientTable.style.display = "none";
    variantTable.style.display = "block";
    variantHeader.innerText = `Variants of "${ingredientName}"`;

    fetch(`../helpers/fetch_variants.php?ingredient_id=${ingredientId}`)
        .then(res => res.json())
        .then(data => {
            variantBody.innerHTML = "";

            if (!data.length) {
                variantBody.innerHTML = `<tr><td colspan="6" class="text-muted">No variants available.</td></tr>`;
                return;
            }

            data.forEach(variant => {
                variantBody.innerHTML += `
                <tr>
                  <td>${variant.variant_name}</td>
                  <td>₱${parseFloat(variant.price).toFixed(2)}</td>
                  <td>${variant.quantity}</td>
                  <td>${variant.quantity_value} ${variant.unit_type}</td>
                  <td><img src="${variant.image_url}" width="50"></td>
                  <td>
                    <button class="btn btn-warning btn-sm" onclick='editVariant(${JSON.stringify(variant)})'>Edit</button>
                    <button class="btn btn-danger btn-sm" onclick="deleteVariant(${variant.variant_id})">Delete</button>
                  </td>
                </tr>
              `;
              
            });
        })
        .catch(error => {
            console.error("Error fetching variants:", error);
            variantBody.innerHTML = `<tr><td colspan="6" class="text-danger">Failed to load variants.</td></tr>`;
        });
}

function backToIngredients() {
    const variantTable = document.getElementById("variantTable");
    const ingredientTable = document.getElementById("ingredientTable");

    if (variantTable && ingredientTable) {
        variantTable.style.display = "none";
        ingredientTable.style.display = "block";
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
