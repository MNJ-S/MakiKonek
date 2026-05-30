<?php
session_start();

if (!isset($_SESSION['admin_id'])) {
    header("Location: login_admin.php");
    exit();
}
require_once __DIR__ . '/../includes/db_connect.php';

$admin_username = $_SESSION['admin_username'];
$admin_role = $_SESSION['admin_role'];

// TOTAL VERIFIED USERS (RESIDENTS)
$res_query = "SELECT COUNT(user_id) AS total FROM users";
$res_result = mysqli_query($conn, $res_query);
$total_residents = ($res_result) ? mysqli_fetch_assoc($res_result)['total'] : 0;

$pending_requests = 0;
$pending_appointments = 0;
?>
<!DOCTYPE html>
<html lang="en" data-bs-theme="light">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard | MakiKonek</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <style>
        body {
            background: linear-gradient(180deg, #f6fff7 0%, #e9f8ff 100%);
        }
        /* Notice we removed the sidebar CSS here because it's now handled by the include! */
        .main-content {
            margin-left: 280px;
            min-height: 100vh;
            padding: 2rem;
        }
        .stat-card {
            background-color: #f4fff5;
            border: 1px solid #d8efd5;
            border-left: 4px solid #3f9f25;
        }
        .stat-card .card-title {
            color: #0b6d36;
        }
        .badge-role {
            background-color: #3f9f25;
        }
        .table-light {
            background-color: rgba(255, 255, 255, 0.98);
        }
        .table thead {
            background-color: #e6f6e7;
        }
    </style>
</head>

<body>

    <!-- THIS ONE LINE REPLACES 50 LINES OF HTML -->
    <?php include __DIR__ . '/partials/admin_sidebar.php'; ?>
    
    <!-- MAIN CONTENT -->
    <main class="main-content flex-grow-1 p-4">
        <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-4 border-bottom border-secondary">
            <h1 class="h2">Dashboard Overview</h1>
            <div class="badge badge-role fs-6">Role: <?php echo htmlspecialchars($admin_role); ?></div>
        </div>

        <div class="row g-4 mb-4">
            <div class="col-md-4">
                <div class="card stat-card shadow-sm h-100">
                    <div class="card-body">
                        <h6 class="card-title text-muted text-uppercase fw-bold">Pending Requests</h6>
                        <h2 class="mb-0 text-warning"><?php echo $pending_requests; ?></h2>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card stat-card shadow-sm h-100">
                    <div class="card-body">
                        <h6 class="card-title text-muted text-uppercase fw-bold">Pending Appointments</h6>
                        <h2 class="mb-0 text-info"><?php echo $pending_appointments; ?></h2>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card stat-card shadow-sm h-100">
                    <div class="card-body">
                        <h6 class="card-title text-muted text-uppercase fw-bold">Total Verified Residents</h6>
                        <h2 class="mb-0 text-success"><?php echo $total_residents; ?></h2>
                    </div>
                </div>
            </div>
        </div>

        <h4 class="mb-3">Recent Service Requests</h4>
        <div class="table-responsive">
            <table class="table table-light table-hover table-bordered align-middle">
                <thead class="table-active">
                    <tr>
                        <th>Reference No.</th>
                        <th>Resident Name</th>
                        <th>Document Type</th>
                        <th>Date Submitted</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td colspan="6" class="text-center text-muted py-4">No pending requests at this time.</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>