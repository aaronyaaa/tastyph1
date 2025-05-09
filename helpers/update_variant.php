<?php
include('../database/config.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $variant_id = $_POST['variant_id'];
    $variant_name = $_POST['variant_name'];
    $price = $_POST['price'];
    $quantity = $_POST['quantity'];
    $quantity_value = $_POST['quantity_value'];
    $unit_type = $_POST['unit_type'];

    // Optional image
    $image_url = null;
    if (isset($_FILES['image_url']) && $_FILES['image_url']['error'] === UPLOAD_ERR_OK) {
        $tmpName = $_FILES['image_url']['tmp_name'];
        $imageName = basename($_FILES['image_url']['name']);
        $targetPath = "../uploads/" . $imageName;

        if (move_uploaded_file($tmpName, $targetPath)) {
            $image_url = $targetPath;
        }
    }

    $sql = $image_url ?
        "UPDATE ingredient_variants SET variant_name=?, price=?, quantity=?, quantity_value=?, unit_type=?, image_url=? WHERE variant_id=?" :
        "UPDATE ingredient_variants SET variant_name=?, price=?, quantity=?, quantity_value=?, unit_type=? WHERE variant_id=?";

    $stmt = $conn->prepare($sql);

    if ($image_url) {
        $stmt->bind_param("sdiissi", $variant_name, $price, $quantity, $quantity_value, $unit_type, $image_url, $variant_id);
    } else {
        $stmt->bind_param("sdiisi", $variant_name, $price, $quantity, $quantity_value, $unit_type, $variant_id);
    }

    if ($stmt->execute()) {
        header("Location: ../includes/manage_ingredient.php?variant_updated=1");
    } else {
        echo "Error: " . $stmt->error;
    }

    $stmt->close();
}
?>
