document.addEventListener("DOMContentLoaded", function () {
    // Ensure cart count updates on page load
    updateCartCount();

    // Add event listeners for "Add to Cart" buttons
    document.querySelectorAll(".add-to-cart").forEach(button => {
        button.addEventListener("click", function () {
            let itemId = this.getAttribute("data-item-id");
            let itemType = this.getAttribute("data-item-type");
            let quantity = parseInt(document.getElementById("quantity").value) || 1;
            let maxStock = parseInt(document.getElementById("maxStock").value); // Ensure we don't exceed stock

            if (quantity < 1) {
                alert("Quantity must be at least 1.");
                return;
            }
            if (quantity > maxStock) {
                alert("You cannot order more than the available stock.");
                return;
            }

            // Add item to cart in the database
            fetch('../cart/add_to_cart.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    item_id: itemId,
                    item_type: itemType,
                    quantity: quantity
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert("Item added to cart successfully!");
                    updateCartCount(); // Update the cart count dynamically
                } else {
                    alert("Error: " + data.message);
                }
            })
            .catch(error => console.error('Error:', error));
        });
    });

    // Add event listeners for quantity buttons
    document.querySelectorAll(".quantity-btn").forEach(button => {
        button.addEventListener("click", function () {
            let amount = parseInt(this.dataset.amount);
            updateQuantity(amount);
        });
    });
});

function updateCartCount() {
    fetch("../api/cart_api.php?getCartCount=true", { method: "GET" })
        .then(response => response.json())
        .then(data => {
            let cartCount = data.count || 0; // Now counting unique items per user
            let badge = document.getElementById("cart-badge");

            if (badge) {
                if (cartCount > 0) {
                    badge.innerText = cartCount;
                    badge.style.display = "inline-block";
                } else {
                    badge.style.display = "none";
                }
            }
        })
        .catch(error => console.error("Error fetching cart count:", error));
}


// Fix updateQuantity function
function updateQuantity(change) {
    let quantityInput = document.getElementById("quantity");
    let maxStockInput = document.getElementById("maxStock");

    if (!quantityInput || !maxStockInput) return; // Ensure the elements exist

    let currentQuantity = parseInt(quantityInput.value);
    let maxStock = parseInt(maxStockInput.value);

    let newQuantity = currentQuantity + change;

    if (newQuantity < 1) {
        newQuantity = 1; // Prevent quantity from going below 1
    } else if (newQuantity > maxStock) {
        newQuantity = maxStock; // Prevent exceeding stock
        alert("You cannot order more than the available stock.");
    }

    quantityInput.value = newQuantity;
}
