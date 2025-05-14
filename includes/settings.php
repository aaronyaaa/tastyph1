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
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root {
            --primary-color: #4a90e2;
            --secondary-color: #f8f9fa;
            --text-color: #2c3e50;
            --border-radius: 12px;
            --box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        body {
            background-color: #f5f7fa;
            color: var(--text-color);
            font-family: 'Segoe UI', system-ui, -apple-system, sans-serif;
        }

        .settings-container {
            max-width: 1200px;
            margin: 2rem auto;
            padding: 0 1rem;
        }

        .settings-header {
            margin-bottom: 2rem;
            text-align: center;
        }

        .settings-header h2 {
            font-size: 2.5rem;
            font-weight: 600;
            color: var(--text-color);
            margin-bottom: 1rem;
        }

        .nav-tabs {
            border: none;
            margin-bottom: 2rem;
            justify-content: center;
            gap: 1rem;
        }

        .nav-tabs .nav-link {
            border: none;
            padding: 1rem 2rem;
            border-radius: var(--border-radius);
            color: var(--text-color);
            font-weight: 500;
            transition: all 0.3s ease;
        }

        .nav-tabs .nav-link:hover {
            background-color: var(--secondary-color);
        }

        .nav-tabs .nav-link.active {
            background-color: var(--primary-color);
            color: white;
        }

        .settings-card {
            background: white;
            border-radius: var(--border-radius);
            box-shadow: var(--box-shadow);
            padding: 2rem;
            margin-bottom: 2rem;
            border: none;
        }

        .settings-card h3 {
            font-size: 1.5rem;
            font-weight: 600;
            margin-bottom: 1.5rem;
            color: var(--text-color);
        }

        .profile-photo-container {
            width: 200px;
            height: 200px;
            margin: 0 auto 2rem;
            position: relative;
        }

        .profile-photo {
            width: 100%;
            height: 100%;
            object-fit: cover;
            border-radius: 50%;
            border: 4px solid white;
            box-shadow: var(--box-shadow);
        }

        .photo-upload-btn {
            position: absolute;
            bottom: 0;
            right: 0;
            background: var(--primary-color);
            color: white;
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .photo-upload-btn:hover {
            transform: scale(1.1);
        }

        .form-label {
            font-weight: 500;
            color: var(--text-color);
            margin-bottom: 0.5rem;
        }

        .form-control {
            border-radius: 8px;
            border: 1px solid #e1e8ed;
            padding: 0.75rem 1rem;
            transition: all 0.3s ease;
        }

        .form-control:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 0.2rem rgba(74, 144, 226, 0.25);
        }

        .btn-primary {
            background-color: var(--primary-color);
            border: none;
            padding: 0.75rem 2rem;
            border-radius: 8px;
            font-weight: 500;
            transition: all 0.3s ease;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(74, 144, 226, 0.3);
        }

        .form-section {
            margin-bottom: 2rem;
            padding-bottom: 2rem;
            border-bottom: 1px solid #e1e8ed;
        }

        .form-section:last-child {
            border-bottom: none;
            margin-bottom: 0;
            padding-bottom: 0;
        }

        @media (max-width: 768px) {
            .settings-container {
                margin: 1rem auto;
            }

            .nav-tabs .nav-link {
                padding: 0.75rem 1rem;
            }

            .settings-card {
                padding: 1.5rem;
            }
        }
    </style>
</head>

<body>
    <div class="settings-container">
        <?php include("../includes/nav_" . strtolower($userType) . ".php"); ?>

        <div class="settings-header">
            <h2>Account Settings</h2>
            <p class="text-muted">Manage your profile information and preferences</p>
        </div>

        <ul class="nav nav-tabs" id="settingsTab" role="tablist">
            <li class="nav-item" role="presentation">
                <a class="nav-link active" id="update-photo-tab" data-bs-toggle="tab" href="#update-photo" role="tab">
                    <i class="fas fa-camera me-2"></i>Profile Photo
                </a>
            </li>
            <li class="nav-item" role="presentation">
                <a class="nav-link" id="update-profile-tab" data-bs-toggle="tab" href="#update-profile" role="tab">
                    <i class="fas fa-user me-2"></i>Profile Information
                </a>
            </li>
        </ul>

        <div class="tab-content" id="settingsTabContent">
            <!-- Update Profile Photo Tab -->
            <div class="tab-pane fade show active" id="update-photo" role="tabpanel">
                <div class="settings-card">
                    <form action="../helpers/update_photo.php" method="post" enctype="multipart/form-data">
                        <div class="profile-photo-container">
                            <?php if (!empty($profilePics)) : ?>
                                <img src="../uploads/<?php echo htmlspecialchars($profilePics); ?>" alt="Profile Photo" class="profile-photo">
                            <?php else : ?>
                                <div class="profile-photo bg-secondary d-flex align-items-center justify-content-center">
                                    <i class="fas fa-user text-light fa-4x"></i>
                                </div>
                            <?php endif; ?>
                            <label for="profile_pics" class="photo-upload-btn">
                                <i class="fas fa-camera"></i>
                            </label>
                            <input type="file" class="d-none" id="profile_pics" name="profile_pics" accept="image/*" required>
                        </div>
                        <div class="text-center">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-upload me-2"></i>Upload Photo
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Update Profile Tab -->
            <div class="tab-pane fade" id="update-profile" role="tabpanel">
                <div class="settings-card">
                    <form action="../helpers/update_profile.php" method="post">
                        <div class="form-section">
                            <h4 class="mb-4">Personal Information</h4>
                            <div class="row g-3">
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
                        </div>

                        <div class="form-section">
                            <h4 class="mb-4">Contact Information</h4>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label for="email" class="form-label">Email Address</label>
                                    <input type="email" class="form-control" id="email" name="email" value="<?php echo htmlspecialchars($email); ?>" required>
                                </div>
                                <div class="col-md-6">
                                    <label for="contactNumber" class="form-label">Contact Number</label>
                                    <input type="text" class="form-control" id="contactNumber" name="contactNumber" value="<?php echo htmlspecialchars($contactNumber); ?>" required>
                                </div>
                            </div>
                        </div>

                        <div class="form-section">
                            <h4 class="mb-4">Security</h4>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label for="password" class="form-label">New Password</label>
                                    <input type="password" class="form-control" id="password" name="password" placeholder="Enter new password">
                                </div>
                                <div class="col-md-6">
                                    <label for="date_of_birth" class="form-label">Date of Birth</label>
                                    <input type="date" class="form-control" id="date_of_birth" name="date_of_birth" value="<?php echo htmlspecialchars($rowdateOfBirth); ?>" required>
                                </div>
                            </div>
                        </div>

                        <div class="form-section">
                            <h4 class="mb-4">Address Information</h4>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label for="country" class="form-label">Country</label>
                                    <input type="text" class="form-control" id="country" name="country" value="<?php echo htmlspecialchars($country); ?>">
                                </div>
                                <div class="col-md-6">
                                    <label for="city" class="form-label">City</label>
                                    <input type="text" class="form-control" id="city" name="city" value="<?php echo htmlspecialchars($city); ?>">
                                </div>
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
                        </div>

                        <div class="text-end mt-4">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-2"></i>Save Changes
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="../assets/js/bootstrap.bundle.min.js"></script>
    <script src="../js/bootstrap.bundle.min.js"></script>
    <script src="../js/scripts.js"></script>
    <script>
        // Add click handler for the photo upload button
        document.querySelector('.photo-upload-btn').addEventListener('click', function() {
            document.getElementById('profile_pics').click();
        });

        // Preview image before upload
        document.getElementById('profile_pics').addEventListener('change', function(e) {
            if (e.target.files && e.target.files[0]) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    const img = document.querySelector('.profile-photo');
                    if (img) {
                        img.src = e.target.result;
                    }
                }
                reader.readAsDataURL(e.target.files[0]);
            }
        });
    </script>
</body>

</html>