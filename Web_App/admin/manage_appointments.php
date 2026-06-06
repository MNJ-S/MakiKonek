<?php
session_start();

if (!isset($_SESSION['admin_id'])) {
    header("Location: login_admin.php");
    exit();
}
require_once __DIR__ . '/../includes/db_connect.php';
?>
<!DOCTYPE html>
<html lang="en" data-bs-theme="light">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Appointments | MakiKonek</title>
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
    </style>
</head>

<body>

    <?php include __DIR__ . '/partials/admin_sidebar.php'; ?>

    <main class="main-content">
        <div class="d-flex justify-content-between align-items-center mb-4 pb-2 border-bottom border-secondary">
            <h2 class="fw-bold page-title"><i class="bi bi-calendar-event text-success me-2"></i> Appointment Schedule</h2>
        </div>

        <div class="custom-card p-4 shadow-sm">
            <div class="table-responsive">
                <table class="table table-light table-hover align-middle border-secondary">
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
                            <td><span class="badge bg-success">Confirmed</span></td>
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

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>