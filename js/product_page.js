function updateQuantity(change) {
    let quantityInput = document.getElementById("quantity");
    let maxStock = parseInt(document.getElementById("maxStock").value); // Get max stock

    let newQuantity = parseInt(quantityInput.value) + change;

    if (newQuantity < 1) {
        newQuantity = 1; // Prevent quantity from going below 1
    } else if (newQuantity > maxStock) {
        newQuantity = maxStock; // Prevent exceeding stock
        alert("You cannot order more than the available stock.");
    }

    quantityInput.value = newQuantity;
}

// Ensure manual input stays within range
function validateQuantity() {
    let quantityInput = document.getElementById("quantity");
    let maxStock = parseInt(document.getElementById("maxStock").value);

    if (quantityInput.value < 1) {
        quantityInput.value = 1; // Minimum value
    } else if (quantityInput.value > maxStock) {
        quantityInput.value = maxStock; // Maximum available stock
        alert("You cannot order more than the available stock.");
    }
}
