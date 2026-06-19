<?php
session_start();
require_once __DIR__ . '/includes/db_connect.php';
require_once __DIR__ . '/includes/prg_flash.php';
require_once __DIR__ . '/includes/input_validation.php';

// If they are already logged in, redirect them to the dashboard
if (isset($_SESSION['resident_id'])) {
    header("Location: resident/dashboard.php");
    exit();
}

$error_message = '';
$success_message = prgFlashPull('signup');

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $surname = trim($_POST['surname'] ?? '');
    $given_name = trim($_POST['given_name'] ?? '');
    $middle_name = trim($_POST['middle_name'] ?? '');
    $suffix = trim($_POST['suffix'] ?? '');
    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    if ($surname === '' || $given_name === '' || $username === '' || $email === '' || $password === '' || $confirm_password === '') {
        $error_message = 'Please complete all required fields.';
    } elseif (!inputIsName($surname) || !inputIsName($given_name) || !inputIsName($middle_name, true) || !inputIsName($suffix, true)) {
        $error_message = 'Names may contain letters, spaces, hyphens, and periods only.';
    } elseif (!preg_match('/^[A-Za-z0-9._-]{4,30}$/', $username)) {
        $error_message = 'Username must be 4-30 characters using letters, numbers, periods, underscores, or hyphens.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL) || !inputLength($email, 254)) {
        $error_message = 'Please enter a valid email address.';
    } elseif (strlen($password) < 8 || strlen($password) > 72) {
        $error_message = 'Password must be between 8 and 72 characters.';
    } elseif ($password !== $confirm_password) {
        $error_message = "Passwords do not match. Please try again.";
    } else {
        // Check if the username or email is already taken
        $check_query = "SELECT user_id FROM users WHERE email = ? OR username = ?";
        $stmt = mysqli_prepare($conn, $check_query);
        mysqli_stmt_bind_param($stmt, "ss", $email, $username);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_store_result($stmt);

        if (mysqli_stmt_num_rows($stmt) > 0) {
            $error_message = "That username or email is already registered.";
        } else {
            // 4. Begin the Database Transaction
            mysqli_begin_transaction($conn);

            try {
                $insert_user = "INSERT INTO users (username, email, password, role) VALUES (?, ?, ?, 'Residente')";
                $stmt_user = mysqli_prepare($conn, $insert_user);
                mysqli_stmt_bind_param($stmt_user, "sss", $username, $email, $password);
                mysqli_stmt_execute($stmt_user);

                $new_user_id = mysqli_insert_id($conn);

                $insert_profile = "INSERT INTO user_profiles (user_id, first_name, last_name, middle_name, suffix) VALUES (?, ?, ?, ?, ?)";
                $stmt_profile = mysqli_prepare($conn, $insert_profile);
                mysqli_stmt_bind_param($stmt_profile, "issss", $new_user_id, $given_name, $surname, $middle_name, $suffix);
                mysqli_stmt_execute($stmt_profile);

                createAdminNotification(
                    $conn,
                    'New Resident Account',
                    trim($given_name . ' ' . $surname) . ' created a resident account.',
                    'Account',
                    'bi-person-plus',
                    'manage_residents.php'
                );

                mysqli_commit($conn);
                prgRedirect('signup.php', 'signup', 'Account created successfully! You can now log in.');
            } catch (Exception $e) {
                mysqli_rollback($conn);
                $error_message = "Registration failed due to a system error. Please try again.";
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign Up | MakiKonek</title>
    <link rel="stylesheet" href="assets/css/signup.css?v=20260529j">
    <link rel="icon" href="../assets/img/Barangay_Makiling_Seal.png" type="image/png">
    <script defer src="assets/js/public.js?v=20260529c"></script>
</head>

<body class="auth-page">
    <main class="auth-shell signup-shell">
        <section class="auth-intro" aria-label="MakiKonek introduction">
            <div class="welcome-ribbon" aria-label="Welcome message">
                <img src="assets/img/green-eco-banner.png" alt="" aria-hidden="true">
            </div>

            <img class="intro-logo" src="assets/img/logo2-makikonek.png" alt="MakiKonek logo">
            <div class="intro-copy">
                <p>Gumawa ng account para makagamit ng online services at updates ng Barangay Makiling.</p>
            </div>

            <div class="assistance-card">
                <div class="assistance-logos">
                    <img src="assets/img/Barangay_Makiling_Seal.png" alt="Barangay Makiling seal">
                    <img src="assets/img/Barangay_Makiling_SK.jpg" alt="Sangguniang Kabataan Makiling logo">
                </div>
                <div class="assistance-info">
                    <strong>Need Assistance?</strong>
                    <span>☎ (049) 123-4567</span>
                    <span>✉ makiling.barangay@gmail.com</span>
                    <span>◷ Monday - Friday, 8:00 AM - 5:00 PM</span>
                </div>
            </div>
        </section>

        <section class="auth-card signup-card" aria-labelledby="signup-title">
            <form class="auth-form" action="signup.php" method="POST">
                <h1 id="signup-title">Magrehistro</h1>
                <p class="form-subtitle">Ilagay ang iyong impormasyon</p>

                <!-- Dynamic Alert Messages -->
                <?php if ($error_message): ?>
                    <div style="background-color: #fee2e2; border: 1px solid #ef4444; color: #b91c1c; padding: 10px; border-radius: 6px; margin-bottom: 15px; font-size: 14px; text-align: center;">
                        <?php echo htmlspecialchars($error_message); ?>
                    </div>
                <?php endif; ?>
                <?php if ($success_message): ?>
                    <div style="background-color: #dcfce7; border: 1px solid #22c55e; color: #15803d; padding: 10px; border-radius: 6px; margin-bottom: 15px; font-size: 14px; text-align: center;">
                        <?php echo $success_message; ?>
                    </div>
                <?php endif; ?>

                <div class="form-grid">
                    <div>
                        <label class="field-label" for="surname">Surname</label>
                        <input id="surname" name="surname" type="text" placeholder="Dela Cruz" autocomplete="family-name" maxlength="60" pattern="[A-Za-zÀ-ÖØ-öø-ÿÑñ .-]+" data-input="name" title="Use letters, spaces, hyphens, and periods only." required>
                    </div>
                    <div>
                        <label class="field-label" for="given_name">Given Name</label>
                        <input id="given_name" name="given_name" type="text" placeholder="Juan" autocomplete="given-name" maxlength="60" pattern="[A-Za-zÀ-ÖØ-öø-ÿÑñ .-]+" data-input="name" title="Use letters, spaces, hyphens, and periods only." required>
                    </div>
                    <div>
                        <label class="field-label" for="middle_name">Middle Name</label>
                        <input id="middle_name" name="middle_name" type="text" placeholder="Santos" maxlength="60" pattern="[A-Za-zÀ-ÖØ-öø-ÿÑñ .-]*" data-input="name" title="Use letters, spaces, hyphens, and periods only.">
                    </div>
                    <div>
                        <label class="field-label" for="suffix">Suffix</label>
                        <input id="suffix" name="suffix" type="text" placeholder="Jr." maxlength="10" pattern="[A-Za-zÀ-ÖØ-öø-ÿÑñ .-]*" data-input="name" title="Use letters, spaces, hyphens, and periods only.">
                    </div>
                </div>

                <label class="field-label" for="username">Username</label>
                <input id="username" name="username" type="text" placeholder="juandelacruz" autocomplete="username" minlength="4" maxlength="30" pattern="[A-Za-z0-9._-]+" required>

                <label class="field-label" for="signup_email">Email</label>
                <input id="signup_email" name="email" type="email" placeholder="juan@example.com" autocomplete="email" maxlength="254" required>

                <label class="field-label" for="new_password">Password</label>
                <input id="new_password" name="password" type="password" placeholder="********" autocomplete="new-password" minlength="8" maxlength="72" required>

                <label class="field-label" for="confirm_password">Confirm Password</label>
                <input id="confirm_password" name="confirm_password" type="password" placeholder="********" autocomplete="new-password" minlength="8" maxlength="72" required>

                <button class="btn btn-primary auth-submit" type="submit">Gumawa ng Account</button>
                <p class="auth-switch">May account na? <a href="login_reg.php" data-auth-transition>Mag-login dito.</a></p>
                <a class="btn btn-outline auth-back" href="public/index.php">← Bumalik sa Homepage</a>
            </form>
        </section>
    </main>
    <script src="assets/js/input-validation.js?v=20260620a"></script>
</body>

</html>
