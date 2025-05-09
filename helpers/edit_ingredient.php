<?php
include("../database/config.php"); // Ensure the database connection is included
include("../database/data_session.php"); // Ensure the database connection is included
include("../database/session.php"); // Ensure the database connection is included


if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Collect data from POST
    $ingredient_id = $_POST['ingredient_id'];
    $ingredient_name = $_POST['ingredient_name'];
    $description = $_POST['description'];
    $price = $_POST['price'];
    $quantity = $_POST['quantity']; // Stock availability
    $quantity_value = $_POST['quantity_value']; // Measurement value
    $unit_type = $_POST['unit_type']; // Measurement unit
    $category_id = $_POST['category_id'];
    $supplier_id = $_SESSION['userId']; // Ensure supplier_id is defined in session

    // Database connection

    // Check if the ingredient exists before updating
    $checkIngredientSql = "SELECT ingredient_id, image_url FROM ingredients WHERE ingredient_id = ? AND supplier_id = ?";
    $stmt = $conn->prepare($checkIngredientSql);
    $stmt->bind_param("ii", $ingredient_id, $supplier_id);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        // Ingredient exists, fetch the existing image URL
        $stmt->bind_result($existing_ingredient_id, $existing_image_url);
        $stmt->fetch();
        $stmt->close(); // Close previous statement

        // Handle image upload
        if (isset($_FILES['image_url']) && $_FILES['image_url']['error'] == 0) {
            // Generate a unique filename
            $imageTmpName = $_FILES['image_url']['tmp_name'];
            $imageName = time() . "_" . basename($_FILES['image_url']['name']);
            $imagePath = '../uploads/' . $imageName;

            if (move_uploaded_file($imageTmpName, $imagePath)) {
                $image_url = $imagePath; // Save new image path
            } else {
                echo "<script>alert('Error uploading image.'); window.history.back();</script>";
                exit;
            }
        } else {
            // No new image was uploaded, keep the existing image URL
            $image_url = $existing_image_url;
        }

        // **✅ Fixed `bind_param()` Types**
        $updateSql = "UPDATE ingredients 
                      SET ingredient_name = ?, description = ?, price = ?, quantity = ?, quantity_value = ?, unit_type = ?, category_id = ?, image_url = ? 
                      WHERE ingredient_id = ? AND supplier_id = ?";
        $stmt = $conn->prepare($updateSql);
        $stmt->bind_param("ssdiisssii", 
            $ingredient_name, 
            $description, 
            $price, 
            $quantity, 
            $quantity_value, 
            $unit_type, 
            $category_id, 
            $image_url, 
            $ingredient_id, 
            $supplier_id
        );

        if ($stmt->execute()) {
            echo "<script>alert('Ingredient updated successfully!'); window.location.href='../includes/manage_ingredient.php';</script>";
        } else {
            echo "<script>alert('Error updating ingredient: " . $stmt->error . "'); window.history.back();</script>";
        }
    } else {
        echo "<script>alert('Error: Ingredient not found or does not belong to this supplier.'); window.history.back();</script>";
    }

    // Close statement and connection
    $stmt->close();
    $conn->close();
}
?>
