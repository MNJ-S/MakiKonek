<?php
session_start();

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
    <link rel="stylesheet" href="../assets/css/header.css?v=20260608a">
    <link rel="stylesheet" href="../assets/css/footer.css?v=20260529e">
    <link rel="stylesheet" href="../assets/css/resident.css?v=20260530a">
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
        <?php include __DIR__ . '/partials/resident_sidebar.php'; ?>

        <main class="resident-main">
            <div class="notifications-header">
                <div class="page-heading">
                    <h1>Notifications</h1>
                    <p>Stay updated with announcements and alerts</p>
                </div>
                <a href="#" class="mark-read-btn">Mark all as read</a>
            </div>

            <div class="notifications-stack">

                <div class="notification-card bg-light-blue border-blue-line">
                    <div class="noti-left-icon">
                        <div class="icon-circle bg-solid-blue">
                            <i class="fa-regular fa-bell"></i>
                        </div>
                    </div>
                    <div class="noti-right-body">
                        <div class="noti-headline-row">
                            <h2 class="noti-title">Community Assembly Meeting</h2>
                            <span class="noti-time">2 hours ago</span>
                        </div>
                        <p class="noti-description">Join us for the quarterly barangay assembly this Saturday at 2:00 PM at the Barangay Hall.</p>
                        <div class="badge-row">
                            <span class="noti-pill pill-blue">ANNOUNCEMENT</span>
                        </div>
                    </div>
                </div>

                <div class="notification-card bg-light-green border-green-line">
                    <div class="noti-left-icon">
                        <div class="icon-circle bg-solid-green">
                            <i class="fa-regular fa-circle-check"></i>
                        </div>
                    </div>
                    <div class="noti-right-body">
                        <div class="noti-headline-row">
                            <h2 class="noti-title">Document Request Approved</h2>
                            <span class="noti-time">1 day ago</span>
                        </div>
                        <p class="noti-description">Your Barangay Clearance request (BC-2024-0001) has been approved and is ready for pickup.</p>
                        <div class="badge-row">
                            <span class="noti-pill pill-green">UPDATE</span>
                        </div>
                    </div>
                </div>

                <div class="notification-card bg-light-orange border-orange-line">
                    <div class="noti-left-icon">
                        <div class="icon-circle bg-solid-orange">
                            <i class="fa-regular fa-bell"></i>
                        </div>
                    </div>
                    <div class="noti-right-body">
                        <div class="noti-headline-row">
                            <h2 class="noti-title">Health and Wellness Program</h2>
                            <span class="noti-time">3 days ago</span>
                        </div>
                        <p class="noti-description">Free health check-up and consultation available at the barangay health center from May 25-27.</p>
                        <div class="badge-row">
                            <span class="noti-pill pill-orange">ADVISORY</span>
                        </div>
                    </div>
                </div>

            </div>
        </main>
    </div>

    <?php
    $footerBase = '../public/';
    $footerAssetBase = '../assets';
    include __DIR__ . '/../includes/footer.php';
    ?>
</body>

</html>
