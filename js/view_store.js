document.addEventListener("DOMContentLoaded", function () {
    let productNameInput = document.getElementById("product_name");
    let productSuggestions = document.getElementById("product_suggestions");

    // Ensure seller_id exists and is correctly retrieved
    let sellerIdElement = document.querySelector("input[name='seller_id']");
    let sellerId = sellerIdElement ? parseInt(sellerIdElement.value, 10) : 0;

    console.log("Seller ID:", sellerId); // Debugging log

    if (productNameInput && productSuggestions) {
        productNameInput.addEventListener("keyup", function () {
            let query = productNameInput.value.trim();

            if (query.length > 1) {
                fetch(`../helpers/fetch_products.php?query=${encodeURIComponent(query)}&seller_id=${sellerId}`)
                    .then(response => response.text())
                    .then(data => {
                        productSuggestions.innerHTML = data;
                        productSuggestions.style.display = "block"; // Show results
                    })
                    .catch(error => console.error("Error fetching products:", error));
            } else {
                productSuggestions.innerHTML = "";
                productSuggestions.style.display = "none"; // Hide suggestions if empty
            }
        });

        // Select product from suggestions
        productSuggestions.addEventListener("click", function (event) {
            if (event.target.closest(".product-item")) {
                let selectedItem = event.target.closest(".product-item");
                productNameInput.value = selectedItem.getAttribute("data-name");
                productSuggestions.innerHTML = "";
                productSuggestions.style.display = "none"; // Hide suggestions
            }
        });

        // Hide suggestions when clicking outside
        document.addEventListener("click", function (event) {
            if (!productNameInput.contains(event.target) && !productSuggestions.contains(event.target)) {
                productSuggestions.style.display = "none";
            }
        });
    }
});

document.querySelectorAll(".add-to-cart").forEach(button => {
    button.addEventListener("click", function () {
        let productId = this.getAttribute("data-product-id");

        fetch("../api/cart_api.php", {
            method: "POST",
            headers: {
                "Content-Type": "application/x-www-form-urlencoded",
            },
            body: `product_id=${productId}&quantity=${document.getElementById("quantity").value}`
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
    });
});
