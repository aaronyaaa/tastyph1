<?php
session_start();
include('../database/config.php');

// Check if user is logged in and is a seller
if (!isset($_SESSION['userId']) || $_SESSION['usertype'] !== 'seller') {
    header('Location: ../index.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $categoryName = trim($_POST['category_name'] ?? '');

    if (empty($categoryName)) {
        $_SESSION['error'] = "Category name is required";
        header('Location: ../includes/manage_products.php');
        exit();
    }

    // Check if category already exists
    $checkSql = "SELECT category_id FROM categories WHERE name = ?";
    $checkStmt = $conn->prepare($checkSql);
    $checkStmt->bind_param("s", $categoryName);
    $checkStmt->execute();
    $result = $checkStmt->get_result();

    if ($result->num_rows > 0) {
        $_SESSION['error'] = "Category already exists";
        header('Location: ../includes/manage_products.php');
        exit();
    }

    // Insert new category
    $sql = "INSERT INTO categories (name) VALUES (?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $categoryName);

    if ($stmt->execute()) {
        $_SESSION['success'] = "Category added successfully";
    } else {
        $_SESSION['error'] = "Error adding category: " . $conn->error;
    }

    header('Location: ../includes/manage_products.php');
    exit();
}
?>
