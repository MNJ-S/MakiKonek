<?php
session_start();

if (!isset($_SESSION['admin_id'])) {
    header("Location: login_admin.php");
    exit();
}

require_once __DIR__ . '/../includes/db_connect.php';
require_once __DIR__ . '/../includes/prg_flash.php';
require_once __DIR__ . '/includes/auto_archive_reservations.php';

date_default_timezone_set('Asia/Manila');

$success_message = prgFlashPull('admin_appointments');
$error_message = '';
$reservation_status_transitions = [
    'Pending' => ['Approved', 'Rejected'],
    'Approved' => ['Completed', 'Cancelled'],
];

function h(?string $value): string
{
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

function appointmentDate(?string $date, string $format = 'M d, Y'): string
{
    if (empty($date) || strtotime($date) === false) {
        return 'N/A';
    }

    return date($format, strtotime($date));
}

function appointmentTime(?string $time): string
{
    if (empty($time) || strtotime($time) === false) {
        return 'N/A';
    }

    return date('h:i A', strtotime($time));
}

function appointmentResidentName(array $row): string
{
    return trim(implode(' ', array_filter([
        $row['first_name'] ?? '',
        !empty($row['middle_name']) ? substr($row['middle_name'], 0, 1) . '.' : '',
        $row['last_name'] ?? '',
    ]))) ?: ($row['username'] ?? 'Resident');
}

function appointmentStatusClass(string $status): string
{
    $normalized = strtolower($status);
    if ($normalized === 'approved') return 'appointment-status-approved';
    if ($normalized === 'completed') return 'appointment-status-completed';
    if ($normalized === 'cancelled' || $normalized === 'rejected') return 'appointment-status-cancelled';
    return 'appointment-status-pending';
}

function reservationTypeLabel(string $facility): string
{
    return stripos($facility, 'court') !== false ? 'Court Reservation' : 'Events Hall Reservation';
}

function reservationIcon(string $facility): string
{
    return stripos($facility, 'court') !== false ? 'bi-trophy' : 'bi-building';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_reservation_status'])) {
    $reservation_id = (int)($_POST['reservation_id'] ?? 0);
    $new_status = trim($_POST['status'] ?? '');

    if ($reservation_id <= 0) {
        $error_message = 'Invalid reservation status update.';
    } else {
        $status_stmt = mysqli_prepare($conn, "SELECT user_id, reference_no, status FROM facility_reservations WHERE reservation_id = ? LIMIT 1");
        mysqli_stmt_bind_param($status_stmt, "i", $reservation_id);
        mysqli_stmt_execute($status_stmt);
        $status_result = mysqli_stmt_get_result($status_stmt);
        $reservation = mysqli_fetch_assoc($status_result);
        $current_status = $reservation['status'] ?? '';
        $user_id = (int)($reservation['user_id'] ?? 0);
        $reference_no = $reservation['reference_no'] ?? '';
        $allowed_next_statuses = $reservation_status_transitions[$current_status] ?? [];

        if (!in_array($new_status, $allowed_next_statuses, true)) {
            $error_message = 'This reservation status can no longer be changed using that action.';
        } else {
            $stmt = mysqli_prepare($conn, "
                UPDATE facility_reservations
                SET status = ?
                WHERE reservation_id = ? AND status = ?
            ");
            mysqli_stmt_bind_param($stmt, "sis", $new_status, $reservation_id, $current_status);

            if (mysqli_stmt_execute($stmt) && mysqli_stmt_affected_rows($stmt) === 1) {
                $notif_title = 'Reservation ' . $new_status;
                $notif_msg = '';
                $notif_icon = 'fa-regular fa-bell';
                
                if ($new_status === 'Approved') {
                    $notif_msg = "Your reservation request ($reference_no) has been approved.";
                    $notif_icon = 'fa-regular fa-circle-check';
                } elseif ($new_status === 'Rejected') {
                    $notif_msg = "Your reservation request ($reference_no) has been rejected.";
                    $notif_icon = 'fa-regular fa-circle-xmark';
                } elseif ($new_status === 'Completed') {
                    $notif_msg = "Your reservation ($reference_no) is marked as completed.";
                    $notif_icon = 'fa-solid fa-check-double';
                } elseif ($new_status === 'Cancelled') {
                    $notif_msg = "Your reservation ($reference_no) has been cancelled.";
                    $notif_icon = 'fa-solid fa-ban';
                }

                if ($notif_msg !== '' && $user_id > 0) {
                    $notif_stmt = mysqli_prepare($conn, "INSERT INTO user_notifications (user_id, title, message, type, icon) VALUES (?, ?, ?, 'Reservation Update', ?)");
                    if ($notif_stmt) {
                        mysqli_stmt_bind_param($notif_stmt, "isss", $user_id, $notif_title, $notif_msg, $notif_icon);
                        mysqli_stmt_execute($notif_stmt);
                        mysqli_stmt_close($notif_stmt);
                    }
                }

                prgRedirect(
                    'manage_appointments.php',
                    'admin_appointments',
                    'Reservation status updated.'
                );
            } else {
                $error_message = 'Could not update reservation status.';
            }
        }
    }
}

$reservations_query = "
    SELECT fr.*, f.name AS facility_name, f.base_fee, f.max_guests,
           u.username, u.email, p.first_name, p.middle_name, p.last_name, p.mobile_number
    FROM facility_reservations fr
    JOIN facilities f ON fr.facility_id = f.facility_id
    JOIN users u ON fr.user_id = u.user_id
    LEFT JOIN user_profiles p ON u.user_id = p.user_id
    ORDER BY
        CASE
            WHEN UPPER(fr.status) = 'PENDING' THEN 0
            WHEN UPPER(fr.status) = 'APPROVED' THEN 1
            ELSE 2
        END,
        fr.reservation_date ASC,
        fr.start_time ASC,
        fr.created_at DESC
";
$reservations_stmt = mysqli_prepare($conn, $reservations_query);
mysqli_stmt_execute($reservations_stmt);
$reservations_result = mysqli_stmt_get_result($reservations_stmt);
$reservations = [];
if ($reservations_result) {
    while ($row = mysqli_fetch_assoc($reservations_result)) {
        $reservations[] = $row;
    }
}

$today = date('Y-m-d');
$week_end = date('Y-m-d', strtotime('+7 days'));
$upcoming_count = 0;
$today_count = 0;
$pending_count = 0;
$completed_count = 0;

foreach ($reservations as $row) {
    $normalized_status = strtolower($row['status']);
    $is_active_reservation = !in_array($normalized_status, ['cancelled', 'rejected'], true);

    if ($is_active_reservation && $row['reservation_date'] >= $today && $row['reservation_date'] <= $week_end) {
        $upcoming_count++;
    }
    if ($is_active_reservation && $row['reservation_date'] === $today) {
        $today_count++;
    }
    if ($normalized_status === 'pending') {
        $pending_count++;
    }
    if ($normalized_status === 'completed') {
        $completed_count++;
    }
}

$today_reservations = array_values(array_filter($reservations, fn($row) => $row['reservation_date'] === $today));
$upcoming_reservations = array_values(array_filter($reservations, fn($row) => $row['reservation_date'] >= $today));
$active_calendar_reservations = array_values(array_filter(
    $reservations,
    fn($row) => in_array(strtolower($row['status']), ['approved', 'completed'], true)
));
$calendar_days = [];
foreach ($active_calendar_reservations as $row) {
    if (date('Y-m', strtotime($row['reservation_date'])) === date('Y-m')) {
        $calendar_days[(int)date('j', strtotime($row['reservation_date']))][] = [
            'facility_name' => $row['facility_name'],
            'start_time' => $row['start_time'],
            'end_time' => $row['end_time'],
            'status' => $row['status'],
        ];
    }
}
?>
<!DOCTYPE html>
<html lang="en" data-bs-theme="light">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Appointments | MakiKonek</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <link rel="stylesheet" href="../assets/css/admin.css?v=20260612v">
</head>

<body class="dashboard-body">
    <?php include __DIR__ . '/partials/admin_sidebar.php'; ?>

    <main class="main-content appointments-main">
        <header class="appointments-header">
            <div>
                <h1>Appointments</h1>
                <p>Manage reservation schedules for events hall and court.</p>
            </div>
            <div class="appointments-header-actions">
                <div class="dashboard-date-card">
                    <i class="bi bi-calendar3"></i>
                    <span>
                        <strong><?php echo date('F d, Y'); ?></strong>
                        <small><?php echo date('l, h:i A'); ?></small>
                    </span>
                </div>
                <button class="dashboard-notification" type="button" aria-label="Notifications">
                    <i class="bi bi-bell"></i>
                    <?php if ($pending_count > 0): ?><span><?php echo min(9, $pending_count); ?></span><?php endif; ?>
                </button>
                <a href="../resident/reservations.php" class="appointment-primary-action"><i class="bi bi-plus-lg"></i> New Reservation</a>
            </div>
        </header>

        <?php if ($success_message): ?>
            <div class="alert alert-success alert-dismissible fade show shadow-sm"><i class="bi bi-check-circle me-2"></i><?php echo h($success_message); ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
        <?php endif; ?>
        <?php if ($error_message): ?>
            <div class="alert alert-danger alert-dismissible fade show shadow-sm"><i class="bi bi-exclamation-triangle me-2"></i><?php echo h($error_message); ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
        <?php endif; ?>

        <section class="appointment-kpi-grid" aria-label="Reservation analytics">
            <article class="appointment-kpi-card">
                <span><i class="bi bi-calendar-week"></i></span>
                <div><p>Upcoming</p><strong><?php echo $upcoming_count; ?></strong><small>Scheduled this week</small></div>
                <em><i style="width: <?php echo min(100, $upcoming_count * 12); ?>%;"></i></em>
            </article>
            <article class="appointment-kpi-card">
                <span><i class="bi bi-calendar-day"></i></span>
                <div><p>Today</p><strong><?php echo $today_count; ?></strong><small>Reservations today</small></div>
                <em><i style="width: <?php echo min(100, $today_count * 25); ?>%;"></i></em>
            </article>
            <article class="appointment-kpi-card appointment-kpi-amber">
                <span><i class="bi bi-hourglass-split"></i></span>
                <div><p>Pending Approval</p><strong><?php echo $pending_count; ?></strong><small>Awaiting admin review</small></div>
                <em><i style="width: <?php echo min(100, $pending_count * 20); ?>%;"></i></em>
            </article>
            <article class="appointment-kpi-card">
                <span><i class="bi bi-check2-circle"></i></span>
                <div><p>Completed</p><strong><?php echo $completed_count; ?></strong><small>Completed reservations</small></div>
                <em><i style="width: <?php echo min(100, $completed_count * 10); ?>%;"></i></em>
            </article>
        </section>

        <nav class="appointment-filter-pills" aria-label="Reservation type filters">
            <button type="button" class="is-active" data-appointment-filter="All">All Reservations</button>
            <button type="button" data-appointment-filter="Events Hall Reservation">Events Hall Reservation</button>
            <button type="button" data-appointment-filter="Court Reservation">Court Reservation</button>
        </nav>

        <section class="appointments-grid">
            <article class="appointment-card appointment-schedule-card">
                <div class="appointment-card-heading">
                    <h2>Today's Schedule</h2>
                    <button type="button" data-appointment-filter-shortcut="All">View All</button>
                </div>
                <div class="appointment-timeline">
                    <?php if (!empty($today_reservations)): ?>
                        <?php foreach ($today_reservations as $row):
                            $resident_name = appointmentResidentName($row);
                            $type = reservationTypeLabel($row['facility_name']);
                        ?>
                            <button class="appointment-timeline-item appointment-open-trigger"
                                type="button"
                                data-type="<?php echo h($type); ?>"
                                data-name="<?php echo h($resident_name); ?>"
                                data-email="<?php echo h($row['email']); ?>"
                                data-phone="<?php echo h($row['mobile_number'] ?? 'N/A'); ?>"
                                data-ref="<?php echo h($row['reference_no']); ?>"
                                data-date="<?php echo h(appointmentDate($row['reservation_date'], 'F d, Y')); ?>"
                                data-time="<?php echo h(appointmentTime($row['start_time']) . ' - ' . appointmentTime($row['end_time'])); ?>"
                                data-purpose="<?php echo h($row['purpose']); ?>"
                                data-guests="<?php echo h((string)$row['expected_guests']); ?>"
                                data-notes="<?php echo h($row['additional_notes'] ?? ''); ?>"
                                data-status="<?php echo h($row['status']); ?>"
                                data-id="<?php echo (int)$row['reservation_id']; ?>">
                                <time><?php echo appointmentTime($row['start_time']); ?> - <?php echo appointmentTime($row['end_time']); ?></time>
                                <span class="appointment-type-icon"><i class="bi <?php echo reservationIcon($row['facility_name']); ?>"></i></span>
                                <span><strong><?php echo h($type); ?></strong><small><?php echo h($resident_name); ?></small></span>
                                <em class="<?php echo appointmentStatusClass($row['status']); ?>"><?php echo h($row['status']); ?></em>
                            </button>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="appointment-empty">
                            <i class="bi bi-calendar2-check"></i>
                            <strong>No reservations today.</strong>
                            <span>Upcoming reservations will appear here.</span>
                        </div>
                    <?php endif; ?>
                </div>
            </article>

            <aside class="appointment-card appointment-calendar-card">
                <div class="appointment-card-heading">
                    <h2><?php echo date('F Y'); ?></h2>
                </div>
                <div class="appointment-calendar">
                    <?php foreach (['Mo', 'Tu', 'We', 'Th', 'Fr', 'Sa', 'Su'] as $day): ?><strong><?php echo $day; ?></strong><?php endforeach; ?>
                    <?php
                    $first_day = (int)date('N', strtotime(date('Y-m-01')));
                    $days_in_month = (int)date('t');
                    for ($blank = 1; $blank < $first_day; $blank++): ?><span></span><?php endfor; ?>
                    <?php for ($day = 1; $day <= $days_in_month; $day++): ?>
                        <button
                            type="button"
                            class="<?php echo $day === (int)date('j') ? 'is-today' : ''; ?> <?php echo isset($calendar_days[$day]) ? 'has-reservation' : ''; ?>"
                            data-calendar-day="<?php echo $day; ?>">
                            <?php echo $day; ?>
                        </button>
                    <?php endfor; ?>
                </div>
                <div class="appointment-calendar-summary">
                    <strong id="calendarSummaryDate"><?php echo date('F d'); ?></strong>
                    <span id="calendarSummaryCount">0 reservations</span>
                    <div id="calendarSummaryEvents"></div>
                </div>
            </aside>
        </section>

        <section class="appointments-grid appointment-secondary-grid">
            <article class="appointment-card appointment-upcoming-card">
                <div class="appointment-card-heading">
                    <h2>Upcoming Reservations</h2>
                </div>
                <div class="appointment-upcoming-list">
                    <?php foreach ($upcoming_reservations as $row):
                        $resident_name = appointmentResidentName($row);
                        $type = reservationTypeLabel($row['facility_name']);
                    ?>
                        <button class="appointment-upcoming-item appointment-open-trigger"
                            type="button"
                            data-type="<?php echo h($type); ?>"
                            data-name="<?php echo h($resident_name); ?>"
                            data-email="<?php echo h($row['email']); ?>"
                            data-phone="<?php echo h($row['mobile_number'] ?? 'N/A'); ?>"
                            data-ref="<?php echo h($row['reference_no']); ?>"
                            data-date="<?php echo h(appointmentDate($row['reservation_date'], 'F d, Y')); ?>"
                            data-time="<?php echo h(appointmentTime($row['start_time']) . ' - ' . appointmentTime($row['end_time'])); ?>"
                            data-purpose="<?php echo h($row['purpose']); ?>"
                            data-guests="<?php echo h((string)$row['expected_guests']); ?>"
                            data-notes="<?php echo h($row['additional_notes'] ?? ''); ?>"
                            data-status="<?php echo h($row['status']); ?>"
                            data-filter-type="<?php echo h($type); ?>"
                            data-id="<?php echo (int)$row['reservation_id']; ?>">
                            <time><strong><?php echo appointmentDate($row['reservation_date'], 'M d'); ?></strong><span><?php echo appointmentTime($row['start_time']); ?></span></time>
                            <span><strong><?php echo h($type); ?></strong><small><?php echo h($resident_name); ?> · <?php echo appointmentTime($row['start_time']); ?> - <?php echo appointmentTime($row['end_time']); ?></small></span>
                            <em class="<?php echo appointmentStatusClass($row['status']); ?>"><?php echo h($row['status']); ?></em>
                        </button>
                    <?php endforeach; ?>
                </div>
            </article>

            <aside class="appointment-card appointment-rules-card">
                <div class="appointment-card-heading">
                    <h2>Reservation Rules</h2>
                </div>
                <ul>
                    <li>Events Hall reservations follow the 9:00 AM to 9:00 PM window.</li>
                    <li>Court reservations follow the 8:00 AM to 8:00 PM window.</li>
                    <li>Pending bookings require staff approval before facility use.</li>
                    <li>Reject overlapping or incomplete requests before confirmation.</li>
                </ul>
            </aside>
        </section>
    </main>

    <aside class="appointment-drawer" id="appointmentDrawer" aria-label="Reservation details">
        <div class="appointment-drawer-header">
            <div>
                <span>Reservation Details</span>
                <h2 id="drawerType">Reservation</h2>
            </div>
            <button type="button" id="drawerClose" aria-label="Close details"><i class="bi bi-x-lg"></i></button>
        </div>
        <section>
            <h3>Resident Information</h3>
            <p><strong id="drawerName">N/A</strong><span id="drawerPhone">N/A</span><span id="drawerEmail">N/A</span></p>
        </section>
        <section>
            <h3>Reservation Information</h3>
            <dl>
                <div><dt>Reservation ID</dt><dd id="drawerRef">N/A</dd></div>
                <div><dt>Date</dt><dd id="drawerDate">N/A</dd></div>
                <div><dt>Time</dt><dd id="drawerTime">N/A</dd></div>
                <div><dt>Expected Guests</dt><dd id="drawerGuests">N/A</dd></div>
                <div><dt>Status</dt><dd id="drawerStatus">N/A</dd></div>
            </dl>
        </section>
        <section>
            <h3>Purpose</h3>
            <p id="drawerPurpose">N/A</p>
            <small id="drawerNotes"></small>
        </section>
        <div class="appointment-drawer-actions" data-reservation-actions="pending">
            <form action="manage_appointments.php" method="POST">
                <input type="hidden" name="reservation_id" id="approveReservationId">
                <input type="hidden" name="status" value="Approved">
                <button type="submit" name="update_reservation_status" class="approve">Approve Reservation</button>
            </form>
            <form action="manage_appointments.php" method="POST">
                <input type="hidden" name="reservation_id" id="rejectReservationId">
                <input type="hidden" name="status" value="Rejected">
                <button type="submit" name="update_reservation_status" class="reject">Reject Reservation</button>
            </form>
        </div>
        <div class="appointment-drawer-actions" data-reservation-actions="approved" hidden>
            <form action="manage_appointments.php" method="POST">
                <input type="hidden" name="reservation_id" id="completeReservationId">
                <input type="hidden" name="status" value="Completed">
                <button type="submit" name="update_reservation_status" class="approve">Mark as Completed</button>
            </form>
            <form action="manage_appointments.php" method="POST">
                <input type="hidden" name="reservation_id" id="cancelReservationId">
                <input type="hidden" name="status" value="Cancelled">
                <button type="submit" name="update_reservation_status" class="reject">Cancel Reservation</button>
            </form>
        </div>
        <div class="appointment-drawer-actions appointment-archive-action" data-reservation-actions="archive" hidden>
            <button type="button" class="reject">Archive Reservation</button>
        </div>
    </aside>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const drawer = document.getElementById('appointmentDrawer');
            const openButtons = Array.from(document.querySelectorAll('.appointment-open-trigger'));
            const calendarReservations = <?php echo json_encode($calendar_days, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT); ?>;

            function openDrawer(button) {
                document.getElementById('drawerType').textContent = button.dataset.type || 'Reservation';
                document.getElementById('drawerName').textContent = button.dataset.name || 'N/A';
                document.getElementById('drawerPhone').textContent = button.dataset.phone || 'N/A';
                document.getElementById('drawerEmail').textContent = button.dataset.email || 'N/A';
                document.getElementById('drawerRef').textContent = button.dataset.ref || 'N/A';
                document.getElementById('drawerDate').textContent = button.dataset.date || 'N/A';
                document.getElementById('drawerTime').textContent = button.dataset.time || 'N/A';
                document.getElementById('drawerGuests').textContent = button.dataset.guests || 'N/A';
                document.getElementById('drawerStatus').textContent = button.dataset.status || 'N/A';
                document.getElementById('drawerPurpose').textContent = button.dataset.purpose || 'N/A';
                document.getElementById('drawerNotes').textContent = button.dataset.notes || '';
                document.getElementById('approveReservationId').value = button.dataset.id || '';
                document.getElementById('rejectReservationId').value = button.dataset.id || '';
                document.getElementById('completeReservationId').value = button.dataset.id || '';
                document.getElementById('cancelReservationId').value = button.dataset.id || '';

                const status = (button.dataset.status || '').toLowerCase();
                document.querySelectorAll('[data-reservation-actions]').forEach(actions => {
                    const actionType = actions.dataset.reservationActions;
                    const showArchive = actionType === 'archive' && ['completed', 'cancelled', 'rejected'].includes(status);
                    actions.hidden = actionType !== status && !showArchive;
                });
                drawer.classList.add('is-open');
            }

            openButtons.forEach(button => {
                button.addEventListener('click', function() {
                    openDrawer(this);
                });
            });

            document.getElementById('drawerClose').addEventListener('click', function() {
                drawer.classList.remove('is-open');
            });

            document.querySelectorAll('[data-appointment-filter]').forEach(button => {
                button.addEventListener('click', function() {
                    const filter = this.dataset.appointmentFilter;
                    document.querySelectorAll('[data-appointment-filter]').forEach(item => item.classList.remove('is-active'));
                    this.classList.add('is-active');
                    document.querySelectorAll('.appointment-upcoming-item').forEach(item => {
                        item.hidden = filter !== 'All' && item.dataset.filterType !== filter;
                    });
                });
            });

            function showCalendarDay(day) {
                const rows = calendarReservations[day] || [];
                const selectedDate = new Date(<?php echo (int)date('Y'); ?>, <?php echo (int)date('n') - 1; ?>, day);
                document.getElementById('calendarSummaryDate').textContent = selectedDate.toLocaleDateString('en-US', {
                    month: 'long',
                    day: 'numeric'
                });
                document.getElementById('calendarSummaryCount').textContent = rows.length + (rows.length === 1 ? ' reservation' : ' reservations');

                const events = document.getElementById('calendarSummaryEvents');
                events.replaceChildren(...rows.map(row => {
                    const item = document.createElement('p');
                    const start = new Date('1970-01-01T' + row.start_time).toLocaleTimeString('en-US', { hour: 'numeric', minute: '2-digit' });
                    const end = new Date('1970-01-01T' + row.end_time).toLocaleTimeString('en-US', { hour: 'numeric', minute: '2-digit' });
                    item.textContent = 'Reserved: ' + row.facility_name + ' - ' + start + ' - ' + end;
                    return item;
                }));
            }

            document.querySelectorAll('[data-calendar-day]').forEach(button => {
                button.addEventListener('click', function() {
                    showCalendarDay(this.dataset.calendarDay);
                });
            });

            showCalendarDay(<?php echo (int)date('j'); ?>);
        });
    </script>
</body>

</html>
