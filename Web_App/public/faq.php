<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$isResidentHeader = isset($_SESSION['resident_id']);
$residentProfileHref = '../resident/profile.php';
$residentLogoutHref = '../resident/logout.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FAQ | MakiKonek</title>
    <link rel="stylesheet" href="../assets/css/home.css?v=20260613a">
    <link rel="stylesheet" href="../assets/css/header.css?v=20260613e">
    <link rel="stylesheet" href="../assets/css/footer.css?v=20260613b">
    <link rel="stylesheet" href="../assets/css/public-info.css?v=20260619a">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" rel="stylesheet">
</head>
<body class="info-page">
    <?php include __DIR__ . '/../includes/header.php'; ?>

    <main class="info-main">
        <section class="info-hero" aria-labelledby="faq-title">
            <h1 id="faq-title">Frequently Asked Questions</h1>
            <p>Quick answers for common MakiKonek service requests and account questions.</p>
        </section>

        <div class="info-list">
            <section class="info-card">
                <h2>Who can use MakiKonek?</h2>
                <p>Residents of Barangay Makiling can register, request barangay services, and monitor request updates through the portal.</p>
            </section>

            <section class="info-card">
                <h2>How do I request a barangay document?</h2>
                <p>Sign in to your resident account, open the services/request page, choose the document you need, complete the form, and submit the required details.</p>
            </section>

            <section class="info-card">
                <h2>How will I know the status of my request?</h2>
                <p>You can check your resident dashboard or notifications for updates from the barangay office.</p>
            </section>

            <section class="info-card">
                <h2>What if I entered incorrect information?</h2>
                <p>Contact the barangay office or update your profile information before submitting a new request to avoid delays.</p>
            </section>
        </div>
    </main>

    <?php include __DIR__ . '/../includes/footer.php'; ?>
</body>
</html>
