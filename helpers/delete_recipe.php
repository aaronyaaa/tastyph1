<?php
session_start();
require_once '../database/config.php';

if (!isset($_SESSION['userId']) || $_SERVER['REQUEST_METHOD'] !== 'POST') {
    die("Unauthorized access.");
}

$userId = $_SESSION['userId'];
$recipeId = $_POST['recipe_id'];

$stmt = $conn->prepare("DELETE FROM recipes WHERE recipe_id = ? AND user_id = ?");
$stmt->bind_param("ii", $recipeId, $userId);

if ($stmt->execute()) {
    header("Location: ../includes/manage_products.php?deleted=1");
    exit;
} else {
    echo "Error deleting recipe: " . $conn->error;
}
?>
