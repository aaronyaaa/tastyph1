<?php
ob_start();  // Start output buffering
include('config.php');
session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];
    $password = $_POST['password'];

    // Prepare SQL statement to check credentials
    $stmt = $conn->prepare("SELECT admin_id, email, password FROM admin WHERE email = ? AND usertype = 'admin'");
    if ($stmt) {
        $stmt->bind_param("s", $email);
        $stmt->execute();

        $stmt->bind_result($id, $stored_email, $stored_password);
        if ($stmt->fetch()) {
            if (password_verify($password, $stored_password)) {
                // Set session variables
                $_SESSION['admin_id'] = $id;
                $_SESSION['email'] = $stored_email;

                // Redirect to admin dashboard
                header("Location: admin_dashboard.php");
                exit(); // Ensure no further code is executed
            } else {
                $message = "Invalid password.";
            }
        } else {
            $message = "No admin user found with that email.";
        }
        $stmt->close();
    } else {
        die("Error preparing SQL statement: " . $conn->error);
    }

    $conn->close();
}

if (!empty($message)) {
    echo "<script>alert('$message'); window.location.href = 'index.php';</script>";
}
?>
