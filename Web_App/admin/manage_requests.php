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
$process_statuses = ['PROCESSING', 'READY FOR PICKUP', 'COMPLETED'];

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

function archiveCompletedRequest(mysqli $conn, int $req_id): bool
{
    $fetch_query = "
        SELECT sr.user_id, sr.reference_no, sr.purpose, sr.document_fee, sr.created_at, dt.name AS document_type_name
        FROM service_requests sr
        JOIN document_types dt ON sr.document_type_id = dt.document_type_id
        WHERE sr.request_id = ?";
    $stmt_fetch = mysqli_prepare($conn, $fetch_query);
    mysqli_stmt_bind_param($stmt_fetch, "i", $req_id);
    mysqli_stmt_execute($stmt_fetch);
    $req_data = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt_fetch));

    if (!$req_data) {
        return false;
    }

    $insert_query = "INSERT INTO completed_requests (original_request_id, user_id, document_type_name, reference_no, purpose, document_fee, requested_at, completed_at) VALUES (?, ?, ?, ?, ?, ?, ?, NOW())";
    $stmt_insert = mysqli_prepare($conn, $insert_query);
    mysqli_stmt_bind_param($stmt_insert, "iissdss", $req_id, $req_data['user_id'], $req_data['document_type_name'], $req_data['reference_no'], $req_data['purpose'], $req_data['document_fee'], $req_data['created_at']);
    mysqli_stmt_execute($stmt_insert);

    $delete_query = "DELETE FROM service_requests WHERE request_id = ?";
    $stmt_delete = mysqli_prepare($conn, $delete_query);
    mysqli_stmt_bind_param($stmt_delete, "i", $req_id);

    return mysqli_stmt_execute($stmt_delete);
}

function statusBadgeClass(string $status): string
{
    $normalized = strtoupper($status);
    if ($normalized === 'APPROVED') return 'badge-approved';
    if ($normalized === 'REJECTED') return 'badge-rejected';
    return 'badge-pending';
}

