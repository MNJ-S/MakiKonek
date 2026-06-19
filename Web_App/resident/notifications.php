<?php
session_start();
require_once __DIR__ . '/../includes/db_connect.php';

if (!isset($_SESSION['resident_id'])) {
    header("Location: ../login_reg.php");
    exit();
}
$pageTitle = 'Notifications';
$activePage = 'notifications';
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle; ?> | MakiKonek</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/header.css?v=20260613e">
    <link rel="icon" href="../assets/img/Barangay_Makiling_Seal.png" type="image/png">
    <link rel="stylesheet" href="../assets/css/footer.css?v=20260613b">
    <link rel="stylesheet" href="../assets/css/resident.css?v=20260613a">
</head>

<body class="resident-page">
    <?php
    $navBase = '../public/';
    $assetBase = '../assets';
    $loginHref = '../login_reg.php';
    $isResidentHeader = true;
    include __DIR__ . '/../includes/header.php';
    ?>

    <div class="resident-shell">
        <?php include __DIR__ . '/partials/resident_sidebar.php';

        // Fetch all notifications for the page view instead of limiting to 10 from header
        $allPageNotifications = [];
        $page_notif_query = "SELECT notification_id, title, message, type, icon, is_read, created_at FROM user_notifications WHERE user_id = ? ORDER BY created_at DESC";
        $page_notif_stmt = mysqli_prepare($conn, $page_notif_query);
        if ($page_notif_stmt) {
            $res_id = (int)$_SESSION['resident_id'];
            mysqli_stmt_bind_param($page_notif_stmt, "i", $res_id);
            mysqli_stmt_execute($page_notif_stmt);
            $page_notif_result = mysqli_stmt_get_result($page_notif_stmt);
            while ($row = mysqli_fetch_assoc($page_notif_result)) {
                $time_diff = max(0, time() - strtotime($row['created_at']));
                if ($time_diff < 60) {
                    $time_str = "Just now";
                } elseif ($time_diff < 3600) {
                    $mins = floor($time_diff / 60);
                    $time_str = $mins . " min" . ($mins > 1 ? "s" : "") . " ago";
                } elseif ($time_diff < 86400) {
                    $hours = floor($time_diff / 3600);
                    $time_str = $hours . " hour" . ($hours > 1 ? "s" : "") . " ago";
                } else {
                    $days = floor($time_diff / 86400);
                    $time_str = $days . " day" . ($days > 1 ? "s" : "") . " ago";
                }

                $allPageNotifications[] = [
                    'id' => $row['notification_id'],
                    'title' => $row['title'],
                    'message' => $row['message'],
                    'time' => $time_str,
                    'type' => $row['type'],
                    'icon' => $row['icon'],
                    'unread' => !$row['is_read'],
                ];
            }
            mysqli_stmt_close($page_notif_stmt);
        }
        ?>

        <main class="resident-main">
            <div class="notifications-header">
                <div class="page-heading">
                    <h1>Notifications</h1>
                    <p>Stay updated with announcements and alerts</p>
                </div>
                <a href="#" class="mark-read-btn" data-mark-all-read <?php echo empty($allPageNotifications) || $residentUnreadCount === 0 ? 'style="display:none;"' : ''; ?>>Mark all as read</a>
            </div>

            <div class="notifications-stack">
                <?php if (empty($allPageNotifications)): ?>
                    <div style="text-align: center; padding: 3rem 1rem; color: #6b7280;">
                        <i class="fa-regular fa-bell-slash" style="font-size: 3rem; margin-bottom: 1rem; color: #9ca3af;"></i>
                        <h2>No notifications yet</h2>
                        <p>You're all caught up! Check back later for updates.</p>
                    </div>
                <?php else: ?>
                    <?php foreach ($allPageNotifications as $notification):
                        $bg_class = 'bg-light-blue';
                        $border_class = 'border-blue-line';
                        $pill_class = 'pill-blue';
                        $icon_bg = 'bg-solid-blue';

                        if ($notification['type'] === 'Request Update') {
                            $bg_class = 'bg-light-green';
                            $border_class = 'border-green-line';
                            $pill_class = 'pill-green';
                            $icon_bg = 'bg-solid-green';
                        } elseif ($notification['type'] === 'Reservation Update') {
                            $bg_class = 'bg-light-orange';
                            $border_class = 'border-orange-line';
                            $pill_class = 'pill-orange';
                            $icon_bg = 'bg-solid-orange';
                        }
                    ?>
                        <div class="notification-card page-notification-card <?php echo $bg_class; ?> <?php echo $border_class; ?>" <?php echo $notification['unread'] ? 'style="font-weight: 600;"' : 'style="opacity: 0.8;"'; ?>>
                            <div class="noti-left-icon">
                                <div class="icon-circle <?php echo $icon_bg; ?>">
                                    <i class="<?php echo htmlspecialchars($notification['icon']); ?>"></i>
                                </div>
                            </div>
                            <div class="noti-right-body">
                                <div class="noti-headline-row">
                                    <h2 class="noti-title"><?php echo htmlspecialchars($notification['title']); ?></h2>
                                    <span class="noti-time"><?php echo htmlspecialchars($notification['time']); ?></span>
                                </div>
                                <p class="noti-description"><?php echo htmlspecialchars($notification['message']); ?></p>
                                <div class="badge-row">
                                    <span class="noti-pill <?php echo $pill_class; ?>"><?php echo strtoupper(htmlspecialchars($notification['type'])); ?></span>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </main>
    </div>

    <?php
    $footerBase = '../public/';
    $footerAssetBase = '../assets';
    include __DIR__ . '/../includes/footer.php';
    ?>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const markAllBtn = document.querySelector('.mark-read-btn');
            const cards = document.querySelectorAll('.page-notification-card');

            if (markAllBtn) {
                markAllBtn.addEventListener('click', function(e) {
                    e.preventDefault();
                    cards.forEach(card => {
                        card.style.fontWeight = 'normal';
                        card.style.opacity = '0.8';
                    });

                    fetch('mark_notifications_read.php', {
                            method: 'POST'
                        })
                        .catch(err => console.error(err));

                    this.style.display = 'none';

                    // Trigger the header's mark as read if it exists to sync UI
                    const headerMarkRead = document.querySelector('[data-mark-all-read]');
                    if (headerMarkRead && headerMarkRead !== this) {
                        headerMarkRead.click();
                    }
                });
            }
        });
    </script>
</body>

</html>