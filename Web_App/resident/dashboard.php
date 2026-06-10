<?php
session_start();
require_once __DIR__ . '/../includes/db_connect.php';

// FORCE LOGIN
if (!isset($_SESSION['resident_id'])) {
    header("Location: ../login_reg.php");
    exit();
}

$resident_id = $_SESSION['resident_id'];
$display_name = "Resident";

// IDENTIFICATION
$name_query = "SELECT first_name FROM user_profiles WHERE user_id = ?";
$stmt = mysqli_prepare($conn, $name_query);
mysqli_stmt_bind_param($stmt, "i", $resident_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

if ($row = mysqli_fetch_assoc($result)) {
    $display_name = $row['first_name'];
}
mysqli_stmt_close($stmt);

$pageTitle = 'Resident Dashboard';
$activePage = 'dashboard';

// Fetch Recent Requests (Documents + Reservations)
$recentRequests = [];
$req_query = "
    (SELECT dt.name AS service, sr.reference_no AS ref, sr.created_at AS date, sr.status 
     FROM service_requests sr 
     JOIN document_types dt ON sr.document_type_id = dt.document_type_id 
     WHERE sr.user_id = ?)
    UNION ALL
    (SELECT f.name AS service, fr.reference_no AS ref, fr.created_at AS date, fr.status 
     FROM facility_reservations fr 
     JOIN facilities f ON fr.facility_id = f.facility_id 
     WHERE fr.user_id = ?)
    ORDER BY date DESC LIMIT 5
";
$req_stmt = mysqli_prepare($conn, $req_query);
mysqli_stmt_bind_param($req_stmt, "ii", $resident_id, $resident_id);
mysqli_stmt_execute($req_stmt);
$req_result = mysqli_stmt_get_result($req_stmt);

while ($row = mysqli_fetch_assoc($req_result)) {
    $status = $row['status'];
    $class = 'pending';
    if (strtolower($status) === 'approved' || strtolower($status) === 'completed') {
        $class = 'approved';
    } elseif (strtolower($status) === 'processing' || strtolower($status) === 'under review' || strtolower($status) === 'in progress') {
        $class = 'progress';
    } elseif (strtolower($status) === 'rejected' || strtolower($status) === 'cancelled') {
        $class = 'rejected'; 
    }
    
    $recentRequests[] = [
        'service' => $row['service'],
        'ref' => $row['ref'],
        'date' => date('M d, Y', strtotime($row['date'])),
        'status' => $status,
        'class' => $class
    ];
}
mysqli_stmt_close($req_stmt);

// Fetch Stats
$total_requests = 0;
$in_progress = 0;
$completed = 0;
$approved = 0;

$stats_query = "
    SELECT status FROM service_requests WHERE user_id = ?
    UNION ALL
    SELECT status FROM facility_reservations WHERE user_id = ?
";
$stats_stmt = mysqli_prepare($conn, $stats_query);
mysqli_stmt_bind_param($stats_stmt, "ii", $resident_id, $resident_id);
mysqli_stmt_execute($stats_stmt);
$stats_result = mysqli_stmt_get_result($stats_stmt);

while ($row = mysqli_fetch_assoc($stats_result)) {
    $total_requests++;
    $s = strtolower($row['status']);
    if ($s === 'processing' || $s === 'under review' || $s === 'in progress') {
        $in_progress++;
    } elseif ($s === 'completed') {
        $completed++;
    } elseif ($s === 'approved') {
        $approved++;
    }
}
mysqli_stmt_close($stats_stmt);

// Fetch Announcements
$announcements = [];
$ann_query = "SELECT title, created_at AS date FROM announcements WHERE status = 'Published' ORDER BY created_at DESC LIMIT 4";
$ann_result = mysqli_query($conn, $ann_query);
if ($ann_result) {
    while ($row = mysqli_fetch_assoc($ann_result)) {
        $announcements[] = [
            'title' => $row['title'],
            'date' => date('M d, Y', strtotime($row['date']))
        ];
    }
}
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
            <section class="welcome-card" aria-labelledby="welcome-title">
                <h1 id="welcome-title">Hello, <?php echo htmlspecialchars($display_name); ?>! &#128075;</h1>
                <p>Welcome back to your dashboard</p>
            </section>

            <section class="stat-grid" aria-label="Request summary">
                <article class="stat-card">
                    <span class="stat-icon green"><i class="fa-regular fa-file-lines"></i></span>
                    <div class="stat-content">
                        <strong><?php echo $total_requests; ?></strong>
                        <span>Total Requests</span>
                    </div>
                </article>
                <article class="stat-card">
                    <span class="stat-icon orange"><i class="fa-regular fa-clock"></i></span>
                    <div class="stat-content">
                        <strong><?php echo $in_progress; ?></strong>
                        <span>In Progress</span>
                    </div>
                </article>
                <article class="stat-card">
                    <span class="stat-icon check"><i class="fa-regular fa-circle-check"></i></span>
                    <div class="stat-content">
                        <strong><?php echo $completed; ?></strong>
                        <span>Completed</span>
                    </div>
                </article>
                <article class="stat-card">
                    <span class="stat-icon purple"><i class="fa-regular fa-circle-check"></i></span>
                    <div class="stat-content">
                        <strong><?php echo $approved; ?></strong>
                        <span>Approved</span>
                    </div>
                </article>
            </section>

            <section class="dashboard-grid">
                <article class="dashboard-card">
                    <h2>Recent Requests</h2>
                    <table class="requests-table">
                        <thead>
                            <tr>
                                <th>Service</th>
                                <th>Reference No.</th>
                                <th>Date Requested</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($recentRequests as $request): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($request['service']); ?></td>
                                    <td><?php echo htmlspecialchars($request['ref']); ?></td>
                                    <td><?php echo htmlspecialchars($request['date']); ?></td>
                                    <td><span class="status <?php echo $request['class']; ?>">&#8226; <?php echo htmlspecialchars($request['status']); ?></span></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                    <a class="view-all-btn" href="requests.php">View All Requests</a>
                </article>

                <aside class="dashboard-card">
                    <h2>Announcements</h2>
                    <div class="announcement-list">
                        <?php foreach ($announcements as $announcement): ?>
                            <article>
                                <h3><?php echo htmlspecialchars($announcement['title']); ?></h3>
                                <time datetime="<?php echo date('Y-m-d', strtotime($announcement['date'])); ?>"><?php echo htmlspecialchars($announcement['date']); ?></time>
                            </article>
                        <?php endforeach; ?>
                    </div>
                    <a class="view-all-btn" href="../public/announcements.php">View All Announcements</a>
                </aside>
            </section>
        </main>
    </div>

    <?php
    $footerBase = '../public/';
    $footerAssetBase = '../assets';
    include __DIR__ . '/../includes/footer.php';
    ?>
</body>

</html>
