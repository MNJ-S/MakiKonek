<?php
session_start();

if (!isset($_SESSION['admin_id'])) {
    header("Location: login_admin.php");
    exit();
}

require_once __DIR__ . '/../includes/db_connect.php';
require_once __DIR__ . '/../includes/prg_flash.php';

$success_message = prgFlashPull('admin_requests');
$error_message = '';
$admin_username = $_SESSION['admin_username'] ?? 'Admin';
$request_statuses = ['Pending', 'Under Review', 'Processing', 'Ready for Pickup', 'Completed', 'Rejected'];

$payment_status_column_check = mysqli_query($conn, "SHOW COLUMNS FROM service_requests LIKE 'payment_status'");
if ($payment_status_column_check && mysqli_num_rows($payment_status_column_check) === 0) {
    mysqli_query($conn, "ALTER TABLE service_requests ADD COLUMN payment_status VARCHAR(30) DEFAULT 'Unpaid' AFTER payment_receipt_path");
    mysqli_query($conn, "
        UPDATE service_requests
        SET payment_status = CASE
            WHEN document_fee <= 0 THEN 'No Fee'
            WHEN payment_method = 'online' AND payment_receipt_path IS NOT NULL AND payment_receipt_path <> '' THEN 'Receipt Submitted'
            WHEN payment_method = 'cash' THEN 'Unpaid'
            ELSE 'Unpaid'
        END
    ");
}

mysqli_query($conn, "
    UPDATE service_requests
    SET payment_status = CASE
        WHEN payment_status IN ('Cash on Pickup', 'Pending Payment') THEN 'Unpaid'
        WHEN payment_status IN ('Cash Received', 'Paid Upon Pickup') THEN 'Paid at Pickup'
        WHEN payment_status = 'Payment Verified' THEN 'Verified'
        ELSE payment_status
    END
");

mysqli_query($conn, "
    CREATE TABLE IF NOT EXISTS request_remarks (
        remark_id INT(11) NOT NULL AUTO_INCREMENT,
        request_id INT(11) NOT NULL,
        admin_id INT(11) DEFAULT NULL,
        admin_name VARCHAR(100) NOT NULL,
        remark TEXT NOT NULL,
        created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (remark_id),
        KEY idx_request_remarks_request (request_id),
        KEY idx_request_remarks_admin (admin_id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci
");

function recordCompletedRequest(mysqli $conn, int $req_id): void
{
    $check_query = "SELECT completed_id FROM completed_requests WHERE original_request_id = ? LIMIT 1";
    $stmt_check = mysqli_prepare($conn, $check_query);
    mysqli_stmt_bind_param($stmt_check, "i", $req_id);
    mysqli_stmt_execute($stmt_check);
    if (mysqli_fetch_assoc(mysqli_stmt_get_result($stmt_check))) {
        return;
    }

    $fetch_query = "
        SELECT sr.request_id, sr.user_id, sr.reference_no, sr.purpose, sr.document_fee, sr.created_at, dt.name as document_type_name
        FROM service_requests sr
        JOIN document_types dt ON sr.document_type_id = dt.document_type_id
        WHERE sr.request_id = ? LIMIT 1
    ";
    $stmt_fetch = mysqli_prepare($conn, $fetch_query);
    mysqli_stmt_bind_param($stmt_fetch, "i", $req_id);
    mysqli_stmt_execute($stmt_fetch);
    $req = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt_fetch));

    if ($req) {
        $insert_query = "INSERT INTO completed_requests (original_request_id, user_id, document_type_name, reference_no, purpose, document_fee, requested_at) VALUES (?, ?, ?, ?, ?, ?, ?)";
        $stmt_insert = mysqli_prepare($conn, $insert_query);
        mysqli_stmt_bind_param(
            $stmt_insert,
            "iisssds",
            $req['request_id'],
            $req['user_id'],
            $req['document_type_name'],
            $req['reference_no'],
            $req['purpose'],
            $req['document_fee'],
            $req['created_at']
        );
        mysqli_stmt_execute($stmt_insert);
    }
}

function markCompletedRequest(mysqli $conn, int $req_id): bool
{
    $fetch_query = "SELECT document_fee, payment_method, payment_status FROM service_requests WHERE request_id = ? LIMIT 1";
    $stmt_fetch = mysqli_prepare($conn, $fetch_query);
    mysqli_stmt_bind_param($stmt_fetch, "i", $req_id);
    mysqli_stmt_execute($stmt_fetch);
    $request = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt_fetch));

    if (!$request) {
        return false;
    }

    if ((float)$request['document_fee'] > 0 && $request['payment_method'] === 'online' && $request['payment_status'] !== 'Verified') {
        return false;
    }

    $completion_payment_status = $request['payment_status'];
    if ((float)$request['document_fee'] > 0 && $request['payment_method'] === 'cash' && $completion_payment_status === 'Unpaid') {
        $completion_payment_status = 'Paid at Pickup';
    }

    $update_query = "UPDATE service_requests SET status = 'Completed', process_status = 'COMPLETED', payment_status = ? WHERE request_id = ?";
    $stmt_update = mysqli_prepare($conn, $update_query);
    mysqli_stmt_bind_param($stmt_update, "si", $completion_payment_status, $req_id);

    $result = mysqli_stmt_execute($stmt_update) && mysqli_stmt_affected_rows($stmt_update) > 0;
    if ($result) {
        recordCompletedRequest($conn, $req_id);
    }
    return $result;
}

function statusBadgeClass(string $status): string
{
    $normalized = strtoupper($status);
    if ($normalized === 'UNDER REVIEW') return 'badge-review';
    if ($normalized === 'APPROVED') return 'badge-review';
    if ($normalized === 'PROCESSING') return 'badge-processing';
    if ($normalized === 'READY FOR PICKUP') return 'badge-ready';
    if ($normalized === 'COMPLETED') return 'badge-completed';
    if ($normalized === 'REJECTED') return 'badge-rejected';
    return 'badge-pending';
}

function paymentBadgeClass(string $status): string
{
    $normalized = strtoupper(normalizePaymentStatus($status));
    if ($normalized === 'NO FEE') return 'badge-muted';
    if ($normalized === 'RECEIPT SUBMITTED') return 'badge-review';
    if ($normalized === 'VERIFIED') return 'badge-completed';
    if ($normalized === 'PAID AT PICKUP') return 'badge-muted';
    if ($normalized === 'REJECTED') return 'badge-rejected';
    return 'badge-payment-pending';
}

function normalizePaymentStatus(?string $status): string
{
    $normalized = strtoupper(trim((string)$status));

    if ($normalized === 'CASH ON PICKUP' || $normalized === 'PENDING PAYMENT') {
        return 'Unpaid';
    }

    if ($normalized === 'CASH RECEIVED' || $normalized === 'PAID UPON PICKUP') {
        return 'Paid at Pickup';
    }

    if ($normalized === 'PAYMENT VERIFIED') {
        return 'Verified';
    }

    return trim((string)$status);
}

function allowedRequestStatuses(string $current_status): array
{
    $flow = [
        'Pending' => ['Pending', 'Under Review', 'Rejected'],
        'Under Review' => ['Under Review', 'Processing', 'Rejected'],
        'Processing' => ['Processing', 'Ready for Pickup', 'Rejected'],
        'Ready for Pickup' => ['Ready for Pickup', 'Completed'],
        'Completed' => ['Completed'],
        'Rejected' => ['Rejected'],
    ];

    return $flow[$current_status] ?? ['Pending', 'Under Review', 'Rejected'];
}

function statusMeta(string $status): array
{
    $meta = [
        'Pending' => ['description' => 'Request submitted', 'tone' => 'pending'],
        'Under Review' => ['description' => 'For checking requirements', 'tone' => 'review'],
        'Processing' => ['description' => 'Document is being prepared', 'tone' => 'processing'],
        'Ready for Pickup' => ['description' => 'Document is ready for release', 'tone' => 'ready'],
        'Completed' => ['description' => 'Document has been released', 'tone' => 'completed'],
        'Rejected' => ['description' => 'Request has been rejected', 'tone' => 'rejected'],
    ];

    return $meta[$status] ?? ['description' => 'Status update', 'tone' => 'pending'];
}

function formatPurpose(?string $purpose): string
{
    $purpose = trim((string)$purpose);
    if ($purpose === '') {
        return 'Not specified';
    }

    return ucwords(strtolower($purpose));
}

function legacyProcessStatus(string $status): string
{
    $normalized = strtoupper($status);
    if ($normalized === 'PROCESSING') return 'PROCESSING';
    if ($normalized === 'READY FOR PICKUP') return 'READY FOR PICKUP';
    if ($normalized === 'COMPLETED') return 'COMPLETED';
    if ($normalized === 'REJECTED') return 'Rejected';
    return 'Pending';
}

function normalizeRequestStatus(string $status, string $process_status = ''): string
{
    $normalized = strtoupper(trim($status));
    $process = strtoupper(trim($process_status));

    if ($normalized === 'APPROVED') {
        if ($process === 'READY FOR PICKUP') return 'Ready for Pickup';
        if ($process === 'PROCESSING') return 'Processing';
        return 'Under Review';
    }

    foreach (['PENDING' => 'Pending', 'UNDER REVIEW' => 'Under Review', 'PROCESSING' => 'Processing', 'READY FOR PICKUP' => 'Ready for Pickup', 'COMPLETED' => 'Completed', 'REJECTED' => 'Rejected'] as $key => $label) {
        if ($normalized === $key) {
            return $label;
        }
    }

    return $status !== '' ? $status : 'Pending';
}

function inferPaymentStatus(array $row): string
{
    if (!empty($row['payment_status'])) {
        return normalizePaymentStatus($row['payment_status']);
    }

    if ((float)($row['document_fee'] ?? 0) <= 0) {
        return 'No Fee';
    }

    if (($row['payment_method'] ?? '') === 'online' && !empty($row['payment_receipt_path'])) {
        return 'Receipt Submitted';
    }

    if (($row['payment_method'] ?? '') === 'cash') {
        return 'Unpaid';
    }

    return 'Unpaid';
}

function h(?string $value): string
{
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

function displayRequestStatus(string $status): string
{
    return $status === 'Pending' ? 'Pending Review' : $status;
}

function documentShortName(string $name): string
{
    $map = [
        'Certificate of Residency' => 'Residency',
        'Certificate of Indigency' => 'Indigency',
        'Building/Construction Permit' => 'Construction Permit',
        'Barangay Clearance' => 'Barangay Clearance',
        'Business Clearance' => 'Business Clearance',
        'Good Moral Certificate' => 'Good Moral',
        'Barangay ID' => 'Barangay ID',
        'Cedula' => 'Cedula',
        'Incident Report' => 'Incident Report',
    ];

    return $map[$name] ?? $name;
}

function paymentDisplayStatus(array $row): string
{
    $payment_status = inferPaymentStatus($row);
    $method = strtolower((string)($row['payment_method'] ?? ''));

    if ((float)($row['document_fee'] ?? 0) <= 0 || $payment_status === 'No Fee') {
        return '-';
    }

    if ($payment_status === 'Paid at Pickup') {
        return 'Paid Upon Pickup';
    }

    if ($payment_status === 'Verified') {
        return $method === 'online' ? 'Online Payment Verified' : 'Payment Verified';
    }

    if ($payment_status === 'Receipt Submitted') {
        return 'Pending Verification';
    }

    if ($payment_status === 'Rejected') {
        return 'Refunded';
    }

    return 'Pending Payment';
}

function paymentDisplayClass(string $status): string
{
    $normalized = strtoupper($status);
    if ($normalized === '-') return 'service-payment-empty';
    if ($normalized === 'ONLINE PAYMENT VERIFIED' || $normalized === 'PAYMENT VERIFIED') return 'service-payment-online';
    if ($normalized === 'PAID UPON PICKUP') return 'service-payment-pickup';
    if ($normalized === 'PENDING VERIFICATION') return 'service-payment-review';
    if ($normalized === 'REFUNDED') return 'service-payment-refunded';
    return 'service-payment-pending';
}

function buildResidentAddress(array $row): string
{
    $parts = array_filter([
        !empty($row['house_no']) ? 'House ' . $row['house_no'] : '',
        $row['street'] ?? '',
        !empty($row['purok_no']) ? 'Purok ' . $row['purok_no'] : '',
        $row['subdivision'] ?? '',
    ]);

    return !empty($parts) ? implode(', ', $parts) : 'Not specified';
}

function statusOptionsJson(array $statuses): string
{
    $payload = array_map(function ($status) {
        $meta = statusMeta($status);
        return [
            'value' => $status,
            'label' => displayRequestStatus($status),
            'description' => $meta['description'],
            'tone' => $meta['tone'],
        ];
    }, $statuses);

    return h(json_encode($payload));
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['complete_request'])) {
    $req_id = (int)$_POST['request_id'];

    mysqli_begin_transaction($conn);
    try {
        if (markCompletedRequest($conn, $req_id)) {
            mysqli_commit($conn);
            prgRedirect(
                'manage_requests.php',
                'admin_requests',
                'Request marked as Completed.'
            );
        } else {
            mysqli_rollback($conn);
            $error_message = "Request could not be completed. Check the payment status first.";
        }
    } catch (Exception $e) {
        mysqli_rollback($conn);
        $error_message = "System error: Could not complete request.";
    }
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_status'])) {
    $req_id = (int)$_POST['request_id'];
    $new_status = trim($_POST['request_status'] ?? $_POST['update_status'] ?? '');

    if (!in_array($new_status, $request_statuses, true)) {
        $error_message = "Invalid request status selected.";
    } else {
        $current_query = "SELECT user_id, reference_no, status, process_status, document_fee, payment_method, payment_status, payment_receipt_path FROM service_requests WHERE request_id = ? LIMIT 1";
        $stmt_current = mysqli_prepare($conn, $current_query);
        mysqli_stmt_bind_param($stmt_current, "i", $req_id);
        mysqli_stmt_execute($stmt_current);
        $current_result = mysqli_stmt_get_result($stmt_current);
        $current_row = mysqli_fetch_assoc($current_result);

        if (!$current_row) {
            $error_message = "Request could not be found.";
        } else {
            $current_status = normalizeRequestStatus($current_row['status'], $current_row['process_status']);
            $allowed_next_statuses = allowedRequestStatuses($current_status);

            if (!in_array($new_status, $allowed_next_statuses, true)) {
                $error_message = "Invalid status move. This request can only move forward in the workflow.";
            } elseif ($new_status === 'Completed' && (float)$current_row['document_fee'] > 0 && $current_row['payment_method'] === 'online' && inferPaymentStatus($current_row) !== 'Verified') {
                $error_message = "Verify the online payment before marking this request as Completed.";
            } else {
                $legacy_process = legacyProcessStatus($new_status);
                $new_payment_status = inferPaymentStatus($current_row);

                if ($new_status === 'Completed' && (float)$current_row['document_fee'] > 0 && $current_row['payment_method'] === 'cash' && $new_payment_status === 'Unpaid') {
                    $new_payment_status = 'Paid at Pickup';
                }

                $update_query = "UPDATE service_requests SET status = ?, process_status = ?, payment_status = ? WHERE request_id = ?";
                $stmt = mysqli_prepare($conn, $update_query);
                mysqli_stmt_bind_param($stmt, "sssi", $new_status, $legacy_process, $new_payment_status, $req_id);

                if (mysqli_stmt_execute($stmt)) {
                    if ($new_status === 'Completed') {
                        recordCompletedRequest($conn, $req_id);
                    }

                    $user_id = (int)$current_row['user_id'];
                    $reference_no = $current_row['reference_no'];

                    $notif_title = 'Request ' . $new_status;
                    $notif_msg = "Your request ($reference_no) has been updated to $new_status.";
                    $notif_icon = 'fa-regular fa-bell';

                    if ($new_status === 'Approved' || $new_status === 'Ready for Pickup') {
                        $notif_msg = "Your document request ($reference_no) is ready for pickup.";
                        $notif_icon = 'fa-regular fa-circle-check';
                    } elseif ($new_status === 'Rejected') {
                        $notif_msg = "Your document request ($reference_no) has been rejected.";
                        $notif_icon = 'fa-regular fa-circle-xmark';
                    } elseif ($new_status === 'Completed') {
                        $notif_msg = "Your document request ($reference_no) has been completed.";
                        $notif_icon = 'fa-solid fa-check-double';
                    }

                    $notif_stmt = mysqli_prepare($conn, "INSERT INTO user_notifications (user_id, title, message, type, icon) VALUES (?, ?, ?, 'Request Update', ?)");
                    if ($notif_stmt) {
                        mysqli_stmt_bind_param($notif_stmt, "isss", $user_id, $notif_title, $notif_msg, $notif_icon);
                        mysqli_stmt_execute($notif_stmt);
                        mysqli_stmt_close($notif_stmt);
                    }
                    createAdminNotification(
                        $conn,
                        "Request " . $new_status,
                        "Document request ({$reference_no}) was updated to {$new_status}.",
                        "Service Request",
                        "bi-file-earmark-text",
                        "manage_requests.php"
                    );
                    prgRedirect(
                        'manage_requests.php',
                        'admin_requests',
                        'Request status updated to ' . $new_status . '.'
                    );
                } else {
                    $error_message = "Failed to update request status.";
                }
            }
        }
    }
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['confirm_payment'])) {
    $req_id = (int)$_POST['request_id'];
    $new_payment_status = trim($_POST['payment_status'] ?? '');
    $allowed_payment_confirmations = ['Verified', 'Paid at Pickup', 'Rejected'];

    if (!in_array($new_payment_status, $allowed_payment_confirmations, true)) {
        $error_message = "Invalid payment confirmation.";
    } else {
        $update_query = "UPDATE service_requests SET payment_status = ? WHERE request_id = ?";
        $stmt = mysqli_prepare($conn, $update_query);
        mysqli_stmt_bind_param($stmt, "si", $new_payment_status, $req_id);

        if (mysqli_stmt_execute($stmt)) {
            prgRedirect(
                'manage_requests.php',
                'admin_requests',
                'Payment marked as ' . $new_payment_status . '.'
            );
        } else {
            $error_message = "Failed to update payment status.";
        }
    }
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add_remark'])) {
    $req_id = (int)$_POST['request_id'];
    $remark = trim($_POST['remark'] ?? '');
    $admin_id = (int)($_SESSION['admin_id'] ?? 0);

    if ($remark === '') {
        $error_message = "Please enter a note before saving.";
    } else {
        $insert_remark = "INSERT INTO request_remarks (request_id, admin_id, admin_name, remark) VALUES (?, ?, ?, ?)";
        $stmt_remark = mysqli_prepare($conn, $insert_remark);
        mysqli_stmt_bind_param($stmt_remark, "iiss", $req_id, $admin_id, $admin_username, $remark);

        if (mysqli_stmt_execute($stmt_remark)) {
            prgRedirect(
                'manage_requests.php',
                'admin_requests',
                'Remark saved to the request log.'
            );
        } else {
            $error_message = "Failed to save remark.";
        }
    }
}

