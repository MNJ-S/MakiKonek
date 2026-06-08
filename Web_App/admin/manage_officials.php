<?php
session_start();

if (!isset($_SESSION['admin_id']) || $_SESSION['admin_role'] !== 'Super Admin') {
    header("Location: dashboard.php");
    exit();
}

require_once __DIR__ . '/../includes/db_connect.php';

$success_message = '';
$error_message = '';

// --- HANDLE ARCHIVING OFFICIALS ---
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['archive_official'])) {
    $user_id = (int)$_POST['user_id'];
    $archive_query = "UPDATE barangay_officials SET is_active = 0 WHERE user_id = ?";
    $stmt = mysqli_prepare($conn, $archive_query);
    mysqli_stmt_bind_param($stmt, "i", $user_id);
    if (mysqli_stmt_execute($stmt)) {
        $success_message = "Official account archived successfully.";
    } else {
        $error_message = "Failed to archive official.";
    }
}

// --- HANDLE CREATING NEW OFFICIAL ACCOUNTS ---
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['create_official'])) {
    $official_role = mysqli_real_escape_string($conn, $_POST['official_role']);
    $username = mysqli_real_escape_string($conn, trim($_POST['username']));
    $email = mysqli_real_escape_string($conn, trim($_POST['email']));
    $password = $_POST['password']; // Plain text as requested
    $first_name = mysqli_real_escape_string($conn, trim($_POST['first_name']));
    $last_name = mysqli_real_escape_string($conn, trim($_POST['last_name']));
    $position = mysqli_real_escape_string($conn, trim($_POST['position']));
    $committee = mysqli_real_escape_string($conn, trim($_POST['committee']));
    $term_start = mysqli_real_escape_string($conn, trim($_POST['term_start']));
    $term_end = mysqli_real_escape_string($conn, trim($_POST['term_end']));

    mysqli_begin_transaction($conn);
    try {
        $insert_user = "INSERT INTO users (username, email, password, role) VALUES (?, ?, ?, ?)";
        $stmt_user = mysqli_prepare($conn, $insert_user);
        mysqli_stmt_bind_param($stmt_user, "ssss", $username, $email, $password, $official_role);
        mysqli_stmt_execute($stmt_user);
        $new_user_id = mysqli_insert_id($conn);

        $insert_profile = "INSERT INTO user_profiles (user_id, first_name, last_name) VALUES (?, ?, ?)";
        $stmt_profile = mysqli_prepare($conn, $insert_profile);
        mysqli_stmt_bind_param($stmt_profile, "iss", $new_user_id, $first_name, $last_name);
        mysqli_stmt_execute($stmt_profile);

        $insert_official = "INSERT INTO barangay_officials (user_id, position, committee, term_start, term_end) VALUES (?, ?, ?, ?, ?)";
        $stmt_official = mysqli_prepare($conn, $insert_official);
        mysqli_stmt_bind_param($stmt_official, "issss", $new_user_id, $position, $committee, $term_start, $term_end);
        mysqli_stmt_execute($stmt_official);

        mysqli_commit($conn);
        $success_message = "Successfully created new {$official_role} account for {$first_name}!";
    } catch (Exception $e) {
        mysqli_rollback($conn);
        $error_message = "System error: Could not create official account.";
    }
}

$fetch_query = "
    SELECT u.user_id, u.username, u.email, u.role, u.created_at, p.first_name, p.last_name, 
           bo.position, bo.committee, bo.term_start, bo.term_end 
    FROM users u 
    JOIN user_profiles p ON u.user_id = p.user_id 
    JOIN barangay_officials bo ON u.user_id = bo.user_id
    WHERE bo.is_active = 1
    ORDER BY bo.position ASC
";
$officials_result = mysqli_query($conn, $fetch_query);
?>
<!DOCTYPE html>
<html lang="en" data-bs-theme="light">

<head>
    <meta charset="UTF-8">
    <title>Manage Officials | MakiKonek</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <link rel="stylesheet" href="../assets/css/admin.css?v=20260608e">
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
    </style>
</head>

