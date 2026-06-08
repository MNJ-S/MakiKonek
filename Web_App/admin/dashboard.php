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
$res_query = "SELECT COUNT(user_id) AS total FROM users WHERE role = 'Residente'";
$res_result = mysqli_query($conn, $res_query);
$total_residents = ($res_result) ? mysqli_fetch_assoc($res_result)['total'] : 0;

// TOTAL PENDING REQUESTS
$req_query = "SELECT COUNT(request_id) AS total FROM service_requests WHERE status = 'PENDING'";
$req_result = mysqli_query($conn, $req_query);
$pending_requests = ($req_result) ? mysqli_fetch_assoc($req_result)['total'] : 0;

$pending_appointments = 0;

$proc_query = "SELECT COUNT(request_id) AS total FROM service_requests WHERE status = 'APPROVED' AND process_status = 'PROCESSING'";
$proc_result = mysqli_query($conn, $proc_query);
$processing_count = ($proc_result) ? mysqli_fetch_assoc($proc_result)['total'] : 0;

$ready_query = "SELECT COUNT(request_id) AS total FROM service_requests WHERE process_status = 'READY FOR PICKUP'";
$ready_result = mysqli_query($conn, $ready_query);
$ready_count = ($ready_result) ? mysqli_fetch_assoc($ready_result)['total'] : 0;

$processing_requests = mysqli_query($conn, "SELECT sr.*, dt.name AS doc_type, p.first_name, p.last_name FROM service_requests sr JOIN document_types dt ON sr.document_type_id = dt.document_type_id JOIN user_profiles p ON sr.user_id = p.user_id WHERE sr.status = 'APPROVED' AND sr.process_status = 'PROCESSING' ORDER BY sr.created_at ASC");

$ready_requests = mysqli_query($conn, "SELECT sr.*, dt.name AS doc_type, p.first_name, p.last_name FROM service_requests sr JOIN document_types dt ON sr.document_type_id = dt.document_type_id JOIN user_profiles p ON sr.user_id = p.user_id WHERE sr.process_status = 'READY FOR PICKUP' ORDER BY sr.created_at ASC");


// RECENT PENDING REQUESTS FOR TABLE
$recent_query = "
    SELECT sr.reference_no, sr.created_at, sr.status, dt.name AS document_type, p.first_name, p.last_name
    FROM service_requests sr
    JOIN document_types dt ON sr.document_type_id = dt.document_type_id
    JOIN user_profiles p ON sr.user_id = p.user_id
    WHERE sr.status = 'PENDING'
    ORDER BY sr.created_at DESC
    LIMIT 5
";
$recent_result = mysqli_query($conn, $recent_query);

// 1. PROCESSING REQUESTS (Approved, but not ready)
$proc_query = "
    SELECT sr.reference_no, sr.request_id, dt.name AS doc_type, p.first_name, p.last_name 
    FROM service_requests sr 
    JOIN document_types dt ON sr.document_type_id = dt.document_type_id 
    JOIN user_profiles p ON sr.user_id = p.user_id 
    WHERE sr.status = 'APPROVED' AND sr.process_status = 'PROCESSING' 
    ORDER BY sr.created_at ASC";
$processing_requests = mysqli_query($conn, $proc_query);

$ready_query = "
    SELECT sr.reference_no, sr.request_id, dt.name AS doc_type, p.first_name, p.last_name 
    FROM service_requests sr 
    JOIN document_types dt ON sr.document_type_id = dt.document_type_id 
    JOIN user_profiles p ON sr.user_id = p.user_id 
    WHERE sr.process_status = 'READY FOR PICKUP' 
    ORDER BY sr.created_at ASC";
$ready_requests = mysqli_query($conn, $ready_query);
?>
<!DOCTYPE html>
<html lang="en" data-bs-theme="light">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard | MakiKonek</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <link rel="stylesheet" href="../assets/css/admin.css?v=20260608a">
    <style>
        body {
            background: linear-gradient(180deg, #f6fff7 0%, #e9f8ff 100%);
        }

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

    <?php include __DIR__ . '/partials/admin_sidebar.php'; ?>

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
                        <th class="text-center">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (mysqli_num_rows($recent_result) > 0): ?>
                        <?php while ($row = mysqli_fetch_assoc($recent_result)): ?>
                            <tr>
                                <td class="fw-bold font-monospace"><?php echo htmlspecialchars($row['reference_no']); ?></td>
                                <td><?php echo htmlspecialchars($row['first_name'] . ' ' . $row['last_name']); ?></td>
                                <td><?php echo htmlspecialchars($row['document_type']); ?></td>
                                <td><?php echo date("M d, Y", strtotime($row['created_at'])); ?></td>
                                <td><span class="badge bg-warning text-dark"><?php echo htmlspecialchars($row['status']); ?></span></td>
                                <td class="text-center">
                                    <a href="manage_requests.php" class="btn btn-sm btn-outline-primary">Review</a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6" class="text-center text-muted py-4">No pending requests at this time.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        <div class="row mt-5">
            <div class="col-md-6">
                <h4 class="mb-3 text-primary"><i class="bi bi-gear-wide-connected"></i> Processing (<?php echo mysqli_num_rows($processing_requests); ?>)</h4>
                <div class="table-responsive custom-card p-3">
                    <table class="table table-hover align-middle">
                        <thead>
                            <tr>
                                <th>Ref</th>
                                <th>Resident</th>
                                <th>Document</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($row = mysqli_fetch_assoc($processing_requests)): ?>
                                <tr>
                                    <td class="font-monospace fw-bold"><?php echo htmlspecialchars($row['reference_no']); ?></td>
                                    <td><?php echo htmlspecialchars($row['first_name'] . ' ' . $row['last_name']); ?></td>
                                    <td><small><?php echo htmlspecialchars($row['doc_type']); ?></small></td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="col-md-6">
                <h4 class="mb-3 text-success"><i class="bi bi-hand-thumbs-up"></i> Ready for Pickup (<?php echo mysqli_num_rows($ready_requests); ?>)</h4>
                <div class="table-responsive custom-card p-3">
                    <table class="table table-hover align-middle">
                        <thead>
                            <tr>
                                <th>Ref</th>
                                <th>Resident</th>
                                <th>Document</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($row = mysqli_fetch_assoc($ready_requests)): ?>
                                <tr>
                                    <td class="font-monospace fw-bold"><?php echo htmlspecialchars($row['reference_no']); ?></td>
                                    <td><?php echo htmlspecialchars($row['first_name'] . ' ' . $row['last_name']); ?></td>
                                    <td><small><?php echo htmlspecialchars($row['doc_type']); ?></small></td>
                                    <td>
                                        <form action="manage_requests.php" method="POST">
                                            <input type="hidden" name="request_id" value="<?php echo $row['request_id']; ?>">
                                            <button type="submit" name="complete_request" class="btn btn-sm btn-success">Picked Up</button>
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

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>
