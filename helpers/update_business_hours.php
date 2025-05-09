<?php
session_start();
include('../database/config.php');

if (!$conn) {
    die("Database connection failed: " . $conn->connect_error);
}

$user_id = $_SESSION['userId'] ?? $_POST['user_id'];
$user_type = $_SESSION['usertype'] ?? 'seller';

// Debugging: Log incoming POST data
error_log("POST Data: " . print_r($_POST, true));

// Step 1: Fetch only the modified schedules
$modified_days = isset($_POST['is_available']) ? array_keys($_POST['is_available']) : [];

if (!empty($modified_days)) {
    foreach ($modified_days as $day) {
        // Capture data from form
        $is_available = ($_POST['is_available'][$day] == "1") ? 1 : 0;
        $open_time = !empty($_POST['open_time'][$day]) ? $_POST['open_time'][$day] : null;
        $close_time = !empty($_POST['close_time'][$day]) ? $_POST['close_time'][$day] : null;

        // If schedule is disabled, set times to NULL
        if ($is_available == 0) {
            $open_time = null;
            $close_time = null;
        }

        // Debugging Log
        error_log("Processing: $day - Open: " . ($open_time ?? 'NULL') . ", Close: " . ($close_time ?? 'NULL') . ", Available: $is_available");

        // Check if record exists for this day
        $check_sql = "SELECT id FROM business_hours WHERE user_id = ? AND business_type = ? AND day_of_week = ?";
        $stmt = $conn->prepare($check_sql);
        $stmt->bind_param("iss", $user_id, $user_type, $day);
        $stmt->execute();
        $stmt->store_result();
        $record_exists = $stmt->num_rows > 0;
        $stmt->close();

        if ($record_exists) {
            // Update existing record
            $update_sql = "UPDATE business_hours SET open_time = ?, close_time = ?, is_available = ? WHERE user_id = ? AND business_type = ? AND day_of_week = ?";
            $stmt = $conn->prepare($update_sql);
            $stmt->bind_param("ssisss", $open_time, $close_time, $is_available, $user_id, $user_type, $day);

            if (!$stmt->execute()) {
                error_log("Error updating $day: " . $stmt->error);
            } else {
                error_log("Successfully updated $day: Open = $open_time, Close = $close_time, Available = $is_available");
            }
            $stmt->close();
        } else {
            // Insert new record if it doesn't exist
            $insert_sql = "INSERT INTO business_hours (user_id, business_type, day_of_week, open_time, close_time, is_available) VALUES (?, ?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($insert_sql);
            $stmt->bind_param("issssi", $user_id, $user_type, $day, $open_time, $close_time, $is_available);

            if (!$stmt->execute()) {
                error_log("Error inserting $day: " . $stmt->error);
            } else {
                error_log("Successfully inserted $day: Open = $open_time, Close = $close_time, Available = $is_available");
            }
            $stmt->close();
        }
    }
}

// Redirect back
header("Location: ../includes/manage_products.php");
exit();
?>
