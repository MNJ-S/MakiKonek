<?php
session_start();
require_once '../includes/db_connect.php';

// If they are already logged in, push them straight to the dashboard
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
            
            // Create the secure session variables!
            $_SESSION['admin_id'] = $row['admin_id'];
            $_SESSION['admin_username'] = $row['username'];
            $_SESSION['admin_role'] = $row['role']; // This powers the RBAC on your dashboard
            
            // Send them through the gate
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
<html lang="en" data-bs-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login | MakiKonek</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background-color: #121212; display: flex; align-items: center; justify-content: center; height: 100vh; }
        .login-card { background-color: #1e1e1e; border: 1px solid #333; width: 100%; max-width: 400px; padding: 2rem; border-radius: 10px; }
    </style>
</head>
<body>

<div class="login-card shadow-lg">
    <div class="text-center mb-4">
        <h3 class="fw-bold text-primary">MakiKonek Admin</h3>
        <p class="text-muted small">Authorized Personnel Only</p>
    </div>

    <?php if($error_message): ?>
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
        <button type="submit" class="btn btn-primary w-100 fw-bold">Login to Dashboard</button>
    </form>
    
    <div class="text-center mt-3">
        <a href="../index.php" class="text-decoration-none text-muted small">&larr; Back to Public Site</a>
    </div>
</div>

</body>
</html>