<?php
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Collect data from POST
    $product_id = $_POST['product_id'];
    $product_name = $_POST['Product_name'];
    $description = $_POST['description'];
    $price = $_POST['price'];
    $quantity = $_POST['quantity'];
    $category_id = $_POST['category_id'];

    // Database connection
    $conn = new mysqli('localhost', 'root', '', 'tastyph1');

    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    // Check if the product exists before updating
    $checkProductSql = "SELECT product_id, image_url FROM products WHERE product_id = ?";
    $stmt = $conn->prepare($checkProductSql);
    $stmt->bind_param("i", $product_id);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        // Product exists, fetch the existing image URL
        $stmt->bind_result($existing_product_id, $existing_image_url);
        $stmt->fetch();

        // Handle image upload
        if (isset($_FILES['image_url']) && $_FILES['image_url']['error'] == 0) {
            $image_url = '../uploads/' . basename($_FILES['image_url']['name']);
            move_uploaded_file($_FILES['image_url']['tmp_name'], $image_url);
        } else {
            $image_url = $existing_image_url;
        }

        // Update product details
        $updateSql = "UPDATE products SET Product_name = ?, description = ?, price = ?, quantity = ?, category_id = ?, image_url = ? WHERE product_id = ?";
        $stmt = $conn->prepare($updateSql);
        $stmt->bind_param("ssdiisi", $product_name, $description, $price, $quantity, $category_id, $image_url, $product_id);

        if ($stmt->execute()) {
            echo "<script>
                alert('Product updated successfully!');
                window.location.href = '../includes/manage_products.php';
            </script>";
        } else {
            echo "<script>
                alert('Error updating product: " . addslashes($stmt->error) . "');
                window.location.href = '../includes/manage_products.php.php';
            </script>";
        }
    } else {
        echo "<script>
            alert('Error: Product not found!');
            window.location.href = '../includes/manage_products.php.php';
        </script>";
    }

    // Close statement and connection
    $stmt->close();
    $conn->close();
}
?>
