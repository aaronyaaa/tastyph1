<?php
session_start();
require_once '../database/config.php';

if (!isset($_SESSION['userId'])) {
    die("Unauthorized access.");
}

$userId = $_SESSION['userId'];
$recipeId = $_POST['recipe_id'];

$title = $_POST['title'];
$servings = $_POST['servings'];
$prep_time = $_POST['prep_time'];
$cook_time = $_POST['cook_time'];
$ingredients = $_POST['ingredients'];
$directions = $_POST['directions'];
$notes = $_POST['notes'];
$recipeImagePath = null;

// If user uploaded a new image
if (isset($_FILES["recipe_image"]) && $_FILES["recipe_image"]["error"] === 0) {
    $targetDir = "../uploads/recipes/";
    if (!is_dir($targetDir)) mkdir($targetDir, 0777, true);

    $fileName = time() . "_" . basename($_FILES["recipe_image"]["name"]);
    $targetFilePath = $targetDir . $fileName;

    if (move_uploaded_file($_FILES["recipe_image"]["tmp_name"], $targetFilePath)) {
        $recipeImagePath = $targetFilePath;
    }
}

$sql = "UPDATE recipes SET title = ?, servings = ?, prep_time = ?, cook_time = ?, ingredients = ?, directions = ?, notes = ?" .
       ($recipeImagePath ? ", recipe_image = ?" : "") .
       " WHERE recipe_id = ? AND user_id = ?";

$stmt = $conn->prepare($sql);

if ($recipeImagePath) {
    $stmt->bind_param("sssssssssii", $title, $servings, $prep_time, $cook_time, $ingredients, $directions, $notes, $recipeImagePath, $recipeId, $userId);
} else {
    $stmt->bind_param("sssssssii", $title, $servings, $prep_time, $cook_time, $ingredients, $directions, $notes, $recipeId, $userId);
}

if ($stmt->execute()) {
    header("Location: ../includes/manage_products.php?updated=1");
} else {
    echo "Error updating recipe: " . $conn->error;
}
?>
