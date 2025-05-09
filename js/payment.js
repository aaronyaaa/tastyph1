document.addEventListener("DOMContentLoaded", function () {
    const totalDisplay = document.getElementById("total");
    const totalHidden = document.getElementById("total_hidden");
    const paymentButtons = document.querySelectorAll(".payment-btn");
    const selectedPayment = document.getElementById("selected_payment");
    const checkoutButton = document.querySelector("button[type='submit']");

    function updateHiddenTotal() {
        let totalAmount = parseFloat(totalDisplay.innerText.replace("₱", "").replace(",", ""));
        if (!isNaN(totalAmount) && totalAmount > 0) {
            totalHidden.value = totalAmount.toFixed(2);
        } else {
            totalHidden.value = "0.00";
        }
        console.log("💰 Updated Hidden Total:", totalHidden.value);
    }

    updateHiddenTotal();
    const observer = new MutationObserver(updateHiddenTotal);
    observer.observe(totalDisplay, { childList: true, subtree: true });

    function togglePayment(method) {
        document.getElementById("cashField").classList.add("d-none");
        document.getElementById("gcashField").classList.add("d-none");
        document.getElementById("cardField").classList.add("d-none");

        if (method === "cash") document.getElementById("cashField").classList.remove("d-none");
        if (method === "gcash") document.getElementById("gcashField").classList.remove("d-none");
        if (method === "card") document.getElementById("cardField").classList.remove("d-none");

        selectedPayment.value = method;

        paymentButtons.forEach(btn => {
            btn.classList.remove("btn-primary");
            btn.classList.add("btn-outline-primary");
        });

        document.querySelector(`[data-method="${method}"]`).classList.add("btn-primary");
        document.querySelector(`[data-method="${method}"]`).classList.remove("btn-outline-primary");
    }

    paymentButtons.forEach(button => {
        button.addEventListener("click", function () {
            const method = this.getAttribute("data-method");
            togglePayment(method);
        });
    });

    checkoutButton.addEventListener("click", function (event) {
        updateHiddenTotal();
        if (!selectedPayment.value) {
            event.preventDefault();
            alert("⚠️ Please select a payment method before proceeding.");
        }
    });
});
