<?php
ob_start();
include('config.php'); // Ensure this path is correct

// Start the session to access session variables
session_start();

$message = ''; // Initialize feedback message

// Debugging connection
if (!$conn) {
    die("Connection not initialized.");
} elseif ($conn->connect_error) {
    die("Connection error: " . $conn->connect_error);
}

// Debugging POST data
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['id'])) {
    $userId = $_POST['id'];
    if (isset($_POST['approve'])) {
        $userStatus = 'approved';
        $userType = isset($_POST['type']) && $_POST['type'] == 'seller' ? 'seller' : 'supplier'; // Determine user type based on action
        $applicationStatus = 'approved';
    } elseif (isset($_POST['disapprove'])) {
        $userStatus = 'disapproved';
        $userType = null; // Keep usertype unchanged on disapproval
        $applicationStatus = 'disapproved';
    }

    if (isset($userStatus) && isset($applicationStatus)) {
        // Update the user type based on seller or supplier
        if ($userType) {
            $stmt = $conn->prepare("UPDATE users SET usertype = ? WHERE id = ?");
            $stmt->bind_param("si", $userType, $userId);
            if (!$stmt->execute()) {
                die("Error updating user: " . $stmt->error);
            }
        }

        // Update the application status (seller or supplier)
        if ($userType == 'seller') {
            $stmt = $conn->prepare("UPDATE apply_seller SET status = ? WHERE seller_id = ?");
            $stmt->bind_param("si", $applicationStatus, $userId);
        } elseif ($userType == 'supplier') {
            $stmt = $conn->prepare("UPDATE apply_supplier SET status = ? WHERE supplier_id = ?");
            $stmt->bind_param("si", $applicationStatus, $userId);
        }

        if (!$stmt->execute()) {
            die("Error updating application: " . $stmt->error);
        } else {
            $message = "User and application status updated successfully.";
        }
        $stmt->close();
    }
} else {
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $message = "Error: No user ID provided.";
    }
}

// Queries to fetch pending applications
$sellerSql = "SELECT 
                users.id, 
                users.first_name, 
                users.middle_name, 
                users.last_name, 
                users.email, 
                users.usertype, 
                apply_seller.business_name, 
                apply_seller.description, 
                apply_seller.address, 
                apply_seller.business_permit, 
                apply_seller.health_permit, 
                apply_seller.application_date, 
                apply_seller.status AS application_status
              FROM 
                users
              LEFT JOIN 
                apply_seller 
              ON 
                users.id = apply_seller.seller_id
              WHERE
                apply_seller.status = 'pending';";

$supplierSql = "SELECT 
                  users.id AS supplier_id, 
                  users.first_name, 
                  users.middle_name, 
                  users.last_name, 
                  users.email, 
                  apply_supplier.business_name, 
                  apply_supplier.description, 
                  apply_supplier.address, 
                  apply_supplier.business_permit, 
                  apply_supplier.health_permit, 
                  apply_supplier.application_date, 
                  apply_supplier.status AS application_status
                FROM 
                  users
                LEFT JOIN 
                  apply_supplier 
                ON 
                  users.id = apply_supplier.supplier_id
                WHERE
                  apply_supplier.status = 'pending'";

$sellerResult = $conn->query($sellerSql);
$supplierResult = $conn->query($supplierSql);

if ($sellerResult === false || $supplierResult === false) {
    die('Error executing query: ' . $conn->error);
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Users</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css" rel="stylesheet">

    <style>
        body {
            display: flex;
        }

        #sidebar {
            width: 250px;
            background: darkviolet;
            color: white;
            height: 100vh;
            padding: 15px;
            position: fixed;
        }

        #sidebar a {
            color: #fff;
            text-decoration: none;
            display: block;
            padding: 10px 0;
        }

        #sidebar a:hover {
            background: violet;
        }

        #content {
            margin-left: 250px;
            padding: 20px;
            width: 100%;
        }

        .table th,
        .table td {
            text-align: center;
        }
    </style>
</head>

