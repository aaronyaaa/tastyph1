<?php
// Connection code
$conn = new mysqli('localhost', 'root', '', 'tastyph1');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $category_name = $_POST['name'];

    $sql = "INSERT INTO categories (name) VALUES ('$category_name')";
    
    if ($conn->query($sql) === TRUE) {
        // Redirect to the page that lists categories or show a success message
        echo "<script>
                alert('Category added successfully!');
                window.location.href = '../includes/manage_products.php'; // Change this to your category listing page
              </script>";
    } else {
        // Display an error message if the query fails
        echo "<script>
                alert('Error: " . $conn->error . "');
                window.location.href = '../includes/manage_products.php'; // Change this to your category listing page
              </script>";
    }
}

$conn->close();
?>
