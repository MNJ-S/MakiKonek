<?php
session_start();

if (!isset($_SESSION['admin_id'])) {
    header("Location: login_admin.php");
    exit();
}

require_once __DIR__ . '/../includes/db_connect.php';

$success_message = '';
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

    return mysqli_stmt_execute($stmt_update) && mysqli_stmt_affected_rows($stmt_update) > 0;
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

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['complete_request'])) {
    $req_id = (int)$_POST['request_id'];

    mysqli_begin_transaction($conn);
    try {
        if (markCompletedRequest($conn, $req_id)) {
            mysqli_commit($conn);
            $success_message = "Request marked as Completed.";
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
        $current_query = "SELECT status, process_status, document_fee, payment_method, payment_status, payment_receipt_path FROM service_requests WHERE request_id = ? LIMIT 1";
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
                    $success_message = "Request status updated to " . $new_status . ".";
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
            $success_message = "Payment marked as " . $new_payment_status . ".";
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
            $success_message = "Remark saved to the request log.";
        } else {
            $error_message = "Failed to save remark.";
        }
    }
}

$selected_tab = $_GET['tab'] ?? 'Clearance';
$type_map = [
    'Clearance' => 'Barangay Clearance',
    'Indigency' => 'Certificate of Indigency',
    'Residency' => 'Certificate of Residency',
    'Moral' => 'Good Moral Certificate',

    'Business' => 'Business Clearance',
    'Construction' => 'Building/Construction Permit',
    'Cedula' => 'Cedula',

    'Identification' => 'Barangay ID',
    'Incident' => 'Incident Report',
];
$filter_type = $type_map[$selected_tab] ?? 'Barangay Clearance';

$query = "
    SELECT sr.*, dt.name AS document_type, p.first_name, p.last_name, p.mobile_number AS phone, p.house_no, p.street, p.purok_no AS address,
           rb.business_name, rb.business_location, rb.business_operator, rb.business_nature, rb.business_address,
           ri.incident_date, ri.incident_time, ri.incident_location, ri.incident_persons, ri.incident_narrative, ri.witness_name AS incident_witness_name
    FROM service_requests sr
    JOIN document_types dt ON sr.document_type_id = dt.document_type_id
    JOIN users u ON sr.user_id = u.user_id
    JOIN user_profiles p ON u.user_id = p.user_id
    LEFT JOIN request_business_clearances rb ON sr.request_id = rb.request_id
    LEFT JOIN request_incident_reports ri ON sr.request_id = ri.request_id
    WHERE dt.name = ?
    ORDER BY sr.created_at DESC
";

