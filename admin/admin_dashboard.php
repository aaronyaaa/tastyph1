<?php
session_start();
include '../database/config.php'; // Adjust if needed

// Check if the admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header("Location: admin_login.php");
    exit();
}

// Fetch Total Users (Users + Suppliers)
$sqlTotalUsers = "SELECT COUNT(*) AS total FROM users";
$sqlTotalSuppliers = "SELECT COUNT(*) AS total FROM supplier";
$resultUsers = $conn->query($sqlTotalUsers);
$resultSuppliers = $conn->query($sqlTotalSuppliers);
$totalUserCount = 0;

if ($resultUsers && $resultSuppliers) {
    $rowUsers = $resultUsers->fetch_assoc();
    $rowSuppliers = $resultSuppliers->fetch_assoc();
    $totalUserCount = $rowUsers['total'] + $rowSuppliers['total']; // Sum users and suppliers
}

// Fetch Approved Applications (Sellers + Suppliers)
$sqlApproved = "SELECT 
    (SELECT COUNT(*) FROM apply_seller WHERE status = 'approved') +
    (SELECT COUNT(*) FROM apply_supplier WHERE status = 'approved') 
    AS total_approved";
$resultApproved = $conn->query($sqlApproved);
$approvedCount = ($resultApproved) ? $resultApproved->fetch_assoc()['total_approved'] : 0;

// Fetch Pending Applications (Sellers + Suppliers)
$sqlPending = "SELECT 
    (SELECT COUNT(*) FROM apply_seller WHERE status = 'pending') +
    (SELECT COUNT(*) FROM apply_supplier WHERE status = 'pending') 
    AS total_pending";
$resultPending = $conn->query($sqlPending);
$pendingCount = ($resultPending) ? $resultPending->fetch_assoc()['total_pending'] : 0;

// Fetch Declined Applications (Sellers + Suppliers)
$sqlDeclined = "SELECT 
    (SELECT COUNT(*) FROM apply_seller WHERE status = 'declined') +
    (SELECT COUNT(*) FROM apply_supplier WHERE status = 'declined') 
    AS total_declined";
$resultDeclined = $conn->query($sqlDeclined);
$declinedCount = ($resultDeclined) ? $resultDeclined->fetch_assoc()['total_declined'] : 0;

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../css/admin_db.css">
</head>
<body>
    <div id="sidebar">
        <h2>Admin Dashboard</h2>
        <a href="admin_dashboard.php">Dashboard</a>
        <a href="manage_users.php">Manage Users</a>
        <a href="logout.php">Logout</a>
    </div>

    <div id="content">
        <h1>Admin Dashboard</h1>
        <div class="row">
            <!-- Total Users -->
            <div class="col-md-3">
                <div class="card bg-info text-white" onclick="openModal('total_users')">
                    <div class="card-header">Total Users</div>
                    <div class="card-body">
                        <h4><?php echo $totalUserCount; ?></h4>
                    </div>
                </div>
            </div>

            <!-- Approved Applications -->
            <div class="col-md-3">
                <div class="card bg-success text-white" onclick="openModal('approved')">
                    <div class="card-header">Approved Applications</div>
                    <div class="card-body">
                        <h4><?php echo $approvedCount; ?></h4>
                    </div>
                </div>
            </div>

            <!-- Declined Applications -->
            <div class="col-md-3">
                <div class="card bg-danger text-white" onclick="openModal('declined')">
                    <div class="card-header">Declined Applications</div>
                    <div class="card-body">
                        <h4><?php echo $declinedCount; ?></h4>
                    </div>
                </div>
            </div>

            <!-- Pending Applications -->
            <div class="col-md-3">
                <div class="card bg-warning text-white" onclick="openModal('pending')">
                    <div class="card-header">Pending Applications</div>
                    <div class="card-body">
                        <h4><?php echo $pendingCount; ?></h4>
                    </div>
                </div>
            </div>
        </div>
    </div>
<!-- Modal -->
<div class="modal fade" id="statusModal" tabindex="-1" aria-labelledby="statusModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="statusModalLabel">Users List</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Business Name</th>
                            <th>Address</th>
                            <th>Application Date</th>
                            <th>User Type</th> <!-- Added User Type Column -->
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody id="modalTableBody">
                        <!-- Data will be inserted here via AJAX -->
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Total Users Modal -->
<div class="modal fade" id="totalUsersModal" tabindex="-1" aria-labelledby="totalUsersModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="totalUsersModalLabel">Total Users (Customers & Sellers)</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Business Name</th>
                            <th>Address</th>
                            <th>Application Date</th>
                            <th>User Type</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody id="totalUsersTableBody">
                        <!-- Data will be loaded here via AJAX -->
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>


    <script>
    function openModal(status) {
        let title = "";
        if (status === "approved") title = "Approved Applications";
        else if (status === "pending") title = "Pending Applications";
        else if (status === "declined") title = "Declined Applications";
        else if (status === "total_users") title = "Total Users (Customers & Sellers)";

        document.getElementById("statusModalLabel").innerText = title;

        fetch("fetch_users.php?status=" + status)
        .then(response => response.text())
        .then(data => {
            document.getElementById("modalTableBody").innerHTML = data;
            let modal = new bootstrap.Modal(document.getElementById("statusModal"));
            modal.show();
        })
        .catch(error => console.error("Error fetching data:", error));
    }
    $(document).ready(function () {
    $("#totalUsersModal").on("show.bs.modal", function () {
        $.ajax({
            url: "fetch_users.php?type=total_users", // Add 'type=total_users'
            method: "GET",
            success: function (data) {
                $("#totalUsersTableBody").html(data);
            }
        });
    });
});

    </script>

    <?php
    // Close the database connection
    $conn->close();
    ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../js/bootstrap.bundle.min.js"></script>
</body>
</html>
