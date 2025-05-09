<?php
// Include the database connection
include('../database/config.php');  // Make sure the path is correct

// Start the session
session_start();

// Check if the seller is logged in
if (!isset($_SESSION['userId'])) {
    echo "Seller is not logged in. Debugging session: ";
    var_dump($_SESSION);
    exit();
}

$userId = $_SESSION['userId']; // Assign the user ID from the session

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Process profile picture upload
    if (isset($_FILES['profile_pics']) && $_FILES['profile_pics']['error'] == 0) {
        $targetDir = "../uploads/";
        $targetFile = $targetDir . basename($_FILES["profile_pics"]["name"]);
        $imageFileType = strtolower(pathinfo($targetFile, PATHINFO_EXTENSION));

        // Check if the file is an image
        if (getimagesize($_FILES["profile_pics"]["tmp_name"]) !== false) {
            if ($_FILES["profile_pics"]["size"] <= 5000000) {  // Max 5MB
                if (move_uploaded_file($_FILES["profile_pics"]["tmp_name"], $targetFile)) {
                    $sql = "UPDATE apply_seller SET profile_pics = ? WHERE seller_id = ?";
                    $stmt = $conn->prepare($sql);
                    $stmt->bind_param("si", $targetFile, $userId);
                    if ($stmt->execute()) {
                        // Redirect immediately after success
                        header("Location: ../includes/manage_products.php");
                        exit();
                    } else {
                        echo "Error updating profile picture.";
                        exit();
                    }
                } else {
                    echo "Sorry, there was an error uploading your file.";
                    exit();
                }
            } else {
                echo "Sorry, your file is too large.";
                exit();
            }
        } else {
            echo "File is not an image.";
            exit();
        }
    }

    // Update other seller profile fields in apply_seller table
    $business_name = isset($_POST['business_name']) ? $_POST['business_name'] : '';
    $description = isset($_POST['description']) ? $_POST['description'] : '';

    if (empty($business_name) || empty($description)) {
        echo "Business name and description are required.";
        exit();
    }

    $sql = "UPDATE apply_seller SET business_name = ?, description = ? WHERE seller_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssi", $business_name, $description, $userId);
    if ($stmt->execute()) {
        // Redirect immediately after success
        header("Location: ../includes/manage_products.php");
        exit();
    } else {
        echo "Error updating business name and description.";
        exit();
    }

    // Now update user details in the users table (name, address, etc.)
    $firstName = isset($_POST['first_name']) ? $_POST['first_name'] : '';
    $middleName = isset($_POST['middle_name']) ? $_POST['middle_name'] : '';
    $lastName = isset($_POST['last_name']) ? $_POST['last_name'] : '';
    $country = isset($_POST['country']) ? $_POST['country'] : '';
    $city = isset($_POST['city']) ? $_POST['city'] : '';
    $streetname = isset($_POST['streetname']) ? $_POST['streetname'] : '';
    $barangay = isset($_POST['barangay']) ? $_POST['barangay'] : '';
    $province = isset($_POST['province']) ? $_POST['province'] : '';

    // Validate user input to ensure no fields are empty
    if (empty($firstName) || empty($lastName) || empty($country) || empty($city) || empty($streetname) || empty($barangay) || empty($province)) {
        echo "All fields are required.";
        exit();
    }

    // Update user details in the users table
    $sql = "UPDATE users SET first_name = ?, middle_name = ?, last_name = ?, country = ?, city = ?, streetname = ?, barangay = ?, province = ? WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssssssssi", $firstName, $middleName, $lastName, $country, $city, $streetname, $barangay, $province, $userId);
    if ($stmt->execute()) {
        // Redirect immediately after success
        header("Location: ../includes/manage_products.php");
        exit();
    } else {
        echo "Error updating user details.";
        exit();
    }
}
?>