$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, "s", $filter_type);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$requests = [];
while ($request_row = mysqli_fetch_assoc($result)) {
    $requests[] = $request_row;
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
    <link rel="stylesheet" href="../assets/css/admin.css?v=20260608b">
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

        .nav-tabs .nav-link {
            color: #0b6d36;
            font-weight: bold;
            border-radius: 8px 8px 0 0;
        }

        .nav-tabs .nav-link.active {
            background-color: #3f9f25;
            color: white !important;
            border-color: #3f9f25;
        }

        .badge-pending {
            background-color: #ffd54f;
            color: #3c2f00;
        }

        .badge-approved {
            background-color: #22c55e;
            color: white;
        }

        .badge-review {
            background-color: #6366f1;
            color: white;
        }

        .badge-rejected {
            background-color: #ef4444;
            color: white;
        }

        .badge-processing {
            background-color: #0d6efd;
            color: white;
        }

        .badge-ready {
            background-color: #14b8a6;
            color: white;
        }

        .badge-completed {
            background-color: #16a34a;
            color: white;
        }

        .badge-payment-pending {
            background-color: #f59e0b;
            color: #2f2100;
        }

        .badge-muted {
            background-color: #e5e7eb;
            color: #374151;
        }

        .remarks-log {
            display: grid;
            gap: 10px;
            max-height: 230px;
            overflow: auto;
        }

        .remark-entry {
            border: 1px solid #d8efd5;
            border-left: 4px solid #3f9f25;
            border-radius: 8px;
            padding: 10px 12px;
            background: #fbfffb;
        }

        .remark-entry p {
            white-space: pre-wrap;
        }
    </style>
</head>

<body>

    <?php include __DIR__ . '/partials/admin_sidebar.php'; ?>

    <main class="main-content">
        <div class="d-flex justify-content-between align-items-center mb-4 pb-2 border-bottom border-secondary">
            <h2 class="fw-bold page-title"><i class="bi bi-file-earmark-text text-success me-2"></i> Resident Service Requests</h2>
        </div>

        <?php if ($success_message): ?>
            <div class="alert alert-success"><i class="bi bi-check-circle me-2"></i> <?php echo $success_message; ?></div>
        <?php endif; ?>
        <?php if ($error_message): ?>
            <div class="alert alert-danger"><i class="bi bi-exclamation-circle me-2"></i> <?php echo $error_message; ?></div>
        <?php endif; ?>

        <ul class="nav nav-tabs admin-page-tabs mb-4 border-bottom border-success">
            <li class="nav-item"><a class="nav-link <?php echo $selected_tab === 'Clearance' ? 'active' : ''; ?>" href="manage_requests.php?tab=Clearance">Barangay Clearance</a></li>
            <li class="nav-item"><a class="nav-link <?php echo $selected_tab === 'Indigency' ? 'active' : ''; ?>" href="manage_requests.php?tab=Indigency">Indigency</a></li>
            <li class="nav-item"><a class="nav-link <?php echo $selected_tab === 'Residency' ? 'active' : ''; ?>" href="manage_requests.php?tab=Residency">Residency</a></li>
            <li class="nav-item"><a class="nav-link <?php echo $selected_tab === 'Moral' ? 'active' : ''; ?>" href="manage_requests.php?tab=Moral">Good Moral Certificate</a></li>
            <li class="nav-item"><a class="nav-link <?php echo $selected_tab === 'Business' ? 'active' : ''; ?>" href="manage_requests.php?tab=Business">Business Clearance</a></li>
            <li class="nav-item"><a class="nav-link <?php echo $selected_tab === 'Construction' ? 'active' : ''; ?>" href="manage_requests.php?tab=Construction">Construction Permit</a></li>
            <li class="nav-item"><a class="nav-link <?php echo $selected_tab === 'Cedula' ? 'active' : ''; ?>" href="manage_requests.php?tab=Cedula">Cedula</a></li>
            <li class="nav-item"><a class="nav-link <?php echo $selected_tab === 'Identification' ? 'active' : ''; ?>" href="manage_requests.php?tab=Identification">Barangay ID</a></li>
            <li class="nav-item"><a class="nav-link <?php echo $selected_tab === 'Incident' ? 'active' : ''; ?>" href="manage_requests.php?tab=Incident">Incident Report</a></li>
        </ul>

        <div class="custom-card p-4 shadow-sm">
            <div class="table-responsive">
                <table class="table table-light table-hover align-middle border-secondary">
                    <thead class="table-active">
                        <tr>
                            <th>Ref ID</th>
                            <th>Resident Name</th>
                            <th>Purpose</th>
                            <th>Submitted</th>
                            <th class="process-status-cell">Request Status</th>
                            <th class="process-status-cell">Payment</th>
                            <th class="text-center">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (count($requests) > 0): ?>
                            <?php foreach ($requests as $row):
                                $request_status = normalizeRequestStatus($row['status'], $row['process_status']);
                                $payment_status = inferPaymentStatus($row);
                                $status_class = statusBadgeClass($request_status);
                                $payment_class = paymentBadgeClass($payment_status);
                                $allowed_statuses = allowedRequestStatuses($request_status);
                                $current_status_meta = statusMeta($request_status);
                                $display_purpose = formatPurpose($row['purpose']);
                            ?>
                                <tr>
                                    <td class="fw-bold font-monospace"><?php echo htmlspecialchars($row['reference_no']); ?></td>
                                    <td><?php echo htmlspecialchars($row['first_name'] . ' ' . $row['last_name']); ?></td>
                                    <td><small><?php echo htmlspecialchars($display_purpose); ?></small></td>
                                    <td><?php echo date('M d, Y', strtotime($row['created_at'])); ?></td>

                                    <td class="process-status-cell">
                                        <?php if (count($allowed_statuses) <= 1): ?>
                                            <span class="badge <?php echo $status_class; ?> status-lock-badge"><?php echo htmlspecialchars($request_status); ?> - Final</span>
                                        <?php else: ?>
                                            <div class="dropdown status-action-dropdown">
                                                <button class="status-pill status-pill-<?php echo htmlspecialchars($current_status_meta['tone']); ?> dropdown-toggle" type="button" data-bs-toggle="dropdown" data-bs-boundary="viewport" data-bs-display="static" aria-expanded="false">
                                                    <?php echo htmlspecialchars($request_status); ?>
                                                </button>
                                                <div class="dropdown-menu status-change-menu">
                                                    <div class="status-menu-title">Change Status</div>
                                                    <?php foreach ($allowed_statuses as $status_option):
                                                        $option_meta = statusMeta($status_option);
                                                        $is_current_status = $request_status === $status_option;
                                                    ?>
                                                        <?php if ($is_current_status): ?>
                                                            <div class="status-menu-item is-current">
                                                                <span class="status-dot status-dot-<?php echo htmlspecialchars($option_meta['tone']); ?>"></span>
                                                                <span>
                                                                    <strong><?php echo htmlspecialchars($status_option); ?></strong>
                                                                    <small><?php echo htmlspecialchars($option_meta['description']); ?></small>
                                                                </span>
                                                            </div>
                                                        <?php else: ?>
                                                            <form action="manage_requests.php?tab=<?php echo htmlspecialchars($selected_tab); ?>" method="POST">
                                                                <input type="hidden" name="request_id" value="<?php echo $row['request_id']; ?>">
                                                                <input type="hidden" name="request_status" value="<?php echo htmlspecialchars($status_option); ?>">
                                                                <button type="submit"
                                                                    name="update_status"
                                                                    value="1"
                                                                    class="status-menu-item"
                                                                    data-current-status="<?php echo htmlspecialchars($request_status); ?>"
                                                                    data-next-status="<?php echo htmlspecialchars($status_option); ?>">
                                                                    <span class="status-dot status-dot-<?php echo htmlspecialchars($option_meta['tone']); ?>"></span>
                                                                    <span>
                                                                        <strong><?php echo htmlspecialchars($status_option); ?></strong>
                                                                        <small><?php echo htmlspecialchars($option_meta['description']); ?></small>
                                                                    </span>
                                                                </button>
                                                            </form>
                                                        <?php endif; ?>
                                                    <?php endforeach; ?>
                                                </div>
                                            </div>
                                        <?php endif; ?>
                                    </td>

                                    <td class="process-status-cell">
                                        <div class="payment-status-stack">
                                            <span class="badge <?php echo $payment_class; ?>"><?php echo htmlspecialchars($payment_status); ?></span>
                                            <?php if ($payment_status === 'Receipt Submitted'): ?>
                                                <form action="manage_requests.php?tab=<?php echo htmlspecialchars($selected_tab); ?>" method="POST">
                                                    <input type="hidden" name="request_id" value="<?php echo $row['request_id']; ?>">
                                                    <input type="hidden" name="payment_status" value="Verified">
                                                    <button class="btn btn-sm btn-outline-success" type="submit" name="confirm_payment" value="1">Verify Receipt</button>
                                                </form>
                                                <form action="manage_requests.php?tab=<?php echo htmlspecialchars($selected_tab); ?>" method="POST">
                                                    <input type="hidden" name="request_id" value="<?php echo $row['request_id']; ?>">
                                                    <input type="hidden" name="payment_status" value="Rejected">
                                                    <button class="btn btn-sm btn-outline-danger" type="submit" name="confirm_payment" value="1">Reject Receipt</button>
                                                </form>
                                            <?php elseif ($payment_status === 'Unpaid'): ?>
                                                <form action="manage_requests.php?tab=<?php echo htmlspecialchars($selected_tab); ?>" method="POST">
                                                    <input type="hidden" name="request_id" value="<?php echo $row['request_id']; ?>">
                                                    <input type="hidden" name="payment_status" value="Paid at Pickup">
                                                    <button class="btn btn-sm btn-outline-success" type="submit" name="confirm_payment" value="1">Mark Paid</button>
                                                </form>
                                            <?php endif; ?>
                                        </div>
                                    </td>

                                    <td class="text-center">
                                        <div class="table-actions">
                                            <a href="print_document.php?req_id=<?php echo $row['request_id']; ?>" target="_blank" class="btn btn-sm btn-outline-dark" title="Print Document">
                                                Print
                                            </a>

                                            <button class="btn btn-sm btn-outline-primary view-details-trigger"
                                                data-bs-toggle="modal" data-bs-target="#requestDetailsModal"
                                                data-ref="<?php echo htmlspecialchars($row['reference_no']); ?>"
                                                data-name="<?php echo htmlspecialchars($row['first_name'] . ' ' . $row['last_name']); ?>"
                                                data-doc="<?php echo htmlspecialchars($row['document_type']); ?>"
                                                data-phone="<?php echo htmlspecialchars($row['phone']); ?>"
                                                data-address="<?php echo htmlspecialchars($row['address']); ?>"
                                                data-purpose="<?php echo htmlspecialchars($row['purpose']); ?>"
                                                data-fee="<?php echo htmlspecialchars($row['document_fee']); ?>"
                                                data-idpath="../<?php echo htmlspecialchars($row['id_path']); ?>"
                                                data-requestid="<?php echo (int)$row['request_id']; ?>"
                                                data-tab="<?php echo htmlspecialchars($selected_tab); ?>"
                                                data-bname="<?php echo htmlspecialchars($row['business_name'] ?? ''); ?>"
                                                data-blocation="<?php echo htmlspecialchars($row['business_location'] ?? ''); ?>"
                                                data-boperator="<?php echo htmlspecialchars($row['business_operator'] ?? ''); ?>"
                                                data-bnature="<?php echo htmlspecialchars($row['business_nature'] ?? ''); ?>"
                                                data-baddress="<?php echo htmlspecialchars($row['business_address'] ?? ''); ?>"
                                                data-idate="<?php echo htmlspecialchars($row['incident_date'] ?? ''); ?>"
                                                data-itime="<?php echo htmlspecialchars($row['incident_time'] ?? ''); ?>"
                                                data-ilocation="<?php echo htmlspecialchars($row['incident_location'] ?? ''); ?>"
                                                data-ipersons="<?php echo htmlspecialchars($row['incident_persons'] ?? ''); ?>"
                                                data-inarrative="<?php echo htmlspecialchars($row['incident_narrative'] ?? ''); ?>"
                                                data-iwitness="<?php echo htmlspecialchars($row['incident_witness_name'] ?? ''); ?>">
                                                View Details
                                            </button>

                                            <div class="dropdown">
                                                <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">More</button>
                                                <ul class="dropdown-menu">
                                                    <?php if ($request_status !== 'Under Review' && in_array('Under Review', $allowed_statuses, true)): ?>
                                                        <li>
                                                            <form action="manage_requests.php?tab=<?php echo htmlspecialchars($selected_tab); ?>" method="POST">
                                                                <input type="hidden" name="request_id" value="<?php echo $row['request_id']; ?>">
                                                                <input type="hidden" name="request_status" value="Under Review">
                                                                <button type="submit" name="update_status" value="1" class="dropdown-item text-success">Move to Under Review</button>
                                                            </form>
                                                        </li>
                                                    <?php endif; ?>
                                                    <?php if ($request_status !== 'Rejected' && in_array('Rejected', $allowed_statuses, true)): ?>
                                                        <li>
                                                            <form action="manage_requests.php?tab=<?php echo htmlspecialchars($selected_tab); ?>" method="POST">
                                                                <input type="hidden" name="request_id" value="<?php echo $row['request_id']; ?>">
                                                                <input type="hidden" name="request_status" value="Rejected">
                                                                <button type="submit" name="update_status" value="1" class="dropdown-item text-danger">Reject Request</button>
                                                            </form>
                                                        </li>
                                                    <?php endif; ?>
                                                    <?php if (!in_array('Under Review', $allowed_statuses, true) && !in_array('Rejected', $allowed_statuses, true)): ?>
                                                        <li><span class="dropdown-item-text text-muted">No quick actions</span></li>
                                                    <?php endif; ?>
                                                </ul>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="7" class="text-center text-muted py-4">No active records found in this queue tab.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
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

    <div class="modal fade" id="requestDetailsModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-dark text-white">
                    <h5 class="modal-title fw-bold">Request Summary: <span id="md-ref"></span></h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p><strong>Resident Name:</strong> <span id="md-name"></span></p>
                    <p><strong>Document Requested:</strong> <span id="md-doc"></span></p>
                    <p><strong>Mobile Number:</strong> <span id="md-phone"></span></p>
                    <p><strong>Home Address:</strong> <span id="md-address"></span></p>
                    <p><strong>Purpose:</strong> <span id="md-purpose"></span></p>
                    <p><strong>Total Cost Fee:</strong> <span id="md-fee" class="fw-bold text-success"></span></p>

                    <div id="md-extra-container" class="mt-3 p-3 bg-light border rounded style-box" style="display:none;">
                        <h6 class="fw-bold text-success border-bottom pb-1 mb-2">Form Specific Requirements Data:</h6>
                        <div id="md-extra-content" class="small"></div>
                    </div>

                    <hr>
                    <h6>Uploaded Attachment Verification:</h6>
                    <a id="md-download-link" href="#" target="_blank" class="btn btn-sm btn-outline-secondary w-100 mb-2"><i class="bi bi-download"></i> View Full File Asset</a>

                    <hr>
                    <div class="row g-3">
                        <div class="col-md-5">
                            <h6 class="fw-bold text-success">Add Note / Remarks</h6>
                            <form action="manage_requests.php?tab=<?php echo htmlspecialchars($selected_tab); ?>" method="POST">
                                <input type="hidden" name="request_id" id="md-remark-request-id">
                                <textarea class="form-control mb-2" name="remark" rows="5" placeholder="Add processing notes, missing requirements, pickup reminders, or release remarks..." required></textarea>
                                <button type="submit" name="add_remark" class="btn btn-success btn-sm w-100">
                                    <i class="bi bi-journal-plus me-1"></i> Save Remark
                                </button>
                            </form>
                        </div>
                        <div class="col-md-7">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <h6 class="fw-bold mb-0">Remark History</h6>
                                <span class="badge bg-light text-dark border">Newest first</span>
                            </div>
                            <div id="md-remarks-log"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const escapeHtml = (value) => String(value || 'N/A')
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;')
                .replace(/'/g, '&#039;');

            document.querySelectorAll('.status-menu-item[name="update_status"]').forEach(button => {
                button.addEventListener('click', function(event) {
                    const currentStatus = this.dataset.currentStatus || '';
                    const nextStatus = this.dataset.nextStatus || '';

                    if (!confirm(`Move request from ${currentStatus} to ${nextStatus}?`)) {
                        event.preventDefault();
                    }
                });
            });

            document.querySelectorAll('.view-details-trigger').forEach(btn => {
                btn.addEventListener('click', function() {
                    document.getElementById('md-ref').textContent = this.dataset.ref;
                    document.getElementById('md-name').textContent = this.dataset.name;
                    document.getElementById('md-doc').textContent = this.dataset.doc;
                    document.getElementById('md-phone').textContent = this.dataset.phone;
                    document.getElementById('md-address').textContent = this.dataset.address;
                    document.getElementById('md-purpose').textContent = this.dataset.purpose;
                    document.getElementById('md-fee').textContent = this.dataset.fee;
                    document.getElementById('md-download-link').href = this.dataset.idpath;
                    document.getElementById('md-remark-request-id').value = this.dataset.requestid;

                    const tabType = this.dataset.tab;
                    const container = document.getElementById('md-extra-container');
                    const contentArea = document.getElementById('md-extra-content');
                    const remarksArea = document.getElementById('md-remarks-log');
                    const remarksTemplate = document.getElementById(`remarks-template-${this.dataset.requestid}`);

                    contentArea.innerHTML = '';
                    remarksArea.innerHTML = remarksTemplate ? remarksTemplate.innerHTML : '<div class="text-center text-muted border rounded p-3 small">No remarks found.</div>';

                    if (tabType === 'Business' && this.dataset.bname) {
                        contentArea.innerHTML = `
                            <p class="mb-1"><strong>Trade Name:</strong> ${escapeHtml(this.dataset.bname)}</p>
                            <p class="mb-1"><strong>Location:</strong> ${escapeHtml(this.dataset.blocation)}</p>
                            <p class="mb-1"><strong>Manager:</strong> ${escapeHtml(this.dataset.boperator)}</p>
                            <p class="mb-1"><strong>Business Nature:</strong> ${escapeHtml(this.dataset.bnature)}</p>
                            <p class="mb-1"><strong>Business Address:</strong> ${escapeHtml(this.dataset.baddress)}</p>
                        `;
                        container.style.display = 'block';
                    } else if (tabType === 'Incident' && this.dataset.idate) {
                        contentArea.innerHTML = `
                            <p class="mb-1"><strong>Incident Date/Time:</strong> ${escapeHtml(this.dataset.idate)} @ ${escapeHtml(this.dataset.itime)}</p>
                            <p class="mb-1"><strong>Location:</strong> ${escapeHtml(this.dataset.ilocation)}</p>
                            <p class="mb-1"><strong>Involved Profiles:</strong> ${escapeHtml(this.dataset.ipersons)}</p>
                            <p class="mb-1"><strong>Narrative Summary:</strong> ${escapeHtml(this.dataset.inarrative)}</p>
                            <p class="mb-1"><strong>Witness Profile Name:</strong> ${escapeHtml(this.dataset.iwitness)}</p>
                        `;
                        container.style.display = 'block';
                    } else {
                        container.style.display = 'none';
                    }
                });
            });
        });
    </script>
</body>

</html>
