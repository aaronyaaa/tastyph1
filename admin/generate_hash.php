<?php
$password = 'admin123';  // Password you want to hash
$hashed_password = password_hash($password, PASSWORD_DEFAULT);
echo "Hashed Password: " . $hashed_password;
?>
