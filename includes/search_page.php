<?php
include("../database/config.php");
include("../database/data_session.php");

// Get the search query from the URL parameter (GET request)
$query = $_GET['query'] ?? '';

// If it's an AJAX request, process it differently
if (isset($_GET['ajax']) && $_GET['ajax'] == 1) {
    // Process search request for AJAX here
    $searchTerm = "%" . $_GET['query'] . "%";
    $items_per_page = 4;
    $offset = 0; // Adjust based on your pagination logic

    // Update the SQL query in search_page.php to use LIKE with wildcards
    $sql = "
(SELECT 'ingredient' AS type, ingredient_id AS id, ingredient_name AS name, description, price, image_url, 'ingredients_page.php' AS link 
FROM ingredients WHERE ingredient_name LIKE ? OR description LIKE ? LIMIT ? OFFSET ?)
UNION
(SELECT 'product' AS type, product_id AS id, Product_name AS name, description, price, image_url, 'product_page.php' AS link 
FROM products WHERE Product_name LIKE ? OR description LIKE ? LIMIT ? OFFSET ?)
UNION
(SELECT 'seller' AS type, seller_id AS id, business_name AS name, description, NULL AS price, profile_pics AS image_url, 'seller_page.php' AS link 
FROM apply_seller WHERE business_name LIKE ? OR description LIKE ? LIMIT ? OFFSET ?)
UNION
(SELECT 'supplier' AS type, supplier_id AS id, business_name AS name, description, NULL AS price, profile_pics AS image_url, 'supplier_page.php' AS link 
FROM apply_supplier WHERE business_name LIKE ? OR description LIKE ? LIMIT ? OFFSET ?)
";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param(
        "ssssssssssssssss",
        $searchTerm,
        $searchTerm,
        $items_per_page,
        $offset,
        $searchTerm,
        $searchTerm,
        $items_per_page,
        $offset,
        $searchTerm,
        $searchTerm,
        $items_per_page,
        $offset,
        $searchTerm,
        $searchTerm,
        $items_per_page,
        $offset
    );
    $stmt->execute();
    $result = $stmt->get_result();

    // Collect the data to return as JSON
    $response = [];
    while ($row = $result->fetch_assoc()) {
        $response[] = $row;
    }

    echo json_encode($response);  // Return search results as JSON
    exit();  // Exit to prevent rendering the page
}

// Normal (non-AJAX) page handling
$searchTerm = "%" . $query . "%";
$items_per_page = 4;

// Get the current page from the URL, defaulting to 1
$page = $_GET['page'] ?? 1;
$page = max(1, intval($page));

// Calculate the offset for the SQL query
$offset = ($page - 1) * $items_per_page;

// Count total results for pagination
$total_sql = "
    (SELECT COUNT(*) FROM ingredients WHERE ingredient_name LIKE ? OR description LIKE ?)
    UNION
    (SELECT COUNT(*) FROM products WHERE Product_name LIKE ? OR description LIKE ?)
    UNION
    (SELECT COUNT(*) FROM apply_seller WHERE business_name LIKE ? OR description LIKE ?)
    UNION
    (SELECT COUNT(*) FROM apply_supplier WHERE business_name LIKE ? OR description LIKE ?)
";
$total_stmt = $conn->prepare($total_sql);
if (!$total_stmt) {
    die("SQL Error: " . $conn->error);
}
$total_stmt->bind_param("ssssssss", $searchTerm, $searchTerm, $searchTerm, $searchTerm, $searchTerm, $searchTerm, $searchTerm, $searchTerm);
$total_stmt->execute();
$total_result = $total_stmt->get_result();
$total_count = 0;
while ($row = $total_result->fetch_row()) {
    $total_count += $row[0];  // Sum all counts from the UNION
}

// Calculate total pages
$total_pages = ceil($total_count / $items_per_page);

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Search Results</title>
    <link href="../css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../css/nav.css">
    <link rel="stylesheet" href="../css/index.css">
    <link rel="stylesheet" href="../css/products.css">

</head>