function processBadgeClass(string $status): string
{
    $normalized = strtoupper($status);
    if ($normalized === 'PROCESSING') return 'badge-processing';
    if ($normalized === 'READY FOR PICKUP') return 'badge-ready';
    if ($normalized === 'COMPLETED') return 'badge-completed';
    return 'badge-pending';
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['complete_request'])) {
    $req_id = (int)$_POST['request_id'];

    mysqli_begin_transaction($conn);
    try {
        if (archiveCompletedRequest($conn, $req_id)) {
            mysqli_commit($conn);
            $success_message = "Request marked as COMPLETED and moved to archives.";
        } else {
            mysqli_rollback($conn);
            $error_message = "Request could not be found.";
        }
    } catch (Exception $e) {
        mysqli_rollback($conn);
        $error_message = "System error: Could not complete request.";
    }
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_status'])) {
    $req_id = (int)$_POST['request_id'];
    $new_status = mysqli_real_escape_string($conn, $_POST['update_status']);

    $process_init = ($new_status === 'APPROVED') ? 'PROCESSING' : 'Pending';

    $update_query = "UPDATE service_requests SET status = ?, process_status = ? WHERE request_id = ?";
    $stmt = mysqli_prepare($conn, $update_query);
    mysqli_stmt_bind_param($stmt, "ssi", $new_status, $process_init, $req_id);

    if (mysqli_stmt_execute($stmt)) {
        $success_message = "Request " . strtolower($new_status) . " successfully.";
    } else {
        $error_message = "Failed to update request status.";
    }
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_process'])) {
    $req_id = (int)$_POST['request_id'];
    $new_process = strtoupper(trim($_POST['process_value'] ?? ''));

    if (!in_array($new_process, $process_statuses, true)) {
        $error_message = "Invalid process status selected.";
    } elseif ($new_process === 'COMPLETED') {
        mysqli_begin_transaction($conn);
        try {
            if (archiveCompletedRequest($conn, $req_id)) {
                mysqli_commit($conn);
                $success_message = "Request marked as COMPLETED and moved to archives.";
            } else {
                mysqli_rollback($conn);
                $error_message = "Request could not be found.";
            }
        } catch (Exception $e) {
            mysqli_rollback($conn);
            $error_message = "System error: Could not complete request.";
        }
    } else {
        $update_query = "UPDATE service_requests SET status = 'APPROVED', process_status = ? WHERE request_id = ?";
        $stmt = mysqli_prepare($conn, $update_query);
        mysqli_stmt_bind_param($stmt, "si", $new_process, $req_id);

        if (mysqli_stmt_execute($stmt)) {
            $success_message = "Process status updated to " . ucwords(strtolower($new_process)) . ".";
        } else {
            $error_message = "Failed to update process status.";
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
                            <th>Status</th>
                            <th class="process-status-cell">Process Status</th>
                            <th class="text-center">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (count($requests) > 0): ?>
                            <?php foreach ($requests as $row):
                                $status_class = statusBadgeClass($row['status']);
                                $process_class = processBadgeClass($row['process_status']);
                            ?>
                                <tr>
                                    <td class="fw-bold font-monospace"><?php echo htmlspecialchars($row['reference_no']); ?></td>
                                    <td><?php echo htmlspecialchars($row['first_name'] . ' ' . $row['last_name']); ?></td>
                                    <td><small><?php echo htmlspecialchars($row['purpose']); ?></small></td>
                                    <td><span class="badge <?php echo $status_class; ?>"><?php echo htmlspecialchars($row['status']); ?></span></td>

                                    <td class="process-status-cell">
                                        <?php if (strtoupper($row['status']) === 'APPROVED'): ?>
                                            <form class="process-status-control" action="manage_requests.php?tab=<?php echo htmlspecialchars($selected_tab); ?>" method="POST">
                                                <input type="hidden" name="request_id" value="<?php echo $row['request_id']; ?>">
                                                <select name="process_value" class="form-select form-select-sm <?php echo $process_class; ?>" onchange="this.form.submit()">
                                                    <option value="PROCESSING" <?php echo ($row['process_status'] === 'PROCESSING') ? 'selected' : ''; ?>>Processing</option>
                                                    <option value="READY FOR PICKUP" <?php echo ($row['process_status'] === 'READY FOR PICKUP') ? 'selected' : ''; ?>>Ready for Pickup</option>
                                                    <option value="COMPLETED">Completed</option>
                                                </select>
                                                <input type="hidden" name="update_process" value="1">
                                            </form>
                                        <?php else: ?>
                                            <span class="badge <?php echo $process_class; ?>"><?php echo htmlspecialchars($row['process_status']); ?></span>
                                        <?php endif; ?>
                                    </td>

                                    <td class="text-center">
                                        <div class="table-actions">
                                            <a href="print_document.php?req_id=<?php echo $row['request_id']; ?>" target="_blank" class="btn btn-sm btn-outline-dark" title="Print Document">
                                                <i class="bi bi-printer"></i>
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
                                                <i class="bi bi-eye"></i>
                                            </button>

                                            <div class="dropdown">
                                                <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown"><i class="bi bi-three-dots-vertical"></i></button>
                                                <ul class="dropdown-menu">
                                                    <li>
                                                        <form action="manage_requests.php?tab=<?php echo htmlspecialchars($selected_tab); ?>" method="POST">
                                                            <input type="hidden" name="request_id" value="<?php echo $row['request_id']; ?>">
                                                            <button type="submit" name="update_status" value="APPROVED" class="dropdown-item text-success"><i class="bi bi-check-lg"></i> Approve</button>
                                                        </form>
                                                    </li>
                                                    <li>
                                                        <form action="manage_requests.php?tab=<?php echo htmlspecialchars($selected_tab); ?>" method="POST">
                                                            <input type="hidden" name="request_id" value="<?php echo $row['request_id']; ?>">
                                                            <button type="submit" name="update_status" value="REJECTED" class="dropdown-item text-danger"><i class="bi bi-x-lg"></i> Reject</button>
                                                        </form>
                                                    </li>
                                                </ul>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="6" class="text-center text-muted py-4">No active records found in this queue tab.</td>
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