$query = "
    SELECT sr.*, dt.name AS document_type, u.email, p.first_name, p.last_name, p.mobile_number AS phone,
           p.house_no, p.street, p.purok_no, p.subdivision,
           rb.business_name, rb.business_location, rb.business_operator, rb.business_nature, rb.business_address,
           ri.incident_date, ri.incident_time, ri.incident_location, ri.incident_persons, ri.incident_narrative, ri.witness_name AS incident_witness_name
    FROM service_requests sr
    JOIN document_types dt ON sr.document_type_id = dt.document_type_id
    JOIN users u ON sr.user_id = u.user_id
    JOIN user_profiles p ON u.user_id = p.user_id
    LEFT JOIN request_business_clearances rb ON sr.request_id = rb.request_id
    LEFT JOIN request_incident_reports ri ON sr.request_id = ri.request_id
    ORDER BY sr.created_at DESC
";

$result = mysqli_query($conn, $query);
$requests = [];
if ($result) {
    while ($request_row = mysqli_fetch_assoc($result)) {
        $requests[] = $request_row;
    }
}

$category_order = [
    'Barangay Clearance',
    'Certificate of Residency',
    'Certificate of Indigency',
    'Cedula',
    'Incident Report',
    'Business Clearance',
    'Building/Construction Permit',
    'Good Moral Certificate',
    'Barangay ID',
];
$category_counts = array_fill_keys($category_order, 0);
foreach ($requests as $request_row) {
    if (array_key_exists($request_row['document_type'], $category_counts)) {
        $category_counts[$request_row['document_type']]++;
    }
}