<body>

    <!-- Navbar -->
    <?php include("../includes/nav_" . strtolower($userType) . ".php"); ?>
    <?php include("floating.php"); ?>
    <!-- Back Button -->
    <!-- Search Bar -->
    <div class="container mt-5">
        <h2>Search Results for "<?= htmlspecialchars($query); ?>"</h2>

        <!-- Large Search Bar Form -->
        <form class="d-flex mb-4" action="search_page.php" method="GET">
            <input class="form-control me-2" type="search" name="query" value="<?= htmlspecialchars($query); ?>" placeholder="Search..." aria-label="Search" required>
            <button class="btn btn-primary" type="submit">Search</button>
        </form>

        <!-- Pagination -->
        <nav aria-label="Page navigation">
            <ul class="pagination justify-content-center">
                <li class="page-item <?= $page <= 1 ? 'disabled' : ''; ?>">
                    <a class="page-link" href="?query=<?= urlencode($query); ?>&page=<?= max(1, $page - 1); ?>">Previous</a>
                </li>
                <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                    <li class="page-item <?= $i === $page ? 'active' : ''; ?>">
                        <a class="page-link" href="?query=<?= urlencode($query); ?>&page=<?= $i; ?>"><?= $i; ?></a>
                    </li>
                <?php endfor; ?>
                <li class="page-item <?= $page >= $total_pages ? 'disabled' : ''; ?>">
                    <a class="page-link" href="?query=<?= urlencode($query); ?>&page=<?= min($total_pages, $page + 1); ?>">Next</a>
                </li>
            </ul>
        </nav>
        <!-- Display Products -->
        <div class="row mb-5">
            <?php
            // Fetch products based on search query
            $product_sql = "SELECT * FROM products WHERE Product_name LIKE ? OR description LIKE ? LIMIT ? OFFSET ?";
            $product_stmt = $conn->prepare($product_sql);
            $product_stmt->bind_param("ssii", $searchTerm, $searchTerm, $items_per_page, $offset);
            $product_stmt->execute();
            $product_result = $product_stmt->get_result();

            while ($product = $product_result->fetch_assoc()): ?>
                <div class="col-md-3 mb-3">
                    <a href="product_page.php?product_id=<?= $product['product_id']; ?>" class="product-link">
                        <div class="card">
                            <img src="../uploads/<?= htmlspecialchars($product['image_url']); ?>" class="card-img-top" alt="Product Image">
                            <div class="card-body">
                                <h5 class="card-title"><?= htmlspecialchars($product['Product_name']); ?></h5>
                                <p><strong>₱<?= number_format($product['price'], 2); ?></strong></p>
                            </div>
                        </div>
                    </a>
                </div>
            <?php endwhile; ?>
        </div>

        <!-- Display Ingredients -->
        <div class="row mb-5">
            <?php
            // Fetch ingredients based on search query
            $ingredient_sql = "SELECT * FROM ingredients WHERE ingredient_name LIKE ? OR description LIKE ? LIMIT ? OFFSET ?";
            $ingredient_stmt = $conn->prepare($ingredient_sql);
            $ingredient_stmt->bind_param("ssii", $searchTerm, $searchTerm, $items_per_page, $offset);
            $ingredient_stmt->execute();
            $ingredients = $ingredient_stmt->get_result();

            if ($ingredients->num_rows > 0):
                while ($row = $ingredients->fetch_assoc()): ?>
                    <div class="col-md-3 mb-3">
                        <a href="ingredient_page.php?ingredient_id=<?= $row['ingredient_id']; ?>" class="ingredient-link">
                            <div class="card">
                                <img src="../uploads/<?= htmlspecialchars($row['image_url']); ?>" class="card-img-top" alt="<?= htmlspecialchars($row['ingredient_name']); ?>">
                                <div class="card-body">
                                    <h5 class="card-title"><?= htmlspecialchars($row['ingredient_name']); ?></h5>
                                    <p><strong>₱<?= number_format($row['price'], 2); ?></strong></p>
                                    <p><strong>Stock:</strong> <?= $row['quantity']; ?> </p>
                                </div>
                            </div>
                        </a>

                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <p class="text-muted">No ingredients available.</p>
            <?php endif; ?>
        </div>

        <!-- Display Stores -->



    </div>




    <script src="../js/bootstrap.bundle.min.js"></script>
    <script src="../js/ajax.js"></script>

</body>

</html>