// Quantity Controls
function updateQuantity(amount) {
    const quantityInput = document.getElementById("quantity");
    let quantity = parseInt(quantityInput.value);
    quantity = Math.max(1, quantity + amount);
    quantityInput.value = quantity;
}

// Add to Cart
function addToCart(ingredientId) {
    const quantity = document.getElementById("quantity").value;
    fetch("../cart/cart.php", {
        method: "POST",
        headers: { "Content-Type": "application/x-www-form-urlencoded" },
        body: `product_id=${ingredientId}&quantity=${quantity}`
    })
        .then(response => response.json())
        .then(data => {
            if (data.status === "success") {
                alert("Added to cart!");
                location.reload();
            } else {
                alert("Error adding to cart.");
            }
        })
        .catch(error => console.error("Error:", error));
}

// Clear all active cards
function clearActiveCards() {
    document.querySelectorAll(".variant-card").forEach(card => {
        card.classList.remove("active-variant");
    });
}
function selectVariant(card, variant) {
    clearActiveCards();
    if (card) card.classList.add("active-variant");

    document.querySelector(".ingredient-title").textContent = variant.variant_name;
    document.querySelector(".ingredient-price").textContent = "₱" + parseFloat(variant.price).toFixed(2);
    document.querySelector(".ingredient-description").textContent = "Variant of ingredient.";
    document.getElementById("mainIngredientImage").src = variant.image_url;
    document.getElementById("quantity").value = 1;
    document.getElementById("quantity").max = variant.quantity;
    document.getElementById("maxStock").value = variant.quantity;
    document.querySelector("input[name='price']").value = variant.price;

    // ✅ Set variant ID in hidden field
    document.getElementById("variantId").value = variant.variant_id;

    const unit = variant.unit_type.toUpperCase();
    document.querySelector(".quantity-selector label").textContent = `Quantity (${unit}):`;

    document.getElementById("stockDisplay").innerHTML =
        `<strong>Stock Available:</strong> ${variant.quantity} ${unit} (${variant.quantity_value} ${unit} per unit)`;
}

function selectOriginalIngredient(card) {
    clearActiveCards();
    if (card) card.classList.add("active-variant");

    document.querySelector(".ingredient-title").textContent = originalData.name;
    document.querySelector(".ingredient-price").textContent = "₱" + parseFloat(originalData.price).toFixed(2);
    document.querySelector(".ingredient-description").textContent = originalData.description;
    document.getElementById("mainIngredientImage").src = originalData.image_url;
    document.getElementById("quantity").value = 1;
    document.getElementById("quantity").max = originalData.quantity;
    document.getElementById("maxStock").value = originalData.quantity;
    document.querySelector("input[name='price']").value = originalData.price;

    // ✅ Clear variant ID when original is selected
    document.getElementById("variantId").value = "";

    const unit = originalData.unit_type.toUpperCase();
    document.querySelector(".quantity-selector label").textContent = `Quantity (${unit}):`;

    document.getElementById("stockDisplay").innerHTML =
        `<strong>Stock Available:</strong> ${originalData.quantity} ${unit} (${originalData.quantity_value} ${unit} per unit)`;
}