<body>
    <div id="sidebar">
        <h2>Admin Dashboard</h2>
        <a href="admin_dashboard.php">Dashboard</a>
        <a href="manage_users.php">Manage Users</a>
        <a href="logout.php">Logout</a>
    </div>

    <div id="content">
        <h1>Manage Users</h1>

        <!-- Display Message if any -->
        <?php if (!empty($message)): ?>
            <div class="alert alert-info"><?php echo htmlspecialchars($message); ?></div>
        <?php endif; ?>

        <!-- Seller Applications -->
        <h2>Seller Applications</h2>
        <div class="table-responsive">
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>First Name</th>
                        <th>Middle Name</th>
                        <th>Last Name</th>
                        <th>Email</th>
                        <th>Business Name</th>
                        <th>Description</th>
                        <th>Address</th>
                        <th>Business Permit</th>
                        <th>Health Permit</th>
                        <th>Application Date</th>
                        <th>Application Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($sellerResult && $sellerResult->num_rows > 0): ?>
                        <?php while ($row = $sellerResult->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($row['id']); ?></td>
                                <td><?php echo htmlspecialchars($row['first_name']); ?></td>
                                <td><?php echo htmlspecialchars($row['middle_name']); ?></td>
                                <td><?php echo htmlspecialchars($row['last_name']); ?></td>
                                <td><?php echo htmlspecialchars($row['email']); ?></td>
                                <td><?php echo htmlspecialchars($row['business_name']); ?></td>
                                <td><?php echo htmlspecialchars($row['description']); ?></td>
                                <td><?php echo htmlspecialchars($row['address']); ?></td>
                                <td>
    <button class="btn btn-info btn-sm view-picture" 
        data-img-src="<?php echo htmlspecialchars($row['business_permit']); ?>">
        View
    </button>
</td>
<td>
    <button class="btn btn-info btn-sm view-picture" 
        data-img-src="<?php echo htmlspecialchars($row['health_permit']); ?>">
        View
    </button>
</td>

                                <td><?php echo htmlspecialchars($row['application_date']); ?></td>
                                <td><?php echo htmlspecialchars($row['application_status']); ?></td>
                                <td>
                                    <form method="post" action="">
                                        <input type="hidden" name="id" value="<?php echo $row['id']; ?>">
                                        <input type="hidden" name="type" value="seller">
                                        <button type="submit" name="approve" class="btn btn-success btn-sm">Approve</button>
                                        <button type="submit" name="disapprove" class="btn btn-danger btn-sm">Disapprove</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="13">No data found.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- Supplier Applications -->
        <h2>Supplier Applications</h2>
        <div class="table-responsive">
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>First Name</th>
                        <th>Middle Name</th>
                        <th>Last Name</th>
                        <th>Email</th>
                        <th>Business Name</th>
                        <th>Description</th>
                        <th>Address</th>
                        <th>Business Permit</th>
                        <th>Health Permit</th>
                        <th>Application Date</th>
                        <th>Application Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($supplierResult && $supplierResult->num_rows > 0): ?>
                        <?php while ($row = $supplierResult->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($row['supplier_id']); ?></td>
                                <td><?php echo htmlspecialchars($row['first_name']); ?></td>
                                <td><?php echo htmlspecialchars($row['middle_name']); ?></td>
                                <td><?php echo htmlspecialchars($row['last_name']); ?></td>
                                <td><?php echo htmlspecialchars($row['email']); ?></td>
                                <td><?php echo htmlspecialchars($row['business_name']); ?></td>
                                <td><?php echo htmlspecialchars($row['description']); ?></td>
                                <td><?php echo htmlspecialchars($row['address']); ?></td>
                                <td>
    <button class="btn btn-info btn-sm view-picture" 
        data-img-src="<?php echo htmlspecialchars($row['business_permit']); ?>">
        View
    </button>
</td>
<td>
    <button class="btn btn-info btn-sm view-picture" 
        data-img-src="<?php echo htmlspecialchars($row['health_permit']); ?>">
        View
    </button>
</td>


                                <td><?php echo htmlspecialchars($row['application_date']); ?></td>
                                <td><?php echo htmlspecialchars($row['application_status']); ?></td>
                                <td>
                                    <form method="post" action="">
                                        <input type="hidden" name="id" value="<?php echo $row['supplier_id']; ?>">
                                        <input type="hidden" name="type" value="supplier">
                                        <button type="submit" name="approve" class="btn btn-success btn-sm">Approve</button>
                                        <button type="submit" name="disapprove" class="btn btn-danger btn-sm">Disapprove</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="13">No data found.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
<!-- Image Modal -->
<div class="modal fade" id="imageModal" tabindex="-1" aria-labelledby="imageModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="imageModalLabel">Document Preview</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body text-center">
                <img id="imagePreview" src="" alt="Permit Image" class="img-fluid">
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<script>
    document.querySelectorAll('.view-picture').forEach(button => {
        button.addEventListener('click', function() {
            let imageSrc = this.getAttribute('data-img-src');
            document.getElementById('imagePreview').src = imageSrc;

            // Show the Bootstrap modal
            let imageModal = new bootstrap.Modal(document.getElementById('imageModal'));
            imageModal.show();
        });
    });
</script>



</html>