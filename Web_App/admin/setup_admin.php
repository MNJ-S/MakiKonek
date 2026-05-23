<?php
// admin/setup_admin.php
require_once __DIR__ . '/../includes/db_connect.php';

// Your updated team array
$test_accounts = [
    [
        'username' => 'jhody_admin',
        'email' => 'atinon.jhody@gmail.com',
        'password' => 'admin123',
        'role' => 'Super Admin'
    ],
    [
        'username' => 'mary_admin',
        'email' => 'mary@makikonek.ph',
        'password' => 'admin123',
        'role' => 'Barangay Staff'
    ],
    [
        'username' => 'shem_admin',
        'email' => 'shem@makikonek.ph',
        'password' => 'admin123',
        'role' => 'Barangay Staff'
    ],
    [
        'username' => 'nat_admin',
        'email' => 'nat@makikonek.ph',
        'password' => 'admin123',
        'role' => 'Barangay Staff'
    ]
];

echo "<h2>MakiKonek Team Setup Engine</h2>";
echo "<ul>";

// Loop through the array and insert each teammate into the database
foreach ($test_accounts as $account) {
    // Hash the password securely
    $hashed_password = password_hash($account['password'], PASSWORD_DEFAULT);

    // Prepare the SQL statement
    $query = "INSERT INTO admin_accounts (username, email, password_hash, role) VALUES (?, ?, ?, ?)";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "ssss", $account['username'], $account['email'], $hashed_password, $account['role']);

    // Execute and print the result
    if (mysqli_stmt_execute($stmt)) {
        echo "<li style='color: green;'>Success: Created <strong>{$account['role']}</strong> account for <em>{$account['username']}</em>.</li>";
    } else {
        echo "<li style='color: red;'>Failed: Error creating account for <em>{$account['username']}</em>.</li>";
    }
}

echo "</ul>";
echo "<h3>Team Credentials:</h3>";
echo "<ul>";
echo "<li><strong>Jhody (Super Admin):</strong> jhody_admin / admin123</li>";
echo "<li><strong>Mary (Staff):</strong> mary_admin / admin123</li>";
echo "<li><strong>Shem (Staff):</strong> shem_admin / admin123</li>";
echo "<li><strong>Nat (Staff):</strong> nat_admin / admin123</li>";
echo "</ul>";

echo "<p style='color: orange; font-weight: bold;'>Remember to delete this setup_admin.php file after confirming success!</p>";
echo "<a href='login_admin.php' style='padding: 10px 15px; background-color: #0d6efd; color: white; text-decoration: none; border-radius: 5px;'>Go to Login Page</a>";
?>