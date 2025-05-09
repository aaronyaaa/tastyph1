<?php
include("../database/session.php");
include("../database/config.php");

// Ensure the user is logged in
$userType = $_SESSION['usertype'] ?? 'user'; // Default to 'user' if not set
$userId = $_SESSION['userId'] ?? null;

// Fetch user details
$first_name = $_SESSION['first_name'] ?? 'Guest';
$last_name = $_SESSION['last_name'] ?? '';

// Fetch cart count from the database
$cartCount = 0;
if ($userId) {
    $sql = "SELECT COUNT(DISTINCT product_id, ingredient_id) AS total_items FROM cart WHERE user_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    $cartData = $result->fetch_assoc();
    $cartCount = $cartData['total_items'] ?? 0; // Only count unique product/ingredient entries
}

?>

<nav class="navbar navbar-expand-lg navbar-dark fixed-top" style="background-color: darkviolet;">
    <div class="container-fluid">
        <img src="../images/logo.png" alt="TASTYPH Logo" class="logo">
        <a class="navbar-brand" href="#"><b>TASTYPH</b></a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav"
            aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-auto">
                <li class="nav-item">
                    <a class="nav-link active" aria-current="page" href="../user/home.php">Home</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="product.php">Products</a>
                </li>


                <!-- Search Bar -->
                <!-- Search Icon in Navbar -->
                <li class="nav-item">
                    <a class="nav-link" href="../includes/search_page.php">
                        <ion-icon name="search-outline" size="large"></ion-icon>
                    </a>
                </li>

                <!-- Add to Cart Icon in Navbar -->
                <li class="nav-item">
                    <a class="nav-link position-relative" href="../cart/cart.php">
                        <ion-icon name="cart-outline" size="large"></ion-icon> <!-- Ionicons Cart Icon -->
                        <span id="cart-badge" class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger"
                            style="<?= $cartCount > 0 ? 'display:inline-block;' : 'display:none;' ?>">
                            <?= $cartCount; ?> <!-- Cart Count -->
                        </span>
                    </a>
                </li>

                <!-- Notifications Icon -->
                
                <!-- Messages Icon -->
                <li class="nav-item">
                    <a class="nav-link" href="../includes/chat.php">
                        <ion-icon name="chatbubble-outline" size="large"></ion-icon> <!-- Ionicons Chat Icon -->
                    </a>
                </li>
                
                <!-- Orders Icon -->
                <li class="nav-item">
                    <a class="nav-link" href="../includes/orders.php">
                        <ion-icon name="list-outline" size="large"></ion-icon> <!-- Ionicons List Icon for Orders -->
                    </a>
                </li>
                
                <li class="nav-item">
                    <a class="nav-link" href="notifications.php">
                        <ion-icon name="notifications-outline" size="large"></ion-icon> <!-- Ionicons Notifications Icon -->
                    </a>
                </li>
                <!-- Dropdown User Menu (Optional for logout or settings) -->
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" id="navbarDropdownMenuLink" role="button"
                        data-bs-toggle="dropdown" aria-expanded="false">
                        <?php echo "Welcome, $firstName $lastName"; ?>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="navbarDropdownMenuLink">
                        <li><a class="dropdown-item" href="../includes/settings.php">Settings</a></li>
                        <li><a class="dropdown-item" href="../includes/manage_products.php">Manage Store</a></li>
                        <li><a class="dropdown-item" href="../cart/my_orders.php">My Orders</a></li>
                        <li><a class="dropdown-item" href="../auth/logout.php">Logout</a></li>
                    </ul>
                </li>

                                <!-- User Profile Picture -->
                                <li class="nav-item">
                    <?php if (!empty($profilePics)) : ?>
                        <img src="../uploads/<?php echo htmlspecialchars($profilePics); ?>" alt="User Photo"
                            class="rounded-circle bg-secondary d-flex align-items-center justify-content-center"
                            style="width: 40px; height: 40px; object-fit: cover; overflow: hidden;">
                    <?php else : ?>
                        <div class="rounded-circle bg-secondary d-flex align-items-center justify-content-center"
                            style="width: 32px; height: 32px; overflow: hidden;">
                            <i class="bi bi-person-fill text-light fs-4"></i>
                        </div>
                    <?php endif; ?>
                </li>

            </ul>
        </div>
    </div>
</nav>

<script>
    document.addEventListener("DOMContentLoaded", function() {
        updateCartCount();
    });
</script>

<!-- Ionicons Script (Ensure It's Loaded) -->
<script type="module" src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.esm.js"></script>
<script nomodule src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.js"></script>

<script src="../js/add_to_cart.js"></script>
