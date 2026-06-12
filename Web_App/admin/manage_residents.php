<?php
session_start();

if (!isset($_SESSION['admin_id'])) {
    header("Location: login_admin.php");
    exit();
}

require_once __DIR__ . '/../includes/db_connect.php';
require_once __DIR__ . '/../includes/prg_flash.php';

date_default_timezone_set('Asia/Manila');

$success_message = prgFlashPull('admin_residents');
$error_message = '';

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['approve_user'])) {
    $target_user_id = (int)$_POST['user_id'];
    
    mysqli_begin_transaction($conn);
    try {
        $update_query = "UPDATE users SET account_status = 'Verified' WHERE user_id = ?";
        $stmt_update = mysqli_prepare($conn, $update_query);
        mysqli_stmt_bind_param($stmt_update, "i", $target_user_id);
        mysqli_stmt_execute($stmt_update);

        $notif_title = "Account Verified";
        $notif_message = "Your resident account has been verified and approved by the admin. You can now access full features.";
        $notif_type = "System Alert";
        $notif_icon = "fa-solid fa-circle-check";
        
        $notif_query = "INSERT INTO user_notifications (user_id, title, message, type, icon) VALUES (?, ?, ?, ?, ?)";
        $notif_stmt = mysqli_prepare($conn, $notif_query);
        mysqli_stmt_bind_param($notif_stmt, "issss", $target_user_id, $notif_title, $notif_message, $notif_type, $notif_icon);
        mysqli_stmt_execute($notif_stmt);

        mysqli_commit($conn);
        prgRedirect(
            'manage_residents.php',
            'admin_residents',
            'Resident account has been approved and verified.'
        );
    } catch (Exception $e) {
        mysqli_rollback($conn);
        $error_message = "System error: Could not approve the resident account.";
    }
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['archive_user'])) {
    $target_user_id = (int)$_POST['user_id'];
    $reason = mysqli_real_escape_string($conn, $_POST['archive_reason'] ?? 'Suspended Account');

    mysqli_begin_transaction($conn);
    try {
        $archive_query = "
            INSERT INTO archived_users (original_user_id, username, email, role, archived_reason)
            SELECT user_id, username, email, role, ? FROM users WHERE user_id = ?";
        $stmt_archive = mysqli_prepare($conn, $archive_query);
        mysqli_stmt_bind_param($stmt_archive, "si", $reason, $target_user_id);
        mysqli_stmt_execute($stmt_archive);

        $archive_profile_query = "
            INSERT INTO archived_user_profiles (
                original_user_id, first_name, last_name, middle_name, suffix, avatar_path,
                sex, civil_status, birth_date, birth_place, religion, nationality,
                mobile_number, house_no, street, purok_no, subdivision, years_residency,
                employed_status, date_registration
            )
            SELECT
                user_id, first_name, last_name, middle_name, suffix, avatar_path,
                sex, civil_status, birth_date, birth_place, religion, nationality,
                mobile_number, house_no, street, purok_no, subdivision, years_residency,
                employed_status, date_registration
            FROM user_profiles WHERE user_id = ?";
        $stmt_archive_prof = mysqli_prepare($conn, $archive_profile_query);
        mysqli_stmt_bind_param($stmt_archive_prof, "i", $target_user_id);
        mysqli_stmt_execute($stmt_archive_prof);

        $delete_query = "DELETE FROM users WHERE user_id = ?";
        $stmt_delete = mysqli_prepare($conn, $delete_query);
        mysqli_stmt_bind_param($stmt_delete, "i", $target_user_id);
        mysqli_stmt_execute($stmt_delete);

        mysqli_commit($conn);
        prgRedirect(
            'manage_residents.php',
            'admin_residents',
            'Resident account moved to suspended archives.'
        );
    } catch (Exception $e) {
        mysqli_rollback($conn);
        $error_message = "System error: Could not suspend the resident account.";
    }
}

