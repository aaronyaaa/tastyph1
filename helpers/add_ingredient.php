<?php
include("../database/config.php"); // Ensure the database connection is included
session_start();

// Ensure the user is a supplier and get their supplier ID
$supplierId = $_SESSION['userId'] ?? null;
if (!$supplierId) {
    echo "<script>alert('Error: Supplier ID not found in session.'); window.history.back();</script>";
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Retrieve form data
    $ingredientName = $_POST['ingredient_name'];
    $description = $_POST['description'];
    $price = $_POST['price'];
    $stockQuantity = $_POST['quantity']; // This represents stock availability
    $measurementValue = $_POST['quantity_value']; // The numeric value of measurement
    $unitType = $_POST['unit_type']; // The unit type (g, kg, ml, etc.)
    $categoryId = $_POST['category_id'];

    // Validate required fields
    if (empty($ingredientName) || empty($price) || empty($stockQuantity) || empty($measurementValue) || empty($unitType) || empty($categoryId)) {
        echo "<script>alert('Error: All fields are required.'); window.history.back();</script>";
        exit();
    }

    // Handle the image upload
    $imageUrl = ''; // Default empty string for the image URL
    if (isset($_FILES['image_url']) && $_FILES['image_url']['error'] == 0) {
        $imageTmpName = $_FILES['image_url']['tmp_name'];
        $imageName = time() . "_" . basename($_FILES['image_url']['name']); // Prevent filename conflicts
        $imagePath = '../uploads/' . $imageName; // Assuming 'uploads' is your image directory

        // Move the uploaded image to the desired folder
        if (move_uploaded_file($imageTmpName, $imagePath)) {
            $imageUrl = $imagePath; // Save the path to the image
        } else {
            echo "<script>alert('Error: Unable to upload the image.'); window.history.back();</script>";
            exit();
        }
    }

    // Insert the ingredient into the database
    $sql = "INSERT INTO ingredients (supplier_id, ingredient_name, description, price, quantity, quantity_value, unit_type, created_at, category_id, image_url) 
            VALUES (?, ?, ?, ?, ?, ?, ?, NOW(), ?, ?)";

    if ($stmt = $conn->prepare($sql)) {
        // Bind the parameters (9 placeholders = 9 variables)
        $stmt->bind_param("issdiisss", 
            $supplierId, 
            $ingredientName, 
            $description, 
            $price, 
            $stockQuantity, 
            $measurementValue, 
            $unitType, 
            $categoryId, 
            $imageUrl
        );

        // Execute the statement
        if ($stmt->execute()) {
            echo "<script>alert('Ingredient added successfully!'); window.location.href='../includes/manage_ingredient.php';</script>";
        } else {
            echo "<script>alert('Error: " . $stmt->error . "'); window.history.back();</script>";
        }
        $stmt->close();
    } else {
        echo "<script>alert('Error preparing statement: " . $conn->error . "'); window.history.back();</script>";
    }

    $conn->close();
}
?>