$remarks_by_request = [];
$request_ids = array_map('intval', array_column($requests, 'request_id'));
if (!empty($request_ids)) {
    $id_list = implode(',', $request_ids);
    $remarks_query = "
        SELECT request_id, admin_name, remark, created_at
        FROM request_remarks
        WHERE request_id IN ($id_list)
        ORDER BY created_at DESC, remark_id DESC
    ";
    $remarks_result = mysqli_query($conn, $remarks_query);

    if ($remarks_result) {
        while ($remark_row = mysqli_fetch_assoc($remarks_result)) {
            $remarks_by_request[(int)$remark_row['request_id']][] = $remark_row;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en" data-bs-theme="light">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Service Requests | MakiKonek</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <link rel="stylesheet" href="../assets/css/notifications.css">
    <link rel="icon" href="../assets/img/Barangay_Makiling_Seal.png" type="image/png">
    <link rel="stylesheet" href="../assets/css/admin.css?v=20260613a">
    <style>
        .remarks-log {
            display: grid;
            gap: 10px;
            max-height: 230px;
            overflow: auto;
        }

        .remark-entry {
            border: 1px solid #d8efd5;
            border-left: 4px solid #0f7a43;
            border-radius: 8px;
            padding: 10px 12px;
            background: #fbfffb;
        }

        .remark-entry p {
            white-space: pre-wrap;
        }
    </style>
</head>

<body class="dashboard-body">

    <?php include __DIR__ . '/partials/admin_sidebar.php'; ?>

    <main class="main-content service-requests-main">
        <header class="service-page-header">
            <div>
                <h1>Service Requests</h1>
                <p>Manage and monitor all incoming service requests.</p>
            </div>
            <div class="dashboard-header-tools">
                <div class="dashboard-date-card">
                    <i class="bi bi-calendar3"></i>
                    <span>
                        <strong><?php echo date('F d, Y'); ?></strong>
                        <small><?php echo date('l, h:i A'); ?></small>
                    </span>
                </div>
                <div class="dropdown dropdown-notification-wrapper">
                    <button class="dashboard-notification" type="button" aria-label="Notifications" data-bs-toggle="dropdown" aria-expanded="false" data-bs-auto-close="outside">
                        <i class="bi bi-bell"></i>
                        <span id="admin-notif-badge" style="display: none;">0</span>
                    </button>

                    <div class="dropdown-menu dropdown-menu-end notification-dropdown">
                        <div class="notification-header">
                            <div>
                                <h6>Notifications</h6>
                                <small id="admin-notif-count-text">0 unread updates</small>
                            </div>
                            <button type="button" class="mark-read-btn" id="markAllReadBtn" style="display: none;">Mark all as read</button>
                        </div>

                        <div class="notification-body" id="admin-notification-body">
                            <div class="p-3 text-center text-muted"><small>Loading...</small></div>
                        </div>
                    </div>
                </div>
            </div>
        </header>

        <?php if ($success_message): ?>
            <div class="alert alert-success"><i class="bi bi-check-circle me-2"></i> <?php echo $success_message; ?></div>
        <?php endif; ?>
        <?php if ($error_message): ?>
            <div class="alert alert-danger"><i class="bi bi-exclamation-circle me-2"></i> <?php echo $error_message; ?></div>
        <?php endif; ?>

        <section class="service-tabs-card" aria-label="Service categories">
            <div class="service-category-tabs" role="tablist">
                <button class="service-tab is-active" type="button" data-category="All Requests">All Requests <span><?php echo count($requests); ?></span></button>
                <?php foreach (array_slice($category_order, 0, 6) as $category): ?>
                    <button class="service-tab" type="button" data-category="<?php echo h($category); ?>"><?php echo h(documentShortName($category)); ?> <span><?php echo (int)$category_counts[$category]; ?></span></button>
                <?php endforeach; ?>
                <?php if (count($category_order) > 6): ?>
                    <div class="dropdown">
                        <button class="service-tab service-more-tab" type="button" data-bs-toggle="dropdown">More <i class="bi bi-chevron-down"></i></button>
                        <div class="dropdown-menu service-more-menu">
                            <?php foreach (array_slice($category_order, 6) as $category): ?>
                                <button class="dropdown-item service-more-item" type="button" data-category="<?php echo h($category); ?>">
                                    <?php echo h(documentShortName($category)); ?> <span><?php echo (int)$category_counts[$category]; ?></span>
                                </button>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </section>

        <section class="service-table-card">
            <div class="service-filter-toolbar">
                <label class="service-search">
                    <span class="visually-hidden">Search service requests</span>
                    <input type="search" id="requestSearch" placeholder="Search by reference no., resident name, or document type...">
                    <i class="bi bi-search"></i>
                </label>
                <select id="statusFilter" aria-label="Status filter">
                    <option value="">Status</option>
                    <option value="Pending Review">Pending Review</option>
                    <option value="Under Review">Under Review</option>
                    <option value="Processing">Processing</option>
                    <option value="Ready for Pickup">Ready for Pickup</option>
                    <option value="Completed">Completed</option>
                    <option value="Rejected">Rejected</option>
                </select>
                <select id="paymentFilter" aria-label="Payment filter">
                    <option value="">Payment</option>
                    <option value="Paid Upon Pickup">Paid Upon Pickup</option>
                    <option value="Online Payment Verified">Online Payment Verified</option>
                    <option value="Pending Verification">Pending Verification</option>
                    <option value="Refunded">Refunded</option>
                </select>
                <div class="service-date-range">
                    <input type="date" id="startDate" aria-label="Start Date">
                    <span></span>
                    <input type="date" id="endDate" aria-label="End Date">
                    <i class="bi bi-calendar3"></i>
                </div>
                <button class="service-export-btn" type="button" id="exportRequests"><i class="bi bi-download"></i> Export</button>
            </div>

            <div class="service-table-wrap" id="requestTableWrap" <?php echo count($requests) > 0 ? '' : 'hidden'; ?>>
                <table class="service-request-table">
                    <thead>
                        <tr>
                            <th>Reference No.</th>
                            <th>Resident Name</th>
                            <th>Document Type</th>
                            <th>Date Submitted</th>
                            <th>Request Status</th>
                            <th>Payment</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($requests as $row):
                            $request_status = normalizeRequestStatus($row['status'], $row['process_status']);
                            $status_label = displayRequestStatus($request_status);
                            $payment_label = paymentDisplayStatus($row);
                            $payment_class = paymentDisplayClass($payment_label);
                            $allowed_statuses = allowedRequestStatuses($request_status);
                            $current_status_meta = statusMeta($request_status);
                            $display_purpose = formatPurpose($row['purpose']);
                            $resident_name = trim($row['first_name'] . ' ' . $row['last_name']);
                            $resident_address = buildResidentAddress($row);
                            $submitted_date = date('Y-m-d', strtotime($row['created_at']));
                            $submitted_display = date('M d, Y', strtotime($row['created_at'])) . '<small>' . date('h:i A', strtotime($row['created_at'])) . '</small>';
                            $attachment_path = !empty($row['id_path']) ? '../' . $row['id_path'] : '';
                            $receipt_path = !empty($row['payment_receipt_path']) ? '../' . $row['payment_receipt_path'] : '';
                            $status_options = statusOptionsJson($allowed_statuses);
                        ?>
                            <tr class="service-request-row"
                                data-category="<?php echo h($row['document_type']); ?>"
                                data-search="<?php echo h(strtolower($row['reference_no'] . ' ' . $resident_name . ' ' . $row['document_type'])); ?>"
                                data-status="<?php echo h($status_label); ?>"
                                data-payment="<?php echo h($payment_label); ?>"
                                data-date="<?php echo h($submitted_date); ?>">
                                <td class="font-monospace fw-bold"><?php echo h($row['reference_no']); ?></td>
                                <td><?php echo h($resident_name); ?></td>
                                <td><?php echo h(documentShortName($row['document_type'])); ?></td>
                                <td class="service-date-cell"><?php echo $submitted_display; ?></td>
                                <td>
                                    <div class="dropdown status-action-dropdown">
                                        <button class="status-pill status-pill-<?php echo h($current_status_meta['tone']); ?> dropdown-toggle" type="button" data-bs-toggle="dropdown" data-bs-boundary="viewport" aria-expanded="false">
                                            <?php echo h($status_label); ?>
                                        </button>
                                        <div class="dropdown-menu status-change-menu">
                                            <div class="status-menu-title">Change Status</div>
                                            <?php foreach ($allowed_statuses as $status_option):
                                                $option_meta = statusMeta($status_option);
                                                $is_current_status = $request_status === $status_option;
                                            ?>
                                                <?php if ($is_current_status): ?>
                                                    <div class="status-menu-item is-current">
                                                        <span class="status-dot status-dot-<?php echo h($option_meta['tone']); ?>"></span>
                                                        <span>
                                                            <strong><?php echo h(displayRequestStatus($status_option)); ?></strong>
                                                            <small><?php echo h($option_meta['description']); ?></small>
                                                        </span>
                                                    </div>
                                                <?php else: ?>
                                                    <form action="manage_requests.php" method="POST">
                                                        <input type="hidden" name="request_id" value="<?php echo (int)$row['request_id']; ?>">
                                                        <input type="hidden" name="request_status" value="<?php echo h($status_option); ?>">
                                                        <button type="submit" name="update_status" value="1" class="status-menu-item" data-current-status="<?php echo h($status_label); ?>" data-next-status="<?php echo h(displayRequestStatus($status_option)); ?>">
                                                            <span class="status-dot status-dot-<?php echo h($option_meta['tone']); ?>"></span>
                                                            <span>
                                                                <strong><?php echo h(displayRequestStatus($status_option)); ?></strong>
                                                                <small><?php echo h($option_meta['description']); ?></small>
                                                            </span>
                                                        </button>
                                                    </form>
                                                <?php endif; ?>
                                            <?php endforeach; ?>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <span class="service-payment-badge <?php echo h($payment_class); ?>"><?php echo h($payment_label); ?></span>
                                </td>
                                <td>
                                    <div class="service-row-actions">
                                        <button class="service-view-btn view-details-trigger" type="button"
                                            data-bs-toggle="modal" data-bs-target="#requestDetailsModal"
                                            data-ref="<?php echo h($row['reference_no']); ?>"
                                            data-name="<?php echo h($resident_name); ?>"
                                            data-doc="<?php echo h($row['document_type']); ?>"
                                            data-phone="<?php echo h($row['phone']); ?>"
                                            data-email="<?php echo h($row['email']); ?>"
                                            data-address="<?php echo h($resident_address); ?>"
                                            data-purpose="<?php echo h($display_purpose); ?>"
                                            data-created="<?php echo h(date('M d, Y h:i A', strtotime($row['created_at']))); ?>"
                                            data-status="<?php echo h($status_label); ?>"
                                            data-payment="<?php echo h($payment_label); ?>"
                                            data-fee="<?php echo h(number_format((float)$row['document_fee'], 2)); ?>"
                                            data-method="<?php echo h(ucwords((string)$row['payment_method'])); ?>"
                                            data-idpath="<?php echo h($attachment_path); ?>"
                                            data-receiptpath="<?php echo h($receipt_path); ?>"
                                            data-requestid="<?php echo (int)$row['request_id']; ?>"
                                            data-tab="<?php echo h($row['document_type']); ?>"
                                            data-status-options="<?php echo $status_options; ?>"
                                            data-bname="<?php echo h($row['business_name'] ?? ''); ?>"
                                            data-blocation="<?php echo h($row['business_location'] ?? ''); ?>"
                                            data-boperator="<?php echo h($row['business_operator'] ?? ''); ?>"
                                            data-bnature="<?php echo h($row['business_nature'] ?? ''); ?>"
                                            data-baddress="<?php echo h($row['business_address'] ?? ''); ?>"
                                            data-idate="<?php echo h($row['incident_date'] ?? ''); ?>"
                                            data-itime="<?php echo h($row['incident_time'] ?? ''); ?>"
                                            data-ilocation="<?php echo h($row['incident_location'] ?? ''); ?>"
                                            data-ipersons="<?php echo h($row['incident_persons'] ?? ''); ?>"
                                            data-inarrative="<?php echo h($row['incident_narrative'] ?? ''); ?>"
                                            data-iwitness="<?php echo h($row['incident_witness_name'] ?? ''); ?>">
                                            View
                                        </button>
                                        <div class="dropdown">
                                            <button class="service-dots-btn" type="button" data-bs-toggle="dropdown" aria-label="More actions"><i class="bi bi-three-dots-vertical"></i></button>
                                            <div class="dropdown-menu service-action-menu">
                                                <a class="dropdown-item" href="print_document.php?req_id=<?php echo (int)$row['request_id']; ?>" target="_blank"><i class="bi bi-printer"></i> Print Document</a>
                                                <button class="dropdown-item view-details-trigger" type="button" data-bs-toggle="modal" data-bs-target="#requestDetailsModal"
                                                    data-ref="<?php echo h($row['reference_no']); ?>" data-name="<?php echo h($resident_name); ?>" data-doc="<?php echo h($row['document_type']); ?>" data-phone="<?php echo h($row['phone']); ?>" data-email="<?php echo h($row['email']); ?>" data-address="<?php echo h($resident_address); ?>" data-purpose="<?php echo h($display_purpose); ?>" data-created="<?php echo h(date('M d, Y h:i A', strtotime($row['created_at']))); ?>" data-status="<?php echo h($status_label); ?>" data-payment="<?php echo h($payment_label); ?>" data-fee="<?php echo h(number_format((float)$row['document_fee'], 2)); ?>" data-method="<?php echo h(ucwords((string)$row['payment_method'])); ?>" data-idpath="<?php echo h($attachment_path); ?>" data-receiptpath="<?php echo h($receipt_path); ?>" data-requestid="<?php echo (int)$row['request_id']; ?>" data-tab="<?php echo h($row['document_type']); ?>" data-status-options="<?php echo $status_options; ?>"><i class="bi bi-pencil-square"></i> Edit Request</button>
                                                <button class="dropdown-item payment-trigger" type="button" data-bs-toggle="modal" data-bs-target="#paymentModal" data-payment="<?php echo h($payment_label); ?>" data-method="<?php echo h(ucwords((string)$row['payment_method'])); ?>" data-amount="<?php echo h(number_format((float)$row['document_fee'], 2)); ?>" data-name="<?php echo h($resident_name); ?>" data-created="<?php echo h(date('M d, Y h:i A', strtotime($row['created_at']))); ?>" data-ref="<?php echo h($row['reference_no']); ?>" data-requestid="<?php echo (int)$row['request_id']; ?>" data-rawpayment="<?php echo h(inferPaymentStatus($row)); ?>" data-receiptpath="<?php echo h($receipt_path); ?>"><i class="bi bi-credit-card"></i> View Payment</button>
                                                <button class="dropdown-item update-status-trigger" type="button" data-bs-toggle="modal" data-bs-target="#updateStatusModal" data-requestid="<?php echo (int)$row['request_id']; ?>" data-current="<?php echo h($status_label); ?>" data-options="<?php echo $status_options; ?>"><i class="bi bi-arrow-repeat"></i> Update Status</button>
                                                <?php if (in_array('Rejected', $allowed_statuses, true) && $request_status !== 'Rejected'): ?>
                                                    <form action="manage_requests.php" method="POST">
                                                        <input type="hidden" name="request_id" value="<?php echo (int)$row['request_id']; ?>">
                                                        <input type="hidden" name="request_status" value="Rejected">
                                                        <button type="submit" name="update_status" value="1" class="dropdown-item service-danger-action"><i class="bi bi-x-circle"></i> Cancel Request</button>
                                                    </form>
                                                <?php else: ?>
                                                    <span class="dropdown-item-text text-muted"><i class="bi bi-x-circle"></i> Cancel Request</span>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <div class="service-empty-state" id="requestEmptyState" <?php echo count($requests) > 0 ? 'hidden' : ''; ?>>
                <i class="bi bi-folder2-open"></i>
                <strong>No service requests found.</strong>
                <span>Try adjusting your filters or search terms.</span>
            </div>
            <div class="service-table-footer">
                <span id="requestResultCount">Showing <?php echo count($requests) > 0 ? '1 to ' . count($requests) . ' of ' . count($requests) : '0'; ?> entries</span>
            </div>
        </section>
    </main>

    <?php foreach ($requests as $row): ?>
        <template id="remarks-template-<?php echo (int)$row['request_id']; ?>">
            <?php if (!empty($remarks_by_request[(int)$row['request_id']])): ?>
                <div class="remarks-log">
                    <?php foreach ($remarks_by_request[(int)$row['request_id']] as $remark): ?>
                        <article class="remark-entry">
                            <div class="d-flex justify-content-between gap-2 mb-1">
                                <strong class="text-success"><?php echo htmlspecialchars($remark['admin_name']); ?></strong>
                                <small class="text-muted"><?php echo date('M d, Y h:i A', strtotime($remark['created_at'])); ?></small>
                            </div>
                            <p class="mb-0 small"><?php echo nl2br(htmlspecialchars($remark['remark'])); ?></p>
                        </article>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="text-center text-muted border rounded p-3 small">No remarks have been logged for this request yet.</div>
            <?php endif; ?>
        </template>
    <?php endforeach; ?>

    <div class="modal fade service-modal" id="requestDetailsModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-xl">
            <div class="modal-content">
                <div class="modal-header">
                    <div>
                        <span class="modal-kicker">Request Details</span>
                        <h5 class="modal-title">Reference No. <span id="md-ref"></span></h5>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="service-detail-grid">
                        <section>
                            <div class="service-detail-list">
                                <p><span>Resident Name</span><strong id="md-name"></strong></p>
                                <p><span>Document Type</span><strong id="md-doc"></strong></p>
                                <p><span>Purpose</span><strong id="md-purpose"></strong></p>
                                <p><span>Date Submitted</span><strong id="md-created"></strong></p>
                                <p><span>Contact Number</span><strong id="md-phone"></strong></p>
                                <p><span>Email Address</span><strong id="md-email"></strong></p>
                                <p><span>Address</span><strong id="md-address"></strong></p>
                            </div>
                            <div id="md-extra-container" class="service-extra-box" hidden>
                                <h6>Request Purpose / Details</h6>
                                <div id="md-extra-content"></div>
                            </div>
                        </section>
                        <aside>
                            <div class="service-status-summary">
                                <p><span>Request Status</span><strong id="md-status"></strong></p>
                                <p><span>Payment Status</span><strong id="md-payment"></strong></p>
                                <p><span>Amount</span><strong>PHP <span id="md-fee"></span></strong></p>
                            </div>
                            <div class="service-attachments">
                                <h6>Attachments</h6>
                                <div id="md-attachments"></div>
                            </div>
                            <div class="service-remarks-panel">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <h6 class="mb-0">Remarks</h6>
                                    <span>Newest first</span>
                                </div>
                                <div id="md-remarks-log"></div>
                                <form action="manage_requests.php" method="POST" class="mt-3">
                                    <input type="hidden" name="request_id" id="md-remark-request-id">
                                    <textarea class="form-control" name="remark" rows="3" placeholder="Add processing note..." required></textarea>
                                    <button type="submit" name="add_remark" class="service-save-note-btn">Save Remark</button>
                                </form>
                            </div>
                        </aside>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade service-modal" id="paymentModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <div>
                        <span class="modal-kicker">Payment</span>
                        <h5 class="modal-title">Payment Information</h5>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="service-detail-list compact">
                        <p><span>Payment Status</span><strong id="pay-status"></strong></p>
                        <p><span>Payment Method</span><strong id="pay-method"></strong></p>
                        <p><span>Amount</span><strong>PHP <span id="pay-amount"></span></strong></p>
                        <p><span>Paid By</span><strong id="pay-name"></strong></p>
                        <p><span>Date Paid</span><strong id="pay-date"></strong></p>
                        <p><span>OR Number</span><strong id="pay-or"></strong></p>
                        <p><span>Notes</span><strong id="pay-notes"></strong></p>
                    </div>
                    <div id="pay-actions" class="service-payment-actions"></div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade service-modal" id="updateStatusModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <div>
                        <span class="modal-kicker">Update Status</span>
                        <h5 class="modal-title">Change Request Status</h5>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="service-current-status">
                        <span>Current Status</span>
                        <strong id="us-current"></strong>
                    </div>
                    <form action="manage_requests.php" method="POST">
                        <input type="hidden" name="request_id" id="us-request-id">
                        <div id="us-options" class="service-status-timeline"></div>
                        <button type="submit" name="update_status" value="1" class="service-save-note-btn">Update Status</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../assets/js/admin_notifications.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const escapeHtml = (value) => String(value || 'N/A')
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;')
                .replace(/'/g, '&#039;');

            const rows = Array.from(document.querySelectorAll('.service-request-row'));
            const searchInput = document.getElementById('requestSearch');
            const statusFilter = document.getElementById('statusFilter');
            const paymentFilter = document.getElementById('paymentFilter');
            const startDate = document.getElementById('startDate');
            const endDate = document.getElementById('endDate');
            const emptyState = document.getElementById('requestEmptyState');
            const tableWrap = document.getElementById('requestTableWrap');
            const resultCount = document.getElementById('requestResultCount');
            let activeCategory = 'All Requests';

            function applyFilters() {
                const query = (searchInput.value || '').toLowerCase().trim();
                const status = statusFilter.value;
                const payment = paymentFilter.value;
                const start = startDate.value;
                const end = endDate.value;
                let visible = 0;

                rows.forEach(row => {
                    const matchesCategory = activeCategory === 'All Requests' || row.dataset.category === activeCategory;
                    const matchesSearch = !query || row.dataset.search.includes(query);
                    const matchesStatus = !status || row.dataset.status === status;
                    const matchesPayment = !payment || row.dataset.payment === payment;
                    const matchesStart = !start || row.dataset.date >= start;
                    const matchesEnd = !end || row.dataset.date <= end;
                    const show = matchesCategory && matchesSearch && matchesStatus && matchesPayment && matchesStart && matchesEnd;
                    row.hidden = !show;
                    if (show) visible++;
                });

                emptyState.hidden = visible > 0;
                tableWrap.hidden = visible === 0;
                resultCount.textContent = visible > 0 ? `Showing 1 to ${visible} of ${visible} entries` : 'Showing 0 entries';
            }

            document.querySelectorAll('.service-tab, .service-more-item').forEach(tab => {
                tab.addEventListener('click', function() {
                    activeCategory = this.dataset.category || 'All Requests';
                    document.querySelectorAll('.service-tab').forEach(item => item.classList.remove('is-active'));
                    const matchingTopTab = Array.from(document.querySelectorAll('.service-tab')).find(item => item.dataset.category === activeCategory);
                    if (matchingTopTab) {
                        matchingTopTab.classList.add('is-active');
                    } else {
                        document.querySelector('.service-more-tab').classList.add('is-active');
                    }
                    applyFilters();
                });
            });

            [searchInput, statusFilter, paymentFilter, startDate, endDate].forEach(control => {
                control.addEventListener('input', applyFilters);
                control.addEventListener('change', applyFilters);
            });

            document.querySelectorAll('.status-menu-item[name="update_status"], .service-danger-action').forEach(button => {
                button.addEventListener('click', function(event) {
                    const currentStatus = this.dataset.currentStatus || '';
                    const nextStatus = this.dataset.nextStatus || '';

                    if (currentStatus && nextStatus && !confirm(`Move request from ${currentStatus} to ${nextStatus}?`)) {
                        event.preventDefault();
                    }
                });
            });

            function populateDetails(button) {
                document.getElementById('md-ref').textContent = button.dataset.ref || '';
                document.getElementById('md-name').textContent = button.dataset.name || 'N/A';
                document.getElementById('md-doc').textContent = button.dataset.doc || 'N/A';
                document.getElementById('md-phone').textContent = button.dataset.phone || 'N/A';
                document.getElementById('md-email').textContent = button.dataset.email || 'N/A';
                document.getElementById('md-address').textContent = button.dataset.address || 'N/A';
                document.getElementById('md-purpose').textContent = button.dataset.purpose || 'N/A';
                document.getElementById('md-created').textContent = button.dataset.created || 'N/A';
                document.getElementById('md-status').textContent = button.dataset.status || 'N/A';
                document.getElementById('md-payment').textContent = button.dataset.payment || 'N/A';
                document.getElementById('md-fee').textContent = button.dataset.fee || '0.00';
                document.getElementById('md-remark-request-id').value = button.dataset.requestid || '';

                const attachments = [];
                if (button.dataset.idpath) {
                    attachments.push(`<a href="${escapeHtml(button.dataset.idpath)}" target="_blank"><i class="bi bi-paperclip"></i> Valid ID / Requirement</a>`);
                }
                if (button.dataset.receiptpath) {
                    attachments.push(`<a href="${escapeHtml(button.dataset.receiptpath)}" target="_blank"><i class="bi bi-receipt"></i> Payment Receipt</a>`);
                }
                document.getElementById('md-attachments').innerHTML = attachments.length ? attachments.join('') : '<span class="text-muted small">No uploaded attachments found.</span>';

                const container = document.getElementById('md-extra-container');
                const contentArea = document.getElementById('md-extra-content');
                const remarksArea = document.getElementById('md-remarks-log');
                const remarksTemplate = document.getElementById(`remarks-template-${button.dataset.requestid}`);

                contentArea.innerHTML = '';
                remarksArea.innerHTML = remarksTemplate ? remarksTemplate.innerHTML : '<div class="text-center text-muted border rounded p-3 small">No remarks found.</div>';

                if (button.dataset.tab === 'Business Clearance' && button.dataset.bname) {
                    contentArea.innerHTML = `
                        <p><strong>Trade Name:</strong> ${escapeHtml(button.dataset.bname)}</p>
                        <p><strong>Location:</strong> ${escapeHtml(button.dataset.blocation)}</p>
                        <p><strong>Operator:</strong> ${escapeHtml(button.dataset.boperator)}</p>
                        <p><strong>Business Nature:</strong> ${escapeHtml(button.dataset.bnature)}</p>
                        <p><strong>Business Address:</strong> ${escapeHtml(button.dataset.baddress)}</p>
                    `;
                    container.hidden = false;
                } else if (button.dataset.tab === 'Incident Report' && button.dataset.idate) {
                    contentArea.innerHTML = `
                        <p><strong>Incident Date/Time:</strong> ${escapeHtml(button.dataset.idate)} at ${escapeHtml(button.dataset.itime)}</p>
                        <p><strong>Location:</strong> ${escapeHtml(button.dataset.ilocation)}</p>
                        <p><strong>Involved Persons:</strong> ${escapeHtml(button.dataset.ipersons)}</p>
                        <p><strong>Narrative:</strong> ${escapeHtml(button.dataset.inarrative)}</p>
                        <p><strong>Witness:</strong> ${escapeHtml(button.dataset.iwitness)}</p>
                    `;
                    container.hidden = false;
                } else {
                    container.hidden = true;
                }
            }

            document.querySelectorAll('.view-details-trigger').forEach(btn => {
                btn.addEventListener('click', function() {
                    populateDetails(this);
                });
            });

            rows.forEach(row => {
                row.addEventListener('click', function(event) {
                    if (event.target.closest('button, a, .dropdown-menu, input, select, form')) return;
                    const trigger = this.querySelector('.view-details-trigger');
                    if (trigger) {
                        populateDetails(trigger);
                        bootstrap.Modal.getOrCreateInstance(document.getElementById('requestDetailsModal')).show();
                    }
                });
            });

            document.querySelectorAll('.payment-trigger').forEach(btn => {
                btn.addEventListener('click', function() {
                    document.getElementById('pay-status').textContent = this.dataset.payment || 'N/A';
                    document.getElementById('pay-method').textContent = this.dataset.method || 'N/A';
                    document.getElementById('pay-amount').textContent = this.dataset.amount || '0.00';
                    document.getElementById('pay-name').textContent = this.dataset.name || 'N/A';
                    document.getElementById('pay-date').textContent = this.dataset.rawpayment === 'Verified' || this.dataset.rawpayment === 'Paid at Pickup' ? this.dataset.created : 'Not recorded';
                    document.getElementById('pay-or').textContent = this.dataset.ref || 'N/A';
                    document.getElementById('pay-notes').textContent = this.dataset.receiptpath ? 'Receipt is available for review.' : 'No payment attachment uploaded.';

                    const actions = document.getElementById('pay-actions');
                    const requestId = this.dataset.requestid;
                    const raw = this.dataset.rawpayment;
                    const receipt = this.dataset.receiptpath;
                    let html = '';
                    if (receipt) {
                        html += `<a href="${escapeHtml(receipt)}" target="_blank" class="service-outline-action"><i class="bi bi-eye"></i> Preview Receipt</a>`;
                    }
                    if (raw === 'Receipt Submitted') {
                        html += `<form action="manage_requests.php" method="POST"><input type="hidden" name="request_id" value="${escapeHtml(requestId)}"><input type="hidden" name="payment_status" value="Verified"><button type="submit" name="confirm_payment" value="1">Verify Payment</button></form>`;
                        html += `<form action="manage_requests.php" method="POST"><input type="hidden" name="request_id" value="${escapeHtml(requestId)}"><input type="hidden" name="payment_status" value="Rejected"><button class="danger" type="submit" name="confirm_payment" value="1">Reject Payment</button></form>`;
                    } else if (raw === 'Unpaid') {
                        html += `<form action="manage_requests.php" method="POST"><input type="hidden" name="request_id" value="${escapeHtml(requestId)}"><input type="hidden" name="payment_status" value="Paid at Pickup"><button type="submit" name="confirm_payment" value="1">Mark Paid Upon Pickup</button></form>`;
                    }
                    actions.innerHTML = html || '<span class="text-muted small">No payment action needed.</span>';
                });
            });

            document.querySelectorAll('.update-status-trigger').forEach(btn => {
                btn.addEventListener('click', function() {
                    const options = JSON.parse(this.dataset.options || '[]');
                    document.getElementById('us-current').textContent = this.dataset.current || 'N/A';
                    document.getElementById('us-request-id').value = this.dataset.requestid || '';
                    document.getElementById('us-options').innerHTML = options.map((option, index) => `
                        <label class="service-status-step ${index === 0 ? 'is-current' : ''}">
                            <input type="radio" name="request_status" value="${escapeHtml(option.value)}" ${index === 0 ? 'checked' : 'required'}>
                            <span class="status-dot status-dot-${escapeHtml(option.tone)}"></span>
                            <strong>${escapeHtml(option.label)}</strong>
                            <small>${escapeHtml(option.description)}</small>
                        </label>
                    `).join('');
                });
            });

            document.getElementById('exportRequests').addEventListener('click', function() {
                const visibleRows = rows.filter(row => !row.hidden);
                const lines = [
                    ['Reference No.', 'Resident Name', 'Document Type', 'Date Submitted', 'Request Status', 'Payment']
                ];
                visibleRows.forEach(row => {
                    const cells = Array.from(row.children).slice(0, 6).map(cell => cell.innerText.replace(/\s+/g, ' ').trim());
                    lines.push(cells);
                });
                const csv = lines.map(line => line.map(value => `"${String(value).replace(/"/g, '""')}"`).join(',')).join('\n');
                const blob = new Blob([csv], {
                    type: 'text/csv;charset=utf-8;'
                });
                const url = URL.createObjectURL(blob);
                const link = document.createElement('a');
                link.href = url;
                link.download = 'maki-konek-service-requests.csv';
                link.click();
                URL.revokeObjectURL(url);
            });

            applyFilters();
        });
    </script>
</body>

</html>