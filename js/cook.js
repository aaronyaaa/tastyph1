$(document).ready(function () {
    // Handle batch count input
    $("#batch_count").on("input", function () {
        let batchCount = parseInt($(this).val()) || 1;
        let maxPossibleBatches = parseInt($("#maxBatches").text());

        if (batchCount > maxPossibleBatches) {
            $(this).val(maxPossibleBatches);
        }

        $(".quantity-use-input").each(function () {
            let neededPerBatch = $(this).closest("tr").data("needed");
            $(this).val(neededPerBatch * batchCount);
        });

        $(".quantity-value").each(function () {
            let neededPerBatch = $(this).closest("tr").data("needed");
            let availableQty = $(this).closest("tr").find(".available-qty").text();
            let quantityValue = availableQty * neededPerBatch;
            let usedQty = $(this).closest("tr").find(".quantity-use-input").val();
            $(this).text(quantityValue - usedQty); // Subtract only from Quantity Value
        });

        updateCookButton();
    });

    // Update cook button state based on available ingredients
    function updateCookButton() {
        let hasIngredients = false;

        $(".available-qty").each(function () {
            if (parseInt($(this).text()) > 0) {
                hasIngredients = true;
            }
        });

        $("#cookButton").prop("disabled", !hasIngredients);
    }

    updateCookButton(); // Call on page load

    // Handle form submission for cooking
    $("#cookForm").submit(function (event) {
        event.preventDefault();

        if ($("#cookButton").prop("disabled")) {
            alert("Not enough ingredients to cook.");
            return;
        }

        $("#cookingModal").modal("show");

        let formData = $(this).serialize();

        setTimeout(function () {
            $("#cookingStatus").text("DONE COOKING!");

            setTimeout(function () {
                $.ajax({
                    url: "../includes/cook_turon.php",
                    type: "POST",
                    data: formData,
                    dataType: "json",
                    success: function (response) {
                        $("#cookingModal").modal("hide");
                        alert(response.message);
                        if (response.success) {
                            location.reload();
                        }
                    },
                    error: function (xhr, status, error) {
                        $("#cookingModal").modal("hide");
                        alert("Error processing request.");
                    }
                });
            }, 2000);
        }, 3000); // Simulate cooking time
    });
});
