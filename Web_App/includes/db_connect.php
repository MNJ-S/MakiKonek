<?php
// Enable detailed database error reporting (Perfect for local XAMPP development)
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

$servername = "localhost";
$username = "root";       
$password = "";           
$database = "makikonek_db"; 

try {
    // Create the connection
    $conn = mysqli_connect($servername, $username, $password, $database);
    
    // Set character set to UTF-8 to handle special characters (like ñ) safely
    mysqli_set_charset($conn, "utf8mb4");
    
} catch (mysqli_sql_exception $e) {
    // If connection fails, stop everything and print the exact error message
    die("<h3 style='color:red;'>Database Connection Failed:</h3> <p>" . $e->getMessage() . "</p>");
}

// The connection ($conn) stays open here for your other files to use!
?>