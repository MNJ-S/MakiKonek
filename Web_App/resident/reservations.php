<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
date_default_timezone_set('Asia/Manila');

if (!isset($_SESSION['resident_id'])) {
    header("Location: ../login_reg.php");
    exit();
}

$resident_id = $_SESSION['resident_id'];
$pageTitle = 'Facility Reservations';
$activePage = 'reservations';
$success_message = '';
$error_message = '';

$facilityOptions = [
    'Basketball Court' => [
        'class' => 'court',
        'icon' => 'fa-solid fa-basketball',
        'description' => 'Reserve the basketball court for sports activities and events',
        'fee' => 'P150.00 / hour',
        'hours' => '8:00 AM - 8:00 PM',
        'capacity' => 'Up to 30 guests',
        'accent' => 'green',
    ],
    'Events Hall' => [
        'class' => 'hall',
        'icon' => 'fa-regular fa-building',
        'description' => 'Book the events hall for celebrations, meetings, and gatherings',
        'fee' => 'P500.00 / first 3 hours',
        'hours' => '9:00 AM - 9:00 PM',
        'capacity' => 'Up to 120 guests',
        'accent' => 'blue',
    ],
];

if (!isset($_SESSION['facility_reservations'][$resident_id])) {
    $_SESSION['facility_reservations'][$resident_id] = [];
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $facility = trim($_POST['facility'] ?? '');
    $reservation_date = trim($_POST['reservation_date'] ?? '');
    $start_time = trim($_POST['start_time'] ?? '');
    $end_time = trim($_POST['end_time'] ?? '');
    $guest_count = trim($_POST['guest_count'] ?? '');
    $purpose = trim($_POST['purpose'] ?? '');
    $notes = trim($_POST['notes'] ?? '');

    $today = date('Y-m-d');

    if (!isset($facilityOptions[$facility])) {
        $error_message = 'Please choose a valid facility.';
    } elseif ($reservation_date < $today) {
        $error_message = 'Please choose today or a future date.';
    } elseif (empty($start_time) || empty($end_time) || $start_time >= $end_time) {
        $error_message = 'Please choose a valid start and end time.';
    } elseif (!is_numeric($guest_count) || (int) $guest_count < 1) {
        $error_message = 'Please enter the expected number of guests.';
    } elseif ($purpose === '') {
        $error_message = 'Please enter the purpose of reservation.';
    }

    if ($error_message === '') {
        $reference_no = 'FR-' . strtoupper(substr(uniqid(), -6));
        $_SESSION['facility_reservations'][$resident_id][] = [
            'reference_no' => $reference_no,
            'facility' => $facility,
            'reservation_date' => $reservation_date,
            'start_time' => $start_time,
            'end_time' => $end_time,
            'guest_count' => (int) $guest_count,
            'purpose' => strtoupper($purpose),
            'notes' => $notes,
            'status' => 'Pending',
            'created_at' => date('M d, Y g:i A'),
        ];

        $success_message = 'Your reservation has been submitted under Reference Number: ' . $reference_no;
    }
}

$myReservations = array_reverse($_SESSION['facility_reservations'][$resident_id]);
$minDate = date('Y-m-d');

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle; ?> | MakiKonek</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/header.css?v=20260608a">
    <link rel="stylesheet" href="../assets/css/footer.css?v=20260529e">
    <link rel="stylesheet" href="../assets/css/resident.css?v=20260530a">
</head>

