<?php
session_start();

if (!isset($_SESSION['admin_id'])) {
    header("Location: login_admin.php");
    exit();
}

require_once __DIR__ . '/../includes/db_connect.php';
require_once __DIR__ . '/includes/auto_archive_reservations.php';

date_default_timezone_set('Asia/Manila');

$admin_username = $_SESSION['admin_username'] ?? 'Admin';
$admin_role = $_SESSION['admin_role'] ?? 'Staff';
$admin_display_name = $admin_username !== '' ? $admin_username : 'Admin';
$hour = (int)date('G');
$greeting = $hour < 12 ? 'Good morning' : ($hour < 18 ? 'Good afternoon' : 'Good evening');

$payment_status_column_check = mysqli_query($conn, "SHOW COLUMNS FROM service_requests LIKE 'payment_status'");
if ($payment_status_column_check && mysqli_num_rows($payment_status_column_check) === 0) {
    mysqli_query($conn, "ALTER TABLE service_requests ADD COLUMN payment_status VARCHAR(30) DEFAULT 'Unpaid' AFTER payment_receipt_path");
}

function dashboardScalar(mysqli $conn, string $query): int
{
    $result = mysqli_query($conn, $query);
    if (!$result) {
        return 0;
    }

    $row = mysqli_fetch_assoc($result);
    return (int)($row['total'] ?? 0);
}

function normalizeDashboardStatus(string $status, string $process_status = ''): string
{
    $normalized = strtoupper(trim($status));
    $process = strtoupper(trim($process_status));

    if ($normalized === 'APPROVED') {
        if ($process === 'READY FOR PICKUP') return 'Ready for Pickup';
        if ($process === 'PROCESSING') return 'Processing';
        return 'Under Review';
    }

    foreach ([
        'PENDING' => 'Pending',
        'UNDER REVIEW' => 'Under Review',
        'PROCESSING' => 'Processing',
        'READY FOR PICKUP' => 'Ready for Pickup',
        'COMPLETED' => 'Completed',
        'REJECTED' => 'Rejected',
    ] as $key => $label) {
        if ($normalized === $key) {
            return $label;
        }
    }

    return $status !== '' ? $status : 'Pending';
}

function dashboardStatusLabel(string $status): string
{
    return $status === 'Pending' ? 'Pending Review' : $status;
}

function dashboardStatusClass(string $status): string
{
    $normalized = strtoupper($status);
    if ($normalized === 'UNDER REVIEW') return 'dash-badge-review';
    if ($normalized === 'PROCESSING') return 'dash-badge-processing';
    if ($normalized === 'READY FOR PICKUP') return 'dash-badge-ready';
    if ($normalized === 'COMPLETED') return 'dash-badge-completed';
    if ($normalized === 'REJECTED') return 'dash-badge-rejected';
    return 'dash-badge-pending';
}

function normalizeDashboardPayment(?string $status, ?string $method = '', float $fee = 0): string
{
    $normalized = strtoupper(trim((string)$status));

    if ($fee <= 0) {
        return '-';
    }

    if ($normalized === 'CASH ON PICKUP' || $normalized === 'PENDING PAYMENT' || $normalized === 'UNPAID') {
        return 'Unpaid';
    }

    if ($normalized === 'CASH RECEIVED' || $normalized === 'PAID UPON PICKUP' || $normalized === 'PAID AT PICKUP') {
        return 'Paid at Pickup';
    }

    if ($normalized === 'PAYMENT VERIFIED' || $normalized === 'VERIFIED') {
        return ($method === 'online') ? 'Online Verified' : 'Verified';
    }

    if ($normalized === 'RECEIPT SUBMITTED') {
        return 'Receipt Submitted';
    }

    if ($normalized === 'REJECTED') {
        return 'Payment Rejected';
    }

    return trim((string)$status) !== '' ? trim((string)$status) : 'Unpaid';
}

function dashboardPaymentClass(string $status): string
{
    $normalized = strtoupper($status);
    if ($normalized === '-') return 'dash-badge-empty';
    if ($normalized === 'ONLINE VERIFIED' || $normalized === 'VERIFIED') return 'dash-badge-paid-online';
    if ($normalized === 'PAID AT PICKUP') return 'dash-badge-paid-pickup';
    if ($normalized === 'RECEIPT SUBMITTED') return 'dash-badge-review';
    if ($normalized === 'PAYMENT REJECTED') return 'dash-badge-rejected';
    return 'dash-badge-payment-due';
}

function dashboardDocShortName(string $name): string
{
    $map = [
        'Certificate of Residency' => 'Residency',
        'Certificate of Indigency' => 'Indigency',
        'Building/Construction Permit' => 'Construction Permit',
        'Barangay Clearance' => 'Barangay Clearance',
        'Business Clearance' => 'Business Clearance',
        'Cedula' => 'Cedula',
        'Incident Report' => 'Incident Report',
    ];

    return $map[$name] ?? $name;
}

