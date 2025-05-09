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