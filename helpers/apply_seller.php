<?php
include("../database/session.php");
include("../database/config.php");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get form data
    $businessName = $_POST['businessName'];
    $businessDescription = $_POST['businessDescription'];
    $businessAddress = $_POST['businessAddress'];
    $userId = $_SESSION['userId'];

    // File uploads (business and health permit)
    $businessPermit = $_FILES['businessPermit'];
    $healthPermit = $_FILES['healthPermit'];

    // Directory to store uploaded files
    $targetDir = "../image/"; // Make sure this directory exists

    // Prepare file paths for storage
    $businessPermitPath = $targetDir . basename($businessPermit['name']);
    $healthPermitPath = $targetDir . basename($healthPermit['name']);

    // Flag for file upload success
    $uploadOk = 1;

    // Handle business permit upload
    if ($businessPermit['size'] > 0) {
        // Check if the file is a valid image or document
        $allowedExtensions = array('jpg', 'jpeg', 'png', 'gif', 'pdf');
        $fileExtension = strtolower(pathinfo($businessPermitPath, PATHINFO_EXTENSION));

        if (!in_array($fileExtension, $allowedExtensions)) {
            echo "Sorry, only JPG, JPEG, PNG, GIF & PDF files are allowed for the business permit.";
            $uploadOk = 0;
        }

        // If valid, move the file to the target directory
        if ($uploadOk && !move_uploaded_file($businessPermit['tmp_name'], $businessPermitPath)) {
            echo "Sorry, there was an error uploading the business permit file.";
            $uploadOk = 0;
        }
    }

    // Handle health permit upload
    if ($healthPermit['size'] > 0) {
        // Check if the file is a valid image or document
        $allowedExtensions = array('jpg', 'jpeg', 'png', 'gif', 'pdf');
        $fileExtension = strtolower(pathinfo($healthPermitPath, PATHINFO_EXTENSION));

        if (!in_array($fileExtension, $allowedExtensions)) {
            echo "Sorry, only JPG, JPEG, PNG, GIF & PDF files are allowed for the health permit.";
            $uploadOk = 0;
        }

        // If valid, move the file to the target directory
        if ($uploadOk && !move_uploaded_file($healthPermit['tmp_name'], $healthPermitPath)) {
            echo "Sorry, there was an error uploading the health permit file.";
            $uploadOk = 0;
        }
    }

    // If file uploads are successful, insert data into the database
    if ($uploadOk) {
        // Debugging: Check if the database connection is valid
        if (!$conn) {
            die("Connection failed: " . mysqli_connect_error());
        }

        // Prepare the statement
        $stmt = $conn->prepare("INSERT INTO apply_seller (seller_id, business_name, description, address, business_permit, health_permit, status) VALUES (?, ?, ?, ?, ?, ?, 'pending')");
        
        if ($stmt === false) {
            die('MySQL prepare error: ' . $conn->error);
        }

        // Bind parameters
        $stmt->bind_param("isssss", $userId, $businessName, $businessDescription, $businessAddress, $businessPermitPath, $healthPermitPath);

        // Execute the statement
        if ($stmt->execute()) {
            header("Location: ../user/home.php?application=success");
            exit();
        } else {
            echo "Error: " . $stmt->error;
        }

        $stmt->close();
    }

    // Close database connection
    $conn->close();
}
?>