$total_residents = dashboardScalar($conn, "SELECT COUNT(user_id) AS total FROM users WHERE role = 'Residente'");
$pending_requests = dashboardScalar($conn, "SELECT COUNT(request_id) AS total FROM service_requests WHERE UPPER(status) = 'PENDING'");
$under_review_count = dashboardScalar($conn, "SELECT COUNT(request_id) AS total FROM service_requests WHERE status = 'Under Review' OR UPPER(status) = 'APPROVED'");
$processing_count = dashboardScalar($conn, "SELECT COUNT(request_id) AS total FROM service_requests WHERE status = 'Processing' OR (UPPER(status) = 'APPROVED' AND process_status = 'PROCESSING')");
$ready_count = dashboardScalar($conn, "SELECT COUNT(request_id) AS total FROM service_requests WHERE status = 'Ready for Pickup' OR process_status = 'READY FOR PICKUP'");
$completed_count = dashboardScalar($conn, "SELECT COUNT(request_id) AS total FROM service_requests WHERE UPPER(status) = 'COMPLETED'");
$appointments_today = dashboardScalar($conn, "SELECT COUNT(reservation_id) AS total FROM facility_reservations WHERE reservation_date = CURRENT_DATE() AND UPPER(status) NOT IN ('CANCELLED', 'REJECTED')");
$residents_for_verification = 0;
$notification_count = min(9, $pending_requests + $ready_count);

$recent_query = "
    SELECT sr.request_id, sr.reference_no, sr.created_at, sr.status, sr.process_status, sr.document_fee,
           sr.payment_method, sr.payment_status, dt.name AS document_type, p.first_name, p.last_name
    FROM service_requests sr
    JOIN document_types dt ON sr.document_type_id = dt.document_type_id
    JOIN user_profiles p ON sr.user_id = p.user_id
    ORDER BY sr.created_at DESC
    LIMIT 5
";
$recent_result = mysqli_query($conn, $recent_query);
$recent_requests = [];
if ($recent_result) {
    while ($row = mysqli_fetch_assoc($recent_result)) {
        $recent_requests[] = $row;
    }
}

$overview_order = [
    'Barangay Clearance',
    'Certificate of Residency',
    'Certificate of Indigency',
    'Cedula',
    'Business Clearance',
    'Building/Construction Permit',
    'Incident Report',
];
$overview_counts = array_fill_keys($overview_order, 0);
$overview_query = "
    SELECT dt.name, COUNT(sr.request_id) AS total
    FROM document_types dt
    LEFT JOIN service_requests sr
        ON sr.document_type_id = dt.document_type_id
        AND MONTH(sr.created_at) = MONTH(CURRENT_DATE())
        AND YEAR(sr.created_at) = YEAR(CURRENT_DATE())
    GROUP BY dt.document_type_id, dt.name
";
$overview_result = mysqli_query($conn, $overview_query);
if ($overview_result) {
    while ($row = mysqli_fetch_assoc($overview_result)) {
        if (array_key_exists($row['name'], $overview_counts)) {
            $overview_counts[$row['name']] = (int)$row['total'];
        }
    }
}
$total_monthly_requests = array_sum($overview_counts);
$max_overview_count = max(1, max($overview_counts));

$activities = [];
foreach ($recent_requests as $row) {
    $status = normalizeDashboardStatus($row['status'], $row['process_status']);
    $name = trim($row['first_name'] . ' ' . $row['last_name']);
    $doc = dashboardDocShortName($row['document_type']);

    if ($status === 'Ready for Pickup') {
        $activities[] = [
            'title' => $doc . ' marked',
            'detail' => 'Ready for Pickup',
            'time' => date('M d, h:i A', strtotime($row['created_at'])),
        ];
    } else {
        $activities[] = [
            'title' => 'New ' . $doc . ' request submitted',
            'detail' => 'by ' . $name,
            'time' => date('M d, h:i A', strtotime($row['created_at'])),
        ];
    }
}

while (count($activities) < 5) {
    $activities[] = [
        'title' => 'No additional activity yet',
        'detail' => 'System activity will appear here',
        'time' => '-',
    ];
}
?>
<!DOCTYPE html>
<html lang="en" data-bs-theme="light">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard | MakiKonek</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <link rel="stylesheet" href="../assets/css/admin.css?v=20260612v">
</head>

