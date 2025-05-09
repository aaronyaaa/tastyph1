<?php
include '../database/config.php';

$status = isset($_GET['status']) ? mysqli_real_escape_string($conn, $_GET['status']) : '';

$output = "";

// Fetch sellers
$sqlSellers = "SELECT seller_id, business_name, address, application_date, status, 'Seller' AS user_type FROM apply_seller WHERE status = '$status'";
$resultSellers = $conn->query($sqlSellers);
if (!$resultSellers) {
    die("Error fetching sellers: " . $conn->error);
}

// Fetch suppliers
$sqlSuppliers = "SELECT supplier_id, business_name, address, application_date, status, 'Supplier' AS user_type FROM apply_supplier WHERE status = '$status'";
$resultSuppliers = $conn->query($sqlSuppliers);
if (!$resultSuppliers) {
    die("Error fetching suppliers: " . $conn->error);
}

// Display data if available
if ($resultSellers->num_rows > 0 || $resultSuppliers->num_rows > 0) {
    while ($row = $resultSellers->fetch_assoc()) {
        $output .= "<tr>
                        <td>{$row['seller_id']}</td>
                        <td>{$row['business_name']}</td>
                        <td>{$row['address']}</td>
                        <td>{$row['application_date']}</td>
                        <td>{$row['user_type']}</td>
                        <td>{$row['status']}</td>
                    </tr>";
    }
    
    while ($row = $resultSuppliers->fetch_assoc()) {
        $output .= "<tr>
                        <td>{$row['supplier_id']}</td>
                        <td>{$row['business_name']}</td>
                        <td>{$row['address']}</td>
                        <td>{$row['application_date']}</td>
                        <td>{$row['user_type']}</td>
                        <td>{$row['status']}</td>
                    </tr>";
    }
} else {
    $output .= "<tr><td colspan='6' class='text-center'>No records found</td></tr>";
}

echo $output;
$conn->close();
?>
