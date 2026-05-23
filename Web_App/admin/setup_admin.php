<?php
require_once '../includes/db_connect.php';

$username = "master_admin";
$email = "admin@makikonek.ph";
$password = "admin123"; // The password you will type to log in
$role = "Super Admin";

// Hash the password securely
$hashed_password = password_hash($password, PASSWORD_DEFAULT);

// Insert into the database
$query = "INSERT INTO admin_accounts (username, email, password_hash, role) VALUES (?, ?, ?, ?)";
$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, "ssss", $username, $email, $hashed_password, $role);

if (mysqli_stmt_execute($stmt)) {
    echo "<h2 style='color: green;'>Success! Super Admin created.</h2>";
    echo "<p>Username: <strong>master_admin</strong></p>";
    echo "<p>Password: <strong>admin123</strong></p>";
    echo "<p><strong>IMPORTANT:</strong> Delete this setup_admin.php file right now so no one else can run it!</p>";
    echo "<a href='login.php'>Go to Login Page</a>";
} else {
    echo "<h2 style='color: red;'>Failed. Account might already exist.</h2>";
}
?>