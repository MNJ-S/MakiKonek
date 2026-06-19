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
    <title>Privacy Policy | MakiKonek</title>
    <link rel="stylesheet" href="../assets/css/home.css?v=20260613a">
    <link rel="stylesheet" href="../assets/css/header.css?v=20260613e">
    <link rel="icon" href="../assets/img/Barangay_Makiling_Seal.png" type="image/png">
    <link rel="stylesheet" href="../assets/css/footer.css?v=20260613b">
    <link rel="stylesheet" href="../assets/css/public-info.css?v=20260619a">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" rel="stylesheet">
</head>

<body class="info-page">
    <?php include __DIR__ . '/../includes/header.php'; ?>

    <main class="info-main">
        <section class="info-hero" aria-labelledby="privacy-title">
            <h1 id="privacy-title">Privacy Policy</h1>
            <p>Learn how MakiKonek collects, uses, stores, and protects resident information in compliance with the Data Privacy Act of 2012.</p>
        </section>

        <div class="info-list">
            <section class="info-card">
                <h2>Information We Collect</h2>
                <p>We collect information needed to verify residency and process barangay services, including name, contact details, address, birthdate, submitted IDs, request details, and payment or transaction references when required.</p>
            </section>

            <section class="info-card">
                <h2>How We Use Your Information</h2>
                <p>Your information is used to confirm your identity, process document requests, coordinate barangay services, send service updates, maintain request records, and support transparent local government transactions.</p>
            </section>

            <section class="info-card">
                <h2>Data Protection and Security</h2>
                <p>MakiKonek applies reasonable technical and administrative safeguards to protect resident information from unauthorized access, misuse, alteration, disclosure, or loss.</p>
            </section>

            <section class="info-card">
                <h2>Data Retention</h2>
                <p>Resident records and transaction information are kept only for as long as needed for service processing, legal compliance, audit requirements, and official barangay recordkeeping.</p>
            </section>

            <section class="info-card">
                <h2>User Rights</h2>
                <p>You may request access, correction, or clarification regarding your personal information, subject to verification and applicable requirements under the Data Privacy Act of 2012.</p>
            </section>

            <section class="info-card">
                <h2>Contact Information</h2>
                <p>For privacy-related questions or requests, contact Barangay Makiling through the official contact details provided on this website or visit the barangay office during office hours.</p>
            </section>
        </div>
    </main>

    <?php include __DIR__ . '/../includes/footer.php'; ?>
</body>

</html>