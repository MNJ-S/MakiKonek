<?php
session_start();

if (!isset($_SESSION['resident_id'])) {
    header("Location: ../login_reg.php");
    exit();
}

$pageTitle = 'Settings';
$activePage = 'settings';

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle; ?> | MakiKonek</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/header.css?v=20260530a">
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
            <header class="page-heading">
                <h1>Settings</h1>
            </header>

            <div class="settings-stack">
                <section class="settings-card">
                    <h2>Account Settings</h2>
                    <form action="" method="POST">
                        <div class="field full">
                            <label>Email Address</label>
                            <input type="email" value="juan.delacruz@email.com">
                        </div>
                        <div class="field full">
                            <label>Phone Number</label>
                            <input type="text" value="+63 912 345 6789">
                        </div>
                        <button type="submit" class="settings-action-btn">Save Changes</button>
                    </form>
                </section>

                <section class="settings-card">
                    <h2>Password</h2>
                    <form action="" method="POST">
                        <div class="field full">
                            <label>Current Password</label>
                            <input type="password">
                        </div>
                        <div class="field full">
                            <label>New Password</label>
                            <input type="password">
                        </div>
                        <div class="field full">
                            <label>Confirm New Password</label>
                            <input type="password">
                        </div>
                        <button type="submit" class="settings-action-btn">Update Password</button>
                    </form>
                </section>

                <section class="settings-card">
                    <h2>Notification Preferences</h2>
                    <div class="preferences-list">
                        <label class="preference-item">
                            <input type="checkbox" checked>
                            <span>Email notifications for document updates</span>
                        </label>
                        <label class="preference-item">
                            <input type="checkbox" checked>
                            <span>SMS notifications for announcements</span>
                        </label>
                        <label class="preference-item">
                            <input type="checkbox" checked>
                            <span>Newsletter and community updates</span>
                        </label>
                    </div>
                </section>
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