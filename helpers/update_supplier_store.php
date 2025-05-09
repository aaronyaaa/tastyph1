<?php
// Include the database connection
include('../database/config.php');  
session_start();  // Start the session

// Check if the supplier is logged in
if (!isset($_SESSION['userId'])) {
    echo "Supplier is not logged in.";
    exit();
}

$userId = $_SESSION['userId']; // Assign user ID from the session

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Start building the update query dynamically
    $fieldsToUpdate = [];
    $values = [];

    // Handle profile picture upload
    if (isset($_FILES['profile_pics']) && $_FILES['profile_pics']['error'] == 0) {
        $targetDir = "../uploads/";
        if (!is_dir($targetDir)) {
            mkdir($targetDir, 0777, true); // Ensure directory exists
        }

        // Create a unique filename to prevent overwrites
        $fileName = $userId . "_supplier_" . time() . "." . pathinfo($_FILES["profile_pics"]["name"], PATHINFO_EXTENSION);
        $targetFile = $targetDir . $fileName;

        $imageFileType = strtolower(pathinfo($targetFile, PATHINFO_EXTENSION));

        // Validate image
        if (getimagesize($_FILES["profile_pics"]["tmp_name"]) !== false) {
            if ($_FILES["profile_pics"]["size"] <= 5000000) {  // Max 5MB
                if (move_uploaded_file($_FILES["profile_pics"]["tmp_name"], $targetFile)) {
                    // Store the image path in the database
                    $fieldsToUpdate[] = "profile_pics = ?";
                    $values[] = $targetFile;
                } else {
                    echo "Error uploading supplier profile picture.";
                    exit();
                }
            } else {
                echo "File size is too large.";
                exit();
            }
        } else {
            echo "Uploaded file is not an image.";
            exit();
        }
    }

    // Check and add only changed fields
    $fields = ['business_name', 'description', 'address', 'business_permit', 'health_permit'];

    foreach ($fields as $field) {
        if (!empty($_POST[$field])) {
            $fieldsToUpdate[] = "$field = ?";
            $values[] = $_POST[$field];
        }
    }

    // Proceed only if there are changes
    if (!empty($fieldsToUpdate)) {
        $sql = "UPDATE apply_supplier SET " . implode(', ', $fieldsToUpdate) . " WHERE supplier_id = ?";
        $values[] = $userId; // Add supplier_id at the end

        // Prepare and execute query
        $stmt = $conn->prepare($sql);
        $stmt->bind_param(str_repeat("s", count($values) - 1) . "i", ...$values);

        if ($stmt->execute()) {
            header("Location: ../includes/manage_ingredient.php"); // Redirect after successful update
            exit();
        } else {
            echo "Error updating supplier profile.";
            exit();
        }
    } else {
        echo "No changes were made.";
        exit();
    }
}
?>
