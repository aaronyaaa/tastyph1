<?php
session_start();
include("../database/config.php");
include("../database/data_session.php");

if (!isset($_SESSION['userId'])) {
    die("Session is not set. Please log in.");
}

$userId = $_SESSION['userId'];
$userType = $_SESSION['usertype'] ?? 'user';

// Date filter logic
$dateFilter = $_GET['date_filter'] ?? 'today';
$dateWhere = '';
switch ($dateFilter) {
    case 'today':
        $dateWhere = "AND DATE(o.order_date) = CURDATE()";
        break;
    case 'last_week':
        $dateWhere = "AND o.order_date >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)";
        break;
    case 'last_month':
        $dateWhere = "AND o.order_date >= DATE_SUB(CURDATE(), INTERVAL 1 MONTH)";
        break;
    case 'last_year':
        $dateWhere = "AND o.order_date >= DATE_SUB(CURDATE(), INTERVAL 1 YEAR)";
        break;
    case 'all':
        $dateWhere = ""; // Show all
        break;
    default:
        $dateWhere = ""; // Show all if unknown
}

$sql = "SELECT 
            o.order_id, o.status AS order_status, o.order_date,
            oi.quantity, oi.total_price AS item_price, oi.variant_id, oi.product_id, oi.ingredient_id,
            p.product_name, p.image_url AS product_image, p.product_id,
            i.ingredient_name, i.image_url AS ingredient_image, i.ingredient_id,
            v.variant_name, v.image_url AS variant_image, v.variant_id,
            s.business_name AS seller_name, sup.business_name AS supplier_name
        FROM orders o
        JOIN order_items oi ON o.order_id = oi.order_id
        LEFT JOIN products p ON oi.product_id = p.product_id
        LEFT JOIN ingredients i ON oi.ingredient_id = i.ingredient_id
        LEFT JOIN ingredient_variants v ON oi.variant_id = v.variant_id
        LEFT JOIN apply_seller s ON p.seller_id = s.seller_id
        LEFT JOIN apply_supplier sup ON i.supplier_id = sup.supplier_id
        WHERE o.user_id = ? $dateWhere
        ORDER BY o.order_date DESC";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    die("No orders found.");
}

