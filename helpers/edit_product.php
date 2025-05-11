<?php
session_start();
include('../database/config.php'); // Use the existing database configuration

// Check if user is logged in and is a seller
if (!isset($_SESSION['userId']) || $_SESSION['usertype'] !== 'seller') {
    header('Location: ../index.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    try {
        // Debug logging
        error_log("POST data received: " . print_r($_POST, true));
        error_log("FILES data received: " . print_r($_FILES, true));

        // Validate required fields
        $required_fields = ['product_id', 'Product_name', 'description', 'price', 'quantity', 'category_id'];
        foreach ($required_fields as $field) {
            if (!isset($_POST[$field]) || empty($_POST[$field])) {
                throw new Exception("Missing required field: $field");
            }
        }

        // Collect and sanitize data from POST
        $product_id = filter_var($_POST['product_id'], FILTER_VALIDATE_INT);
        $product_name = trim($_POST['Product_name']);
        $description = trim($_POST['description']);
        $price = filter_var($_POST['price'], FILTER_VALIDATE_FLOAT);
        $quantity = filter_var($_POST['quantity'], FILTER_VALIDATE_INT);
        $category_id = filter_var($_POST['category_id'], FILTER_VALIDATE_INT);

        // Debug logging
        error_log("Sanitized data: product_id=$product_id, category_id=$category_id");

        // Validate numeric fields
        if ($product_id === false || $price === false || $quantity === false || $category_id === false) {
            throw new Exception("Invalid numeric values provided");
        }

        // Check if the product exists and belongs to the current seller
        $checkProductSql = "SELECT product_id, image_url FROM products WHERE product_id = ? AND seller_id = ?";
        $stmt = $conn->prepare($checkProductSql);
        if (!$stmt) {
            throw new Exception("Database error: " . $conn->error);
        }

        $stmt->bind_param("ii", $product_id, $_SESSION['userId']);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 0) {
            throw new Exception("Product not found or unauthorized access");
        }

        $product = $result->fetch_assoc();
        $existing_image_url = $product['image_url'];

        // Handle image upload
        $image_url = $existing_image_url; // Default to existing image
        if (isset($_FILES['image_url']) && $_FILES['image_url']['error'] == 0) {
            $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
            $file_type = $_FILES['image_url']['type'];
            
            if (!in_array($file_type, $allowed_types)) {
                throw new Exception("Invalid file type. Only JPG, PNG and GIF are allowed.");
            }

            $upload_dir = '../uploads/';
            if (!file_exists($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }

            $file_extension = pathinfo($_FILES['image_url']['name'], PATHINFO_EXTENSION);
            $new_filename = uniqid('product_') . '.' . $file_extension;
            $image_url = $upload_dir . $new_filename;

            if (!move_uploaded_file($_FILES['image_url']['tmp_name'], $image_url)) {
                throw new Exception("Failed to upload image");
            }

            // Delete old image if it exists and is not the default
            if ($existing_image_url && file_exists($existing_image_url) && strpos($existing_image_url, 'default') === false) {
                unlink($existing_image_url);
            }
        }

        // Update product details
        $updateSql = "UPDATE products SET 
                     Product_name = ?, 
                     description = ?, 
                     price = ?, 
                     quantity = ?, 
                     category_id = ?, 
                     image_url = ? 
                     WHERE product_id = ? AND seller_id = ?";
                     
        $stmt = $conn->prepare($updateSql);
        if (!$stmt) {
            throw new Exception("Database error: " . $conn->error);
        }

        // Debug logging
        error_log("Executing update with values: name=$product_name, category_id=$category_id");

        $stmt->bind_param("ssdiisii", 
            $product_name, 
            $description, 
            $price, 
            $quantity, 
            $category_id, 
            $image_url, 
            $product_id,
            $_SESSION['userId']
        );

        if (!$stmt->execute()) {
            throw new Exception("Error updating product: " . $stmt->error);
        }

        // Debug logging
        error_log("Product updated successfully");

        $_SESSION['success'] = "Product updated successfully!";
        header("Location: ../includes/manage_products.php");
        exit();

    } catch (Exception $e) {
        error_log("Error in edit_product.php: " . $e->getMessage());
        $_SESSION['error'] = $e->getMessage();
        header("Location: ../includes/manage_products.php");
        exit();
    } finally {
        if (isset($stmt)) {
            $stmt->close();
        }
    }
} else {
    header("Location: ../includes/manage_products.php");
    exit();
}
?>
