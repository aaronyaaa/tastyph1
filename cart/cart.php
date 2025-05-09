<?php
session_start();
include("../database/config.php");
include("../database/session.php");

// Ensure session and database connection exist
if (!isset($_SESSION['userId'])) {
    die("Session is not set. Please log in.");
}

if (!$conn) {
    die("Database connection error.");
}

$userType = $_SESSION['usertype'] ?? 'user';

// Ensure navigation file exists before including it


$userId = $_SESSION['userId'] ?? null;
if (!$userId) {
    die("Please log in to view your cart.");
}

// Fetch user location (Mockup - Replace with real location system)
$userLocation = "Toril (Pob.), Davao, Davao Del Sur";

// Fetch cart items grouped by store
$sql = "SELECT 
            c.cart_id, c.quantity, c.total_price, c.added_at,
            c.product_id, c.ingredient_id, c.variant_id,
            p.product_name, p.image_url AS product_image, p.seller_id, p.price AS product_price, p.quantity AS product_stock,
            i.ingredient_name, i.image_url AS ingredient_image, i.supplier_id, i.price AS ingredient_price, i.quantity AS ingredient_stock,
            v.variant_name, v.image_url AS variant_image, v.price AS variant_price, v.quantity AS variant_stock, v.ingredient_id AS variant_ingredient_id,
            s.business_name AS seller_name, sup.business_name AS supplier_name
        FROM cart c
        LEFT JOIN products p ON c.product_id = p.product_id
        LEFT JOIN ingredients i ON c.ingredient_id = i.ingredient_id
        LEFT JOIN ingredient_variants v ON c.variant_id = v.variant_id
        LEFT JOIN apply_seller s ON p.seller_id = s.seller_id
        LEFT JOIN apply_supplier sup ON COALESCE(i.supplier_id, (
            SELECT i2.supplier_id FROM ingredients i2 WHERE i2.ingredient_id = v.ingredient_id
        )) = sup.supplier_id
        WHERE c.user_id = ?
        ORDER BY sup.business_name, s.business_name";



$stmt = $conn->prepare($sql);
if (!$stmt) {
    die("Cart query failed: " . $conn->error);
}
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();

