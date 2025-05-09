<?php
// Check if the session is already started
if (session_status() == PHP_SESSION_NONE) {
    session_start(); // Start session only if no session is active
}

// Check if the user is logged in, if not redirect to login page
if (!isset($_SESSION['userId'])) {
    header("Location: ../index.php"); // Redirect to login page
    exit();
}

// Include database connection
include('../database/config.php'); // Make sure you have a 'config.php' file with DB connection details

// Fetch user details from the database
$userId = $_SESSION['userId']; // User ID from session

// Prepare and execute SQL query to fetch all user details
$sql = "SELECT id, first_name, middle_name, last_name, date_of_birth, contact_number, country, city, streetname, barangay, province, email, profile_pics FROM users WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $userId);
$stmt->execute();
$stmt->store_result();

// Check if the user exists
if ($stmt->num_rows > 0) {
    // Bind result variables for all columns
    $stmt->bind_result($id, $firstName, $middleName, $lastName, $dateOfBirth, $contactNumber, $country, $city, $streetname, $barangay, $province, $email, $profilePics);

    // Fetch the result
    $stmt->fetch();

    // Use htmlspecialchars to prevent XSS (Cross-Site Scripting)
    $firstName = htmlspecialchars($firstName);
    $middleName = htmlspecialchars($middleName);
    $lastName = htmlspecialchars($lastName);
    $dateOfBirth = htmlspecialchars($dateOfBirth);
    $contactNumber = htmlspecialchars($contactNumber);
    $country = htmlspecialchars($country);
    $city = htmlspecialchars($city);
    $streetname = htmlspecialchars($streetname);
    $barangay = htmlspecialchars($barangay);
    $province = htmlspecialchars($province);
    $email = htmlspecialchars($email);
    $profilePics = htmlspecialchars(string: $profilePics);
} else {
    // If no user found, redirect to login page
    header("Location: ../index.php");
    exit();
}

$stmt->close();
?>
