<?php
session_start();

// THE GATEKEEPER
if (!isset($_SESSION['admin_id'])) {
    header("Location: login_admin.php");
    exit();
}
// Database connection will go here once the appointments table is created
// require_once __DIR__ . '/../includes/db_connect.php';
?>

<!DOCTYPE html>
<html lang="en" data-bs-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Appointments | MakiKonek</title>
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
    <main class="main-content mx-auto" style="max-width: 1200px;">
        
        <div class="d-flex justify-content-between align-items-center mb-4 pb-2 border-bottom border-secondary">
            <h2 class="fw-bold text-white"><i class="bi bi-calendar-event text-info me-2"></i> Appointment Schedule</h2>
            <a href="dashboard.php" class="btn btn-outline-light"><i class="bi bi-arrow-left me-1"></i> Dashboard</a>
        </div>

        <div class="custom-card p-4 shadow-sm">
            <div class="table-responsive">
                <table class="table table-dark table-hover align-middle border-secondary">
                    <thead class="table-active">
                        <tr>
                            <th>Apt ID</th>
                            <th>Resident Name</th>
                            <th>Purpose</th>
                            <th>Scheduled Date</th>
                            <th>Time</th>
                            <th>Status</th>
                            <th class="text-center">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td class="fw-bold font-monospace">APT-001</td>
                            <td>Maria Clara</td>
                            <td>Document Pick-up</td>
                            <td>May 26, 2026</td>
                            <td>10:00 AM</td>
                            <td><span class="badge bg-primary">Confirmed</span></td>
                            <td class="text-center">
                                <button class="btn btn-sm btn-outline-info" title="Reschedule"><i class="bi bi-clock-history"></i></button>
                                <button class="btn btn-sm btn-outline-success" title="Mark Completed"><i class="bi bi-check2-all"></i></button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

    </main>
</div>
</body>
</html>