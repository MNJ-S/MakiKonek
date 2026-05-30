<?php
session_start();
require_once __DIR__ . '/includes/db_connect.php';

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

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $login_input = mysqli_real_escape_string($conn, $_POST['email']);
    $password = $_POST['password'];

    $selected_role = $_POST['role'] ?? 'Residente';

    // --- ROUTE 1: PURE ADMIN LOGIN ---
    if ($selected_role === 'Admin') {
        $query = "SELECT * FROM admin_accounts WHERE email = ? OR username = ? LIMIT 1";
        $stmt = mysqli_prepare($conn, $query);
        mysqli_stmt_bind_param($stmt, "ss", $login_input, $login_input);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);

        if ($row = mysqli_fetch_assoc($result)) {
            if (password_verify($password, $row['password_hash'])) {
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
    }
    // --- ROUTE 2: RESIDENTE, OPISYAL, & SK LOGIN ---
    else {
        // Check the users table and specifically match the role they selected
        $query = "SELECT * FROM users WHERE (email = ? OR username = ?) AND role = ? LIMIT 1";
        $stmt = mysqli_prepare($conn, $query);
        mysqli_stmt_bind_param($stmt, "sss", $login_input, $login_input, $selected_role);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);

        if ($row = mysqli_fetch_assoc($result)) {
            if (password_verify($password, $row['password_hash'])) {

                // Set the session variables
                $_SESSION['resident_id'] = $row['user_id'];
                $_SESSION['resident_username'] = $row['username'];
                $_SESSION['resident_role'] = $row['role'];

                // You can route them to different dashboards later based on this role!
                if ($row['role'] === 'SK' || $row['role'] === 'Opisyal') {
                    // For now, they go to the resident dashboard, but you can change this URL later
                    header("Location: resident/dashboard.php");
                } else {
                    header("Location: resident/dashboard.php");
                }
                exit();
            } else {
                $error_message = "Incorrect password for {$selected_role} account.";
            }
        } else {
            $error_message = "No {$selected_role} account found with those credentials. Please check your role selection.";
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
    <link rel="stylesheet" href="assets/css/login.css?v=20260529m">
    <script defer src="assets/js/public.js?v=20260529c"></script>
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
                        <?php echo $error_message; ?>
                    </div>
                <?php endif; ?>

                <fieldset class="role-selector">
                    <legend>Account role</legend>
                    <label>
                        <input type="radio" name="role" value="Residente" checked>
                        <span><svg class="role-icon" viewBox="0 0 24 24" aria-hidden="true">
                                <path d="M16 21v-2a4 4 0 0 0-4-4H7a4 4 0 0 0-4 4v2"></path>
                                <circle cx="9.5" cy="7" r="4"></circle>
                                <path d="M19 8v6"></path>
                                <path d="M22 11h-6"></path>
                            </svg>Residente</span>
                    </label>
                    <label>
                        <input type="radio" name="role" value="Opisyal">
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
                        <input type="radio" name="role" value="SK">
                        <span><svg class="role-icon" viewBox="0 0 24 24" aria-hidden="true">
                                <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                                <circle cx="9" cy="7" r="4"></circle>
                                <path d="M23 21v-2a4 4 0 0 0-3-3.87"></path>
                                <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
                            </svg>SK</span>
                    </label>
                    <label>
                        <input type="radio" name="role" value="Admin">
                        <span><svg class="role-icon" viewBox="0 0 24 24" aria-hidden="true">
                                <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10"></path>
                                <path d="m9 12 2 2 4-4"></path>
                            </svg>Admin</span>
                    </label>
                </fieldset>

                <label class="field-label" for="email">Email Address</label>
                <input id="email" name="email" type="email" placeholder="Ilagay ang iyong email" autocomplete="email" required>

                <label class="field-label" for="password">Password</label>
                <input id="password" name="password" type="password" placeholder="Ilagay ang iyong password" autocomplete="current-password" required>

                <div class="form-row">
                    <label class="check-label"><input type="checkbox" name="remember"> Tandaan ako</label>
                    <a href="#">Nakalimutan ang password?</a>
                </div>

                <button class="btn btn-primary auth-submit" type="submit">Mag-login</button>

                <p class="auth-switch">Wala pang account? <a href="signup.php" data-auth-transition>Magrehistro dito.</a></p>
                <a class="btn btn-outline auth-back" href="public/index.php">← Bumalik sa Homepage</a>
            </form>
        </section>
    </main>
</body>

</html>