<body class="dashboard-body">
    <?php include __DIR__ . '/partials/admin_sidebar.php'; ?>

    <main class="main-content dashboard-main">
        <header class="dashboard-header">
            <div>
                <h1><?php echo htmlspecialchars($greeting); ?>, <?php echo htmlspecialchars($admin_display_name); ?>! <span aria-hidden="true">&#128075;</span></h1>
                <p>Here's what's happening in MakiKonek today.</p>
            </div>
            <div class="dashboard-header-tools">
                <div class="dashboard-date-card">
                    <i class="bi bi-calendar3"></i>
                    <span>
                        <strong><?php echo date('F d, Y | l'); ?></strong>
                        <small><?php echo date('h:i A'); ?></small>
                    </span>
                </div>
                <button class="dashboard-notification" type="button" aria-label="Notifications">
                    <i class="bi bi-bell"></i>
                    <?php if ($notification_count > 0): ?>
                        <span><?php echo $notification_count; ?></span>
                    <?php endif; ?>
                </button>
            </div>
        </header>

        <section class="dashboard-top-grid" aria-label="Dashboard overview">
            <article class="bento-card overview-hero">
                <div class="overview-copy">
                    <span class="eyebrow">Overview</span>
                    <h2><?php echo $pending_requests; ?> requests need your review</h2>
                    <p><?php echo $ready_count; ?> ready for pickup · <?php echo $appointments_today; ?> appointments today</p>
                    <a href="manage_requests.php" class="dashboard-primary-action">View Pending Requests <i class="bi bi-arrow-right"></i></a>
                </div>
                <div class="barangay-illustration" aria-hidden="true">
                    <span class="sun"></span>
                    <span class="hill"></span>
                    <span class="hall-roof"></span>
                    <span class="hall-body"></span>
                    <span class="hall-door"></span>
                    <span class="flag-pole"></span>
                    <span class="flag"></span>
                    <span class="tree"></span>
                </div>
            </article>

            <article class="bento-card kpi-card-centered">
                <span class="kpi-icon-top"><i class="bi bi-file-earmark-text"></i></span>
                <div class="kpi-content-wrap">
                    <h3>Pending Requests</h3>
                    <strong><?php echo $pending_requests; ?></strong>
                    <p>Needs review</p>
                </div>
            </article>

            <article class="bento-card kpi-card-centered">
                <span class="kpi-icon-top"><i class="bi bi-list-check"></i></span>
                <div class="kpi-content-wrap">
                    <h3>Processing</h3>
                    <strong><?php echo $processing_count; ?></strong>
                    <p>Being prepared</p>
                </div>
            </article>

            <article class="bento-card kpi-card-centered">
                <span class="kpi-icon-top"><i class="bi bi-inbox"></i></span>
                <div class="kpi-content-wrap">
                    <h3>Ready for Pickup</h3>
                    <strong><?php echo $ready_count; ?></strong>
                    <p>Awaiting claim</p>
                </div>
            </article>

            <article class="bento-card kpi-card-centered">
                <span class="kpi-icon-top"><i class="bi bi-check2-circle"></i></span>
                <div class="kpi-content-wrap">
                    <h3>Completed</h3>
                    <strong><?php echo $completed_count; ?></strong>
                    <p>Fully processed</p>
                </div>
            </article>

            <article class="bento-card kpi-card-centered">
                <span class="kpi-icon-top"><i class="bi bi-people"></i></span>
                <div class="kpi-content-wrap">
                    <h3>Verified Residents</h3>
                    <strong><?php echo $total_residents; ?></strong>
                    <p>Total accounts</p>
                </div>
            </article>
        </section>

        <section class="dashboard-mid-grid" aria-label="Operational summary">
            <article class="bento-card attention-card">
                <div class="card-heading">
                    <h2><span><i class="bi bi-flag"></i></span> Needs Attention</h2>
                </div>
                <ul class="attention-list">
                    <li><span>Pending service requests</span><strong><?php echo $pending_requests; ?></strong></li>
                    <li><span>Requests under review</span><strong><?php echo $under_review_count; ?></strong></li>
                    <li><span>Residents for verification</span><strong><?php echo $residents_for_verification; ?></strong></li>
                    <li><span>Documents ready for pickup</span><strong><?php echo $ready_count; ?></strong></li>
                    <li><span>Appointments today</span><strong><?php echo $appointments_today; ?></strong></li>
                </ul>
                <a href="manage_requests.php" class="dashboard-text-link">Go to All Requests <i class="bi bi-arrow-right"></i></a>
            </article>

            <article class="bento-card activity-card">
                <div class="card-heading">
                    <h2><span><i class="bi bi-activity"></i></span> Recent Activity</h2>
                    <a href="manage_requests.php">View All</a>
                </div>
                <div class="activity-timeline">
                    <?php foreach (array_slice($activities, 0, 5) as $activity): ?>
                        <div class="activity-item">
                            <span class="timeline-dot"></span>
                            <div>
                                <strong><?php echo htmlspecialchars($activity['title']); ?></strong>
                                <small><?php echo htmlspecialchars($activity['detail']); ?></small>
                            </div>
                            <time><?php echo htmlspecialchars($activity['time']); ?></time>
                        </div>
                    <?php endforeach; ?>
                </div>
            </article>

            <article class="bento-card overview-card">
                <div class="card-heading">
                    <h2>Service Request Overview</h2>
                    <button class="period-filter" type="button">This Month <i class="bi bi-chevron-down"></i></button>
                </div>
                <div class="request-bars">
                    <?php foreach ($overview_order as $doc_name):
                        $count = $overview_counts[$doc_name] ?? 0;
                        $width = max(7, (int)round(($count / $max_overview_count) * 100));
                    ?>
                        <div class="request-bar-row">
                            <span><?php echo htmlspecialchars(dashboardDocShortName($doc_name)); ?></span>
                            <div class="bar-track"><span style="width: <?php echo $width; ?>%;"></span></div>
                            <strong><?php echo $count; ?></strong>
                        </div>
                    <?php endforeach; ?>
                </div>
                <div class="overview-total">
                    <span>Total Requests</span>
                    <strong><?php echo $total_monthly_requests; ?></strong>
                </div>
            </article>
        </section>

        <section class="dashboard-bottom-grid" aria-label="Recent requests and quick actions">
            <article class="bento-card recent-requests-card">
                <div class="card-heading">
                    <h2>Recent Service Requests</h2>
                    <a href="manage_requests.php">View All Requests</a>
                </div>
                <div class="table-responsive dashboard-table-wrap">
                    <table class="dashboard-table">
                        <thead>
                            <tr>
                                <th>Reference No.</th>
                                <th>Resident</th>
                                <th>Document Type</th>
                                <th>Submitted</th>
                                <th>Status</th>
                                <th>Payment</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($recent_requests)): ?>
                                <?php foreach ($recent_requests as $row):
                                    $status = normalizeDashboardStatus($row['status'], $row['process_status']);
                                    $payment = normalizeDashboardPayment($row['payment_status'] ?? '', $row['payment_method'] ?? '', (float)$row['document_fee']);
                                ?>
                                    <tr>
                                        <td class="font-monospace fw-bold"><?php echo htmlspecialchars($row['reference_no']); ?></td>
                                        <td><?php echo htmlspecialchars($row['first_name'] . ' ' . $row['last_name']); ?></td>
                                        <td><?php echo htmlspecialchars(dashboardDocShortName($row['document_type'])); ?></td>
                                        <td><?php echo date('M d, h:i A', strtotime($row['created_at'])); ?></td>
                                        <td><span class="dash-badge <?php echo dashboardStatusClass($status); ?>"><?php echo htmlspecialchars(dashboardStatusLabel($status)); ?></span></td>
                                        <td><span class="dash-badge <?php echo dashboardPaymentClass($payment); ?>"><?php echo htmlspecialchars($payment); ?></span></td>
                                        <td>
                                            <div class="dashboard-row-actions">
                                                <a href="manage_requests.php" class="table-view-btn">View</a>
                                                <button type="button" aria-label="More actions"><i class="bi bi-three-dots-vertical"></i></button>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="7" class="text-center text-muted py-4">No service requests yet.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
                <a href="manage_requests.php" class="dashboard-table-footer">View All Requests <i class="bi bi-arrow-right"></i></a>
            </article>

            <aside class="bento-card quick-actions-card">
                <div class="card-heading">
                    <h2><span><i class="bi bi-stars"></i></span> Quick Actions</h2>
                </div>
                <div class="quick-action-grid">
                    <a href="manage_requests.php" class="quick-action-item">
                        <strong>Manage Requests</strong>
                        <small>View and process service requests</small>
                        <i class="bi bi-arrow-right"></i>
                    </a>
                    <a href="manage_residents.php" class="quick-action-item">
                        <strong>Add Resident</strong>
                        <small>Register new resident</small>
                        <i class="bi bi-arrow-right"></i>
                    </a>
                    <a href="manage_appointments.php" class="quick-action-item">
                        <strong>Appointments</strong>
                        <small>View and manage appointments</small>
                        <i class="bi bi-arrow-right"></i>
                    </a>
                    <a href="manage_announcements.php" class="quick-action-item">
                        <strong>Announcements</strong>
                        <small>Create and manage announcements</small>
                        <i class="bi bi-arrow-right"></i>
                    </a>
                    <a href="#" class="quick-action-item">
                        <strong>Generate Reports</strong>
                        <small>Export system reports</small>
                        <i class="bi bi-arrow-right"></i>
                    </a>
                    <a href="#" class="quick-action-item">
                        <strong>System Logs</strong>
                        <small>View activity logs</small>
                        <i class="bi bi-arrow-right"></i>
                    </a>
                </div>
            </aside>
        </section>
    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>
