<?php
include('../database/config.php');
session_start();

// Validate inputs
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $ingredient_id = $_POST['ingredient_id'];
    $variant_name = $_POST['variant_name'];
    $price = $_POST['price'];
    $quantity = $_POST['quantity'];
    $quantity_value = $_POST['quantity_value'];
    $unit_type = $_POST['unit_type'];

    // Handle image upload
    $imagePath = null;
    if (isset($_FILES['variant_image']) && $_FILES['variant_image']['error'] === UPLOAD_ERR_OK) {
        $tmpName = $_FILES['variant_image']['tmp_name'];
        $imageName = basename($_FILES['variant_image']['name']);
        $targetPath = "../uploads/" . $imageName;

        if (move_uploaded_file($tmpName, $targetPath)) {
            $imagePath = $targetPath;
        }
    }

    // Prepare SQL
    $sql = "INSERT INTO ingredient_variants 
            (ingredient_id, variant_name, price, quantity, quantity_value, unit_type, image_url) 
            VALUES (?, ?, ?, ?, ?, ?, ?)";

    $stmt = $conn->prepare($sql);

    if ($stmt === false) {
        die("SQL prepare error: " . $conn->error); // Debug output
    }

    $stmt->bind_param("isdiiss", $ingredient_id, $variant_name, $price, $quantity, $quantity_value, $unit_type, $imagePath);

    if ($stmt->execute()) {
        header("Location: ../includes/manage_ingredient.php?success=1");
        exit();
    } else {
        echo "Execution error: " . $stmt->error;
    }

    $stmt->close();
}
?>
