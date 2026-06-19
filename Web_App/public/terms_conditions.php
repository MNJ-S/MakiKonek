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
    <title>Terms and Conditions | MakiKonek</title>
    <link rel="stylesheet" href="../assets/css/home.css?v=20260613a">
    <link rel="stylesheet" href="../assets/css/header.css?v=20260613e">
    <link rel="stylesheet" href="../assets/css/footer.css?v=20260613b">
    <link rel="stylesheet" href="../assets/css/public-info.css?v=20260619a">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" rel="stylesheet">
</head>
<body class="info-page">
    <?php include __DIR__ . '/../includes/header.php'; ?>

    <main class="info-main">
        <section class="info-hero" aria-labelledby="terms-title">
            <h1 id="terms-title">Terms and Conditions</h1>
            <p>Review the rules, responsibilities, and guidelines for using the MakiKonek Digital Service Portal.</p>
        </section>

        <div class="info-list">
            <section class="info-card">
                <h2>Account Responsibility</h2>
                <p>Users are responsible for providing accurate account information, protecting login credentials, and ensuring that their MakiKonek account is used only for legitimate barangay service transactions.</p>
            </section>

            <section class="info-card">
                <h2>Service Requests</h2>
                <p>All service requests submitted through the portal are subject to barangay review, validation, and approval based on the submitted information and required documents.</p>
            </section>

            <section class="info-card">
                <h2>Payments and Fees</h2>
                <p>Applicable fees must be paid through the official payment options provided by the barangay. Requests may be delayed or rejected if payment information is incomplete or invalid.</p>
            </section>

            <section class="info-card">
                <h2>User Conduct</h2>
                <p>Users must not misuse the portal, submit misleading information, interfere with system functions, or use MakiKonek for unauthorized, harmful, or unlawful activities.</p>
            </section>

            <section class="info-card">
                <h2>Fraudulent Submissions</h2>
                <p>Falsified documents, incorrect profile data, fake payment proofs, or misleading submissions may result in request rejection, account suspension, and possible legal action under applicable laws.</p>
            </section>

            <section class="info-card">
                <h2>Limitation of Liability</h2>
                <p>MakiKonek supports digital access to barangay services, but processing timelines may vary depending on verification requirements, office schedules, system availability, and official barangay procedures.</p>
            </section>

            <section class="info-card">
                <h2>Changes to Terms</h2>
                <p>Barangay Makiling may update these terms when needed to reflect service changes, policy updates, legal requirements, or improvements to the MakiKonek portal.</p>
            </section>
        </div>
    </main>

    <?php include __DIR__ . '/../includes/footer.php'; ?>
</body>
</html>
