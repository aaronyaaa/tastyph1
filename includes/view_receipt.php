<?php
session_start();
include("../database/config.php");
include("../database/data_session.php");

if (!isset($_GET['receipt_id'])) {
    die("Receipt ID not provided");
}

$receiptId = intval($_GET['receipt_id']);

// Get receipt information
$stmt = $conn->prepare("
    SELECT r.*, 
           DATE_FORMAT(r.issue_date, '%M %d, %Y %h:%i %p') as formatted_date
    FROM receipts r
    WHERE r.receipt_id = ?
");
$stmt->bind_param("i", $receiptId);
$stmt->execute();
$receipt = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$receipt) {
    die("Receipt not found");
}

// Get receipt items
$stmt = $conn->prepare("
    SELECT *
    FROM receipt_items
    WHERE receipt_id = ?
    ORDER BY receipt_item_id
");
$stmt->bind_param("i", $receiptId);
$stmt->execute();
$items = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Receipt #<?= htmlspecialchars($receipt['receipt_number']) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        @media print {
            .no-print {
                display: none !important;
            }
            .receipt-container {
                width: 100% !important;
                max-width: none !important;
                margin: 0 !important;
                padding: 0 !important;
            }
        }
        .receipt-container {
            max-width: 800px;
            margin: 20px auto;
            padding: 20px;
            border: 1px solid #ddd;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        .receipt-header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #000;
            padding-bottom: 20px;
        }
        .receipt-details {
            margin-bottom: 30px;
        }
        .receipt-items {
            margin-bottom: 30px;
        }
        .receipt-items th {
            background-color: #f8f9fa;
        }
        .receipt-total {
            text-align: right;
            font-size: 1.2em;
            font-weight: bold;
        }
        .receipt-footer {
            margin-top: 50px;
            text-align: center;
            border-top: 1px solid #ddd;
            padding-top: 20px;
        }
    </style>
</head>
<body>
    <div class="container receipt-container">
        <div class="no-print mb-3">
            <button onclick="window.print()" class="btn btn-primary">Print Receipt</button>
            <a href="manage_orders.php" class="btn btn-secondary">Back to Orders</a>
        </div>

        <div class="receipt-header">
            <h2><?= htmlspecialchars($receipt['supplier_name']) ?></h2>
            <p>Official Receipt</p>
            <p>Receipt #: <?= htmlspecialchars($receipt['receipt_number']) ?></p>
            <p>Date: <?= htmlspecialchars($receipt['formatted_date']) ?></p>
        </div>

        <div class="receipt-details row">
            <div class="col-md-6">
                <h5>Supplier Information</h5>
                <p>
                    <?= htmlspecialchars($receipt['supplier_name']) ?><br>
                    <?= htmlspecialchars($receipt['supplier_address']) ?><br>
                    Contact: <?= htmlspecialchars($receipt['supplier_contact']) ?><br>
                    TIN: <?= htmlspecialchars($receipt['supplier_tin']) ?>
                </p>
            </div>
            <div class="col-md-6">
                <h5>Buyer Information</h5>
                <p>
                    <?= htmlspecialchars($receipt['buyer_name']) ?><br>
                    <?= htmlspecialchars($receipt['buyer_address']) ?><br>
                    Contact: <?= htmlspecialchars($receipt['buyer_contact']) ?>
                </p>
            </div>
        </div>

        <div class="receipt-items">
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>Item</th>
                        <th>Quantity</th>
                        <th>Unit Price</th>
                        <th>Subtotal</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($items as $item): ?>
                        <tr>
                            <td><?= htmlspecialchars($item['item_name']) ?></td>
                            <td>
                                <?= htmlspecialchars($item['quantity']) ?> 
                                <?= htmlspecialchars($item['quantity_value'] . ' ' . $item['unit_type']) ?>
                            </td>
                            <td>₱<?= number_format($item['unit_price'], 2) ?></td>
                            <td>₱<?= number_format($item['subtotal'], 2) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <div class="receipt-total">
            <p>Total Amount: ₱<?= number_format($receipt['total_amount'], 2) ?></p>
            <p>Payment Method: <?= htmlspecialchars($receipt['payment_method']) ?></p>
            <p>Payment Status: <?= htmlspecialchars($receipt['payment_status']) ?></p>
        </div>

        <div class="receipt-footer">
            <p>Thank you for your business!</p>
            <?php if ($receipt['notes']): ?>
                <p class="text-muted"><?= htmlspecialchars($receipt['notes']) ?></p>
            <?php endif; ?>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 