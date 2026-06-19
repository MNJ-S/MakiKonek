<?php
session_start();

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');

if (!isset($_SESSION['admin_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Your admin session has expired.']);
    exit();
}

require_once __DIR__ . '/../../includes/db_connect.php';

$admin_id = (int)$_SESSION['admin_id'];

try {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $payload = json_decode(file_get_contents('php://input'), true);
        $action = is_array($payload) ? ($payload['action'] ?? '') : '';

        if ($action === 'mark_all_read') {
            $stmt = mysqli_prepare(
                $conn,
                "UPDATE admin_notifications
                 SET is_read = 1
                 WHERE is_read = 0 AND (admin_id IS NULL OR admin_id = ?)"
            );
            mysqli_stmt_bind_param($stmt, 'i', $admin_id);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);
        } elseif ($action === 'mark_read') {
            $notification_id = (int)($payload['notification_id'] ?? 0);
            if ($notification_id <= 0) {
                throw new InvalidArgumentException('Invalid notification.');
            }

            $stmt = mysqli_prepare(
                $conn,
                "UPDATE admin_notifications
                 SET is_read = 1
                 WHERE notification_id = ? AND (admin_id IS NULL OR admin_id = ?)"
            );
            mysqli_stmt_bind_param($stmt, 'ii', $notification_id, $admin_id);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);
        } else {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Unsupported notification action.']);
            exit();
        }
    } elseif ($_SERVER['REQUEST_METHOD'] !== 'GET') {
        http_response_code(405);
        echo json_encode(['success' => false, 'message' => 'Method not allowed.']);
        exit();
    }

    $count_stmt = mysqli_prepare(
        $conn,
        "SELECT COUNT(*) AS unread_count
         FROM admin_notifications
         WHERE is_read = 0 AND (admin_id IS NULL OR admin_id = ?)"
    );
    mysqli_stmt_bind_param($count_stmt, 'i', $admin_id);
    mysqli_stmt_execute($count_stmt);
    $count_row = mysqli_fetch_assoc(mysqli_stmt_get_result($count_stmt));
    mysqli_stmt_close($count_stmt);

    $notification_stmt = mysqli_prepare(
        $conn,
        "SELECT notification_id, title, message, type, icon, action_url, is_read, created_at
         FROM admin_notifications
         WHERE admin_id IS NULL OR admin_id = ?
         ORDER BY created_at DESC, notification_id DESC
         LIMIT 20"
    );
    mysqli_stmt_bind_param($notification_stmt, 'i', $admin_id);
    mysqli_stmt_execute($notification_stmt);
    $result = mysqli_stmt_get_result($notification_stmt);

    $notifications = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $notifications[] = [
            'notification_id' => (int)$row['notification_id'],
            'title' => $row['title'],
            'message' => $row['message'],
            'type' => $row['type'],
            'icon' => $row['icon'],
            'action_url' => $row['action_url'],
            'is_read' => (int)$row['is_read'],
            'created_at' => $row['created_at'],
        ];
    }
    mysqli_stmt_close($notification_stmt);

    echo json_encode([
        'success' => true,
        'unread_count' => (int)($count_row['unread_count'] ?? 0),
        'data' => $notifications,
    ]);
} catch (Throwable $e) {
    error_log('Admin notification endpoint error: ' . $e->getMessage());
    http_response_code($e instanceof InvalidArgumentException ? 400 : 500);
    echo json_encode([
        'success' => false,
        'message' => $e instanceof InvalidArgumentException
            ? $e->getMessage()
            : 'Notifications could not be loaded.',
    ]);
}
