document.addEventListener("DOMContentLoaded", function () {
    // Ensure the cart count updates on page load
    updateCartCount();

    // Add event listeners for "Add to Cart" buttons
    document.querySelectorAll(".add-to-cart").forEach(button => {
        button.addEventListener("click", function () {
            let productId = this.getAttribute("data-product-id");

            fetch("../api/cart_api.php", {
                method: "POST",
                headers: {
                    "Content-Type": "application/x-www-form-urlencoded",
                },
                body: `product_id=${productId}&quantity=1`
            })
            .then(response => response.json())
            .then(data => {
                if (data.status === "success") {
                    alert("Added to cart!");
                    updateCartCount(); // Update cart count dynamically
                } else {
                    alert("Error adding to cart.");
                }
            })
            .catch(error => console.error("Error:", error));
        });
    });
});

function updateCartCount() {
    fetch("../api/cart_api.php", { method: "GET" })
        .then(response => response.json())
        .then(data => {
            let cartCount = data.count || 0;
            let badge = document.querySelector(".cart-badge");

            if (badge) { // Ensure badge exists before modifying it
                if (cartCount > 0) {
                    badge.innerText = cartCount;
                    badge.style.display = "inline-block";
                } else {
                    badge.style.display = "none";
                }
            }
        })
        .catch(error => console.error("Error:", error));
}
