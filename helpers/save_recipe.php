

<?php
session_start();
require_once '../database/config.php';

if (!isset($_SESSION['userId'])) {
    die("You must be logged in to save a recipe.");
}

$userId = $_SESSION['userId'];


$title = $_POST['title'] ?? '';
$servings = $_POST['servings'] ?? '';
$prep_time = $_POST['prep_time'] ?? '';
$cook_time = $_POST['cook_time'] ?? '';
$ingredients = $_POST['ingredients'] ?? '';
$directions = $_POST['directions'] ?? '';
$notes = $_POST['notes'] ?? '';


$recipeImagePath = null;

if (isset($_FILES["recipe_image"]) && $_FILES["recipe_image"]["error"] == 0) {
    $targetDir = "../uploads/recipes/";
    if (!is_dir($targetDir)) {
        mkdir($targetDir, 0777, true);
    }

    $fileName = basename($_FILES["recipe_image"]["name"]);
    $targetFilePath = $targetDir . time() . "_" . $fileName;

    if (move_uploaded_file($_FILES["recipe_image"]["tmp_name"], $targetFilePath)) {
        $recipeImagePath = $targetFilePath;
    }
}

$sql = "INSERT INTO recipes (user_id, title, servings, prep_time, cook_time, ingredients, directions, notes, recipe_image) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";

$stmt = $conn->prepare($sql);
$stmt->bind_param("issssssss", $userId, $title, $servings, $prep_time, $cook_time, $ingredients, $directions, $notes, $recipeImagePath);
if ($stmt->execute()) {
    // Prevent resubmission by redirecting with header()
    header("Location: ../path-to-your-dashboard-or-form.php?success=1");
    exit;
}

