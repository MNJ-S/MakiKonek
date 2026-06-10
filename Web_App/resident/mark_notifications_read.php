<?php
session_start();
if (!isset($_SESSION['resident_id'])) {
    http_response_code(401);
    exit;
}
require_once __DIR__ . '/../includes/db_connect.php';
$res_id = (int)$_SESSION['resident_id'];
mysqli_query($conn, "UPDATE user_notifications SET is_read = 1 WHERE user_id = $res_id AND is_read = 0");
echo 'OK';
?>