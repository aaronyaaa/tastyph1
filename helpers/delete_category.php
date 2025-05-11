<?php
session_start();
include('../database/config.php');

// Check if user is logged in and is a seller
if (!isset($_SESSION['userId']) || $_SESSION['usertype'] !== 'seller') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $categoryId = intval($_POST['category_id'] ?? 0);

    if ($categoryId <= 0) {
        echo json_encode(['success' => false, 'message' => 'Invalid category ID']);
        exit();
    }

    // Check if category is in use
    $checkSql = "SELECT COUNT(*) as count FROM products WHERE category_id = ?";
    $checkStmt = $conn->prepare($checkSql);
    $checkStmt->bind_param("i", $categoryId);
    $checkStmt->execute();
    $result = $checkStmt->get_result();
    $row = $result->fetch_assoc();

    if ($row['count'] > 0) {
        echo json_encode(['success' => false, 'message' => 'Cannot delete category: It is being used by products']);
        exit();
    }

    // Delete category
    $sql = "DELETE FROM categories WHERE category_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $categoryId);

    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Category deleted successfully']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error deleting category: ' . $conn->error]);
    }
    exit();
}

echo json_encode(['success' => false, 'message' => 'Invalid request method']);
exit();
?> 