<?php
session_start();

if (!isset($_SESSION['resident_id'])) {
    header("Location: ../login_reg.php");
    exit();
}

require_once __DIR__ . '/../includes/db_connect.php';
require_once __DIR__ . '/../includes/prg_flash.php';
require_once __DIR__ . '/../includes/input_validation.php';
require_once __DIR__ . '/../includes/auth.php';

$pageTitle = 'Settings';
$activePage = 'settings';
$resident_id = (int)$_SESSION['resident_id'];
$success_message = prgFlashPull('resident_settings');
$error_message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['settings_action'] ?? '';

    if ($action === 'account') {
        $email = trim($_POST['email'] ?? '');
        $mobile_number = trim($_POST['mobile_number'] ?? '');
        if ($email === '' || $mobile_number === '') {
            $error_message = 'Email address and phone number are required.';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL) || !inputLength($email, 254)) {
            $error_message = 'Please enter a valid email address.';
        } elseif (!inputIsPhone($mobile_number)) {
            $error_message = 'Phone number must use a valid Philippine mobile format.';
        } else {
            $check = mysqli_prepare($conn, 'SELECT user_id FROM users WHERE email = ? AND user_id <> ? LIMIT 1');
            mysqli_stmt_bind_param($check, 'si', $email, $resident_id);
            mysqli_stmt_execute($check);
            if (mysqli_num_rows(mysqli_stmt_get_result($check)) > 0) {
                $error_message = 'That email address is already in use.';
            } else {
                mysqli_begin_transaction($conn);
                try {
                    $user_stmt = mysqli_prepare($conn, 'UPDATE users SET email = ? WHERE user_id = ?');
                    mysqli_stmt_bind_param($user_stmt, 'si', $email, $resident_id);
                    mysqli_stmt_execute($user_stmt);
                    $profile_stmt = mysqli_prepare($conn, 'UPDATE user_profiles SET mobile_number = ? WHERE user_id = ?');
                    mysqli_stmt_bind_param($profile_stmt, 'si', $mobile_number, $resident_id);
                    mysqli_stmt_execute($profile_stmt);
                    mysqli_commit($conn);
                    prgRedirect('settings.php', 'resident_settings', 'Account settings updated successfully.');
                } catch (Throwable $e) {
                    mysqli_rollback($conn);
                    $error_message = 'Account settings could not be updated.';
                }
            }
        }
    } elseif ($action === 'password') {
        $current_password = $_POST['current_password'] ?? '';
        $new_password = $_POST['new_password'] ?? '';
        $confirm_password = $_POST['confirm_password'] ?? '';
        if ($current_password === '' || $new_password === '' || $confirm_password === '') {
            $error_message = 'Please complete all password fields.';
        } elseif (strlen($new_password) < 8 || strlen($new_password) > 72) {
            $error_message = 'New password must be between 8 and 72 characters.';
        } elseif ($new_password !== $confirm_password) {
            $error_message = 'New passwords do not match.';
        } else {
            $password_stmt = mysqli_prepare($conn, 'SELECT password FROM users WHERE user_id = ? LIMIT 1');
            mysqli_stmt_bind_param($password_stmt, 'i', $resident_id);
            mysqli_stmt_execute($password_stmt);
            $account = mysqli_fetch_assoc(mysqli_stmt_get_result($password_stmt));
            if (!$account || !makikonekVerifyAccountPassword(
                $conn,
                'users',
                'user_id',
                $resident_id,
                $current_password,
                (string)$account['password']
            )) {
                $error_message = 'Current password is incorrect.';
            } else {
                $stored_password = makikonekStorePassword($new_password);
                $update_stmt = mysqli_prepare($conn, 'UPDATE users SET password = ? WHERE user_id = ?');
                mysqli_stmt_bind_param($update_stmt, 'si', $stored_password, $resident_id);
                if (mysqli_stmt_execute($update_stmt)) {
                    prgRedirect('settings.php', 'resident_settings', 'Password updated successfully.');
                }
                $error_message = 'Password could not be updated.';
            }
        }
    } else {
        $error_message = 'Invalid settings request.';
    }
}

$account_stmt = mysqli_prepare($conn, 'SELECT u.email, p.mobile_number FROM users u LEFT JOIN user_profiles p ON p.user_id = u.user_id WHERE u.user_id = ? LIMIT 1');
mysqli_stmt_bind_param($account_stmt, 'i', $resident_id);
mysqli_stmt_execute($account_stmt);
$account_data = mysqli_fetch_assoc(mysqli_stmt_get_result($account_stmt)) ?: [];

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
        <?php include __DIR__ . '/partials/resident_sidebar.php'; ?>

        <main class="resident-main">
            <header class="page-heading">
                <h1>Settings</h1>
            </header>

            <?php if ($success_message): ?><div class="alert alert-success"><?php echo htmlspecialchars($success_message); ?></div><?php endif; ?>
            <?php if ($error_message): ?><div class="alert alert-danger"><?php echo htmlspecialchars($error_message); ?></div><?php endif; ?>

            <div class="settings-stack">
                <section class="settings-card">
                    <h2>Account Settings</h2>
                    <form action="settings.php" method="POST">
                        <input type="hidden" name="settings_action" value="account">
                        <div class="field full">
                            <label for="settings_email">Email Address</label>
                            <input id="settings_email" name="email" type="email" maxlength="254" value="<?php echo htmlspecialchars($account_data['email'] ?? ''); ?>" required>
                        </div>
                        <div class="field full">
                            <label for="settings_phone">Phone Number</label>
                            <input id="settings_phone" name="mobile_number" type="tel" inputmode="numeric" maxlength="11" pattern="09[0-9]{9}" data-input="phone" placeholder="09171234567" value="<?php echo htmlspecialchars($account_data['mobile_number'] ?? ''); ?>" required>
                        </div>
                        <button type="submit" class="settings-action-btn">Save Changes</button>
                    </form>
                </section>

                <section class="settings-card">
                    <h2>Password</h2>
                    <form action="settings.php" method="POST">
                        <input type="hidden" name="settings_action" value="password">
                        <div class="field full">
                            <label for="current_password">Current Password</label>
                            <input id="current_password" name="current_password" type="password" maxlength="72" autocomplete="current-password" required>
                        </div>
                        <div class="field full">
                            <label for="new_password">New Password</label>
                            <input id="new_password" name="new_password" type="password" minlength="8" maxlength="72" autocomplete="new-password" required>
                        </div>
                        <div class="field full">
                            <label for="confirm_password">Confirm New Password</label>
                            <input id="confirm_password" name="confirm_password" type="password" minlength="8" maxlength="72" autocomplete="new-password" required>
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
    <script src="../assets/js/input-validation.js?v=20260620a"></script>
</body>

</html>
