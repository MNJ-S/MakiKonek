<?php
// Enable strict error reporting so PHP tells us exactly what is wrong
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

$servername = "localhost";
$username = "root";
$password = "";
$database = "makikonek_db";

try {
    $conn = mysqli_connect($servername, $username, $password, $database);
    mysqli_set_charset($conn, "utf8mb4");
} catch (mysqli_sql_exception $e) {
    // ERROR MESSAGE   
    die("<h3 style='color:red;'>Database Connection Failed:</h3> <p>" . $e->getMessage() . "</p>");
}