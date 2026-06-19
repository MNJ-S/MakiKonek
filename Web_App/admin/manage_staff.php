<?php
session_start();

if (!isset($_SESSION['admin_id']) || $_SESSION['admin_role'] !== 'Super Admin') {
    header("Location: dashboard.php");
    exit();
}

require_once __DIR__ . '/../includes/db_connect.php';
require_once __DIR__ . '/../includes/prg_flash.php';
require_once __DIR__ . '/../includes/input_validation.php';

date_default_timezone_set('Asia/Manila');

$success_message = prgFlashPull('admin_staff');
$error_message = '';

$archive_column_check = mysqli_query($conn, "SHOW COLUMNS FROM admin_accounts LIKE 'archived_at'");
if ($archive_column_check && mysqli_num_rows($archive_column_check) === 0) {
    mysqli_query($conn, "ALTER TABLE admin_accounts ADD COLUMN archived_at DATETIME NULL");
}

function adminStaffEscape(?string $value): string
{
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

function adminStaffInitials(string $username): string
{
    $clean = preg_replace('/[^A-Za-z0-9]/', '', $username);
    return strtoupper(substr($clean, 0, 2)) ?: 'ST';
}

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['add_staff'])) {
    $new_username = trim($_POST['username'] ?? '');
    $new_email = trim($_POST['email'] ?? '');
    $new_password = $_POST['password'] ?? '';
    $new_role = trim($_POST['role'] ?? 'Barangay Staff');

    if ($new_username === '' || $new_email === '' || $new_password === '') {
        $error_message = "Please complete all staff account fields.";
    } elseif (!preg_match('/^[A-Za-z0-9._-]{4,30}$/', $new_username)) {
        $error_message = 'Username must be 4-30 characters using letters, numbers, periods, underscores, or hyphens.';
    } elseif (!filter_var($new_email, FILTER_VALIDATE_EMAIL) || !inputLength($new_email, 254)) {
        $error_message = 'Please enter a valid email address.';
    } elseif (strlen($new_password) < 8 || strlen($new_password) > 72) {
        $error_message = 'Password must be between 8 and 72 characters.';
    } elseif (!in_array($new_role, ['Barangay Staff', 'Super Admin'], true)) {
        $error_message = 'Please choose a valid staff role.';
    } else {
        $insert_query = "INSERT INTO admin_accounts (username, email, password, role) VALUES (?, ?, ?, ?)";
        $stmt = mysqli_prepare($conn, $insert_query);
        mysqli_stmt_bind_param($stmt, "ssss", $new_username, $new_email, $new_password, $new_role);

        if (mysqli_stmt_execute($stmt)) {
            prgRedirect(
                'manage_staff.php',
                'admin_staff',
                "New {$new_role} account created successfully."
            );
        } else {
            $error_message = "Failed to create account. Username or email might already exist.";
        }
    }
}

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['update_role'])) {
    $update_id = (int) $_POST['admin_id'];
    $updated_role = trim($_POST['role'] ?? 'Barangay Staff');

    if (!in_array($updated_role, ['Barangay Staff', 'Super Admin'], true) || $update_id < 1) {
        $error_message = 'Invalid staff role update.';
    } else {

    $update_query = "UPDATE admin_accounts SET role = ? WHERE admin_id = ?";
    $stmt = mysqli_prepare($conn, $update_query);
    mysqli_stmt_bind_param($stmt, "si", $updated_role, $update_id);

    if (mysqli_stmt_execute($stmt)) {
        prgRedirect('manage_staff.php', 'admin_staff', 'Role updated successfully.');
    } else {
        $error_message = "Failed to update role.";
    }
    }
}

if (isset($_GET['archive_id'])) {
    $archive_id = (int) $_GET['archive_id'];
    if ($archive_id === (int)$_SESSION['admin_id']) {
        $error_message = "Security check: you cannot archive your own active account.";
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
            prgRedirect(
                'manage_staff.php',
                'admin_staff',
                'Staff account successfully moved to archives.'
            );
        } catch (Exception $e) {
            mysqli_rollback($conn);
            $error_message = "Failed to archive account.";
        }
    }
}

$fetch_query = "SELECT admin_id, username, email, role, created_at FROM admin_accounts ORDER BY role DESC, username ASC";
$result = mysqli_query($conn, $fetch_query);
$staff_accounts = [];
if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        $staff_accounts[] = $row;
    }
}

$super_admin_count = count(array_filter($staff_accounts, fn($row) => $row['role'] === 'Super Admin'));
$staff_count = count(array_filter($staff_accounts, fn($row) => $row['role'] !== 'Super Admin'));
$new_this_month = count(array_filter($staff_accounts, fn($row) => date('Y-m', strtotime($row['created_at'])) === date('Y-m')));
?>
<!DOCTYPE html>
<html lang="en" data-bs-theme="light">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Staff Management | MakiKonek</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="icon" href="../assets/img/Barangay_Makiling_Seal.png" type="image/png">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <link rel="stylesheet" href="../assets/css/admin.css?v=20260613a">
</head>

