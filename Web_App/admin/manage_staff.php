<?php
session_start();

if (!isset($_SESSION['admin_id']) || $_SESSION['admin_role'] !== 'Super Admin') {
    header("Location: dashboard.php");
    exit();
}

require_once __DIR__ . '/../includes/db_connect.php';

$success_message = '';
$error_message = '';

$archive_column_check = mysqli_query($conn, "SHOW COLUMNS FROM admin_accounts LIKE 'archived_at'");
if ($archive_column_check && mysqli_num_rows($archive_column_check) === 0) {
    mysqli_query($conn, "ALTER TABLE admin_accounts ADD COLUMN archived_at DATETIME NULL");
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add_staff'])) {
    $new_username = mysqli_real_escape_string($conn, $_POST['username']);
    $new_email = mysqli_real_escape_string($conn, $_POST['email']);
    $new_password = $_POST['password'];
    $new_role = mysqli_real_escape_string($conn, $_POST['role']);

    $insert_query = "INSERT INTO admin_accounts (username, email, password, role) VALUES (?, ?, ?, ?)";
    $stmt = mysqli_prepare($conn, $insert_query);
    mysqli_stmt_bind_param($stmt, "ssss", $new_username, $new_email, $new_password, $new_role);

    if (mysqli_stmt_execute($stmt)) {
        $success_message = "New {$new_role} account created successfully!";
    } else {
        $error_message = "Failed to create account. Username or email might already exist.";
    }
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_role'])) {
    $update_id = (int) $_POST['admin_id'];
    $updated_role = mysqli_real_escape_string($conn, $_POST['role']);

    $update_query = "UPDATE admin_accounts SET role = ? WHERE admin_id = ?";
    $stmt = mysqli_prepare($conn, $update_query);
    mysqli_stmt_bind_param($stmt, "si", $updated_role, $update_id);

    if (mysqli_stmt_execute($stmt)) {
        $success_message = "Role updated successfully.";
    } else {
        $error_message = "Failed to update role.";
    }
}

// --- ARCHIVE STAFF ---
if (isset($_GET['archive_id'])) {
    $archive_id = (int) $_GET['archive_id'];
    if ($archive_id === $_SESSION['admin_id']) {
        $error_message = "Security Check: You cannot archive your own active account!";
    } else {
        mysqli_begin_transaction($conn);
        try {
            $archive_query = "
                INSERT INTO archived_admin_accounts (original_admin_id, username, email, role)
                SELECT admin_id, username, email, role FROM admin_accounts WHERE admin_id = ?";
            $stmt_archive = mysqli_prepare($conn, $archive_query);
            mysqli_stmt_bind_param($stmt_archive, "i", $archive_id);
            mysqli_stmt_execute($stmt_archive);

            $delete_query = "DELETE FROM admin_accounts WHERE admin_id = ?";
            $stmt_delete = mysqli_prepare($conn, $delete_query);
            mysqli_stmt_bind_param($stmt_delete, "i", $archive_id);
            mysqli_stmt_execute($stmt_delete);

            mysqli_commit($conn);
            $success_message = "Staff account successfully moved to archives.";
        } catch (Exception $e) {
            mysqli_rollback($conn);
            $error_message = "Failed to archive account.";
        }
    }
}

