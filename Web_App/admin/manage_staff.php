<?php
session_start();

// 1. THE STRICT GATEKEEPER: Must be logged in AND must be a Super Admin
if (!isset($_SESSION['admin_id']) || $_SESSION['admin_role'] !== 'Super Admin') {
    // If a standard Barangay Staff tries to access this, kick them back to their dashboard
    header("Location: dashboard.php");
    exit();
}

require_once __DIR__ . '/../includes/db_connect.php';

$success_message = '';
$error_message = '';

// --- HANDLE ADDING NEW STAFF ---
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add_staff'])) {
    $new_username = mysqli_real_escape_string($conn, $_POST['username']);
    $new_email = mysqli_real_escape_string($conn, $_POST['email']);
    $new_password = $_POST['password'];
    $new_role = mysqli_real_escape_string($conn, $_POST['role']);

    // Hash the password
    $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);

    $insert_query = "INSERT INTO admin_accounts (username, email, password_hash, role) VALUES (?, ?, ?, ?)";
    $stmt = mysqli_prepare($conn, $insert_query);
    mysqli_stmt_bind_param($stmt, "ssss", $new_username, $new_email, $hashed_password, $new_role);

    if (mysqli_stmt_execute($stmt)) {
        $success_message = "New {$new_role} account created successfully!";
    } else {
        $error_message = "Failed to create account. Username or email might already exist.";
    }
}

// --- HANDLE DELETING STAFF ---
if (isset($_GET['delete_id'])) {
    $delete_id = (int)$_GET['delete_id'];
    
    // Safety Check: Prevent the Super Admin from deleting themselves!
    if ($delete_id === $_SESSION['admin_id']) {
        $error_message = "Security Check: You cannot delete your own active account!";
    } else {
        $delete_query = "DELETE FROM admin_accounts WHERE admin_id = ?";
        $stmt = mysqli_prepare($conn, $delete_query);
        mysqli_stmt_bind_param($stmt, "i", $delete_id);
        
        if (mysqli_stmt_execute($stmt)) {
            $success_message = "Staff account removed permanently.";
        } else {
            $error_message = "Failed to delete account.";
        }
    }
}

// --- FETCH ALL STAFF FOR THE TABLE ---
$fetch_query = "SELECT admin_id, username, email, role, created_at FROM admin_accounts ORDER BY role DESC, username ASC";
$result = mysqli_query($conn, $fetch_query);
?>

<!DOCTYPE html>
<html lang="en" data-bs-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Staff | MakiKonek</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <style>
        body { background-color: #121212; }
        .main-content { min-height: 100vh; padding: 2rem; }
        .custom-card { background-color: #1e1e1e; border: 1px solid #333; border-radius: 8px; }
    </style>
</head>
<body>

<div class="container-fluid">
    <div class="row">
        <main class="main-content col-md-10 mx-auto">
            
            <div class="d-flex justify-content-between align-items-center mb-4 pb-2 border-bottom border-secondary">
                <h2 class="fw-bold text-white"><i class="bi bi-shield-lock text-primary me-2"></i> Manage Internal Staff</h2>
                <a href="dashboard.php" class="btn btn-outline-light"><i class="bi bi-arrow-left me-1"></i> Back to Dashboard</a>
            </div>

            <?php if ($success_message): ?>
                <div class="alert alert-success alert-dismissible fade show shadow-sm" role="alert">
                    <i class="bi bi-check-circle me-2"></i> <?php echo $success_message; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>
            
            <?php if ($error_message): ?>
                <div class="alert alert-danger alert-dismissible fade show shadow-sm" role="alert">
                    <i class="bi bi-exclamation-triangle me-2"></i> <?php echo $error_message; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <div class="row g-4">
                <div class="col-lg-4">
                    <div class="custom-card p-4 shadow-sm h-100">
                        <h4 class="mb-4 fw-bold border-bottom border-secondary pb-2">Add New Account</h4>
                        <form action="manage_staff.php" method="POST">
                            <div class="mb-3">
                                <label class="form-label text-muted">Username</label>
                                <input type="text" name="username" class="form-control bg-dark border-secondary text-white" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label text-muted">Email Address</label>
                                <input type="email" name="email" class="form-control bg-dark border-secondary text-white" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label text-muted">Temporary Password</label>
                                <input type="password" name="password" class="form-control bg-dark border-secondary text-white" required>
                            </div>
                            <div class="mb-4">
                                <label class="form-label text-muted">Assign Role</label>
                                <select name="role" class="form-select bg-dark border-secondary text-white">
                                    <option value="Barangay Staff">Barangay Staff (Restricted)</option>
                                    <option value="Super Admin">Super Admin (Full Access)</option>
                                </select>
                            </div>
                            <button type="submit" name="add_staff" class="btn btn-primary w-100 fw-bold">
                                <i class="bi bi-person-plus me-2"></i> Create Account
                            </button>
                        </form>
                    </div>
                </div>

                <div class="col-lg-8">
                    <div class="custom-card p-4 shadow-sm h-100">
                        <h4 class="mb-4 fw-bold border-bottom border-secondary pb-2">Active Personnel</h4>
                        <div class="table-responsive">
                            <table class="table table-dark table-hover align-middle border-secondary">
                                <thead class="table-active">
                                    <tr>
                                        <th>Username</th>
                                        <th>Email</th>
                                        <th>Role</th>
                                        <th>Date Added</th>
                                        <th class="text-center">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while ($row = mysqli_fetch_assoc($result)): ?>
                                        <tr>
                                            <td class="fw-bold"><?php echo htmlspecialchars($row['username']); ?></td>
                                            <td class="text-muted"><?php echo htmlspecialchars($row['email']); ?></td>
                                            <td>
                                                <?php if ($row['role'] === 'Super Admin'): ?>
                                                    <span class="badge bg-primary">Super Admin</span>
                                                <?php else: ?>
                                                    <span class="badge bg-secondary">Staff</span>
                                                <?php endif; ?>
                                            </td>
                                            <td class="text-muted small">
                                                <?php echo date("M d, Y", strtotime($row['created_at'])); ?>
                                            </td>
                                            <td class="text-center">
                                                <?php if ($row['admin_id'] === $_SESSION['admin_id']): ?>
                                                    <button class="btn btn-sm btn-outline-secondary disabled" title="You cannot delete yourself">
                                                        <i class="bi bi-trash"></i>
                                                    </button>
                                                <?php else: ?>
                                                    <a href="manage_staff.php?delete_id=<?php echo $row['admin_id']; ?>" 
                                                       class="btn btn-sm btn-outline-danger"
                                                       onclick="return confirm('Are you absolutely sure you want to permanently delete <?php echo $row['username']; ?>?');">
                                                        <i class="bi bi-trash"></i>
                                                    </a>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

        </main>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>