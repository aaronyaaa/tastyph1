<?php
session_start();
include('../database/config.php');

// Check if user is logged in
if (!isset($_SESSION['userId'])) {
    die("User not logged in.");
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_FILES['profile_pics'])) {
    $userId = $_SESSION['userId'];
    $profilePics = $_FILES['profile_pics'];

    // File upload handling
    $targetDir = "../uploads/"; // Ensure it's correct
    $fileName = basename($profilePics['name']);
    $targetFile = $targetDir . $fileName; 
    $uploadOk = 1;
    $imageFileType = strtolower(pathinfo($targetFile, PATHINFO_EXTENSION));

    // Validate file is an image
    if (!getimagesize($profilePics['tmp_name'])) {
        $message = "File is not an image.";
        $uploadOk = 0;
    }

    // Check file size (5MB limit)
    if ($profilePics['size'] > 5 * 1024 * 1024) {
        $message = "Sorry, your file is too large.";
        $uploadOk = 0;
    }

    // Allowed extensions
    $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif'];
    if (!in_array($imageFileType, $allowedExtensions)) {
        $message = "Only JPG, JPEG, PNG & GIF files are allowed.";
        $uploadOk = 0;
    }

    // Upload file
    if ($uploadOk && move_uploaded_file($profilePics['tmp_name'], $targetFile)) {
        if (file_exists($targetFile)) { // Ensure file exists before updating database
            $relativePath = "../uploads/" . $fileName; // Store relative path in DB
            $updateQuery = "UPDATE users SET profile_pics = ? WHERE id = ?";
            $stmt = $conn->prepare($updateQuery);
            $stmt->bind_param("si", $relativePath, $userId);
            if ($stmt->execute()) {
                $_SESSION['profile_pics'] = $relativePath; // Update session
                $message = "Profile picture updated successfully.";
            } else {
                $message = "Database update failed!";
            }
        } else {
            $message = "File upload failed!";
        }
    }

    $conn->close();

    // Redirect with message
    echo "<script>alert('$message'); window.location.href = '../includes/settings.php';</script>";
} else {
    header("Location: ../includes/settings.php");
    exit();
}
?>