function h(?string $value): string
{
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

function residentFullName(array $row): string
{
    return trim(implode(' ', array_filter([
        $row['first_name'] ?? '',
        !empty($row['middle_name']) ? substr($row['middle_name'], 0, 1) . '.' : '',
        $row['last_name'] ?? '',
        $row['suffix'] ?? '',
    ]))) ?: ($row['username'] ?? 'Resident');
}

function residentInitials(string $name): string
{
    $parts = preg_split('/\s+/', trim($name));
    $first = strtoupper(substr($parts[0] ?? 'R', 0, 1));
    $last = strtoupper(substr($parts[count($parts) - 1] ?? 'S', 0, 1));
    return $first . $last;
}

function residentAddress(array $row): string
{
    $parts = array_filter([
        !empty($row['house_no']) ? 'House ' . $row['house_no'] : '',
        $row['street'] ?? '',
        !empty($row['purok_no']) ? 'Purok ' . $row['purok_no'] : '',
        $row['subdivision'] ?? '',
    ]);

    return !empty($parts) ? implode(', ', $parts) : 'No address on file';
}

function residentPurok(array $row): string
{
    return !empty($row['purok_no']) ? 'Purok ' . $row['purok_no'] : 'No Purok';
}

function residentIsVerified(array $row): bool
{
    foreach (['first_name', 'last_name', 'mobile_number', 'purok_no', 'birth_date', 'sex', 'civil_status'] as $field) {
        if (trim((string)($row[$field] ?? '')) === '') {
            return false;
        }
    }

    return true;
}

function residentStatus(array $row): string
{
    return $row['account_status'] ?? 'Pending Verification';
}

function residentStatusClass(string $status): string
{
    if ($status === 'Verified') return 'resident-status-verified';
    if ($status === 'Rejected') return 'resident-status-rejected';
    if ($status === 'Suspended') return 'resident-status-suspended';
    return 'resident-status-pending';
}

function formatResidentDate(?string $date, string $format = 'M d, Y'): string
{
    if (empty($date) || strtotime($date) === false) {
        return 'N/A';
    }

    return date($format, strtotime($date));
}

function residentTableExists(mysqli $conn, string $table): bool
{
    $safe_table = mysqli_real_escape_string($conn, $table);
    $result = mysqli_query($conn, "SHOW TABLES LIKE '$safe_table'");
    return $result && mysqli_num_rows($result) > 0;
}

$query = "
    SELECT u.user_id, u.username, u.email, u.created_at, u.account_status, p.*
    FROM users u
    INNER JOIN user_profiles p ON u.user_id = p.user_id
    WHERE u.role = 'Residente'
    ORDER BY u.created_at DESC
";
$result = mysqli_query($conn, $query);
$residents = [];
if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        $residents[] = $row;
    }
}

$resident_ids = array_map('intval', array_column($residents, 'user_id'));
$gov_ids_by_user = [];
$emergency_by_user = [];
$activities_by_user = [];
$documents_by_user = [];

