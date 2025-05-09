<?php
session_start();

// Include your database connection file
include('../database/config.php'); // Replace with the correct path to your DB connection file

// Ensure the user is logged in
if (!isset($_SESSION['userId'])) {
    header("Location: ../index.php"); // Redirect to login if not logged in
    exit();
}

$userId = $_SESSION['userId']; // User ID from session

// Fetch the user's existing profile information from the database
$sql = "SELECT * FROM users WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

// Check if form has been submitted
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Get form data
    $firstName = isset($_POST['first_name']) ? $_POST['first_name'] : $user['first_name'];
    $middleName = isset($_POST['middle_name']) ? $_POST['middle_name'] : $user['middle_name'];
    $lastName = isset($_POST['last_name']) ? $_POST['last_name'] : $user['last_name'];
    $dateOfBirth = isset($_POST['date_of_birth']) ? $_POST['date_of_birth'] : $user['date_of_birth'];
    $contactNumber = isset($_POST['contact_number']) ? $_POST['contact_number'] : $user['contact_number'];
    $country = isset($_POST['country']) ? $_POST['country'] : $user['country'];
    $city = isset($_POST['city']) ? $_POST['city'] : $user['city'];
    $streetname = isset($_POST['streetname']) ? $_POST['streetname'] : $user['streetname'];
    $barangay = isset($_POST['barangay']) ? $_POST['barangay'] : $user['barangay'];
    $province = isset($_POST['province']) ? $_POST['province'] : $user['province'];
    $email = isset($_POST['email']) ? $_POST['email'] : $user['email'];

    // Handle profile picture upload
    if (isset($_FILES['profile_pics']) && $_FILES['profile_pics']['error'] == 0) {
        $profilePics = $_FILES['profile_pics']['name'];
        $uploadDir = '../uploads/';
        $uploadFile = $uploadDir . basename($profilePics);

        // Move the uploaded file to the "uploads" directory
        if (move_uploaded_file($_FILES['profile_pics']['tmp_name'], $uploadFile)) {
            // If successful, store the file name
        } else {
            // If failed, set the profilePics to NULL (no change)
            $profilePics = NULL;
        }
    } else {
        // If no new profile picture is uploaded, keep the old one
        $profilePics = $user['profile_pics'];
    }

    // Prepare and execute the SQL query to update the user details
    $sql = "UPDATE users SET first_name = ?, middle_name = ?, last_name = ?, date_of_birth = ?, contact_number = ?, country = ?, city = ?, streetname = ?, barangay = ?, province = ?, email = ?, profile_pics = ? WHERE id = ?";
    $stmt = $conn->prepare($sql);

    // Check if any of the form fields are missing or set to NULL
    if ($stmt) {
        // Bind the parameters correctly (we expect a mix of strings and integers, with NULL as possible values)
        $stmt->bind_param("ssssssssssssi", $firstName, $middleName, $lastName, $dateOfBirth, $contactNumber, $country, $city, $streetname, $barangay, $province, $email, $profilePics, $userId);

        if ($stmt->execute()) {
            // If the update is successful, redirect to the profile page
            header("Location: ../user/home.php");
        } else {
            // If the update fails, display an error message
            echo "Error updating profile. Please try again.";
        }
    } else {
        echo "Error preparing the query. Please try again.";
    }

    // Close the statement and connection
    $stmt->close();
    $conn->close();
}
?>
