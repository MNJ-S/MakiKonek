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


/**
 * Creates a notification for the admin dashboard.
 * * @param mysqli $conn Database connection
 * @param string $title Short title of the notification
 * @param string $message Detailed message
 * @param string $type Category (e.g., 'RESERVATION', 'ACCOUNT', 'SYSTEM')
 * @param string $icon Bootstrap icon class (e.g., 'bi-calendar-event')
 * @param string|null $action_url URL to redirect to when clicked
 * @param int|null $admin_id Specific admin ID, or NULL for all admins
 * @return bool
 */
function createAdminNotification($conn, $title, $message, $type = 'System', $icon = 'bi-bell', $action_url = null, $admin_id = null)
{
    try {
        $query = "INSERT INTO admin_notifications (admin_id, title, message, type, icon, action_url) VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = mysqli_prepare($conn, $query);
        mysqli_stmt_bind_param($stmt, "isssss", $admin_id, $title, $message, $type, $icon, $action_url);
        $result = mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
        return $result;
    } catch (mysqli_sql_exception $e) {
        error_log('Unable to create admin notification: ' . $e->getMessage());
        return false;
    }
}
