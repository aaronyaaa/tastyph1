<?php
include('../database/config.php'); // Include the database configuration file
// Get form data
$firstName = $_POST['firstName'] ?? '';
$middleName = $_POST['middleName'] ?? null;
$lastName = $_POST['lastName'] ?? '';
$contactNumber = $_POST['contactNumber'] ?? '';
$email = $_POST['email'] ?? '';
$password = $_POST['password'] ?? '';
$dateOfBirth = $_POST['date_of_birth'] ?? null; // Fetch the date_of_birth

// Validate required fields
if (empty($firstName) || empty($lastName) || empty($contactNumber) || empty($email) || empty($password) || empty($dateOfBirth)) {
    $message = "All required fields must be filled.";
} elseif (strlen($password) < 6) {
    $message = "Password must be at least 6 characters long.";
} elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $message = "Invalid email format.";
} else {
    // Default optional fields
    $country = null;
    $city = null;
    $streetname = null;
    $barangay = null;
    $province = null;
    $profilePics = null;

    // Prepare SQL query
    $stmt = $conn->prepare(
        "INSERT INTO users (first_name, middle_name, last_name, contact_number, email, password, date_of_birth, country, city, streetname, barangay, province, profile_pics, usertype) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'user')"
    );

    if ($stmt === false) {
        $message = "Error in SQL query preparation: " . $conn->error;
    } else {
        // Bind parameters
        $stmt->bind_param(
            "sssssssssssss", // Match the number of placeholders (13 '?' placeholders in this query)
            $firstName,
            $middleName,
            $lastName,
            $contactNumber,
            $email,
            $password,
            $dateOfBirth,
            $country,
            $city,
            $streetname,
            $barangay,
            $province,
            $profilePics
        );

        // Execute query
        if ($stmt->execute()) {
            header("Location: ../index.php?registration=success");
            exit();
        } else {
            $message = "Error: " . $stmt->error;
        }

        $stmt->close();
    }
}

$conn->close();

// Output JavaScript for error messages
echo "<script type='text/javascript'>
    alert(" . json_encode($message) . ");
    window.location.href = '../index.php';
</script>";
?>