if (!empty($resident_ids)) {
    $id_list = implode(',', $resident_ids);

    if (residentTableExists($conn, 'user_government_ids')) {
        $gov_result = mysqli_query($conn, "SELECT user_id, id_type, id_number FROM user_government_ids WHERE user_id IN ($id_list)");
        if ($gov_result) {
            while ($row = mysqli_fetch_assoc($gov_result)) {
                $gov_ids_by_user[(int)$row['user_id']][$row['id_type']] = $row['id_number'];
            }
        }
    }

    if (residentTableExists($conn, 'user_emergency_contacts')) {
        $emergency_result = mysqli_query($conn, "SELECT user_id, name, relationship, contact_number, address FROM user_emergency_contacts WHERE user_id IN ($id_list) ORDER BY contact_id DESC");
        if ($emergency_result) {
            while ($row = mysqli_fetch_assoc($emergency_result)) {
                $user_id = (int)$row['user_id'];
                if (!isset($emergency_by_user[$user_id])) {
                    $emergency_by_user[$user_id] = $row;
                }
            }
        }
    }

    if (residentTableExists($conn, 'service_requests') && residentTableExists($conn, 'document_types')) {
        $activity_result = mysqli_query($conn, "
            SELECT sr.user_id, dt.name AS label, sr.created_at, sr.id_path
            FROM service_requests sr
            JOIN document_types dt ON sr.document_type_id = dt.document_type_id
            WHERE sr.user_id IN ($id_list)
            ORDER BY sr.created_at DESC
        ");
        if ($activity_result) {
            while ($row = mysqli_fetch_assoc($activity_result)) {
                $user_id = (int)$row['user_id'];
                $activities_by_user[$user_id][] = [
                    'title' => 'Submitted ' . $row['label'],
                    'date' => formatResidentDate($row['created_at']),
                ];
                if (!empty($row['id_path'])) {
                    $documents_by_user[$user_id][] = [
                        'label' => $row['label'] . ' Requirement',
                        'path' => '../' . $row['id_path'],
                    ];
                }
            }
        }
    }

    if (residentTableExists($conn, 'facility_reservations')) {
        $reservation_result = mysqli_query($conn, "
            SELECT user_id, reference_no, reservation_date, created_at
            FROM facility_reservations
            WHERE user_id IN ($id_list)
            ORDER BY created_at DESC
        ");
        if ($reservation_result) {
            while ($row = mysqli_fetch_assoc($reservation_result)) {
                $activities_by_user[(int)$row['user_id']][] = [
                    'title' => 'Appointment Reservation',
                    'date' => formatResidentDate($row['created_at']),
                ];
            }
        }
    }
}

$total_residents = count($residents);
$verified_residents = 0;
$pending_residents = 0;
$new_this_month = 0;
foreach ($residents as $row) {
    if (residentIsVerified($row)) {
        $verified_residents++;
    } else {
        $pending_residents++;
    }

    if (!empty($row['created_at']) && date('Y-m', strtotime($row['created_at'])) === date('Y-m')) {
        $new_this_month++;
    }
}
$verified_percent = $total_residents > 0 ? round(($verified_residents / $total_residents) * 100, 1) : 0;
?>
<!DOCTYPE html>
<html lang="en" data-bs-theme="light">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Residents | MakiKonek</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <link rel="stylesheet" href="../assets/css/admin.css?v=20260612v">
</head>

<body class="dashboard-body">
    <?php include __DIR__ . '/partials/admin_sidebar.php'; ?>

    <main class="main-content residents-main">
        <div class="residents-layout">
            <section class="residents-workspace">
                <header class="residents-page-header">
                    <div>
                        <h1>Residents</h1>
                        <p>Manage resident accounts and verification.</p>
                    </div>
                    <div class="dashboard-header-tools">
                        <div class="dashboard-date-card">
                            <i class="bi bi-calendar3"></i>
                            <span>
                                <strong><?php echo date('F d, Y'); ?></strong>
                                <small><?php echo date('l, h:i A'); ?></small>
                            </span>
                        </div>
                        <button class="dashboard-notification" type="button" aria-label="Notifications">
                            <i class="bi bi-bell"></i>
                            <?php if ($pending_residents > 0): ?>
                                <span><?php echo min(9, $pending_residents); ?></span>
                            <?php endif; ?>
                        </button>
                    </div>
                </header>

                <?php if ($success_message): ?>
                    <div class="alert alert-success alert-dismissible fade show shadow-sm"><i class="bi bi-check-circle me-2"></i> <?php echo h($success_message); ?> <button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
                <?php endif; ?>
                <?php if ($error_message): ?>
                    <div class="alert alert-danger alert-dismissible fade show shadow-sm"><i class="bi bi-exclamation-triangle me-2"></i> <?php echo h($error_message); ?> <button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
                <?php endif; ?>

                <section class="resident-kpi-grid" aria-label="Resident analytics">
                    <article class="resident-kpi-card">
                        <span class="resident-kpi-icon"><i class="bi bi-people"></i></span>
                        <div>
                            <p>Total Residents</p>
                            <strong><?php echo $total_residents; ?></strong>
                            <small>All registered residents</small>
                        </div>
                        <span class="resident-trend-line"></span>
                    </article>
                    <article class="resident-kpi-card">
                        <span class="resident-kpi-icon"><i class="bi bi-shield-check"></i></span>
                        <div>
                            <p>Verified Residents</p>
                            <strong><?php echo $verified_residents; ?></strong>
                            <small><?php echo $verified_percent; ?>% of total residents</small>
                        </div>
                        <span class="resident-progress"><span style="width: <?php echo min(100, $verified_percent); ?>%;"></span></span>
                    </article>
                    <article class="resident-kpi-card resident-kpi-amber">
                        <span class="resident-kpi-icon"><i class="bi bi-hourglass-split"></i></span>
                        <div>
                            <p>Pending Verification</p>
                            <strong><?php echo $pending_residents; ?></strong>
                            <small>Awaiting admin review</small>
                        </div>
                        <span class="resident-progress"><span style="width: <?php echo $total_residents > 0 ? min(100, ($pending_residents / $total_residents) * 100) : 0; ?>%;"></span></span>
                    </article>
                    <article class="resident-kpi-card">
                        <span class="resident-kpi-icon"><i class="bi bi-person-plus"></i></span>
                        <div>
                            <p>New This Month</p>
                            <strong><?php echo $new_this_month; ?></strong>
                            <small>Registered this month</small>
                        </div>
                        <span class="resident-progress"><span style="width: <?php echo $total_residents > 0 ? min(100, ($new_this_month / $total_residents) * 100) : 0; ?>%;"></span></span>
                    </article>
                </section>

                <section class="resident-directory-card">
                    <div class="resident-toolbar">
                        <label class="resident-search">
                            <span class="visually-hidden">Search residents</span>
                            <input type="search" id="residentSearch" placeholder="Search by name, email, or contact number...">
                            <i class="bi bi-search"></i>
                        </label>
                        <select id="residentStatusFilter" aria-label="Resident status">
                            <option value="">All Status</option>
                            <option value="Verified">Verified</option>
                            <option value="Pending Verification">Pending Verification</option>
                            <option value="Rejected">Rejected</option>
                            <option value="Suspended">Suspended</option>
                        </select>
                        <select id="residentPurokFilter" aria-label="Resident purok">
                            <option value="">All Purok</option>
                            <?php for ($i = 1; $i <= 6; $i++): ?>
                                <option value="Purok <?php echo $i; ?>">Purok <?php echo $i; ?></option>
                            <?php endfor; ?>
                        </select>
                    </div>

                    <div class="resident-directory-table-wrap" id="residentTableWrap" <?php echo $total_residents > 0 ? '' : 'hidden'; ?>>
                        <table class="resident-directory-table">
                            <thead>
                                <tr>
                                    <th>Resident</th>
                                    <th>Status</th>
                                    <th>Address</th>
                                    <th>Registered On</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($residents as $index => $row):
                                    $full_name = residentFullName($row);
                                    $initials = residentInitials($full_name);
                                    $status = residentStatus($row);
                                    $status_class = residentStatusClass($status);
                                    $purok = residentPurok($row);
                                    $address = residentAddress($row);
                                    $avatar = !empty($row['avatar_path']) ? '../' . $row['avatar_path'] : '';
                                    $user_id = (int)$row['user_id'];
                                    $gov_ids = $gov_ids_by_user[$user_id] ?? [];
                                    $emergency = $emergency_by_user[$user_id] ?? [];
                                    $documents = $documents_by_user[$user_id] ?? [];
                                    $activities = $activities_by_user[$user_id] ?? [];
                                    array_unshift($activities, ['title' => 'Registered Account', 'date' => formatResidentDate($row['created_at'])]);
                                ?>
                                    <tr class="resident-row"
                                        data-page-index="<?php echo (int)$index; ?>"
                                        data-search="<?php echo h(strtolower($full_name . ' ' . $row['email'] . ' ' . ($row['mobile_number'] ?? ''))); ?>"
                                        data-status="<?php echo h($status); ?>"
                                        data-purok="<?php echo h($purok); ?>">
                                        <td>
                                            <button class="resident-person-button resident-open-trigger" type="button"
                                                data-userid="<?php echo $user_id; ?>"
                                                data-name="<?php echo h($full_name); ?>"
                                                data-initials="<?php echo h($initials); ?>"
                                                data-avatar="<?php echo h($avatar); ?>"
                                                data-status="<?php echo h($status); ?>"
                                                data-email="<?php echo h($row['email']); ?>"
                                                data-mobile="<?php echo h($row['mobile_number'] ?? 'N/A'); ?>"
                                                data-birthdate="<?php echo h(formatResidentDate($row['birth_date'] ?? '', 'F d, Y')); ?>"
                                                data-gender="<?php echo h(ucwords(strtolower((string)($row['sex'] ?? 'N/A')))); ?>"
                                                data-civil="<?php echo h(ucwords(strtolower((string)($row['civil_status'] ?? 'N/A')))); ?>"
                                                data-address="<?php echo h($address); ?>"
                                                data-purok="<?php echo h($purok); ?>"
                                                data-registered="<?php echo h(formatResidentDate($row['created_at'], 'M d, Y \a\t h:i A')); ?>"
                                                data-residentid="RES-<?php echo str_pad((string)$user_id, 6, '0', STR_PAD_LEFT); ?>"
                                                data-emergency="<?php echo h(trim(($emergency['name'] ?? 'N/A') . ' (' . ($emergency['relationship'] ?? 'N/A') . ')')); ?>"
                                                data-emergencyphone="<?php echo h($emergency['contact_number'] ?? 'N/A'); ?>"
                                                data-documents="<?php echo h(json_encode($documents)); ?>"
                                                data-activities="<?php echo h(json_encode(array_slice($activities, 0, 8))); ?>">
                                                <span class="resident-avatar resident-avatar-<?php echo ($index % 6) + 1; ?>"><?php echo h($initials); ?></span>
                                                <span>
                                                    <strong><?php echo h($full_name); ?></strong>
                                                    <small><?php echo h($row['email']); ?></small>
                                                    <small><?php echo h($row['mobile_number'] ?? 'No contact number'); ?></small>
                                                </span>
                                            </button>
                                        </td>
                                        <td><span class="resident-status-badge <?php echo h($status_class); ?>"><?php echo h($status); ?></span></td>
                                        <td><?php echo h($purok); ?></td>
                                        <td><?php echo h(formatResidentDate($row['created_at'])); ?></td>
                                        <td>
                                            <div class="resident-action-group">
                                                <button class="resident-action-btn resident-open-trigger" type="button"
                                                    data-userid="<?php echo $user_id; ?>"
                                                    data-name="<?php echo h($full_name); ?>"
                                                    data-initials="<?php echo h($initials); ?>"
                                                    data-avatar="<?php echo h($avatar); ?>"
                                                    data-status="<?php echo h($status); ?>"
                                                    data-email="<?php echo h($row['email']); ?>"
                                                    data-mobile="<?php echo h($row['mobile_number'] ?? 'N/A'); ?>"
                                                    data-birthdate="<?php echo h(formatResidentDate($row['birth_date'] ?? '', 'F d, Y')); ?>"
                                                    data-gender="<?php echo h(ucwords(strtolower((string)($row['sex'] ?? 'N/A')))); ?>"
                                                    data-civil="<?php echo h(ucwords(strtolower((string)($row['civil_status'] ?? 'N/A')))); ?>"
                                                    data-address="<?php echo h($address); ?>"
                                                    data-purok="<?php echo h($purok); ?>"
                                                    data-registered="<?php echo h(formatResidentDate($row['created_at'], 'M d, Y \a\t h:i A')); ?>"
                                                    data-residentid="RES-<?php echo str_pad((string)$user_id, 6, '0', STR_PAD_LEFT); ?>"
                                                    data-emergency="<?php echo h(trim(($emergency['name'] ?? 'N/A') . ' (' . ($emergency['relationship'] ?? 'N/A') . ')')); ?>"
                                                    data-emergencyphone="<?php echo h($emergency['contact_number'] ?? 'N/A'); ?>"
                                                    data-documents="<?php echo h(json_encode($documents)); ?>"
                                                    data-activities="<?php echo h(json_encode(array_slice($activities, 0, 8))); ?>">
                                                    <?php echo $status === 'Verified' ? 'Open Profile' : 'Review'; ?>
                                                </button>
                                                <div class="dropdown">
                                                    <button class="resident-dots-btn" type="button" data-bs-toggle="dropdown" aria-label="More resident actions"><i class="bi bi-three-dots-vertical"></i></button>
                                                    <div class="dropdown-menu resident-action-menu">
                                                        <button class="dropdown-item resident-open-trigger" type="button" data-name="<?php echo h($full_name); ?>" data-initials="<?php echo h($initials); ?>" data-avatar="<?php echo h($avatar); ?>" data-status="<?php echo h($status); ?>" data-email="<?php echo h($row['email']); ?>" data-mobile="<?php echo h($row['mobile_number'] ?? 'N/A'); ?>" data-birthdate="<?php echo h(formatResidentDate($row['birth_date'] ?? '', 'F d, Y')); ?>" data-gender="<?php echo h(ucwords(strtolower((string)($row['sex'] ?? 'N/A')))); ?>" data-civil="<?php echo h(ucwords(strtolower((string)($row['civil_status'] ?? 'N/A')))); ?>" data-address="<?php echo h($address); ?>" data-purok="<?php echo h($purok); ?>" data-registered="<?php echo h(formatResidentDate($row['created_at'], 'M d, Y \a\t h:i A')); ?>" data-residentid="RES-<?php echo str_pad((string)$user_id, 6, '0', STR_PAD_LEFT); ?>" data-emergency="<?php echo h(trim(($emergency['name'] ?? 'N/A') . ' (' . ($emergency['relationship'] ?? 'N/A') . ')')); ?>" data-emergencyphone="<?php echo h($emergency['contact_number'] ?? 'N/A'); ?>" data-documents="<?php echo h(json_encode($documents)); ?>" data-activities="<?php echo h(json_encode(array_slice($activities, 0, 8))); ?>"><i class="bi bi-person"></i> Open Profile</button>
                                                        <button class="dropdown-item resident-doc-tab-trigger" type="button"><i class="bi bi-file-earmark"></i> View Documents</button>
                                                        <?php if ($status !== 'Verified'): ?>
                                                            <form action="manage_residents.php" method="POST" onsubmit="return confirm('Approve verification for <?php echo h($full_name); ?>?');" style="margin:0;">
                                                                <input type="hidden" name="user_id" value="<?php echo $user_id; ?>">
                                                                <button type="submit" name="approve_user" class="dropdown-item"><i class="bi bi-check-circle"></i> Approve Resident</button>
                                                            </form>
                                                            <span class="dropdown-item-text text-muted"><i class="bi bi-x-circle"></i> Reject Verification</span>
                                                        <?php endif; ?>
                                                        <form action="manage_residents.php" method="POST" onsubmit="return confirm('Archive <?php echo h($full_name); ?> and move this account to archives?');">
                                                            <input type="hidden" name="user_id" value="<?php echo $user_id; ?>">
                                                            <input type="hidden" name="archive_reason" value="Archived Account">
                                                            <button type="submit" name="archive_user" class="dropdown-item resident-danger-action"><i class="bi bi-archive"></i> Archive Account</button>
                                                        </form>
                                                        <form action="manage_residents.php" method="POST" onsubmit="return confirm('Suspend <?php echo h($full_name); ?> and move this account to archives?');">
                                                            <input type="hidden" name="user_id" value="<?php echo $user_id; ?>">
                                                            <input type="hidden" name="archive_reason" value="Suspended Account">
                                                            <button type="submit" name="archive_user" class="dropdown-item resident-danger-action"><i class="bi bi-slash-circle"></i> Suspend Account</button>
                                                        </form>
                                                    </div>
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <div class="resident-empty-state" id="residentEmptyState" <?php echo $total_residents > 0 ? 'hidden' : ''; ?>>
                        <i class="bi bi-person-lines-fill"></i>
                        <strong>No residents found.</strong>
                        <span>Try adjusting your filters or search terms.</span>
                    </div>
                    <div class="resident-pagination-bar">
                        <span id="residentResultCount">Showing <?php echo $total_residents > 0 ? '1 to ' . min(7, $total_residents) . ' of ' . $total_residents : '0'; ?> residents</span>
                        <div class="resident-pagination" id="residentPagination"></div>
                    </div>
                </section>
            </section>

            <aside class="resident-profile-drawer" id="residentDrawer">
                <div class="drawer-header">
                    <h2>Resident Profile</h2>
                </div>
                <div class="drawer-profile-head">
                    <span class="resident-drawer-avatar" id="drawerAvatar">--</span>
                    <div>
                        <strong id="drawerName">Select a resident</strong>
                        <span class="resident-status-badge resident-status-pending" id="drawerStatus">Pending Verification</span>
                    </div>
                </div>
                <div class="drawer-meta">
                    <p><span>Resident ID:</span> <strong id="drawerResidentId">RES-000000</strong></p>
                    <p><span>Registered:</span> <strong id="drawerRegistered">N/A</strong></p>
                </div>
                <div class="drawer-actions">
                    <button type="button" class="drawer-approve" disabled><i class="bi bi-check-circle"></i> Approve Resident</button>
                    <button type="button" class="drawer-reject" disabled><i class="bi bi-x-circle"></i> Reject Verification</button>
                    <form action="manage_residents.php" method="POST" id="drawerArchiveForm" class="d-inline">
                        <input type="hidden" name="user_id" id="drawerArchiveId">
                        <input type="hidden" name="archive_reason" value="Archived Account">
                        <button type="submit" name="archive_user" class="drawer-suspend"><i class="bi bi-archive"></i> Archive</button>
                    </form>
                    <form action="manage_residents.php" method="POST" id="drawerSuspendForm" class="d-inline">
                        <input type="hidden" name="user_id" id="drawerSuspendId">
                        <input type="hidden" name="archive_reason" value="Suspended Account">
                        <button type="submit" name="archive_user" class="drawer-suspend"><i class="bi bi-slash-circle"></i> Suspend</button>
                    </form>
                </div>
                <nav class="drawer-tabs" aria-label="Resident profile sections">
                    <button type="button" class="is-active" data-drawer-tab="profile"><i class="bi bi-person"></i> Profile</button>
                    <button type="button" data-drawer-tab="documents"><i class="bi bi-file-earmark-text"></i> Documents</button>
                    <button type="button" data-drawer-tab="activity"><i class="bi bi-clock-history"></i> Activity</button>
                </nav>
                <section class="drawer-tab-panel is-active" data-drawer-panel="profile">
                    <h3>Personal Information</h3>
                    <dl>
                        <div><dt>Email Address</dt><dd id="drawerEmail">N/A</dd></div>
                        <div><dt>Contact Number</dt><dd id="drawerMobile">N/A</dd></div>
                        <div><dt>Date of Birth</dt><dd id="drawerBirthdate">N/A</dd></div>
                        <div><dt>Gender</dt><dd id="drawerGender">N/A</dd></div>
                        <div><dt>Civil Status</dt><dd id="drawerCivil">N/A</dd></div>
                    </dl>
                    <h3>Residential Address</h3>
                    <p class="drawer-text" id="drawerAddress">N/A</p>
                    <h3>Emergency Contact</h3>
                    <p class="drawer-text"><strong id="drawerEmergency">N/A</strong><br><span id="drawerEmergencyPhone">N/A</span></p>
                </section>
                <section class="drawer-tab-panel" data-drawer-panel="documents">
                    <h3>Uploaded Verification Files</h3>
                    <div class="drawer-documents" id="drawerDocuments"></div>
                </section>
                <section class="drawer-tab-panel" data-drawer-panel="activity">
                    <h3>Resident History</h3>
                    <div class="drawer-activity" id="drawerActivity"></div>
                </section>
            </aside>
        </div>
    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const rows = Array.from(document.querySelectorAll('.resident-row'));
            const searchInput = document.getElementById('residentSearch');
            const statusFilter = document.getElementById('residentStatusFilter');
            const purokFilter = document.getElementById('residentPurokFilter');
            const emptyState = document.getElementById('residentEmptyState');
            const tableWrap = document.getElementById('residentTableWrap');
            const resultCount = document.getElementById('residentResultCount');
            const pagination = document.getElementById('residentPagination');
            const perPage = 7;
            let currentPage = 1;
            let filteredRows = rows;

            const escapeHtml = (value) => String(value || 'N/A')
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;')
                .replace(/'/g, '&#039;');

            function applyFilters() {
                const query = (searchInput.value || '').toLowerCase().trim();
                const status = statusFilter.value;
                const purok = purokFilter.value;

                filteredRows = rows.filter(row => {
                    return (!query || row.dataset.search.includes(query)) &&
                        (!status || row.dataset.status === status) &&
                        (!purok || row.dataset.purok === purok);
                });

                currentPage = Math.min(currentPage, Math.max(1, Math.ceil(filteredRows.length / perPage)));
                renderPage();
            }

            function renderPage() {
                const total = filteredRows.length;
                const pageCount = Math.max(1, Math.ceil(total / perPage));
                currentPage = Math.min(currentPage, pageCount);
                const start = (currentPage - 1) * perPage;
                const end = start + perPage;

                rows.forEach(row => row.hidden = true);
                filteredRows.slice(start, end).forEach(row => row.hidden = false);

                emptyState.hidden = total > 0;
                tableWrap.hidden = total === 0;
                resultCount.textContent = total > 0 ? `Showing ${start + 1} to ${Math.min(end, total)} of ${total} residents` : 'Showing 0 residents';

                let html = `<button type="button" data-page="${Math.max(1, currentPage - 1)}"><i class="bi bi-chevron-left"></i></button>`;
                for (let i = 1; i <= Math.min(5, pageCount); i++) {
                    html += `<button type="button" data-page="${i}" class="${i === currentPage ? 'is-active' : ''}">${i}</button>`;
                }
                html += `<button type="button" data-page="${Math.min(pageCount, currentPage + 1)}"><i class="bi bi-chevron-right"></i></button>`;
                pagination.innerHTML = html;
            }

            pagination.addEventListener('click', function(event) {
                const button = event.target.closest('button[data-page]');
                if (!button) return;
                currentPage = Number(button.dataset.page || 1);
                renderPage();
            });

            [searchInput, statusFilter, purokFilter].forEach(control => {
                control.addEventListener('input', applyFilters);
                control.addEventListener('change', applyFilters);
            });

            function setDrawerTab(tabName) {
                document.querySelectorAll('.drawer-tabs button').forEach(button => button.classList.toggle('is-active', button.dataset.drawerTab === tabName));
                document.querySelectorAll('.drawer-tab-panel').forEach(panel => panel.classList.toggle('is-active', panel.dataset.drawerPanel === tabName));
            }

            function openDrawer(button, tabName = 'profile') {
                const status = button.dataset.status || 'Pending Verification';
                document.getElementById('drawerAvatar').textContent = button.dataset.initials || '--';
                document.getElementById('drawerName').textContent = button.dataset.name || 'Resident';
                document.getElementById('drawerStatus').textContent = status;
                document.getElementById('drawerStatus').className = `resident-status-badge ${status === 'Verified' ? 'resident-status-verified' : 'resident-status-pending'}`;
                document.getElementById('drawerResidentId').textContent = button.dataset.residentid || 'RES-000000';
                document.getElementById('drawerRegistered').textContent = button.dataset.registered || 'N/A';
                document.getElementById('drawerEmail').textContent = button.dataset.email || 'N/A';
                document.getElementById('drawerMobile').textContent = button.dataset.mobile || 'N/A';
                document.getElementById('drawerBirthdate').textContent = button.dataset.birthdate || 'N/A';
                document.getElementById('drawerGender').textContent = button.dataset.gender || 'N/A';
                document.getElementById('drawerCivil').textContent = button.dataset.civil || 'N/A';
                document.getElementById('drawerAddress').textContent = button.dataset.address || 'N/A';
                document.getElementById('drawerEmergency').textContent = button.dataset.emergency || 'N/A';
                document.getElementById('drawerEmergencyPhone').textContent = button.dataset.emergencyphone || 'N/A';
                document.getElementById('drawerSuspendId').value = button.dataset.userid || '';
                document.getElementById('drawerArchiveId').value = button.dataset.userid || '';

                const documents = JSON.parse(button.dataset.documents || '[]');
                document.getElementById('drawerDocuments').innerHTML = documents.length ?
                    documents.map(doc => `<a href="${escapeHtml(doc.path)}" target="_blank"><span><i class="bi bi-file-earmark"></i> ${escapeHtml(doc.label)}</span><strong>Preview</strong></a>`).join('') :
                    '<p class="drawer-muted">No uploaded verification files found.</p>';

                const activities = JSON.parse(button.dataset.activities || '[]');
                document.getElementById('drawerActivity').innerHTML = activities.length ?
                    activities.map(item => `<article><span></span><strong>${escapeHtml(item.title)}</strong><small>${escapeHtml(item.date)}</small></article>`).join('') :
                    '<p class="drawer-muted">No resident activity yet.</p>';

                setDrawerTab(tabName);
                document.getElementById('residentDrawer').classList.add('is-open');
            }

            document.querySelectorAll('.resident-open-trigger').forEach(button => {
                button.addEventListener('click', function() {
                    openDrawer(this);
                });
            });

            document.querySelectorAll('.resident-doc-tab-trigger').forEach(button => {
                button.addEventListener('click', function() {
                    const trigger = this.closest('tr').querySelector('.resident-open-trigger');
                    if (trigger) openDrawer(trigger, 'documents');
                });
            });

            rows.forEach(row => {
                row.addEventListener('click', function(event) {
                    if (event.target.closest('button, a, form, .dropdown-menu')) return;
                    const trigger = this.querySelector('.resident-open-trigger');
                    if (trigger) openDrawer(trigger);
                });
            });

            document.querySelectorAll('.drawer-tabs button').forEach(button => {
                button.addEventListener('click', function() {
                    setDrawerTab(this.dataset.drawerTab);
                });
            });

            document.getElementById('drawerSuspendForm').addEventListener('submit', function(event) {
                if (!confirm('Suspend this resident account and move it to archives?')) {
                    event.preventDefault();
                }
            });

            applyFilters();
            const firstResident = document.querySelector('.resident-open-trigger');
            if (firstResident) {
                openDrawer(firstResident);
            }
        });
    </script>
</body>

</html>
