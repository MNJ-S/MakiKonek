<?php
$pageTitle = 'Resident Dashboard';
$activePage = 'dashboard';

$recentRequests = [
    ['service' => 'Barangay Clearance', 'ref' => 'BC-2026-0001', 'date' => 'May 28, 2026', 'status' => 'Approved', 'class' => 'approved'],
    ['service' => 'Certificate of Residency', 'ref' => 'CR-2026-0002', 'date' => 'May 27, 2026', 'status' => 'In Progress', 'class' => 'progress'],
    ['service' => 'Indigency Certificate', 'ref' => 'IC-2026-0003', 'date' => 'May 26, 2026', 'status' => 'Pending', 'class' => 'pending'],
];

$announcements = [
    ['title' => 'Schedule of Barangay Assembly', 'date' => 'May 25, 2026'],
    ['title' => 'Clean-Up Drive This Saturday', 'date' => 'May 30, 2026'],
    ['title' => 'Road Maintenance on Main Street', 'date' => 'May 22, 2026'],
    ['title' => 'Health and Wellness Program', 'date' => 'May 20, 2026'],
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle; ?> | MakiKonek</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/header.css?v=20260530a">
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
                <h1 id="welcome-title">Hello, Juan Dela Cruz! &#128075;</h1>
                <p>Welcome back to your dashboard</p>
            </section>

            <section class="stat-grid" aria-label="Request summary">
                <article class="stat-card">
                    <span class="stat-icon green"><i class="fa-regular fa-file-lines"></i></span>
                    <strong>3</strong>
                    <span>Total Requests</span>
                </article>
                <article class="stat-card">
                    <span class="stat-icon orange"><i class="fa-regular fa-clock"></i></span>
                    <strong>1</strong>
                    <span>In Progress</span>
                </article>
                <article class="stat-card">
                    <span class="stat-icon check"><i class="fa-solid fa-check"></i></span>
                    <strong>1</strong>
                    <span>Completed</span>
                </article>
                <article class="stat-card">
                    <span class="stat-icon purple"><i class="fa-solid fa-check-double"></i></span>
                    <strong>1</strong>
                    <span>Approved</span>
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
