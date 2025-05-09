<?php
// Include the database connection
include('../database/config.php');
include('../database/data_session.php');
include('../database/session.php');



// Check if the supplier is logged in
if (!isset($_SESSION['userId'])) {
    echo "Supplier is not logged in.";
    exit();
}

$userId = $_SESSION['userId']; // Get the user ID from session

// Fetch current profile picture
$sqlGetPic = "SELECT profile_pics FROM apply_supplier WHERE supplier_id = ?";
$stmtGetPic = $conn->prepare($sqlGetPic);
$stmtGetPic->bind_param("i", $userId);
$stmtGetPic->execute();
$resultGetPic = $stmtGetPic->get_result();
$currentPic = ($resultGetPic->num_rows > 0) ? $resultGetPic->fetch_assoc()['profile_pics'] : '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Check if required fields exist in POST data
    $requiredFields = ['business_name', 'description', 'first_name', 'last_name', 'country', 'city', 'streetname', 'barangay', 'province'];
    
    foreach ($requiredFields as $field) {
        if (!isset($_POST[$field]) || empty(trim($_POST[$field]))) {
            echo "Error: $field is required.";
            exit();
        }
    }

    // Handle profile picture upload
    $newProfilePic = $currentPic; // Default to existing profile pic

    if (isset($_FILES['profile_pics']) && $_FILES['profile_pics']['error'] == 0) {
        $targetDir = "../uploads/";
        $fileName = time() . "_" . basename($_FILES["profile_pics"]["name"]); // Unique filename
        $targetFile = $targetDir . $fileName;
        $imageFileType = strtolower(pathinfo($targetFile, PATHINFO_EXTENSION));

        // Validate image file
        if (getimagesize($_FILES["profile_pics"]["tmp_name"]) !== false) {
            if ($_FILES["profile_pics"]["size"] <= 5000000) { // Limit to 5MB
                if (move_uploaded_file($_FILES["profile_pics"]["tmp_name"], $targetFile)) {
                    $newProfilePic = $targetFile;
                } else {
                    echo "Error uploading file.";
                    exit();
                }
            } else {
                echo "File too large.";
                exit();
            }
        } else {
            echo "Invalid image file.";
            exit();
        }
    }

    // Update supplier profile
    $business_name = trim($_POST['business_name']);
    $description = trim($_POST['description']);

    $sqlUpdateProfile = "UPDATE apply_supplier SET business_name = ?, description = ?, profile_pics = ? WHERE supplier_id = ?";
    $stmtUpdateProfile = $conn->prepare($sqlUpdateProfile);
    $stmtUpdateProfile->bind_param("sssi", $business_name, $description, $newProfilePic, $userId);

    if (!$stmtUpdateProfile->execute()) {
        echo "Error updating profile.";
        exit();
    }

    // Update user details
    $firstName = trim($_POST['first_name']);
    $middleName = trim($_POST['middle_name'] ?? ''); // Middle name is optional
    $lastName = trim($_POST['last_name']);
    $country = trim($_POST['country']);
    $city = trim($_POST['city']);
    $streetname = trim($_POST['streetname']);
    $barangay = trim($_POST['barangay']);
    $province = trim($_POST['province']);

    $sqlUpdateUser = "UPDATE users SET first_name = ?, middle_name = ?, last_name = ?, country = ?, city = ?, streetname = ?, barangay = ?, province = ? WHERE id = ?";
    $stmtUpdateUser = $conn->prepare($sqlUpdateUser);
    $stmtUpdateUser->bind_param("ssssssssi", $firstName, $middleName, $lastName, $country, $city, $streetname, $barangay, $province, $userId);

    if (!$stmtUpdateUser->execute()) {
        echo "Error updating user details.";
        exit();
    }

    // Redirect to manage ingredients
    header("Location: ../includes/manage_ingredient.php");
    exit();
}
?>