// Organize products by store
$cartItems = [];
while ($row = $result->fetch_assoc()) {
    $storeName = $row['seller_name'] ?? $row['supplier_name'];
    $cartItems[$storeName][] = $row;
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Shopping Cart</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../css/cart.css">
    <link rel="stylesheet" href="../css/nav.css">
    <link rel="stylesheet" href="../css/index.css">


</head>

<body>
    <?php $navFile = "../includes/nav_" . strtolower($userType) . ".php";
    if (!file_exists($navFile)) {
        die("Navigation file missing: $navFile");
    }
    include($navFile); ?>

    <div class="container mt-4 cart-container">
        <div class="cart-items">
            <h2>Your Cart</h2>

            <!-- Select All Checkbox -->
            <div>
                <input type="checkbox" id="select-all">
                <label for="select-all">SELECT ALL (<?= count($cartItems) ?> ITEM(S))</label>
            </div>
            <!-- Delete Selected Button -->
            <button class="btn btn-danger btn-sm my-2" onclick="deleteSelectedItems()">Delete Selected</button>

            <?php if (!empty($cartItems)): ?>
                <?php foreach ($cartItems as $store => $items): ?>
                    <div class="store-container">
                        <div class="store-header">
                            <input type="checkbox" class="store-checkbox">
                            <?= htmlspecialchars($store) ?>
                        </div>

                        <?php foreach ($items as $row): ?>
                            <div class="product-item">
                                <input type="checkbox" class="item-checkbox" data-cart-id="<?= $row['cart_id'] ?>">
                                <?php
                                $itemName = $row['variant_name'] ?? $row['product_name'] ?? $row['ingredient_name'];
                                $itemImage = $row['variant_image'] ?? $row['product_image'] ?? $row['ingredient_image'];
                                $itemPrice = $row['variant_price'] ?? $row['product_price'] ?? $row['ingredient_price'];
                                $itemStock = $row['variant_stock'] ?? $row['product_stock'] ?? $row['ingredient_stock'];
                                ?>
                                <img src="../uploads/<?= htmlspecialchars($itemImage); ?>" class="product-image">
                                <div class="product-details">
                                    <strong><?= htmlspecialchars($itemName); ?></strong>
                                    <div><span class="text-muted">Original Price:</span> ₱<?= number_format($itemPrice, 2); ?></div>
                                    <div><span class="text-muted">Available Stock:</span> <?= htmlspecialchars($itemStock); ?></div>
                                    <div>
                                        <span class="text-muted">Subtotal:</span>
                                        <span class="product-subtotal" data-cart-id="<?= $row['cart_id'] ?>">₱<?= number_format($row['total_price'], 2); ?></span>
                                    </div>
                                </div>
                                <div class="quantity-selector">
                                    <button type="button" onclick="updateQuantity(-1, <?= $row['cart_id'] ?>)">-</button>
                                    <input type="number"
                                        value="<?= htmlspecialchars($row['quantity']); ?>"
                                        min="1"
                                        max="<?= htmlspecialchars($itemStock); ?>"
                                        step="1"
                                        class="form-control text-center quantity-input"
                                        data-cart-id="<?= $row['cart_id'] ?>"
                                        style="width: 80px;">
                                    <button type="button" onclick="updateQuantity(1, <?= $row['cart_id'] ?>)">+</button>
                                </div>

                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p>Your cart is empty.</p>
            <?php endif; ?>
        </div>

        <!-- Checkout Summary Section -->
        <div class="cart-summary">
            <h4>Order Summary</h4>
            <p><strong>Location:</strong> <?= htmlspecialchars($userLocation); ?></p>
            <p><strong>Subtotal:</strong> <span id="subtotal">0.00</span></p>
            <p><strong>Shipping Fee:</strong> <span id="shipping">0.00</span></p>
            <input type="text" class="form-control" placeholder="Enter Voucher Code">
            <button class="btn btn-primary btn-block mt-2">APPLY</button>
            <hr>
            <p><strong>Total:</strong><span id="total">0.00</span></p>

            <!-- Hidden input to store the displayed total price -->
            <input type="hidden" id="total_hidden" name="total_price" value="0.00">

            <!-- Checkout Form --><!-- Hidden input to store selected cart items -->
            <form action="../helpers/submit_cart.php" method="POST" enctype="multipart/form-data">
                <h5>Payment Method</h5>

                <!-- Hidden input to store selected cart items -->
                <input type="hidden" id="selected_items" name="selected_items" value="">

                <div class="btn-group w-100" role="group">
                    <button type="button" class="btn btn-outline-primary payment-btn" data-method="cash">Cash</button>
                    <button type="button" class="btn btn-outline-primary payment-btn" data-method="gcash">GCash</button>
                    <button type="button" class="btn btn-outline-primary payment-btn" data-method="card">Card Payment</button>
                </div>

                <!-- Hidden input for storing selected payment method -->
                <input type="hidden" id="selected_payment" name="payment_method" required>

                <!-- Cash input field -->
                <div id="cashField" class="mt-3 d-none">
                    <label for="cash_amount">Enter Cash Amount:</label>
                    <div class="input-group">
                        <span class="input-group-text">₱</span>
                        <input type="number" class="form-control" id="cash_amount" name="cash_amount" placeholder="Enter cash amount">
                    </div>
                </div>

                <!-- GCash receipt upload field -->
                <div id="gcashField" class="mt-3 d-none">
                    <label for="gcash_receipt">Upload GCash Receipt:</label>
                    <input type="file" class="form-control" id="gcash_receipt" name="gcash_receipt" accept="image/*">
                </div>

                <!-- Card Payment Fields -->
                <div id="cardField" class="mt-3 d-none">
                    <label for="card_number">Card Number:</label>
                    <input type="text" class="form-control" id="card_number" name="card_number" placeholder="XXXX XXXX XXXX XXXX" maxlength="19">
                    <div class="row mt-2">
                        <div class="col-6">
                            <label for="expiry_date">Expiry Date:</label>
                            <input type="text" class="form-control" id="expiry_date" name="expiry_date" placeholder="MM/YY" maxlength="5">
                        </div>
                        <div class="col-6">
                            <label for="cvv">CVV:</label>
                            <input type="text" class="form-control" id="cvv" name="cvv" placeholder="123" maxlength="3">
                        </div>
                    </div>
                </div>

                <div class="mt-4">
                    <button type="submit" class="btn btn-success" onclick="updateSelectedItems()">Checkout</button>
                </div>
            </form>

        </div>

    </div>

    <script src="../js/cart.js"></script>
    <script src="../js/payment.js"></script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>

</body>

</html>