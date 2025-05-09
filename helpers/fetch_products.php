<?php
// Database connection
include('../database/config.php'); // FIXED TYPO

$query = trim($_GET['query'] ?? '');
$seller_id = intval($_GET['seller_id'] ?? 0);

// Debugging logs (check errors in browser console or PHP error log)
error_log("Query: $query | Seller ID: $seller_id");

// Validate input
if (empty($query) || $seller_id <= 0) {
    echo "<div class='product-item text-muted'>No matches found</div>";
    exit;
}

// Prepare SQL query
$sql = "SELECT product_id, product_name, image_url FROM products WHERE seller_id = ? AND product_name LIKE ? LIMIT 5";
$stmt = $conn->prepare($sql);

if (!$stmt) {
    die("SQL Error: " . $conn->error);
}

$searchTerm = "%$query%";
$stmt->bind_param("is", $seller_id, $searchTerm);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        echo "<div class='product-item' data-name='" . htmlspecialchars($row['product_name']) . "'>";
        echo "<img src='" . htmlspecialchars($row['image_url']) . "' alt='Product Image' class='product-image'>";
        echo "<span>" . htmlspecialchars($row['product_name']) . "</span>";
        echo "</div>";
    }
} else {
    echo "<div class='product-item text-muted'>No matches found</div>";
}

$stmt->close();
$conn->close();
?>
