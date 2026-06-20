<?php
session_start();
require_once __DIR__ . '/includes/db_connect.php';
require_once __DIR__ . '/includes/auth.php';

// If they are already logged in, push them to their respective dashboards
if (isset($_SESSION['resident_id'])) {
    header("Location: resident/dashboard.php");
    exit();
}
if (isset($_SESSION['admin_id'])) {
    header("Location: admin/dashboard.php");
    exit();
}

$error_message = '';
$selected_role = 'Residente';

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $login_input = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $selected_role = $_POST['role'] ?? 'Residente';
    $allowed_roles = ['Residente', 'Opisyal', 'SK', 'Admin'];

    if ($login_input === '' || $password === '') {
        $error_message = 'Please enter your username or email and password.';
    } elseif (!in_array($selected_role, $allowed_roles, true)) {
        $error_message = 'Please choose a valid account role.';
    } elseif ($selected_role === 'Admin') {
        $query = "SELECT * FROM admin_accounts WHERE email = ? OR username = ? LIMIT 1";
        $stmt = mysqli_prepare($conn, $query);
        mysqli_stmt_bind_param($stmt, "ss", $login_input, $login_input);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);

        if ($row = mysqli_fetch_assoc($result)) {
            if (makikonekVerifyAccountPassword(
                $conn,
                'admin_accounts',
                'admin_id',
                (int)$row['admin_id'],
                $password,
                (string)$row['password']
            )) {
                session_regenerate_id(true);
                $_SESSION['admin_id'] = $row['admin_id'];
                $_SESSION['admin_username'] = $row['username'];
                $_SESSION['admin_role'] = $row['role'];
                header("Location: admin/dashboard.php");
                exit();
            } else {
                $error_message = "Incorrect password for Admin account.";
            }
        } else {
            $error_message = "No Admin account found with those credentials.";
        }
    } else {
        $query = "SELECT u.*, p.first_name
                  FROM users u
                  LEFT JOIN user_profiles p ON p.user_id = u.user_id
                  WHERE (u.email = ? OR u.username = ?) AND u.role = ?
                  LIMIT 1";
        $stmt = mysqli_prepare($conn, $query);
        mysqli_stmt_bind_param($stmt, "sss", $login_input, $login_input, $selected_role);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);

        if ($row = mysqli_fetch_assoc($result)) {
            if (makikonekVerifyAccountPassword(
                $conn,
                'users',
                'user_id',
                (int)$row['user_id'],
                $password,
                (string)$row['password']
            )) {
                session_regenerate_id(true);
                $_SESSION['resident_id'] = $row['user_id'];
                $_SESSION['resident_username'] = $row['username'];
                $_SESSION['resident_first_name'] = trim((string)($row['first_name'] ?? ''));
                $_SESSION['resident_role'] = $row['role'];
                header("Location: resident/dashboard.php");
                exit();
            } else {
                $error_message = "Incorrect password for {$selected_role} account.";
            }
        } else {
            $error_message = "No {$selected_role} account found.";
        }
    }
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login | MakiKonek</title>
    <link rel="stylesheet" href="assets/css/login.css?v=20260620o">
    <link rel="icon" href="assets/img/Barangay_Makiling_Seal.png" type="image/png">
    <script defer src="assets/js/public.js?v=20260529c"></script>
    <script defer src="assets/js/password-visibility.js?v=20260620a"></script>
</head>

