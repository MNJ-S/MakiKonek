<?php
session_start();

if (!isset($_SESSION['admin_id']) || $_SESSION['admin_role'] !== 'Super Admin') {
    header("Location: dashboard.php");
    exit();
}

require_once __DIR__ . '/../includes/db_connect.php';

$success_message = '';
$error_message = '';

// --- HANDLE CREATING NEW SK OR OPISYAL ACCOUNTS ---
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['create_official'])) {
    $official_role = mysqli_real_escape_string($conn, $_POST['official_role']);
    $username = mysqli_real_escape_string($conn, trim($_POST['username']));
    $email = mysqli_real_escape_string($conn, trim($_POST['email']));
    $password = $_POST['password'];

    $first_name = mysqli_real_escape_string($conn, trim($_POST['first_name']));
    $last_name = mysqli_real_escape_string($conn, trim($_POST['last_name']));

    $check_query = "SELECT user_id FROM users WHERE email = ? OR username = ?";
    $stmt = mysqli_prepare($conn, $check_query);
    mysqli_stmt_bind_param($stmt, "ss", $email, $username);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_store_result($stmt);

    if (mysqli_stmt_num_rows($stmt) > 0) {
        $error_message = "That username or email is already registered in the system.";
    } else {
        mysqli_begin_transaction($conn);
        try {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);

            $insert_user = "INSERT INTO users (username, email, password_hash, role) VALUES (?, ?, ?, ?)";
            $stmt_user = mysqli_prepare($conn, $insert_user);
            mysqli_stmt_bind_param($stmt_user, "ssss", $username, $email, $hashed_password, $official_role);
            mysqli_stmt_execute($stmt_user);

            $new_user_id = mysqli_insert_id($conn);

            $insert_profile = "INSERT INTO user_profiles (user_id, first_name, last_name) VALUES (?, ?, ?)";
            $stmt_profile = mysqli_prepare($conn, $insert_profile);
            mysqli_stmt_bind_param($stmt_profile, "iss", $new_user_id, $first_name, $last_name);
            mysqli_stmt_execute($stmt_profile);

            mysqli_commit($conn);
            $success_message = "Successfully created new {$official_role} account for {$first_name}!";
        } catch (Exception $e) {
            mysqli_rollback($conn);
            $error_message = "System error: Could not create official account.";
        }
    }
}

// --- FETCH ALL ACTIVE OFFICIALS ---
$fetch_query = "
    SELECT u.user_id, u.username, u.email, u.role, u.created_at, p.first_name, p.last_name 
    FROM users u 
    INNER JOIN user_profiles p ON u.user_id = p.user_id 
    WHERE u.role IN ('SK', 'Opisyal') 
    ORDER BY u.role ASC, u.created_at DESC
";
$officials_result = mysqli_query($conn, $fetch_query);
?>
<!DOCTYPE html>
<html lang="en" data-bs-theme="light">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Officials | MakiKonek</title>
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
            background-color: rgba(255, 255, 255, 0.98);
            border: 1px solid #d8efd5;
            border-radius: 16px;
        }

        .page-title {
            color: #0b6d36;
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

        .table thead {
            background-color: #e6f6e7;
        }

        .badge-opisyal {
            background-color: #0d6efd;
        }

        .badge-sk {
            background-color: #fd7e14;
        }
    </style>
</head>

<body>

    <?php include __DIR__ . '/partials/admin_sidebar.php'; ?>

    <main class="main-content">
        <div class="d-flex justify-content-between align-items-center mb-4 pb-2 border-bottom border-secondary">
            <h2 class="fw-bold page-title"><i class="bi bi-person-badge text-success me-2"></i> Barangay Officials & SK</h2>
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
                    <h5 class="fw-bold border-bottom pb-2 mb-3">Create Official Account</h5>
                    <form action="manage_officials.php" method="POST">
                        <div class="mb-3"><label class="form-label text-muted small">Given Name</label><input type="text" name="first_name" class="form-control" required></div>
                        <div class="mb-3"><label class="form-label text-muted small">Surname</label><input type="text" name="last_name" class="form-control" required></div>
                        <div class="mb-3"><label class="form-label text-muted small">Username</label><input type="text" name="username" class="form-control" required></div>
                        <div class="mb-3"><label class="form-label text-muted small">Email Address</label><input type="email" name="email" class="form-control" required></div>
                        <div class="mb-3"><label class="form-label text-muted small">Temporary Password</label><input type="password" name="password" class="form-control" required></div>
                        <div class="mb-4">
                            <label class="form-label text-muted small">Assign Role</label>
                            <select name="official_role" class="form-select" required>
                                <option value="Opisyal">Barangay Opisyal</option>
                                <option value="SK">Sangguniang Kabataan (SK)</option>
                            </select>
                        </div>
                        <button type="submit" name="create_official" class="btn btn-create w-100 fw-bold"><i class="bi bi-plus-lg me-1"></i> Create Account</button>
                    </form>
                </div>
            </div>

            <div class="col-lg-8">
                <div class="custom-card p-4 shadow-sm h-100">
                    <h5 class="fw-bold border-bottom pb-2 mb-3">Active Officials Directory</h5>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>Username</th>
                                    <th>Role</th>
                                    <th>Date Added</th>
                                    <th class="text-center">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (mysqli_num_rows($officials_result) > 0): ?>
                                    <?php while ($row = mysqli_fetch_assoc($officials_result)): ?>
                                        <tr>
                                            <td class="fw-bold"><?php echo htmlspecialchars($row['first_name'] . ' ' . $row['last_name']); ?></td>
                                            <td class="text-muted"><?php echo htmlspecialchars($row['username']); ?></td>
                                            <td><span class="badge <?php echo $row['role'] === 'Opisyal' ? 'badge-opisyal' : 'badge-sk'; ?>"><?php echo htmlspecialchars($row['role']); ?></span></td>
                                            <td class="small text-muted"><?php echo date("M d, Y", strtotime($row['created_at'])); ?></td>
                                            <td class="text-center">
                                                <button class="btn btn-sm btn-outline-primary" title="View Profile"><i class="bi bi-eye"></i></button>
                                                <button class="btn btn-sm btn-outline-secondary" title="Archive Account"><i class="bi bi-archive"></i></button>
                                            </td>
                                        </tr>
                                    <?php endwhile; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="5" class="text-center py-4 text-muted">No official accounts have been created yet.</td>
                                    </tr>
                                <?php endif; ?>
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