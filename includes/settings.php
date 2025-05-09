<?php
// settings.php - Settings Page with Profile Update and Photo Update Forms
session_start();

// Include authentication and database connection files if needed
require '../database/config.php';
require '../database/session.php';

$userType = $_SESSION['usertype'] ?? 'user'; // Default to 'user' if not set

// Query to get store applications from the 'apply_seller' table
$sql = "SELECT seller_id, id, business_name, description, address, business_permit, health_permit, application_date, status FROM apply_seller";
$result = $conn->query($sql);

$sql = "SELECT supplier_id, business_name, description, address, business_permit, health_permit, application_date, status FROM apply_supplier";
$result = $conn->query($sql);

$seller_id = $_SESSION['seller_id'] ?? $_GET['seller_id'] ?? 0;

$sql = "SELECT DISTINCT seller_id FROM products";
$stmt = $conn->prepare($sql);
$stmt->execute();
$result = $stmt->get_result();

$sql = "SELECT profile_pics FROM users WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $_SESSION['userId']);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();

$profilePics = !empty($row['profile_pics']) ? htmlspecialchars($row['profile_pics']) : 'path/to/default/profile/pic.jpg';
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Settings</title>
    <link rel="stylesheet" href="../assets/css/bootstrap.min.css">
    <link href="../css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../css/nav.css">
    <link rel="stylesheet" href="../css/modal.css">
    <link rel="stylesheet" href="../css/settings.css">
</head>

<body>

    <div class="container mt-5">
        <?php include("../includes/nav_" . strtolower($userType) . ".php"); ?>

        <h2>Profile Settings</h2>

        <!-- Tab navigation -->
        <ul class="nav nav-tabs" id="settingsTab" role="tablist">
            <li class="nav-item" role="presentation">
                <a class="nav-link active" id="update-photo-tab" data-bs-toggle="tab" href="#update-photo" role="tab" aria-controls="update-photo" aria-selected="true">Update Profile Photo</a>
            </li>
            <li class="nav-item" role="presentation">
                <a class="nav-link" id="update-profile-tab" data-bs-toggle="tab" href="#update-profile" role="tab" aria-controls="update-profile" aria-selected="false">Update Profile</a>
            </li>
        </ul>

        <!-- Tab content -->
        <div class="tab-content mt-3" id="settingsTabContent">
            <!-- Update Profile Photo Tab -->
            <div class="tab-pane fade show active" id="update-photo" role="tabpanel" aria-labelledby="update-photo-tab">
                <div class="card p-3">
                    <h3>Update Profile Photo</h3>
                    <form action="../helpers/update_photo.php" method="post" enctype="multipart/form-data">
                        <div class="mb-3 text-center">
                            <?php if (!empty($profilePics)) : ?>
                                <img src="../uploads/<?php echo htmlspecialchars($profilePics); ?>" alt="User Photo" class="img-fluid rounded-circle" style="width: 30%; height: auto; object-fit: cover;">
                            <?php else : ?>
                                <div class="bg-secondary d-flex align-items-center justify-content-center rounded-circle" style="width: 100px; height: 100px; overflow: hidden;">
                                    <i class="bi bi-person-fill text-light fs-4"></i>
                                </div>
                            <?php endif; ?>
                        </div>

                        <div class="mb-3">
                            <label for="profile_pics" class="form-label">Choose Photo</label>
                            <input type="file" class="form-control" id="profile_pics" name="profile_pics" accept="image/*" required>
                        </div>

                        <button type="submit" class="btn btn-primary">Upload Photo</button>
                    </form>
                </div>
            </div>

            <!-- Update Profile Tab -->
            <div class="tab-pane fade" id="update-profile" role="tabpanel" aria-labelledby="update-profile-tab">
                <div class="card p-3">
                    <h3>Update Profile</h3>
                    <form action="../helpers/update_profile.php" method="post" enctype="multipart/form-data">
                        <div class="row mb-3">
                            <div class="col-md-4">
                                <label for="firstName" class="form-label">First Name</label>
                                <input type="text" class="form-control" id="first_name" name="first_name" value="<?php echo htmlspecialchars($firstName); ?>" required>
                            </div>
                            <div class="col-md-4">
                                <label for="middleName" class="form-label">Middle Name</label>
                                <input type="text" class="form-control" id="middle_name" name="middle_name" value="<?php echo htmlspecialchars($middleName); ?>" placeholder="Enter your middle name">
                            </div>
                            <div class="col-md-4">
                                <label for="lastName" class="form-label">Last Name</label>
                                <input type="text" class="form-control" id="last_name" name="last_name" value="<?php echo htmlspecialchars($lastName); ?>" required>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="email" class="form-label">Email</label>
                                <input type="email" class="form-control" id="email" name="email" value="<?php echo htmlspecialchars($email); ?>" required>
                            </div>
                            <div class="col-md-6">
                                <label for="contactNumber" class="form-label">Contact Number</label>
                                <input type="text" class="form-control" id="contactNumber" name="contactNumber" value="<?php echo htmlspecialchars($contactNumber); ?>" required>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-12">
                                <label for="password" class="form-label">New Password</label>
                                <input type="password" class="form-control" id="password" name="password" placeholder="Enter new password">
                            </div>
                            <div class="mb-6">
                                <label for="date_of_birth" class="form-label">Date of Birth</label>
                                <input type="date" class="form-control" id="date_of_birth" name="date_of_birth" value="<?php echo htmlspecialchars($rowdateOfBirth); ?>" required>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="country" class="form-label">Country</label>
                                <input type="text" class="form-control" id="country" name="country" value="<?php echo htmlspecialchars($country); ?>">
                            </div>
                            <div class="col-md-6">
                                <label for="city" class="form-label">City</label>
                                <input type="text" class="form-control" id="city" name="city" value="<?php echo htmlspecialchars($city); ?>">
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-4">
                                <label for="streetname" class="form-label">Street Name</label>
                                <input type="text" class="form-control" id="streetname" name="streetname" value="<?php echo htmlspecialchars($streetname); ?>">
                            </div>
                            <div class="col-md-4">
                                <label for="barangay" class="form-label">Barangay</label>
                                <input type="text" class="form-control" id="barangay" name="barangay" value="<?php echo htmlspecialchars($barangay); ?>">
                            </div>
                            <div class="col-md-4">
                                <label for="province" class="form-label">Province</label>
                                <input type="text" class="form-control" id="province" name="province" value="<?php echo htmlspecialchars($province); ?>">
                            </div>
                        </div>

                        <button type="submit" class="btn btn-primary">Save Changes</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="../assets/js/bootstrap.bundle.min.js"></script>

    <script src="../js/bootstrap.bundle.min.js"></script>
    <script src="../js/scripts.js"></script>
</body>

</html>