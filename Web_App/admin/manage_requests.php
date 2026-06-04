<?php
session_start();

if (!isset($_SESSION['admin_id'])) {
    header("Location: login_admin.php");
    exit();
}

require_once __DIR__ . '/../includes/db_connect.php';

$success_message = '';
$error_message = '';

// --- HANDLE ACTION STATE CHANGERS ---
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_status'])) {
    $req_id = (int)$_POST['request_id'];
    $new_status = mysqli_real_escape_string($conn, $_POST['status_value']);

    $update = "UPDATE service_requests SET status = ? WHERE request_id = ?";
    $stmt = mysqli_prepare($conn, $update);
    mysqli_stmt_bind_param($stmt, "si", $new_status, $req_id);
    if (mysqli_stmt_execute($stmt)) {
        $success_message = "Request state updated to " . $new_status . " successfully.";
    } else {
        $error_message = "Failed to update target row state records.";
    }
}

// Track current type configuration filter tab
$selected_tab = $_GET['tab'] ?? 'Clearance';

// Map tab inputs directly to matching string matches inside the database
$type_map = [
    'Clearance' => 'Barangay Clearance',
    'Indigency' => 'Certificate of Indigency',
    'Residency' => 'Certificate of Residency',
    'Business' => 'Business Clearance',
    'Incident' => 'Incident Report',
    'Identification' => 'Barangay ID',
    'Cedula' => 'Cedula',
    'Moral' => 'Good Moral Certificate',
    'Construction' => 'Building/Construction Permit'
];

$filter_type = $type_map[$selected_tab] ?? 'Barangay Clearance';

if ($selected_tab === 'Others') {
    $query = "SELECT * FROM service_requests WHERE document_type NOT IN ('Barangay Clearance', 'Certificate of Indigency', 'Certificate of Residency', 'Business Clearance', 'Incident Report', 'Barangay ID', 'Cedula', 'Good Moral Certificate', 'Building/Construction Permit') ORDER BY created_at DESC";
} else {
    $query = "SELECT * FROM service_requests WHERE document_type = ? ORDER BY created_at DESC";
}