<body class="resident-page">
    <?php
    $navBase = '../public/';
    $assetBase = '../assets';
    $loginHref = '../login_reg.php';
    $isResidentHeader = true;
    include __DIR__ . '/../includes/header.php';
    ?>

    <div class="resident-shell">
        <?php include __DIR__ . '/partials/resident_sidebar.php'; ?>

        <main class="resident-main">
            <header class="page-heading">
                <h1>Facility Reservations</h1>
                <p>Select a facility, choose your schedule, and submit your booking request</p>
            </header>


            <div class="facility-grid">
                <?php foreach ($facilityOptions as $facilityName => $facility): ?>
                <article class="facility-card <?php echo htmlspecialchars($facility['class']); ?>">
                    <div class="facility-icon">
                        <i class="<?php echo htmlspecialchars($facility['icon']); ?>"></i>
                    </div>
                    <h2><?php echo htmlspecialchars($facilityName); ?></h2>
                    <p><?php echo htmlspecialchars($facility['description']); ?></p>
                    <ul class="facility-meta">
                        <li><i class="fa-regular fa-clock"></i><?php echo htmlspecialchars($facility['hours']); ?></li>
                        <li><i class="fa-solid fa-users"></i><?php echo htmlspecialchars($facility['capacity']); ?></li>
                    </ul>
                    <button
                        class="book-now-btn"
                        type="button"
                        data-facility-name="<?php echo htmlspecialchars($facilityName); ?>"
                        data-facility-fee="<?php echo htmlspecialchars($facility['fee']); ?>"
                        data-facility-hours="<?php echo htmlspecialchars($facility['hours']); ?>"
                        data-facility-capacity="<?php echo htmlspecialchars($facility['capacity']); ?>"
                        data-facility-accent="<?php echo htmlspecialchars($facility['accent']); ?>">
                        Book Now
                    </button>
                </article>
                <?php endforeach; ?>
            </div>

            <section class="reservation-booking-card" data-booking-panel>
                <div class="empty-request reservation-empty" data-reservation-empty>
                    <div>
                        <i class="fa-regular fa-calendar-check"></i>
                        <h2>Choose a facility to continue</h2>
                        <p>Tap Book Now on the court or events hall card to open the reservation form</p>
                    </div>
                </div>

                <form class="reservation-form" action="reservations.php" method="POST" data-reservation-form>
                    <input type="hidden" name="facility" data-selected-facility-input>

                    <div class="reservation-form-header">
                        <div>
                            <span class="reservation-kicker">Reservation Details</span>
                            <h2 data-selected-facility-title>Facility</h2>
                            <p data-selected-facility-subtitle>Select your preferred date and time</p>
                        </div>
                        <button class="reservation-close-btn" type="button" data-clear-reservation aria-label="Close reservation form">
                            <i class="fa-solid fa-xmark"></i>
                        </button>
                    </div>

                    <div class="reservation-summary-strip">
                        <span><i class="fa-solid fa-money-bill-wave"></i><strong data-selected-facility-fee>P0.00</strong></span>
                        <span><i class="fa-regular fa-clock"></i><strong data-selected-facility-hours>Schedule</strong></span>
                        <span><i class="fa-solid fa-users"></i><strong data-selected-facility-capacity>Capacity</strong></span>
                    </div>

                    <div class="form-grid reservation-form-grid">
                        <div class="field">
                            <label>Reservation Date *</label>
                            <input type="date" name="reservation_date" min="<?php echo $minDate; ?>" required>
                        </div>
                        <div class="field">
                            <label>Start Time *</label>
                            <input type="time" name="start_time" required>
                        </div>
                        <div class="field">
                            <label>End Time *</label>
                            <input type="time" name="end_time" required>
                        </div>
                        <div class="field">
                            <label>Expected Guests *</label>
                            <input type="number" name="guest_count" min="1" max="200" placeholder="e.g. 25" required>
                        </div>
                        <div class="field full">
                            <label>Purpose *</label>
                            <input type="text" name="purpose" placeholder="e.g. Basketball practice, birthday, meeting" required oninput="this.value = this.value.toUpperCase()">
                        </div>
                        <div class="field full">
                            <label>Additional Notes</label>
                            <textarea name="notes" rows="4" placeholder="Add setup requests, equipment needs, or other details"></textarea>
                        </div>
                    </div>

                    <div class="reservation-note-box">
                        <i class="fa-solid fa-circle-info"></i>
                        <p>Reservation requests are subject to barangay staff approval. Please wait for confirmation before using the facility.</p>
                    </div>

                    <div class="form-actions reservation-actions">
                        <button class="cancel-btn" type="button" data-clear-reservation>Cancel</button>
                        <button class="submit-btn" type="submit">Submit Reservation</button>
                    </div>
                </form>
            </section>

            <section class="reservations-container">
                <h2>My Reservations</h2>
                <?php if (empty($myReservations)): ?>
                <div class="empty-reservations">
                    <i class="fa-regular fa-calendar"></i>
                    <h3>No reservations yet</h3>
                    <p>Book a facility to see your reservations here</p>
                </div>
                <?php else: ?>
                <div class="reservation-list">
                    <?php foreach ($myReservations as $reservation): ?>
                    <article class="reservation-item">
                        <div class="reservation-item-icon <?php echo $reservation['facility'] === 'Basketball Court' ? 'court' : 'hall'; ?>">
                            <i class="<?php echo $reservation['facility'] === 'Basketball Court' ? 'fa-solid fa-basketball' : 'fa-regular fa-building'; ?>"></i>
                        </div>
                        <div class="reservation-item-body">
                            <div class="reservation-item-top">
                                <div>
                                    <h3><?php echo htmlspecialchars($reservation['facility']); ?></h3>
                                    <span><?php echo htmlspecialchars($reservation['reference_no']); ?> • Submitted <?php echo htmlspecialchars($reservation['created_at']); ?></span>
                                </div>
                                <strong class="status pending"><?php echo htmlspecialchars($reservation['status']); ?></strong>
                            </div>
                            <div class="reservation-details">
                                <span><i class="fa-regular fa-calendar"></i><?php echo date('M d, Y', strtotime($reservation['reservation_date'])); ?></span>
                                <span><i class="fa-regular fa-clock"></i><?php echo date('g:i A', strtotime($reservation['start_time'])); ?> - <?php echo date('g:i A', strtotime($reservation['end_time'])); ?></span>
                                <span><i class="fa-solid fa-users"></i><?php echo htmlspecialchars((string) $reservation['guest_count']); ?> guests</span>
                            </div>
                            <p><?php echo htmlspecialchars($reservation['purpose']); ?></p>
                            <?php if (!empty($reservation['notes'])): ?>
                            <small><?php echo htmlspecialchars($reservation['notes']); ?></small>
                            <?php endif; ?>
                        </div>
                    </article>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
            </section>
        </main>
    </div>

    <div id="notification-container" class="resident-toast-stack">
        <?php if (!empty($success_message)): ?>
            <div class="toast-notification toast-success">
                <?php echo htmlspecialchars($success_message); ?>
            </div>
        <?php endif; ?>
        <?php if (!empty($error_message)): ?>
            <div class="toast-notification toast-error">
                <?php echo htmlspecialchars($error_message); ?>
            </div>
        <?php endif; ?>
    </div>

    <?php
    $footerBase = '../public/';
    $footerAssetBase = '../assets';
    include __DIR__ . '/../includes/footer.php';
    ?>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const buttons = document.querySelectorAll('.book-now-btn');
            const emptyState = document.querySelector('[data-reservation-empty]');
            const form = document.querySelector('[data-reservation-form]');
            const facilityInput = document.querySelector('[data-selected-facility-input]');
            const facilityTitle = document.querySelector('[data-selected-facility-title]');
            const facilitySubtitle = document.querySelector('[data-selected-facility-subtitle]');
            const facilityFee = document.querySelector('[data-selected-facility-fee]');
            const facilityHours = document.querySelector('[data-selected-facility-hours]');
            const facilityCapacity = document.querySelector('[data-selected-facility-capacity]');
            const clearButtons = document.querySelectorAll('[data-clear-reservation]');

            const showForm = (button) => {
                buttons.forEach((item) => item.closest('.facility-card').classList.remove('active'));
                button.closest('.facility-card').classList.add('active');

                facilityInput.value = button.dataset.facilityName;
                facilityTitle.textContent = button.dataset.facilityName;
                facilitySubtitle.textContent = 'Complete the form for ' + button.dataset.facilityName;
                facilityFee.textContent = button.dataset.facilityFee;
                facilityHours.textContent = button.dataset.facilityHours;
                facilityCapacity.textContent = button.dataset.facilityCapacity;

                form.dataset.accent = button.dataset.facilityAccent;
                emptyState.style.display = 'none';
                form.classList.add('is-visible');
                form.scrollIntoView({ behavior: 'smooth', block: 'start' });
            };

            const resetForm = () => {
                form.reset();
                form.classList.remove('is-visible');
                emptyState.style.display = 'grid';
                buttons.forEach((item) => item.closest('.facility-card').classList.remove('active'));
            };

            buttons.forEach((button) => {
                button.addEventListener('click', () => showForm(button));
            });

            clearButtons.forEach((button) => {
                button.addEventListener('click', resetForm);
            });

            form.addEventListener('submit', function(event) {
                const start = form.querySelector('[name="start_time"]').value;
                const end = form.querySelector('[name="end_time"]').value;

                if (start && end && start >= end) {
                    event.preventDefault();
                    alert('End time must be later than start time.');
                }
            });

            document.querySelectorAll('.toast-notification').forEach((toast) => {
                setTimeout(() => {
                    toast.style.opacity = '0';
                    setTimeout(() => toast.remove(), 500);
                }, 5000);
            });
        });
    </script>
</body>

</html>
