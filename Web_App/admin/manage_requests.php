<?php
session_start();

// THE GATEKEEPER
if (!isset($_SESSION['admin_id'])) {
    header("Location: login_admin.php");
    exit();
}
// Database connection will go here once the service_requests table is created
// require_once __DIR__ . '/../includes/db_connect.php';
?>

<!DOCTYPE html>
<html lang="en" data-bs-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Service Requests | MakiKonek</title>
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
            <h2 class="fw-bold text-white"><i class="bi bi-file-earmark-text text-warning me-2"></i> Service Requests</h2>
            <a href="dashboard.php" class="btn btn-outline-light"><i class="bi bi-arrow-left me-1"></i> Dashboard</a>
        </div>

        <div class="custom-card p-4 shadow-sm">
            <div class="table-responsive">
                <table class="table table-dark table-hover align-middle border-secondary">
                    <thead class="table-active">
                        <tr>
                            <th>Ref ID</th>
                            <th>Resident Name</th>
                            <th>Document Type</th>
                            <th>Date Submitted</th>
                            <th>Status</th>
                            <th class="text-center">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td class="fw-bold font-monospace">REQ-001</td>
                            <td>Juan Dela Cruz</td>
                            <td>Barangay Clearance</td>
                            <td>May 24, 2026</td>
                            <td><span class="badge bg-warning text-dark">Pending</span></td>
                            <td class="text-center">
                                <button class="btn btn-sm btn-success me-1" title="Approve"><i class="bi bi-check-lg"></i></button>
                                <button class="btn btn-sm btn-danger" title="Reject"><i class="bi bi-x-lg"></i></button>
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