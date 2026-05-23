<?php
session_start();

// 1. THE GATEKEEPER: Check if the user is logged in AND is an admin/staff
// (We will set these session variables when we build the admin login page)
if (!isset($_SESSION['admin_id'])) {
    // If they aren't an admin, kick them back to the admin login page
    header("Location: login_admin.php");
    exit();
}

// Require the database connection (directa sa XAMPP)
require_once '../includes/db_connect.php';

// Retrieve session variables for display and Role-Based Access Control
$admin_username = $_SESSION['admin_username'];
$admin_role = $_SESSION['admin_role']; // Eto yung Super Admin or Staff na gagamitin natin

// --- Placeholder for Dashboard Analytics Queries ---
// **Replace these with real COUNT() queries once tables are full
$total_residents = 0; 
$pending_requests = 0;
$pending_appointments = 0;

?>
<!DOCTYPE html>
<html lang="en" data-bs-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard | MakiKonek</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <style>
        body { background-color: #121212; }
        .sidebar { height: 100vh; background-color: #1e1e1e; border-right: 1px solid #333; }
        .sidebar .nav-link { color: #aaa; margin-bottom: 5px; border-radius: 5px; }
        .sidebar .nav-link:hover, .sidebar .nav-link.active { background-color: #0d6efd; color: white; }
        .main-content { min-height: 100vh; }
        .stat-card { background-color: #1e1e1e; border: 1px solid #333; border-left: 4px solid #0d6efd; }
    </style>
</head>
<body>

<div class="d-flex">
    <nav class="sidebar p-3 d-flex flex-column" style="width: 280px;">
        <a href="dashboard.php" class="d-flex align-items-center mb-4 text-white text-decoration-none">
            <span class="fs-4 fw-bold">MakiKonek Admin</span>
        </a>
        
        <ul class="nav nav-pills flex-column mb-auto">
            <li class="nav-item">
                <a href="dashboard.php" class="nav-link active">
                    <i class="bi bi-speedometer2 me-2"></i> Dashboard
                </a>
            </li>
            <li>
                <a href="manage_requests.php" class="nav-link">
                    <i class="bi bi-file-earmark-text me-2"></i> Service Requests
                </a>
            </li>
            <li>
                <a href="manage_residents.php" class="nav-link">
                    <i class="bi bi-people me-2"></i> Residents
                </a>
            </li>
            <li>
                <a href="manage_appointments.php" class="nav-link">
                    <i class="bi bi-calendar-event me-2"></i> Appointments
                </a>
            </li>
            
            <?php if ($admin_role === 'Super Admin'): ?>
            <li class="mt-3 mb-1 text-uppercase text-muted small fw-bold px-3">Super Admin Only</li>
            <li>
                <a href="manage_staff.php" class="nav-link">
                    <i class="bi bi-shield-lock me-2"></i> Manage Staff
                </a>
            </li>
            <?php endif; ?>
        </ul>
        
        <hr>
        <div class="dropdown">
            <a href="#" class="d-flex align-items-center text-white text-decoration-none dropdown-toggle" data-bs-toggle="dropdown">
                <i class="bi bi-person-circle fs-4 me-2"></i>
                <strong><?php echo htmlspecialchars($admin_username); ?></strong>
            </a>
            <ul class="dropdown-menu dropdown-menu-dark text-small shadow">
                <li><a class="dropdown-item" href="#">Profile</a></li>
                <li><hr class="dropdown-divider"></li>
                <li><a class="dropdown-item text-danger" href="logout.php">Sign out</a></li>
            </ul>
        </div>
    </nav>

    <main class="main-content flex-grow-1 p-4">
        <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-4 border-bottom border-secondary">
            <h1 class="h2">Dashboard Overview</h1>
            <div class="badge bg-primary fs-6">Role: <?php echo htmlspecialchars($admin_role); ?></div>
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
            <table class="table table-dark table-hover table-bordered align-middle">
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
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>