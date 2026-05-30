<?php
session_start();
require_once __DIR__ . '/../includes/db_connect.php';

if (isset($_SESSION['admin_id'])) {
    header("Location: dashboard.php");
    exit();
}

$error_message = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $login_input = mysqli_real_escape_string($conn, $_POST['username_or_email']);
    $password = $_POST['password'];

    // Search the admin table by EITHER username OR email
    $query = "SELECT * FROM admin_accounts WHERE username = ? OR email = ? LIMIT 1";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "ss", $login_input, $login_input);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    if ($row = mysqli_fetch_assoc($result)) {
        // Verify the typed password against the hashed database password
        if (password_verify($password, $row['password_hash'])) {

            $_SESSION['admin_id'] = $row['admin_id'];
            $_SESSION['admin_username'] = $row['username'];
            $_SESSION['admin_role'] = $row['role']; // RBAC Dashboard

            header("Location: dashboard.php");
            exit();
        } else {
            $error_message = "Incorrect password.";
        }
    } else {
        $error_message = "Admin account not found.";
    }
}
?>

<!DOCTYPE html>
<html lang="en" data-bs-theme="light">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login | MakiKonek</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(180deg, #f6fff7 0%, #e9f8ff 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
        }

        .login-card {
            background-color: rgba(255, 255, 255, 0.96);
            border: 1px solid rgba(63, 159, 37, 0.22);
            width: 100%;
            max-width: 420px;
            padding: 2rem;
            border-radius: 16px;
            box-shadow: 0 24px 70px rgba(63, 159, 37, 0.12);
        }

        .brand-title {
            color: #0b6d36;
        }

        .btn-login {
            background-color: #3f9f25;
            border-color: #3f9f25;
        }

        .btn-login:hover,
        .btn-login:focus {
            background-color: #0b6d36;
            border-color: #0b6d36;
        }

        .login-card a {
            color: #0b6d36;
        }
    </style>
</head>

<body>

    <div class="login-card shadow-lg">
        <div class="text-center mb-4">
            <h3 class="fw-bold brand-title">MakiKonek Admin</h3>
            <p class="text-muted small">Authorized Personnel Only</p>
        </div>

        <?php if ($error_message): ?>
            <div class="alert alert-danger py-2 text-center"><?php echo $error_message; ?></div>
        <?php endif; ?>

        <form action="login_admin.php" method="POST">
            <div class="mb-3">
                <label class="form-label text-muted">Username or Email</label>
                <input type="text" name="username_or_email" class="form-control" required autofocus>
            </div>
            <div class="mb-4">
                <label class="form-label text-muted">Password</label>
                <input type="password" name="password" class="form-control" required>
            </div>
            <button type="submit" class="btn btn-login text-white w-100 fw-bold">Login to Dashboard</button>
        </form>

        <div class="text-center mt-3">
            <a href="../index.php" class="text-decoration-none text-muted small">&larr; Back to Public Site</a>
        </div>
    </div>

</body>

</html>