// Group orders by order_id then by store name
$orders = [];
while ($row = $result->fetch_assoc()) {
    $orderId = $row['order_id'];
    $storeName = $row['seller_name'] ?? $row['supplier_name'] ?? 'Unknown Store';
    if (!isset($orders[$orderId])) {
        $orders[$orderId] = [
            'order_status' => $row['order_status'],
            'order_date' => $row['order_date'],
            'stores' => []
        ];
    }
    if (!isset($orders[$orderId]['stores'][$storeName])) {
        $orders[$orderId]['stores'][$storeName] = [];
    }
    $orders[$orderId]['stores'][$storeName][] = $row;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <title>My Orders</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="../css/index.css" />
    <link rel="stylesheet" href="../css/nav.css" />
</head>
<body>
<?php include("../includes/nav_" . strtolower($userType) . ".php"); ?>

<div class="container mt-5">
    <h2 class="mb-4">My Orders</h2>

    <form method="get" class="mb-4">
        <div class="row g-2 align-items-center">
            <div class="col-auto">
                <label for="date_filter" class="col-form-label">Show orders from:</label>
            </div>
            <div class="col-auto">
                <select name="date_filter" id="date_filter" class="form-select" onchange="this.form.submit()">
                    <option value="today" <?= $dateFilter == 'today' ? 'selected' : '' ?>>Today</option>
                    <option value="last_week" <?= $dateFilter == 'last_week' ? 'selected' : '' ?>>Last 7 Days</option>
                    <option value="last_month" <?= $dateFilter == 'last_month' ? 'selected' : '' ?>>Last Month</option>
                    <option value="last_year" <?= $dateFilter == 'last_year' ? 'selected' : '' ?>>Last Year</option>
                    <option value="all" <?= $dateFilter == 'all' ? 'selected' : '' ?>>All Time</option>
                </select>
            </div>
        </div>
    </form>

    <div class="accordion" id="orderAccordion">
        <?php
        $orderIndex = 0;
        $maxVisible = 5; // Show only 5 initially
        ?>
        <?php foreach ($orders as $orderId => $orderData): ?>
            <div class="accordion-item <?= $orderIndex >= $maxVisible ? 'extra-order d-none' : '' ?>" data-order-index="<?= $orderIndex ?>">
                <h2 class="accordion-header" id="headingOrder<?= $orderIndex ?>">
                    <button class="accordion-button <?= $orderIndex > 0 ? 'collapsed' : '' ?>" type="button" data-bs-toggle="collapse" data-bs-target="#collapseOrder<?= $orderIndex ?>" aria-expanded="<?= $orderIndex == 0 ? 'true' : 'false' ?>" aria-controls="collapseOrder<?= $orderIndex ?>">
                        Order #<?= $orderId ?> (<?= date('M d, Y', strtotime($orderData['order_date'] ?? '')) ?>)
                        (Status: <?= htmlspecialchars($orderData['order_status']) ?>)
                    </button>
                </h2>
                <div id="collapseOrder<?= $orderIndex ?>" class="accordion-collapse collapse <?= $orderIndex == 0 ? 'show' : '' ?>" aria-labelledby="headingOrder<?= $orderIndex ?>" data-bs-parent="#orderAccordion">
                    <div class="accordion-body">
                        <?php foreach ($orderData['stores'] as $storeName => $items): ?>
                            <h5 class="d-flex justify-content-between align-items-center">
                                <span><?= htmlspecialchars($storeName) ?></span>
                                <button type="button" 
                                        class="btn btn-sm btn-outline-primary view-receipt-btn" 
                                        data-order-id="<?= $orderId ?>" 
                                        data-store-name="<?= htmlspecialchars($storeName) ?>" 
                                        data-bs-toggle="modal" data-bs-target="#receiptModal">
                                    View Receipt
                                </button>
                            </h5>

                            <?php foreach ($items as $row): ?>
                                <?php
                                    // Compose the display name showing variant if present
                                    if (!empty($row['variant_name'])) {
                                        $itemName = $row['ingredient_name'] . ' - ' . $row['variant_name'];
                                    } else {
                                        $itemName = $row['product_name'] ?? $row['ingredient_name'];
                                    }
                                    $itemImage = $row['variant_image'] ?? $row['product_image'] ?? $row['ingredient_image'];
                                    $itemId = $row['variant_id'] ?? $row['product_id'] ?? $row['ingredient_id'];
                                    $itemType = !empty($row['variant_id']) ? 'variant' : (!empty($row['product_id']) ? 'product' : 'ingredient');
                                ?>
                                <div class="d-flex border-top py-2">
                                    <img src="../uploads/<?= htmlspecialchars($itemImage); ?>" 
                                         class="img-thumbnail me-3" style="width: 100px; height: 100px;">
                                    <div class="flex-grow-1">
                                        <h6>
                                            <a href="item_details.php?item_id=<?= $itemId ?>&type=<?= $itemType ?>&order_id=<?= $row['order_id']; ?>" 
                                               class="text-decoration-none">
                                                <?= htmlspecialchars($itemName); ?>
                                            </a>
                                        </h6>
                                        <p class="text-muted mb-1">Qty: <?= htmlspecialchars($row['quantity']); ?></p>
                                        <p class="mb-0">₱<?= number_format($row['item_price'], 2); ?></p>
                                        <p class="mt-2">
                                            <span class="badge 
                                                <?php 
                                                    if ($row['order_status'] === 'Pending') echo 'bg-warning';
                                                    elseif ($row['order_status'] === 'Shipped') echo 'bg-primary';
                                                    elseif ($row['order_status'] === 'Delivered') echo 'bg-success';
                                                    else echo 'bg-secondary'; 
                                                ?>">
                                                <?= htmlspecialchars($row['order_status']); ?>
                                            </span>
                                        </p>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
            <?php $orderIndex++; ?>
        <?php endforeach; ?>
    </div>

    <!-- Receipt Modal -->
    <div class="modal fade" id="receiptModal" tabindex="-1" aria-labelledby="receiptModalLabel" aria-hidden="true">
      <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title" id="receiptModalLabel">Receipt Details</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
            <div id="receiptContent">
              <p class="text-center">Loading receipt...</p>
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
          </div>
        </div>
      </div>
    </div>

    <?php if(count($orders) > $maxVisible): ?>
        <div class="text-center mt-3">
            <button id="showMoreBtn" class="btn btn-outline-primary">Show More</button>
        </div>
    <?php endif; ?>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const showMoreBtn = document.getElementById('showMoreBtn');
    if (!showMoreBtn) return;

    showMoreBtn.addEventListener('click', function () {
        const hiddenOrders = document.querySelectorAll('.extra-order.d-none');
        hiddenOrders.forEach(order => {
            order.classList.remove('d-none');
        });
        showMoreBtn.style.display = 'none';
    });
});
</script>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const receiptModal = document.getElementById('receiptModal');
    const receiptContent = document.getElementById('receiptContent');

    document.querySelectorAll('.view-receipt-btn').forEach(button => {
        button.addEventListener('click', function () {
            const orderId = this.getAttribute('data-order-id');

            receiptContent.innerHTML = '<p class="text-center">Loading receipt...</p>';

            fetch(`../helpers/fetch_receipt.php?order_id=${orderId}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        const receipt = data.receipt;
                        const items = data.items;

                        let html = `
                        <h6>Receipt Number: ${receipt.receipt_id}</h6>
                        <p><strong>Issue Date:</strong> ${receipt.payment_date}</p>
                        <p><strong>Buyer:</strong> ${receipt.buyer_name}<br>
                           <strong>Address:</strong> ${receipt.buyer_address}<br>
                           <strong>Contact:</strong> ${receipt.buyer_contact}</p>
                        <p><strong>Supplier:</strong> ${receipt.supplier_name}<br>
                           <strong>Address:</strong> ${receipt.supplier_address}<br>
                           <strong>Contact:</strong> ${receipt.supplier_contact}</p>
                        <p><strong>Payment Method:</strong> ${receipt.payment_method}</p>
                        <p><strong>Total Paid:</strong> ₱${parseFloat(receipt.total_paid).toFixed(2)}</p>
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>Item Name</th>
                                    <th>Quantity</th>
                                    <th>Unit Price</th>
                                    <th>Subtotal</th>
                                </tr>
                            </thead>
                            <tbody>
                        `;

                        items.forEach(item => {
                            html += `
                                <tr>
                                    <td>${item.description}</td>
                                    <td>${item.quantity} ${item.unit_type}</td>
                                    <td>₱${parseFloat(item.unit_price).toFixed(2)}</td>
                                    <td>₱${parseFloat(item.total_price).toFixed(2)}</td>
                                </tr>
                            `;
                        });

                        html += `
                            </tbody>
                        </table>
                        `;

                        receiptContent.innerHTML = html;
                    } else {
                        receiptContent.innerHTML = `<p class="text-danger">Receipt not found for this order.</p>`;
                    }
                })
                .catch(() => {
                    receiptContent.innerHTML = `<p class="text-danger">Failed to load receipt data.</p>`;
                });
        });
    });
});
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
