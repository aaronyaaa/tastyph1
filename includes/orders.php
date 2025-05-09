<?php
session_start();
include('../database/config.php');


if (!isset($_SESSION['userId'])) {
    header("Location: login.php");
    exit;
}

$seller_id = $_SESSION['userId'];
$userType = $_SESSION['usertype'] ?? 'user';

$status = $_GET['status'] ?? 'pending';

function fetch_orders($conn, $seller_id, $status)
{
    $sql = "SELECT 
                r.request_id, 
                r.user_id, 
                r.product_name, 
                r.quantity, 
                r.additional_notes, 
                r.status, 
                r.request_date, 
                CONCAT(b.first_name, ' ', b.last_name) AS buyer_name,
                b.id AS buyer_id
            FROM requests r
            JOIN users b ON r.user_id = b.id
            WHERE r.seller_id = ? AND r.status = ?
            ORDER BY r.request_date DESC";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("is", $seller_id, $status);
    $stmt->execute();
    return $stmt->get_result();
}

$orders = fetch_orders($conn, $seller_id, $status);


// Handle Approve Action
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action'], $_POST['request_id'])) {
    $request_id = intval($_POST['request_id']);
    $action = $_POST['action'];

    if ($action == 'approve') {
        $status_update = 'approved';
        $sql = "UPDATE requests SET status = ? WHERE request_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("si", $status_update, $request_id);
        $stmt->execute();
        header("Location: orders.php");
        exit;
    }
}

// Handle Reject Action with Reason
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['reject_request_id'], $_POST['rejection_reason'])) {
    $request_id = intval($_POST['reject_request_id']);
    $rejection_reason = trim($_POST['rejection_reason']);
    $rejection_date = date("Y-m-d H:i:s");

    $update_sql = "UPDATE requests SET status = 'rejected' WHERE request_id = ?";
    $stmt = $conn->prepare($update_sql);
    $stmt->bind_param("i", $request_id);
    $stmt->execute();

    $insert_sql = "INSERT INTO rejections (request_id, rejection_reason, rejection_date, rejected_by) VALUES (?, ?, ?, ?)";
    $stmt = $conn->prepare($insert_sql);
    $stmt->bind_param("issi", $request_id, $rejection_reason, $rejection_date, $seller_id);
    $stmt->execute();

    header("Location: orders.php");
    exit;
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Requests Management</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../css/nav.css">
    <link rel="stylesheet" href="../css/modal.css">
    <link rel="stylesheet" href="../css/orders.css">
    <link rel="stylesheet" href="../css/index.css">

</head>

<body>

    <?php include("../includes/nav_" . strtolower($userType) . ".php"); ?>
    <?php include("modal2.php"); // Ensure user is authenticated and session is active
    ?>

    <div class="container mt-5">
        <h1 class="text-center mb-4">Requests Management</h1>

        <div class="dropdown mb-4">
            <button class="btn btn-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                <?= ucfirst($status) ?> Orders
            </button>
            <ul class="dropdown-menu">
                <li><a class="dropdown-item" href="?status=pending">Pending</a></li>
                <li><a class="dropdown-item" href="?status=approved">Approved</a></li>
                <li><a class="dropdown-item" href="?status=rejected">Declined</a></li>
            </ul>
        </div>

        <div class="shadow p-4 rounded">
            <h3 class="<?= ($status == 'pending') ? 'text-warning' : (($status == 'approved') ? 'text-success' : 'text-danger') ?>">
                <?= ucfirst($status) ?> Orders
            </h3>
            <div class="row">
                <?php while ($row = $orders->fetch_assoc()): ?>
                    <?php
                    $rej_reason = '';
                    if ($status == 'rejected') {
                        $rej_stmt = $conn->prepare("SELECT rejection_reason FROM rejections WHERE request_id = ? LIMIT 1");
                        $rej_stmt->bind_param("i", $row['request_id']);
                        $rej_stmt->execute();
                        $rej_result = $rej_stmt->get_result();
                        $rej_row = $rej_result->fetch_assoc();
                        $rej_reason = $rej_row['rejection_reason'] ?? 'No reason provided';
                    }
                    ?>
                    <div class="col-md-4 mb-3">
                        <div class="card shadow-sm">
                            <div class="card-body">
                                <h5><?= htmlspecialchars($row['product_name']) ?></h5>
                                <p><strong>Buyer:</strong> <?= htmlspecialchars($row['buyer_name']) ?></p>
                                <p><strong>Quantity:</strong> <?= htmlspecialchars($row['quantity']) ?></p>
                                <p><strong>Additional Notes:</strong> <?= htmlspecialchars($row['additional_notes'] ?: '---') ?></p>
                                <p><strong>Request Date:</strong> <?= htmlspecialchars($row['request_date']) ?></p>

                                <?php if ($status == 'pending'): ?>
                                    <form action="orders.php" method="POST">
                                        <input type="hidden" name="request_id" value="<?= $row['request_id'] ?>">
                                        <button name="action" value="approve" class="btn btn-success btn-sm">Approve</button>
                                        <button type="button" class="btn btn-danger btn-sm" data-bs-toggle="collapse" data-bs-target="#reject<?= $row['request_id'] ?>">Reject</button>
                                        <a href="chat.php?receiver_id=<?= $row['buyer_id'] ?>" class="btn btn-secondary btn-sm">Message</a>
                                        <button type="button" class="btn btn-outline-primary btn-sm" data-bs-toggle="modal" data-bs-target="#availabilityModal">
                                            Availability
                                        </button>

                                        <div class="collapse mt-2" id="reject<?= $row['request_id'] ?>">
                                            <form action="orders.php" method="POST">
                                                <input type="hidden" name="reject_request_id" value="<?= $row['request_id'] ?>">
                                                <textarea name="rejection_reason" class="form-control mb-2" required placeholder="Reason..."></textarea>
                                                <button class="btn btn-danger btn-sm">Submit</button>
                                            </form>
                                        </div>
                                    <?php elseif ($status == 'rejected'): ?>
                                        <p class="text-danger"><strong>Reason:</strong> <?= htmlspecialchars($rej_reason) ?></p>
                                    <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>
        </div>

    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>