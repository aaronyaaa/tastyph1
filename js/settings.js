document.addEventListener("DOMContentLoaded", function () {
    const fileInput = document.getElementById("profile_pics");
    const previewImage = document.getElementById("preview");
    const defaultImage = "../uploads/default-profile.png"; // Path to default image

    fileInput.addEventListener("change", function (event) {
        const file = event.target.files[0];

        if (file) {
            const reader = new FileReader();
            reader.onload = function (e) {
                previewImage.src = e.target.result;
            };
            reader.readAsDataURL(file);
        } else {
            // Reset to default if no file is selected
            previewImage.src = defaultImage;
        }
    });
});
