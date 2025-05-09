<?php
include('../database/config.php');

$ingredient_id = $_GET['ingredient_id'] ?? '';

if (!is_numeric($ingredient_id)) {
    echo json_encode([]);
    exit;
}

$sql = "SELECT * FROM ingredient_variants WHERE ingredient_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $ingredient_id);
$stmt->execute();
$result = $stmt->get_result();

$variants = [];
while ($row = $result->fetch_assoc()) {
    $variants[] = $row;
}
echo json_encode($variants);
?>
