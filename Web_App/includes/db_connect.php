<?php
require_once __DIR__ . '/config.php';

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
date_default_timezone_set('Asia/Manila');

try {
    $conn = mysqli_init();

    mysqli_real_connect(
        $conn,
        DB_HOST,
        DB_USER,
        DB_PASS,
        DB_NAME,
        DB_PORT,
        NULL,
        MYSQLI_CLIENT_SSL
    );

    mysqli_query($conn, "SET time_zone = '+08:00';");

    mysqli_set_charset($conn, "utf8mb4");
} catch (mysqli_sql_exception $e) {
    die("<h3 style='color:red;'>Database Connection Failed:</h3> <p>" . $e->getMessage() . "</p>");
}
