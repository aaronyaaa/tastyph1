<?php
include('../database/config.php');
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $ingredient_id = $_POST['ingredient_id'];
    $variant_name = $_POST['variant_name'];
    $price = $_POST['price'];
    $quantity = $_POST['quantity'];
    $quantity_value = $_POST['quantity_value'];
    $unit_type = $_POST['unit_type'];

    // Assuming supplier_id and seller_id come from session or form
    $supplier_id = $_SESSION['userId'] ?? null; // For example, current logged-in user is supplier
    // If seller_id is relevant, get it similarly, e.g. from a form or session
    $seller_id = $_POST['seller_id'] ?? null; // You can adjust as needed

    // Handle image upload
    $imagePath = null;
    if (isset($_FILES['variant_image']) && $_FILES['variant_image']['error'] === UPLOAD_ERR_OK) {
        $tmpName = $_FILES['variant_image']['tmp_name'];
        $imageName = time() . "_" . basename($_FILES['variant_image']['name']); // To avoid conflicts
        $targetPath = "../uploads/" . $imageName;

        if (move_uploaded_file($tmpName, $targetPath)) {
            $imagePath = $targetPath;
        }
    }

    // Prepare SQL with new columns supplier_id and seller_id
    $sql = "INSERT INTO ingredient_variants 
            (ingredient_id, variant_name, price, quantity, quantity_value, unit_type, image_url, supplier_id, seller_id) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";

    $stmt = $conn->prepare($sql);
    if ($stmt === false) {
        die("SQL prepare error: " . $conn->error);
    }

    $stmt->bind_param(
        "isdiissii", 
        $ingredient_id, 
        $variant_name, 
        $price, 
        $quantity, 
        $quantity_value, 
        $unit_type, 
        $imagePath, 
        $supplier_id, 
        $seller_id
    );

    if ($stmt->execute()) {
        header("Location: ../includes/manage_ingredient.php?success=1");
        exit();
    } else {
        echo "Execution error: " . $stmt->error;
    }

    $stmt->close();
}
?>
