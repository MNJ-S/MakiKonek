<?php
if (!isset($conn)) {
    return;
}

$now = date('Y-m-d H:i:s');

// 1. Get pending reservations that will be rejected to send notifications
$pending_to_reject_query = "SELECT user_id, reference_no FROM facility_reservations WHERE status = 'Pending' AND CONCAT(reservation_date, ' ', end_time) < ?";
$ptr_stmt = mysqli_prepare($conn, $pending_to_reject_query);
if ($ptr_stmt) {
    mysqli_stmt_bind_param($ptr_stmt, "s", $now);
    mysqli_stmt_execute($ptr_stmt);
    $ptr_result = mysqli_stmt_get_result($ptr_stmt);
    while ($row = mysqli_fetch_assoc($ptr_result)) {
        $notif_title = 'Reservation Rejected';
        $notif_msg = "Your reservation request (" . $row['reference_no'] . ") has been rejected due to schedule expiration.";
        $notif_icon = 'fa-regular fa-circle-xmark';
        $notif_stmt = mysqli_prepare($conn, "INSERT INTO user_notifications (user_id, title, message, type, icon) VALUES (?, ?, ?, 'Reservation Update', ?)");
        if ($notif_stmt) {
            mysqli_stmt_bind_param($notif_stmt, "isss", $row['user_id'], $notif_title, $notif_msg, $notif_icon);
            mysqli_stmt_execute($notif_stmt);
            mysqli_stmt_close($notif_stmt);
        }
    }
    mysqli_stmt_close($ptr_stmt);
}

// 2. Update past Pending reservations to Rejected
$reject_query = "
    UPDATE facility_reservations 
    SET status = 'Rejected' 
    WHERE status = 'Pending' AND CONCAT(reservation_date, ' ', end_time) < ?
";
$stmt1 = mysqli_prepare($conn, $reject_query);
if ($stmt1) {
    mysqli_stmt_bind_param($stmt1, "s", $now);
    mysqli_stmt_execute($stmt1);
    mysqli_stmt_close($stmt1);
}

// 3. Get approved reservations that will be completed to send notifications
$approved_to_complete_query = "SELECT user_id, reference_no FROM facility_reservations WHERE status = 'Approved' AND CONCAT(reservation_date, ' ', end_time) < ?";
$atc_stmt = mysqli_prepare($conn, $approved_to_complete_query);
if ($atc_stmt) {
    mysqli_stmt_bind_param($atc_stmt, "s", $now);
    mysqli_stmt_execute($atc_stmt);
    $atc_result = mysqli_stmt_get_result($atc_stmt);
    while ($row = mysqli_fetch_assoc($atc_result)) {
        $notif_title = 'Reservation Completed';
        $notif_msg = "Your reservation (" . $row['reference_no'] . ") is marked as completed.";
        $notif_icon = 'fa-solid fa-check-double';
        $notif_stmt = mysqli_prepare($conn, "INSERT INTO user_notifications (user_id, title, message, type, icon) VALUES (?, ?, ?, 'Reservation Update', ?)");
        if ($notif_stmt) {
            mysqli_stmt_bind_param($notif_stmt, "isss", $row['user_id'], $notif_title, $notif_msg, $notif_icon);
            mysqli_stmt_execute($notif_stmt);
            mysqli_stmt_close($notif_stmt);
        }
    }
    mysqli_stmt_close($atc_stmt);
}

// 4. Update past Approved reservations to Completed
$complete_query = "
    UPDATE facility_reservations 
    SET status = 'Completed' 
    WHERE status = 'Approved' AND CONCAT(reservation_date, ' ', end_time) < ?
";
$stmt2 = mysqli_prepare($conn, $complete_query);
if ($stmt2) {
    mysqli_stmt_bind_param($stmt2, "s", $now);
    mysqli_stmt_execute($stmt2);
    mysqli_stmt_close($stmt2);
}

// 5. Insert any Completed, Rejected, or Cancelled reservations into completed_reservations if not already there
$archive_query = "
    INSERT INTO completed_reservations 
    (original_reservations_id, user_id, facility_name, reference_no, reservation_date, start_time, end_time, purpose, reservation_fee, reserved_at, status)
    SELECT 
        fr.reservation_id, fr.user_id, f.name, fr.reference_no, fr.reservation_date, fr.start_time, fr.end_time, fr.purpose, fr.reservation_fee, fr.created_at, fr.status
    FROM facility_reservations fr
    JOIN facilities f ON fr.facility_id = f.facility_id
    LEFT JOIN completed_reservations cr ON fr.reservation_id = cr.original_reservations_id
    WHERE fr.status IN ('Completed', 'Rejected', 'Cancelled')
      AND cr.completed_id IS NULL
";
mysqli_query($conn, $archive_query);
?>