<body class="dashboard-body">
    <?php include __DIR__ . '/partials/admin_sidebar.php'; ?>

    <main class="main-content admin-modern-page staff-main">
        <header class="admin-modern-header">
            <div>
                <h1>Staff Management</h1>
                <p>Manage internal admin access and role permissions.</p>
            </div>
            <div class="admin-modern-actions">
                <div class="dashboard-date-card">
                    <i class="bi bi-calendar3"></i>
                    <span>
                        <strong><?php echo date('F d, Y'); ?></strong>
                        <small><?php echo date('l, h:i A'); ?></small>
                    </span>
                </div>
                <a href="#staffCreateCard" class="admin-primary-btn"><i class="bi bi-person-plus"></i> Add Staff</a>
            </div>
        </header>

        <?php if ($success_message): ?>
            <div class="alert alert-success alert-dismissible fade show shadow-sm"><i class="bi bi-check-circle me-2"></i><?php echo adminStaffEscape($success_message); ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
        <?php endif; ?>
        <?php if ($error_message): ?>
            <div class="alert alert-danger alert-dismissible fade show shadow-sm"><i class="bi bi-exclamation-triangle me-2"></i><?php echo adminStaffEscape($error_message); ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
        <?php endif; ?>

        <section class="official-kpi-grid" aria-label="Staff analytics">
            <article class="admin-kpi-panel">
                <span><i class="bi bi-shield-check"></i></span>
                <div>
                    <p>Super Admins</p><strong><?php echo $super_admin_count; ?></strong><small>Full access accounts</small>
                </div>
            </article>
            <article class="admin-kpi-panel admin-kpi-blue">
                <span><i class="bi bi-person-workspace"></i></span>
                <div>
                    <p>Barangay Staff</p><strong><?php echo $staff_count; ?></strong><small>Operational users</small>
                </div>
            </article>
            <article class="admin-kpi-panel">
                <span><i class="bi bi-person-plus"></i></span>
                <div>
                    <p>New This Month</p><strong><?php echo $new_this_month; ?></strong><small>Recently added</small>
                </div>
            </article>
        </section>

        <section class="staff-layout">
            <article class="admin-card staff-directory-card">
                <div class="admin-card-heading">
                    <div>
                        <h2>Active Personnel</h2>
                        <p>Current admin accounts with system access.</p>
                    </div>
                    <input type="search" id="staffSearch" placeholder="Search staff...">
                </div>

                <div class="staff-account-list">
                    <?php if (!empty($staff_accounts)): ?>
                        <?php foreach ($staff_accounts as $row): ?>
                            <article class="staff-account-card" data-staff-search="<?php echo adminStaffEscape(strtolower($row['username'] . ' ' . $row['email'] . ' ' . $row['role'])); ?>">
                                <span class="staff-avatar"><?php echo adminStaffEscape(adminStaffInitials($row['username'])); ?></span>
                                <div>
                                    <h3><?php echo adminStaffEscape($row['username']); ?></h3>
                                    <p><?php echo adminStaffEscape($row['email']); ?></p>
                                    <small>Added <?php echo adminStaffEscape(date('M d, Y', strtotime($row['created_at']))); ?></small>
                                </div>
                                <form action="manage_staff.php" method="POST">
                                    <input type="hidden" name="admin_id" value="<?php echo (int)$row['admin_id']; ?>">
                                    <select name="role">
                                        <option value="Barangay Staff" <?php echo $row['role'] === 'Barangay Staff' ? 'selected' : ''; ?>>Barangay Staff</option>
                                        <option value="Super Admin" <?php echo $row['role'] === 'Super Admin' ? 'selected' : ''; ?>>Super Admin</option>
                                    </select>
                                    <button type="submit" name="update_role">Save</button>
                                </form>
                                <?php if ((int)$row['admin_id'] === (int)$_SESSION['admin_id']): ?>
                                    <button type="button" class="staff-archive-btn is-disabled" title="You cannot archive yourself"><i class="bi bi-lock"></i></button>
                                <?php else: ?>
                                    <a class="staff-archive-btn" href="manage_staff.php?archive_id=<?php echo (int)$row['admin_id']; ?>" onclick="return confirm('Archive <?php echo adminStaffEscape($row['username']); ?>?');" aria-label="Archive staff"><i class="bi bi-archive"></i></a>
                                <?php endif; ?>
                            </article>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="admin-empty-state">
                            <i class="bi bi-person-workspace"></i>
                            <strong>No staff accounts found.</strong>
                            <span>Create an account to add internal access.</span>
                        </div>
                    <?php endif; ?>
                </div>
            </article>

            <aside class="admin-card staff-create-card" id="staffCreateCard">
                <div class="admin-card-heading">
                    <div>
                        <h2>Add Staff Account</h2>
                        <p>Create controlled admin access.</p>
                    </div>
                </div>
                <form action="manage_staff.php" method="POST" class="admin-form-grid">
                    <label>Username<input type="text" name="username" minlength="4" maxlength="30" pattern="[A-Za-z0-9._-]+" required></label>
                    <label>Email Address<input type="email" name="email" maxlength="254" required></label>
                    <label>Temporary Password<input type="password" name="password" minlength="8" maxlength="72" required></label>
                    <label>Assign Role<select name="role">
                            <option value="Barangay Staff">Barangay Staff</option>
                            <option value="Super Admin">Super Admin</option>
                        </select></label>
                    <button type="submit" name="add_staff">Create Staff Account</button>
                </form>
            </aside>
        </section>
    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const search = document.getElementById('staffSearch');
            search.addEventListener('input', function() {
                const term = this.value.trim().toLowerCase();
                document.querySelectorAll('[data-staff-search]').forEach(card => {
                    card.hidden = term !== '' && !card.dataset.staffSearch.includes(term);
                });
            });
        });
    </script>
</body>

</html>
