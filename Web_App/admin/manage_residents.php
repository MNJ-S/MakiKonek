<?php
session_start();

if (!isset($_SESSION['admin_id'])) {
    header("Location: login_admin.php");
    exit();
}

require_once __DIR__ . '/../includes/db_connect.php';

// EXTENDED QUERY: Fetches all structural profile and identity elements from both tables
$query = "
    SELECT u.user_id, u.email, u.created_at, p.* 
    FROM users u
    INNER JOIN user_profiles p ON u.user_id = p.user_id
    ORDER BY u.created_at DESC
";
$result = mysqli_query($conn, $query);
?>
<!DOCTYPE html>
<html lang="en" data-bs-theme="light">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Residents | MakiKonek</title>
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

        .modal-profile-header {
            background: linear-gradient(135deg, #102b21 0%, #2e6f40 100%);
            color: white;
        }
    </style>
</head>

<body>

    <?php include __DIR__ . '/partials/admin_sidebar.php'; ?>

    <main class="main-content">
        <div class="d-flex justify-content-between align-items-center mb-4 pb-2 border-bottom border-secondary">
            <h2 class="fw-bold page-title"><i class="bi bi-people text-success me-2"></i> Resident Directory</h2>
        </div>

        <div class="custom-card p-4 shadow-sm">
            <div class="table-responsive">
                <table class="table table-light table-hover align-middle border-secondary">
                    <thead class="table-active">
                        <tr>
                            <th>Full Name</th>
                            <th>Purok Address</th>
                            <th>Contact Number</th>
                            <th>Email Address</th>
                            <th>Registration Date</th>
                            <th class="text-center">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (mysqli_num_rows($result) > 0): ?>
                            <?php while ($row = mysqli_fetch_assoc($result)):
                                // Cleanly combine full matching name variables
                                $full_name = $row['first_name'] . ' ' . (!empty($row['middle_name']) ? substr($row['middle_name'], 0, 1) . '. ' : '') . $row['last_name'] . (!empty($row['suffix']) ? ' ' . $row['suffix'] : '');
                                $avatar = !empty($row['avatar_path']) ? '../' . $row['avatar_path'] : '../assets/img/avatar-placeholder.png';
                            ?>
                                <tr>
                                    <td class="fw-bold"><?php echo htmlspecialchars($full_name); ?></td>
                                    <td>Purok <?php echo htmlspecialchars($row['purok_no']); ?></td>
                                    <td><?php echo htmlspecialchars($row['mobile_number']); ?></td>
                                    <td class="text-muted"><?php echo htmlspecialchars($row['email']); ?></td>
                                    <td class="small"><?php echo date("M d, Y", strtotime($row['created_at'])); ?></td>
                                    <td class="text-center">
                                        <!-- VIEW INJECTION GATEWAY: Pass row details over into data objects -->
                                        <button class="btn btn-sm btn-primary view-profile-trigger"
                                            title="View Full Profile"
                                            data-name="<?php echo htmlspecialchars($full_name); ?>"
                                            data-avatar="<?php echo htmlspecialchars($avatar); ?>"
                                            data-email="<?php echo htmlspecialchars($row['email']); ?>"
                                            data-mobile="<?php echo htmlspecialchars($row['mobile_number']); ?>"
                                            data-sex="<?php echo htmlspecialchars($row['sex'] ?? 'N/A'); ?>"
                                            data-civil="<?php echo htmlspecialchars($row['civil_status'] ?? 'N/A'); ?>"
                                            data-birthdate="<?php echo htmlspecialchars(!empty($row['birth_date']) ? date('M d, Y', strtotime($row['birth_date'])) : 'N/A'); ?>"
                                            data-birthplace="<?php echo htmlspecialchars($row['birth_place'] ?? 'N/A'); ?>"
                                            data-religion="<?php echo htmlspecialchars($row['religion'] ?? 'N/A'); ?>"
                                            data-nationality="<?php echo htmlspecialchars($row['nationality'] ?? 'N/A'); ?>"
                                            data-address="<?php echo htmlspecialchars("House " . ($row['house_no'] ?? '') . ", " . ($row['street'] ?? '') . ", Purok " . $row['purok_no'] . " " . ($row['subdivision'] ?? '')); ?>"
                                            data-nationalid="<?php echo htmlspecialchars($row['national_id'] ?? 'N/A'); ?>"
                                            data-philhealth="<?php echo htmlspecialchars($row['philhealth_no'] ?? 'N/A'); ?>"
                                            data-voters="<?php echo htmlspecialchars($row['voters_id'] ?? 'N/A'); ?>"
                                            data-sss="<?php echo htmlspecialchars($row['sss_no'] ?? 'N/A'); ?>"
                                            data-tin="<?php echo htmlspecialchars($row['tin_no'] ?? 'N/A'); ?>"
                                            data-years="<?php echo htmlspecialchars($row['years_residency'] ?? 'N/A'); ?>"
                                            data-employed="<?php echo htmlspecialchars($row['employed_status'] ?? 'N/A'); ?>"
                                            data-pagibig="<?php echo htmlspecialchars($row['pagibig_no'] ?? 'N/A'); ?>"
                                            data-ename="<?php echo htmlspecialchars($row['emergency_name'] ?? 'N/A'); ?>"
                                            data-erel="<?php echo htmlspecialchars($row['emergency_relationship'] ?? 'N/A'); ?>"
                                            data-ephone="<?php echo htmlspecialchars($row['emergency_contact'] ?? 'N/A'); ?>"
                                            data-eaddress="<?php echo htmlspecialchars($row['emergency_address'] ?? 'N/A'); ?>">
                                            <i class="bi bi-eye"></i> View
                                        </button>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="6" class="text-center text-muted py-4">No residents registered yet.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>

    <!-- VIEW MODAL SCREEN -->
    <div class="modal fade" id="residentProfileModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content shadow-lg" style="border:none; border-radius:12px; overflow:hidden;">
                <div class="modal-header modal-profile-header p-4">
                    <div class="d-flex align-items-center gap-3">
                        <img id="m-avatar" src="" alt="Avatar" style="width: 75px; height: 75px; object-fit: cover; border-radius: 50%; border: 3px solid rgba(255,255,255,0.4);">
                        <div>
                            <h4 class="modal-title fw-bold mb-0" id="m-name">Resident Name</h4>
                            <span class="badge bg-success border border-light mt-1">Verified Resident</span>
                        </div>
                    </div>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4 bg-light">
                    <div class="row g-4">

                        <div class="col-md-7">
                            <div class="card p-3 mb-3 border-0 shadow-sm">
                                <h6 class="text-success fw-bold border-bottom pb-2 mb-2"><i class="bi bi-person-lines-fill me-1"></i> Personal Profile Information</h6>
                                <table class="table table-sm table-borderless mb-0 small">
                                    <tr>
                                        <td class="text-muted" style="width:35%;">Sex / Status:</td>
                                        <td class="fw-bold"><span id="m-sex"></span> / <span id="m-civil"></span></td>
                                    </tr>
                                    <tr>
                                        <td class="text-muted">Birth Date:</td>
                                        <td class="fw-bold" id="m-birthdate"></td>
                                    </tr>
                                    <tr>
                                        <td class="text-muted">Birth Place:</td>
                                        <td id="m-birthplace"></td>
                                    </tr>
                                    <tr>
                                        <td class="text-muted">Religion:</td>
                                        <td id="m-religion"></td>
                                    </tr>
                                    <tr>
                                        <td class="text-muted">Nationality:</td>
                                        <td id="m-nationality"></td>
                                    </tr>
                                    <tr>
                                        <td class="text-muted">Contact No:</td>
                                        <td class="fw-bold text-dark" id="m-mobile"></td>
                                    </tr>
                                    <tr>
                                        <td class="text-muted">Email Link:</td>
                                        <td class="text-primary" id="m-email"></td>
                                    </tr>
                                </table>
                            </div>
                            <div class="card p-3 border-0 shadow-sm">
                                <h6 class="text-success fw-bold border-bottom pb-2 mb-2"><i class="bi bi-geo-alt-fill me-1"></i> Residential Address</h6>
                                <p class="mb-0 small fw-bold text-secondary" id="m-address"></p>
                            </div>
                        </div>

                        <div class="col-md-5">
                            <div class="card p-3 mb-3 border-0 shadow-sm bg-white">
                                <h6 class="text-success fw-bold border-bottom pb-2 mb-2"><i class="bi bi-card-checklist me-1"></i> Government Identification</h6>
                                <table class="table table-sm table-borderless mb-0 style-table small" style="font-size:11px;">
                                    <tr>
                                        <td class="text-muted">National ID:</td>
                                        <td class="font-monospace" id="m-nationalid"></td>
                                    </tr>
                                    <tr>
                                        <td class="text-muted">PhilHealth:</td>
                                        <td class="font-monospace" id="m-philhealth"></td>
                                    </tr>
                                    <tr>
                                        <td class="text-muted">Voter's ID:</td>
                                        <td class="font-monospace" id="m-voters"></td>
                                    </tr>
                                    <tr>
                                        <td class="text-muted">SSS Number:</td>
                                        <td class="font-monospace" id="m-sss"></td>
                                    </tr>
                                    <tr>
                                        <td class="text-muted">TIN Number:</td>
                                        <td class="font-monospace" id="m-tin"></td>
                                    </tr>
                                    <tr>
                                        <td class="text-muted">Pag-IBIG:</td>
                                        <td class="font-monospace" id="m-pagibig"></td>
                                    </tr>
                                    <tr>
                                        <td class="text-muted">Residency:</td>
                                        <td><span class="fw-bold" id="m-years"></span> Years</td>
                                    </tr>
                                    <tr>
                                        <td class="text-muted">Job Status:</td>
                                        <td id="m-employed"></td>
                                    </tr>
                                </table>
                            </div>
                            <div class="card p-3 border-0 shadow-sm" style="background-color: #fff9f9; border-left: 4px solid #dc3545 !important;">
                                <h6 class="text-danger fw-bold border-bottom pb-2 mb-2"><i class="bi bi-exclamation-triangle-fill me-1"></i> Emergency Reference</h6>
                                <div class="small">
                                    <div class="fw-bold text-dark" id="m-ename"></div>
                                    <div class="text-muted small" id="m-erel"></div>
                                    <div class="font-monospace text-danger my-1" id="m-ephone"></div>
                                    <div class="text-secondary style-desc" style="font-size:11px;" id="m-eaddress"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const modalObj = new bootstrap.Modal(document.getElementById('residentProfileModal'));

            document.querySelectorAll('.view-profile-trigger').forEach(button => {
                button.addEventListener('click', function() {
                    document.getElementById('m-name').textContent = this.dataset.name;
                    document.getElementById('m-avatar').src = this.dataset.avatar;
                    document.getElementById('m-email').textContent = this.dataset.email;
                    document.getElementById('m-mobile').textContent = this.dataset.mobile;
                    document.getElementById('m-sex').textContent = this.dataset.sex;
                    document.getElementById('m-civil').textContent = this.dataset.civil;
                    document.getElementById('m-birthdate').textContent = this.dataset.birthdate;
                    document.getElementById('m-birthplace').textContent = this.dataset.birthplace;
                    document.getElementById('m-religion').textContent = this.dataset.religion;
                    document.getElementById('m-nationality').textContent = this.dataset.nationality;
                    document.getElementById('m-address').textContent = this.dataset.address;
                    document.getElementById('m-nationalid').textContent = this.dataset.nationalid;
                    document.getElementById('m-philhealth').textContent = this.dataset.philhealth;
                    document.getElementById('m-voters').textContent = this.dataset.voters;
                    document.getElementById('m-sss').textContent = this.dataset.sss;
                    document.getElementById('m-tin').textContent = this.dataset.tin;
                    document.getElementById('m-years').textContent = this.dataset.years;
                    document.getElementById('m-employed').textContent = this.dataset.employed;
                    document.getElementById('m-pagibig').textContent = this.dataset.pagibig;
                    document.getElementById('m-ename').textContent = this.dataset.ename;
                    document.getElementById('m-erel').textContent = this.dataset.erel;
                    document.getElementById('m-ephone').textContent = this.dataset.ephone;
                    document.getElementById('m-eaddress').textContent = this.dataset.eaddress;

                    modalObj.show();
                });
            });
        });
    </script>
</body>

</html>