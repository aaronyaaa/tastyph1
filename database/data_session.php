<?php
include("../database/session.php");
include("../database/config.php");

// Get the current user type from the session
$userType = $_SESSION['usertype'] ?? 'user'; // Default to 'user' if not set

// Query to get store applications from the 'apply_seller' table
$sql = "SELECT seller_id, id, business_name, description, address, business_permit, health_permit, application_date, status FROM apply_seller";
$result = $conn->query($sql);

$sql = "SELECT supplier_id, business_name, description, address, business_permit, health_permit, application_date, status FROM apply_supplier";
$result = $conn->query($sql);

$seller_id = $_SESSION['seller_id'] ?? $_GET['seller_id'] ?? 0;

$sql = "SELECT DISTINCT seller_id FROM products";
$stmt = $conn->prepare($sql);
$stmt->execute();
$result = $stmt->get_result();

?>