$fetch_query = "SELECT admin_id, username, email, role, created_at FROM admin_accounts ORDER BY role DESC, username ASC";
$result = mysqli_query($conn, $fetch_query);
?>
<!DOCTYPE html>
<html lang="en" data-bs-theme="light">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Staff | MakiKonek</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <style>
        body {
            background: linear-gradient(180deg, #f6fff7 0%, #e9f8ff 100%);
        }

        .main-content {
            margin-left: 280px;
            min-height: 100vh;
            padding: 2rem;
        }

        .custom-card {
            background-color: #f4fff5;
            border: 1px solid #d8efd5;
            border-radius: 16px;
        }

        .page-title {
            color: #0b6d36;
        }

        .table thead {
            background-color: #e6f6e7;
        }

        .form-control.bg-light,
        .form-select.bg-light {
            background-color: #e4f5e1 !important;
            border-color: #c8e5c5 !important;
            color: #0b6d36 !important;
        }

        .btn-create {
            background-color: #3f9f25;
            border-color: #3f9f25;
            color: white;
        }

        .btn-create:hover {
            background-color: #0b6d36;
            border-color: #0b6d36;
            color: white;
        }
    </style>
</head>

<body>

    <?php include __DIR__ . '/partials/admin_sidebar.php'; ?>

    <main class="main-content">
        <div class="d-flex justify-content-between align-items-center mb-4 pb-2 border-bottom border-secondary">
            <h2 class="fw-bold page-title"><i class="bi bi-shield-lock text-success me-2"></i> Manage Internal Staff</h2>
        </div>

        <?php if ($success_message): ?>
            <div class="alert alert-success alert-dismissible fade show shadow-sm"><i class="bi bi-check-circle me-2"></i> <?php echo $success_message; ?> <button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
        <?php endif; ?>
        <?php if ($error_message): ?>
            <div class="alert alert-danger alert-dismissible fade show shadow-sm"><i class="bi bi-exclamation-triangle me-2"></i> <?php echo $error_message; ?> <button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
        <?php endif; ?>

        <div class="row g-4">
            <div class="col-lg-4">
                <div class="custom-card p-4 shadow-sm h-100">
                    <h4 class="mb-4 fw-bold border-bottom border-secondary pb-2">Add New Account</h4>
                    <form action="manage_staff.php" method="POST">
                        <div class="mb-3"><label class="form-label text-muted">Username</label><input type="text" name="username" class="form-control bg-light text-dark" required></div>
                        <div class="mb-3"><label class="form-label text-muted">Email Address</label><input type="email" name="email" class="form-control bg-light text-dark" required></div>
                        <div class="mb-3"><label class="form-label text-muted">Temporary Password</label><input type="password" name="password" class="form-control bg-light text-dark" required></div>
                        <div class="mb-4">
                            <label class="form-label text-muted">Assign Role</label>
                            <select name="role" class="form-select bg-light text-dark">
                                <option value="Barangay Staff">Barangay Staff (Restricted)</option>
                                <option value="Super Admin">Super Admin (Full Access)</option>
                            </select>
                        </div>
                        <button type="submit" name="add_staff" class="btn btn-create w-100 fw-bold"><i class="bi bi-person-plus me-2"></i> Create Account</button>
                    </form>
                </div>
            </div>

            <div class="col-lg-8">
                <div class="custom-card p-4 shadow-sm h-100">
                    <h4 class="mb-4 fw-bold border-bottom border-secondary pb-2">Active Personnel</h4>
                    <div class="table-responsive">
                        <table class="table table-light table-hover align-middle border-secondary">
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
                                            <form action="manage_staff.php" method="POST" class="d-flex align-items-center gap-2 mb-0">
                                                <input type="hidden" name="admin_id" value="<?php echo $row['admin_id']; ?>">
                                                <select name="role" class="form-select form-select-sm">
                                                    <option value="Barangay Staff" <?php echo $row['role'] === 'Barangay Staff' ? 'selected' : ''; ?>>Barangay Staff</option>
                                                    <option value="Super Admin" <?php echo $row['role'] === 'Super Admin' ? 'selected' : ''; ?>>Super Admin</option>
                                                </select>
                                                <button type="submit" name="update_role" class="btn btn-sm btn-outline-primary">Save</button>
                                            </form>
                                        </td>
                                        <td class="text-muted small"><?php echo date("M d, Y", strtotime($row['created_at'])); ?></td>
                                        <td class="text-center">
                                            <?php if ($row['admin_id'] === $_SESSION['admin_id']): ?>
                                                <button class="btn btn-sm btn-outline-secondary disabled" title="You cannot archive yourself"><i class="bi bi-archive"></i></button>
                                            <?php else: ?>
                                                <a href="manage_staff.php?archive_id=<?php echo $row['admin_id']; ?>" class="btn btn-sm btn-outline-warning" onclick="return confirm('Are you sure you want to archive <?php echo $row['username']; ?>?');"><i class="bi bi-archive"></i></a>
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
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>