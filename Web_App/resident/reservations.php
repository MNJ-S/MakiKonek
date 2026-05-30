<?php
$pageTitle = 'Facility Reservations';
$activePage = 'reservations';
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
            <header class="page-heading">
                <h1>Facility Reservations</h1>
            </header>


            <div class="facility-grid">
                <article class="facility-card court">
                    <div class="facility-icon">
                        <i class="fa-regular fa-calendar-days"></i>
                    </div>
                    <h2>Basketball Court</h2>
                    <p>Reserve the basketball court for sports activities and events</p>
                    <a href="#" class="book-now-btn">Book Now</a>
                </article>

                <article class="facility-card hall">
                    <div class="facility-icon">
                        <i class="fa-regular fa-calendar-days"></i>
                    </div>
                    <h2>Events Hall</h2>
                    <p>Book the events hall for celebrations, meetings, and gatherings</p>
                    <a href="#" class="book-now-btn">Book Now</a>
                </article>
            </div>

            <section class="reservations-container">
                <h2>My Reservations</h2>
                <div class="empty-reservations">
                    <i class="fa-regular fa-calendar"></i>
                    <h3>No reservations yet</h3>
                    <p>Book a facility to see your reservations here</p>
                </div>
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