<body>
    <?php include __DIR__ . '/partials/admin_sidebar.php'; ?>
    <main class="main-content">
        <h2 class="fw-bold page-title mb-4"><i class="bi bi-person-badge text-success me-2"></i> Barangay Officials & SK</h2>

        <?php if ($success_message): ?><div class="alert alert-success"><?php echo $success_message; ?></div><?php endif; ?>
        <?php if ($error_message): ?><div class="alert alert-danger"><?php echo $error_message; ?></div><?php endif; ?>

        <div class="row g-4">
            <div class="col-lg-4">
                <div class="custom-card p-4 shadow-sm">
                    <form action="manage_officials.php" method="POST">
                        <div class="mb-2"><label class="small">Given Name</label><input type="text" name="first_name" class="form-control" required></div>
                        <div class="mb-2"><label class="small">Surname</label><input type="text" name="last_name" class="form-control" required></div>
                        <div class="mb-2"><label class="small">Username</label><input type="text" name="username" class="form-control" required></div>
                        <div class="mb-2"><label class="small">Email</label><input type="email" name="email" class="form-control" required></div>
                        <div class="mb-2"><label class="small">Password</label><input type="password" name="password" class="form-control" required></div>
                        <div class="mb-2"><label class="small">Role</label><select name="official_role" class="form-select">
                                <option value="Opisyal">Barangay Opisyal</option>
                                <option value="SK">SK</option>
                            </select></div>
                        <div class="mb-2"><label class="small">Position</label><input type="text" name="position" class="form-control" required></div>
                        <div class="mb-2"><label class="small">Committee</label><input type="text" name="committee" class="form-control"></div>
                        <div class="row">
                            <div class="col-6 mb-2"><label class="small">Term Start</label><input type="date" name="term_start" class="form-control" required></div>
                            <div class="col-6 mb-2"><label class="small">Term End</label><input type="date" name="term_end" class="form-control"></div>
                        </div>
                        <button type="submit" name="create_official" class="btn btn-create w-100">Create Account</button>
                    </form>
                </div>
            </div>
            <div class="col-lg-8">
                <div class="custom-card p-4 shadow-sm">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Role</th>
                                <th>Position</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($row = mysqli_fetch_assoc($officials_result)): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($row['first_name'] . ' ' . $row['last_name']); ?></td>
                                    <td><?php echo htmlspecialchars($row['role']); ?></td>
                                    <td><?php echo htmlspecialchars($row['position']); ?></td>
                                    <td>
                                        <button class="btn btn-sm btn-outline-primary view-official-trigger"
                                            data-name="<?php echo htmlspecialchars($row['first_name'] . ' ' . $row['last_name']); ?>"
                                            data-role="<?php echo htmlspecialchars($row['role']); ?>"
                                            data-pos="<?php echo htmlspecialchars($row['position']); ?>"
                                            data-comm="<?php echo htmlspecialchars($row['committee'] ?? 'N/A'); ?>"
                                            data-start="<?php echo htmlspecialchars($row['term_start']); ?>"
                                            data-end="<?php echo htmlspecialchars($row['term_end'] ?? 'Present'); ?>"><i class="bi bi-eye"></i></button>
                                        <form action="manage_officials.php" method="POST" class="d-inline">
                                            <input type="hidden" name="user_id" value="<?php echo $row['user_id']; ?>">
                                            <button type="submit" name="archive_official" class="btn btn-sm btn-outline-secondary"><i class="bi bi-archive"></i></button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </main>

    <!-- Modal -->
    <div class="modal fade" id="officialModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5>Official Profile</h5>
                </div>
                <div class="modal-body">
                    <p><strong>Name:</strong> <span id="m-name"></span></p>
                    <p><strong>Role:</strong> <span id="m-role"></span></p>
                    <p><strong>Position:</strong> <span id="m-pos"></span></p>
                    <p><strong>Committee:</strong> <span id="m-comm"></span></p>
                    <p><strong>Term:</strong> <span id="m-term"></span></p>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.querySelectorAll('.view-official-trigger').forEach(btn => {
            btn.addEventListener('click', function() {
                document.getElementById('m-name').textContent = this.dataset.name;
                document.getElementById('m-role').textContent = this.dataset.role;
                document.getElementById('m-pos').textContent = this.dataset.pos;
                document.getElementById('m-comm').textContent = this.dataset.comm;
                document.getElementById('m-term').textContent = this.dataset.start + ' to ' + this.dataset.end;
                new bootstrap.Modal(document.getElementById('officialModal')).show();
            });
        });
    </script>
</body>

</html>
