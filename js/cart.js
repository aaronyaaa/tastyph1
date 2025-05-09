
document.addEventListener("DOMContentLoaded", function () {
    const selectAllCheckbox = document.getElementById("select-all");
    const itemCheckboxes = document.querySelectorAll(".item-checkbox");
    const selectedItemsInput = document.getElementById("selected_items");
    const subtotalDisplay = document.getElementById("subtotal");
    const totalDisplay = document.getElementById("total");
    const totalHiddenInput = document.getElementById("total_hidden");
    const selectedPaymentInput = document.getElementById("selected_payment");
    const cashAmountInput = document.getElementById("cash_amount");

    function updateTotalPrice() {
        let total = 0;

        itemCheckboxes.forEach((itemCheckbox) => {
            if (itemCheckbox.checked) {
                const cartId = itemCheckbox.dataset.cartId;
                const subtotalElement = document.querySelector(`.product-subtotal[data-cart-id="${cartId}"]`);
                if (subtotalElement) {
                    const itemPrice = parseFloat(subtotalElement.innerText.replace("₱", "").replace(",", ""));
                    if (!isNaN(itemPrice)) {
                        total += itemPrice;
                    }
                }
            }
        });

        subtotalDisplay.innerText = `₱${total.toFixed(2)}`;
        totalDisplay.innerText = `₱${total.toFixed(2)}`;
        totalHiddenInput.value = total.toFixed(2);
        console.log("💰 Total updated:", totalHiddenInput.value);
    }

    function updateSelectedItems() {
        const selectedIds = Array.from(itemCheckboxes)
            .filter(checkbox => checkbox.checked)
            .map(checkbox => checkbox.dataset.cartId);

        selectedItemsInput.value = selectedIds.join(",");
        console.log("🛒 Selected Items:", selectedItemsInput.value);
    }

    function validateCheckout(event) {
        updateTotalPrice();
        updateSelectedItems();

        const total = parseFloat(totalHiddenInput.value);
        if (total <= 0) {
            event.preventDefault();
            alert("⚠️ Please select items before proceeding to checkout.");
            return false;
        }

        if (!selectedPaymentInput.value) {
            event.preventDefault();
            alert("⚠️ Please select a payment method.");
            return false;
        }

        if (selectedPaymentInput.value === 'cash') {
            const cashAmount = parseFloat(cashAmountInput.value);
            if (isNaN(cashAmount) || cashAmount < total) {
                event.preventDefault();
                alert(`⚠️ Cash amount must be at least ₱${total.toFixed(2)}.`);
                return false;
            }
        }

        return true;
    }

    // Event listeners
    selectAllCheckbox.addEventListener("change", function () {
        itemCheckboxes.forEach(cb => cb.checked = selectAllCheckbox.checked);
        updateTotalPrice();
        updateSelectedItems();
    });

    itemCheckboxes.forEach(cb => cb.addEventListener("change", () => {
        updateTotalPrice();
        updateSelectedItems();
    }));

    // Attach validateCheckout directly on form submit
    document.querySelector("form").addEventListener("submit", validateCheckout);

    // Initial totals
    updateTotalPrice();
    updateSelectedItems();

    // Attach input listener to all quantity fields (in case user directly types)
    document.querySelectorAll(".quantity-input").forEach(input => {
        input.addEventListener("input", () => {
            const cartId = input.dataset.cartId;
            const value = parseInt(input.value);

            if (!isNaN(value) && value >= 1) {
                sendQuantityUpdate(cartId, value);
            }
        });
    });
});

function deleteSelectedItems() {
    const selected = Array.from(document.querySelectorAll(".item-checkbox:checked"))
        .map(checkbox => checkbox.dataset.cartId);

    if (selected.length === 0) {
        alert("Please select at least one item to delete.");
        return;
    }

    if (!confirm("Are you sure you want to delete the selected item(s)?")) {
        return;
    }

    fetch("../helpers/delete_cart_items.php", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({ cart_ids: selected })
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            alert("Selected items deleted.");
            location.reload();
        } else {
            alert("Failed to delete some or all items.");
        }
    })
    .catch(err => {
        console.error("Error deleting items:", err);
        alert("Something went wrong.");
    });
}

function updateQuantity(change, cartId) {
    const input = document.querySelector(`.quantity-input[data-cart-id="${cartId}"]`);
    if (!input) return;

    let quantity = parseInt(input.value) + change;
    const max = parseInt(input.max);

    if (quantity < 1) quantity = 1;
    if (quantity > max) quantity = max;

    input.value = quantity;

    sendQuantityUpdate(cartId, quantity);
}

function sendQuantityUpdate(cartId, newQuantity) {
    console.log("🛠 Sending quantity update for cartId:", cartId, "→", newQuantity);

    fetch("../helpers/update_cart_quantity.php", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({ cart_id: cartId, quantity: newQuantity })
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            const subtotalElem = document.querySelector(`.product-subtotal[data-cart-id="${cartId}"]`);
            if (subtotalElem) {
                subtotalElem.innerText = `₱${parseFloat(data.new_subtotal).toFixed(2)}`;
            }

            // Trigger change event to recalculate total
            const checkbox = document.querySelector(`.item-checkbox[data-cart-id="${cartId}"]`);
            if (checkbox) checkbox.dispatchEvent(new Event('change'));
        } else {
            alert("Failed to update quantity.");
        }
    })
    .catch(err => {
        console.error("Quantity update error:", err);
    });
}
