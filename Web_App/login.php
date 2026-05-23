<?php
session_start();
require_once __DIR__ . '/includes/db_connect.php';

// If the resident is already logged in, push them straight to their dashboard
if (isset($_SESSION['resident_id'])) {
    header("Location: resident/dashboard.php");
    exit();
}

$error_message = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Grab the input (allowing either username or email)
    $login_input = mysqli_real_escape_string($conn, $_POST['username_or_email']);
    $password = $_POST['password'];

    // Search the users table for a match
    $query = "SELECT * FROM users WHERE username = ? OR email = ? LIMIT 1";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "ss", $login_input, $login_input);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    if ($row = mysqli_fetch_assoc($result)) {
        // Verify the typed password against the hashed database password
        if (password_verify($password, $row['password_hash'])) {
            
            // Success! Create the secure session variables for the Resident
            $_SESSION['resident_id'] = $row['user_id'];
            $_SESSION['resident_username'] = $row['username'];
            
            // Redirect them to the resident dashboard
            header("Location: resident/dashboard.php");
            exit();
        } else {
            $error_message = "Incorrect password. Please try again.";
        }
    } else {
        $error_message = "No account found with that username or email.";
    }
}
?>

<!DOCTYPE html>
<html lang="en" data-bs-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Resident Login | MakiKonek</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { 
            background-color: #121212; 
            display: flex; 
            align-items: center; 
            justify-content: center; 
            height: 100vh; 
        }
        .login-card { 
            background-color: #1e1e1e; 
            border: 1px solid #333; 
            width: 100%; 
            max-width: 420px; 
            padding: 2.5rem; 
            border-radius: 12px; 
        }
    </style>
</head>
<body>

<div class="container d-flex justify-content-center">
    <div class="login-card shadow-lg">
        <div class="text-center mb-4">
            <h2 class="fw-bold text-primary">MakiKonek</h2>
            <p class="text-muted">Resident Digital Portal</p>
        </div>

        <?php if($error_message): ?>
            <div class="alert alert-danger py-2 text-center shadow-sm">
                <?php echo $error_message; ?>
            </div>
        <?php endif; ?>

        <form action="login.php" method="POST">
            <div class="mb-3">
                <label class="form-label text-light">Username or Email</label>
                <input type="text" name="username_or_email" class="form-control bg-dark text-light border-secondary" required autofocus>
            </div>
            
            <div class="mb-4">
                <div class="d-flex justify-content-between">
                    <label class="form-label text-light">Password</label>
                    <a href="#" class="text-decoration-none small text-primary">Forgot?</a>
                </div>
                <input type="password" name="password" class="form-control bg-dark text-light border-secondary" required>
            </div>
            
            <button type="submit" class="btn btn-primary w-100 fw-bold py-2 mb-3">Sign In</button>
        </form>
        
        <div class="text-center border-top border-secondary pt-3 mt-3">
            <p class="text-muted small mb-0">Don't have an account?</p>
            <a href="register.php" class="text-decoration-none fw-bold text-primary">Register Here</a>
        </div>
        
        <div class="text-center mt-3">
            <a href="index.php" class="text-decoration-none text-muted small">&larr; Back to Homepage</a>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>