<body class="auth-page">
    <main class="auth-shell">
        <!-- Community intro -->
        <section class="auth-intro" aria-label="MakiKonek introduction">
            <div class="welcome-ribbon" aria-label="Welcome message">
                <img src="assets/img/green-eco-banner.png" alt="" aria-hidden="true">
            </div>

            <img class="intro-logo" src="assets/img/logo2-makikonek.png" alt="MakiKonek logo">
            <div class="intro-copy">
                <p>Mag-login para ma-access ang mga serbisyo, anunsyo, at impormasyon ng Barangay Makiling.</p>
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

        <!-- Login form -->
        <section class="auth-card" aria-labelledby="login-title">
            <form class="auth-form" action="login_reg.php" method="POST">
                <h1 id="login-title">Mag-login</h1>
                <p class="form-subtitle">Piliin ang iyong account type</p>

                <!-- ERROR MESS -->
                <?php if ($error_message): ?>
                    <div style="background-color: #fee2e2; border: 1px solid #ef4444; color: #b91c1c; padding: 10px; border-radius: 6px; margin-bottom: 15px; font-size: 14px; text-align: center;">
                        <?php echo htmlspecialchars($error_message, ENT_QUOTES, 'UTF-8'); ?>
                    </div>
                <?php endif; ?>

                <fieldset class="role-selector">
                    <legend>Account role</legend>
                    <label>
                        <input type="radio" name="role" value="Residente" <?php echo $selected_role === 'Residente' ? 'checked' : ''; ?>>
                        <span><svg class="role-icon" viewBox="0 0 24 24" aria-hidden="true">
                                <path d="M16 21v-2a4 4 0 0 0-4-4H7a4 4 0 0 0-4 4v2"></path>
                                <circle cx="9.5" cy="7" r="4"></circle>
                                <path d="M19 8v6"></path>
                                <path d="M22 11h-6"></path>
                            </svg>Residente</span>
                    </label>
                    <label>
                        <input type="radio" name="role" value="Opisyal" <?php echo $selected_role === 'Opisyal' ? 'checked' : ''; ?>>
                        <span><svg class="role-icon" viewBox="0 0 24 24" aria-hidden="true">
                                <path d="M3 21h18"></path>
                                <path d="M5 21V9l7-4 7 4v12"></path>
                                <path d="M9 21v-7"></path>
                                <path d="M15 21v-7"></path>
                                <path d="M9 9h6"></path>
                                <path d="M12 5V3"></path>
                            </svg>Opisyal</span>
                    </label>
                    <label>
                        <input type="radio" name="role" value="SK" <?php echo $selected_role === 'SK' ? 'checked' : ''; ?>>
                        <span><svg class="role-icon" viewBox="0 0 24 24" aria-hidden="true">
                                <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                                <circle cx="9" cy="7" r="4"></circle>
                                <path d="M23 21v-2a4 4 0 0 0-3-3.87"></path>
                                <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
                            </svg>SK</span>
                    </label>
                    <label>
                        <input type="radio" name="role" value="Admin" <?php echo $selected_role === 'Admin' ? 'checked' : ''; ?>>
                        <span><svg class="role-icon" viewBox="0 0 24 24" aria-hidden="true">
                                <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10"></path>
                                <path d="m9 12 2 2 4-4"></path>
                            </svg>Admin</span>
                    </label>
                </fieldset>

                <label class="field-label" for="email">Username or Email Address</label>
                <input id="email" name="email" type="text" placeholder="Ilagay ang username o email" autocomplete="username" value="<?php echo htmlspecialchars($_POST['email'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" required>

                <label class="field-label" for="password">Password</label>
                <div class="password-field">
                    <input id="password" name="password" type="password" placeholder="Ilagay ang iyong password" autocomplete="current-password" required>
                    <button class="password-toggle" type="button" data-password-toggle="password" aria-label="Ipakita ang password" aria-pressed="false">
                        <svg class="eye-open" viewBox="0 0 24 24" aria-hidden="true">
                            <path d="M2 12s3.5-6 10-6 10 6 10 6-3.5 6-10 6S2 12 2 12z"></path>
                            <circle cx="12" cy="12" r="3"></circle>
                        </svg>
                        <svg class="eye-closed" viewBox="0 0 24 24" aria-hidden="true">
                            <path d="m3 3 18 18"></path>
                            <path d="M10.6 10.7a2 2 0 0 0 2.7 2.7"></path>
                            <path d="M9.9 4.2A11.8 11.8 0 0 1 12 4c6.5 0 10 8 10 8a18.5 18.5 0 0 1-2.1 3.2"></path>
                            <path d="M6.6 6.6C3.6 8.6 2 12 2 12s3.5 8 10 8a9.8 9.8 0 0 0 4.1-.9"></path>
                        </svg>
                    </button>
                </div>

                <div class="form-row">
                    <label class="check-label"><input type="checkbox" name="remember"> Tandaan ako</label>
                    <a href="forgot_password.php" data-auth-transition>Nakalimutan ang password?</a>
                </div>

                <button class="btn btn-primary auth-submit" type="submit">Mag-login</button>

                <p class="auth-switch">Wala pang account? <a href="signup.php" data-auth-transition>Magrehistro dito.</a></p>
                <a class="btn btn-outline auth-back" href="public/index.php">← Bumalik sa Homepage</a>
            </form>
        </section>
    </main>
</body>

</html>