$stmt = mysqli_prepare($conn, $query);
if ($selected_tab !== 'Others') {
    mysqli_stmt_bind_param($stmt, "s", $filter_type);
}
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
?>
<!DOCTYPE html>
<html lang="en" data-bs-theme="light">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Service Requests | MakiKonek</title>
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

        .badge-rejected {
            background-color: #ef4444;
            color: white;
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

        <ul class="nav nav-tabs mb-4 border-bottom border-success">
            <li class="nav-item"><a class="nav-link <?php echo $selected_tab === 'Clearance' ? 'active' : ''; ?>" href="manage_requests.php?tab=Clearance">Barangay Clearance</a></li>
            <li class="nav-item"><a class="nav-link <?php echo $selected_tab === 'Indigency' ? 'active' : ''; ?>" href="manage_requests.php?tab=Indigency">Indigency</a></li>
            <li class="nav-item"><a class="nav-link <?php echo $selected_tab === 'Residency' ? 'active' : ''; ?>" href="manage_requests.php?tab=Residency">Residency</a></li>
            <li class="nav-item"><a class="nav-link <?php echo $selected_tab === 'Business' ? 'active' : ''; ?>" href="manage_requests.php?tab=Business">Business Clearance</a></li>
            <li class="nav-item"><a class="nav-link <?php echo $selected_tab === 'Incident' ? 'active' : ''; ?>" href="manage_requests.php?tab=Incident">Incident Report</a></li>
            <li class="nav-item"><a class="nav-link <?php echo $selected_tab === 'Identification' ? 'active' : ''; ?>" href="manage_requests.php?tab=Identification">Barangay ID</a></li>
            <li class="nav-item"><a class="nav-link <?php echo $selected_tab === 'Cedula' ? 'active' : ''; ?>" href="manage_requests.php?tab=Cedula">Cedula</a></li>
            <li class="nav-item"><a class="nav-link <?php echo $selected_tab === 'Moral' ? 'active' : ''; ?>" href="manage_requests.php?tab=Moral">Good Moral Certificate</a></li>
            <li class="nav-item"><a class="nav-link <?php echo $selected_tab === 'Construction' ? 'active' : ''; ?>" href="manage_requests.php?tab=Construction">Construction Permit</a></li>
        </ul>

        <!-- BARANGAY CLEARANCE -->
        <!-- CERTIFICATE OF RESIDENCY -->
        <!-- BUSINESS CLEARANCE -->
        <!-- INCIDENT REPORT -->
        <!-- BARANGAY IDENTIFICATION -->
        <!-- CEDULA -->
        <!-- GOOD MORAL CERTIFICATE -->
        <!-- CONSTRUCTION-->

        <!-- CERTIFICATE OF INDIGENCY -->
        <div class="custom-card p-4 shadow-sm">
            <div class="table-responsive">
                <table class="table table-light table-hover align-middle border-secondary">
                    <thead class="table-active">
                        <tr>
                            <th>Ref ID</th>
                            <th>Resident Name</th>
                            <th>Purpose</th>
                            <th>Date Submitted</th>
                            <th>Status</th>
                            <th class="text-center">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (mysqli_num_rows($result) > 0): ?>
                            <?php while ($row = mysqli_fetch_assoc($result)):
                                $status_class = 'badge-pending';
                                if ($row['status'] === 'APPROVED') $status_class = 'badge-approved';
                                if ($row['status'] === 'REJECTED') $status_class = 'badge-rejected';
                            ?>
                                <tr>
                                    <td class="fw-bold font-monospace"><?php echo $row['reference_no']; ?></td>
                                    <td class="fw-bold"><?php echo htmlspecialchars($row['first_name'] . ' ' . $row['last_name']); ?></td>
                                    <td><small><?php echo htmlspecialchars($row['purpose']); ?></small></td>
                                    <td class="small"><?php echo date("M d, Y", strtotime($row['created_at'])); ?></td>
                                    <td><span class="badge <?php echo $status_class; ?>"><?php echo $row['status']; ?></span></td>
                                    <td class="text-center">
                                        <div class="d-flex justify-content-center gap-1">
                                            <a href="print_document.php?req_id=<?php echo $row['request_id']; ?>" target="_blank" class="btn btn-sm btn-outline-dark" title="Edit & Print Document">
                                                <i class="bi bi-printer"></i>
                                            </a>
                                            <button class="btn btn-sm btn-outline-primary view-details-trigger"
                                                data-bs-toggle="modal" data-bs-target="#requestDetailsModal"
                                                data-ref="<?php echo $row['reference_no']; ?>"
                                                data-name="<?php echo htmlspecialchars($row['first_name'] . ' ' . $row['last_name']); ?>"
                                                data-doc="<?php echo htmlspecialchars($row['document_type']); ?>"
                                                data-phone="<?php echo htmlspecialchars($row['phone']); ?>"
                                                data-address="<?php echo htmlspecialchars($row['address']); ?>"
                                                data-purpose="<?php echo htmlspecialchars($row['purpose']); ?>"
                                                data-fee="<?php echo htmlspecialchars($row['document_fee']); ?>"
                                                data-idpath="../<?php echo $row['id_path']; ?>">
                                                <i class="bi bi-eye"></i>
                                            </button>

                                            <form action="manage_requests.php?tab=<?php echo $selected_tab; ?>" method="POST" class="d-inline">
                                                <input type="hidden" name="request_id" value="<?php echo $row['request_id']; ?>">
                                                <input type="hidden" name="status_value" value="APPROVED">
                                                <button type="submit" name="update_status" class="btn btn-sm btn-success" title="Approve"><i class="bi bi-check-lg"></i></button>
                                            </form>
                                            <form action="manage_requests.php?tab=<?php echo $selected_tab; ?>" method="POST" class="d-inline">
                                                <input type="hidden" name="request_id" value="<?php echo $row['request_id']; ?>">
                                                <input type="hidden" name="status_value" value="REJECTED">
                                                <button type="submit" name="update_status" class="btn btn-sm btn-danger" title="Reject"><i class="bi bi-x-lg"></i></button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
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

    <div class="modal fade" id="requestDetailsModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-md modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-dark text-white">
                    <h5 class="modal-title fw-bold">Request Summary: <span id="md-ref"></span></h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-disconnect data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p><strong>Resident Name:</strong> <span id="md-name"></span></p>
                    <p><strong>Document Requested:</strong> <span id="md-doc"></span></p>
                    <p><strong>Mobile Number:</strong> <span id="md-phone"></span></p>
                    <p><strong>Home Address:</strong> <span id="md-address"></span></p>
                    <p><strong>Purpose:</strong> <span id="md-purpose"></span></p>
                    <p><strong>Total Cost Fee:</strong> <span id="md-fee" class="fw-bold text-success"></span></p>
                    <hr>
                    <h6>Uploaded Attachment Verification:</h6>
                    <a id="md-download-link" href="#" target="_blank" class="btn btn-sm btn-outline-secondary w-100 mb-2"><i class="bi bi-download"></i> View Full File Asset</a>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
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
                });
            });
        });
    </script>
</body>

</html>
