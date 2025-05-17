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
    $conn->begin_transaction();
    
    try {
        // First insert into ingredients table
        $sql = "INSERT INTO ingredients (supplier_id, ingredient_name, description, price, quantity, quantity_value, unit_type, created_at, category_id, image_url) 
                VALUES (?, ?, ?, ?, ?, ?, ?, NOW(), ?, ?)";

        $stmt = $conn->prepare($sql);
        if (!$stmt) {
            throw new Exception("Error preparing ingredient insert: " . $conn->error);
        }

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

        if (!$stmt->execute()) {
            throw new Exception("Error inserting ingredient: " . $stmt->error);
        }
        
        $ingredientId = $conn->insert_id;
        $stmt->close();

        // Then create inventory record
        $inventorySql = "INSERT INTO ingredients_inventory 
                        (ingredient_id, ingredient_name, description, quantity, quantity_value, unit_type, price, supplier_id, user_id) 
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
        
        $inventoryStmt = $conn->prepare($inventorySql);
        if (!$inventoryStmt) {
            throw new Exception("Error preparing inventory insert: " . $conn->error);
        }

        $inventoryStmt->bind_param("issiiidsi", 
            $ingredientId,
            $ingredientName,
            $description,
            $stockQuantity,
            $measurementValue,
            $unitType,
            $price,
            $supplierId,
            $supplierId  // user_id is same as supplier_id for supplier's own inventory
        );

        if (!$inventoryStmt->execute()) {
            throw new Exception("Error inserting inventory: " . $inventoryStmt->error);
        }
        
        $inventoryStmt->close();
        
        // Commit transaction
        $conn->commit();
        echo "<script>alert('Ingredient added successfully!'); window.location.href='../includes/manage_ingredient.php';</script>";
        
    } catch (Exception $e) {
        // Rollback transaction on error
        $conn->rollback();
        echo "<script>alert('Error: " . $e->getMessage() . "'); window.history.back();</script>";
    }

    $conn->close();
}
?>
