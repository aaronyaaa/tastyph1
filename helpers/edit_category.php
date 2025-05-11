<?php
session_start();
include('../database/config.php');

// Check if user is logged in and is a seller
if (!isset($_SESSION['userId']) || $_SESSION['usertype'] !== 'seller') {
    header('Location: ../index.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $categoryId = intval($_POST['category_id'] ?? 0);
    $categoryName = trim($_POST['category_name'] ?? '');

    if (empty($categoryName) || $categoryId <= 0) {
        $_SESSION['error'] = "Invalid category data";
        header('Location: ../includes/manage_products.php');
        exit();
    }

    // Check if category name already exists (excluding current category)
    $checkSql = "SELECT category_id FROM categories WHERE name = ? AND category_id != ?";
    $checkStmt = $conn->prepare($checkSql);
    $checkStmt->bind_param("si", $categoryName, $categoryId);
    $checkStmt->execute();
    $result = $checkStmt->get_result();

    if ($result->num_rows > 0) {
        $_SESSION['error'] = "Category name already exists";
        header('Location: ../includes/manage_products.php');
        exit();
    }

    // Update category
    $sql = "UPDATE categories SET name = ? WHERE category_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("si", $categoryName, $categoryId);

    if ($stmt->execute()) {
        $_SESSION['success'] = "Category updated successfully";
    } else {
        $_SESSION['error'] = "Error updating category: " . $conn->error;
    }

    header('Location: ../includes/manage_products.php');
    exit();
}
?> 