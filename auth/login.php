<?php
session_start(); // Start session for managing user login state

include('../database/config.php'); // Include database configuration

// Check database connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Check if form was submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Retrieve email and password from form
    $loginEmail = $_POST['loginEmail'] ?? '';
    $loginPassword = $_POST['loginPassword'] ?? '';

    // Validate input
    if (empty($loginEmail) || empty($loginPassword)) {
        $_SESSION['loginError'] = "Email and password are required.";
        echo "<script>alert('Email and password are required.'); window.location.href = 'index.php';</script>";
        exit();
    }

    // Prepare SQL statement to retrieve user data
    $stmt = $conn->prepare("SELECT id, first_name, middle_name, last_name, email, password, usertype, profile_pics FROM users WHERE email = ?");
    if (!$stmt) {
        die("Error preparing statement: " . $conn->error);
    }

    $stmt->bind_param("s", $loginEmail);

    // Execute the statement
    if (!$stmt->execute()) {
        die("Error executing statement: " . $stmt->error);
    }

    // Bind result variables
    $stmt->bind_result($userId, $firstName, $middleName, $lastName, $email, $storedPassword, $userType, $profilePics);

    // Fetch user data
    if ($stmt->fetch()) {
        // Verify password (you should hash and verify passwords securely with `password_hash` and `password_verify`)
        if ($loginPassword === $storedPassword) {
            // Password is correct, set session variables
            $_SESSION['userId'] = $userId;
            $_SESSION['firstName'] = $firstName;
            $_SESSION['middleName'] = $middleName;
            $_SESSION['lastName'] = $lastName;
            $_SESSION['email'] = $email;
            $_SESSION['usertype'] = $userType;
            $_SESSION['profilePics'] = $profilePics;

            // Redirect based on usertype
            if ($userType === 'supplier') {
                header("Location: ../includes/supplier_dashboard.php"); // Redirect to supplier dashboard
            } elseif ($userType === 'seller') {
                header("Location: ../user/home.php"); // Redirect to seller dashboard
            } else {
                header("Location: ../user/home.php"); // Redirect to regular user dashboard
            }
            exit();
        } else {
            // Password is incorrect
            echo "<script>alert('Invalid password.'); window.location.href = '../index.php';</script>";
            exit();
        }
    } else {
        // User not found
        echo "<script>alert('User not found.'); window.location.href = '../index.php';</script>";
        exit();
    }

    // Close statement
    $stmt->close();
}

// Close connection
$conn->close();
?>
