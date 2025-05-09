<?php
include("../database/config.php");

$query = $_GET['query'] ?? '';
if (empty($query)) {
    echo json_encode([]); // No results if query is empty
    exit();
}

$searchTerm = "%" . $query . "%";

// SQL query to search across multiple tables for suggestions
$sql = "
    (SELECT ingredient_name AS name, ingredient_id AS id, 'ingredient_page.php' AS link FROM ingredients WHERE ingredient_name LIKE ? LIMIT 5)
    UNION
    (SELECT Product_name AS name, product_id AS id, 'product_page.php' AS link FROM products WHERE Product_name LIKE ? LIMIT 5)
    UNION
    (SELECT business_name AS name, seller_id AS id, 'store_page.php' AS link FROM apply_seller WHERE business_name LIKE ? LIMIT 5)
    UNION
    (SELECT business_name AS name, supplier_id AS id, 'supplier_page.php' AS link FROM apply_supplier WHERE business_name LIKE ? LIMIT 5)
";

$stmt = $conn->prepare($sql);
$stmt->bind_param("ssss", $searchTerm, $searchTerm, $searchTerm, $searchTerm);
$stmt->execute();
$result = $stmt->get_result();

$suggestions = [];
while ($row = $result->fetch_assoc()) {
    $suggestions[] = $row;
}

echo json_encode($suggestions);
?>
