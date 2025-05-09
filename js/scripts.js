document.addEventListener("DOMContentLoaded", function() {
    // Get all the store cards
    const storeCards = document.querySelectorAll('.store-card');

    // Loop through each store card and apply logic based on user type and approval status
    storeCards.forEach(function(card) {
        const sellerId = card.getAttribute('data-seller-id');
        const status = card.getAttribute('data-status');  // 0 = unapproved, 1 = approved
        const userType = window.userType;  // This comes from the PHP embedded variable
        const userId = window.userId;  // This also comes from PHP

        if (userType === 'seller') {
            // If the user is a seller, hide the store card if it’s not approved or it's not their own store
            if (status === '0' || sellerId !== userId) {
                card.style.display = 'none';
            }
        }
    });
});


document.addEventListener("DOMContentLoaded", function () {
    let carousel = document.querySelector("#imageCarousel");
    let bootstrapCarousel = new bootstrap.Carousel(carousel, {
        interval: 2000, // Auto swipe every 2 seconds
        ride: "carousel"
    });
});
// Toggle between Store and Supplier sections
document.getElementById("stores-filter").addEventListener("click", function() {
    document.getElementById("store-section").style.display = "block";
    document.getElementById("supplier-section").style.display = "none";
    this.classList.add("active");
    document.getElementById("suppliers-filter").classList.remove("active");
});

document.getElementById("suppliers-filter").addEventListener("click", function() {
    document.getElementById("store-section").style.display = "none";
    document.getElementById("supplier-section").style.display = "block";
    this.classList.add("active");
    document.getElementById("stores-filter").classList.remove("active");
});

// Store Scroll Actions
const leftButtonStore = document.querySelector('.scroll-button-left');
const rightButtonStore = document.querySelector('.scroll-button-right');
const storeList = document.querySelector('.store-list');

// Supplier Scroll Actions
const leftButtonSupplier = document.querySelector('.scroll-button-left.supplier-section');
const rightButtonSupplier = document.querySelector('.scroll-button-right.supplier-section');
const supplierList = document.querySelector('.supplier-list');

// Store Scroll
leftButtonStore.addEventListener('click', function() {
    storeList.scrollBy({ left: -300, behavior: 'smooth' });
});

rightButtonStore.addEventListener('click', function() {
    storeList.scrollBy({ left: 300, behavior: 'smooth' });
});

// Supplier Scroll
leftButtonSupplier.addEventListener('click', function() {
    supplierList.scrollBy({ left: -300, behavior: 'smooth' });
});

rightButtonSupplier.addEventListener('click', function() {
    supplierList.scrollBy({ left: 300, behavior: 'smooth' });
});

