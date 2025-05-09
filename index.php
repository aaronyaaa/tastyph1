<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Online Store</title>
    <link href="css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/styles.css">
    <link rel="stylesheet" href="css/nav.css">
    <link rel="stylesheet" href="css/modal.css">

</head>

<body>
    <!-- Navbar -->
    <?php include("includes/nav.php");?>
    

    <!-- Hero Section -->
    <?php include("includes/body.php"); ?>

    <!-- Footer -->
    <?php include("includes/footer.php"); ?>

    <?php include("includes/modal.php"); ?>
	
    <script src="js/bootstrap.bundle.min.js"></script>

	<script>
document.addEventListener("DOMContentLoaded", function() {
    document.querySelectorAll("[data-bs-toggle='modal']").forEach(button => {
        button.addEventListener("click", function(event) {
            let targetModal = document.querySelector(this.dataset.bsTarget);
            if (targetModal) {
                let modal = new bootstrap.Modal(targetModal);
                modal.show();
            }
        });
    });
});
</script>

</body>

</html>
