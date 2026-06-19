<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$isResidentHeader = isset($_SESSION['resident_id']);
$residentProfileHref = '../resident/profile.php';
$residentLogoutHref = '../resident/logout.php';
$serviceRequestHref = $isResidentHeader ? '../resident/requests.php' : '../login_reg.php';
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>E-Services - MakiKonek</title>

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

    <link rel="stylesheet" href="../assets/css/home.css?v=20260613a">
    <link rel="stylesheet" href="../assets/css/header.css?v=20260613e">
    <link rel="stylesheet" href="../assets/css/services.css?v=20260613a">
    <link rel="stylesheet" href="../assets/css/footer.css?v=20260613b">
</head>

<body>

    <?php include '../includes/header.php'; ?>

    <!-- Services Content -->
    <main class="services-container">
        <header class="services-hero site-hero">
            <div class="hero-inner-frame">
                <div class="hero-copy">
                    <span class="hero-tag">Digital Barangay Services</span>
                    <h1>Apply Barangay Services Online</h1>
                    <p>Request clearances, certificates, permits, and other barangay documents without visiting the hall. Submit requirements, upload files, and track your application progress anytime.</p>
                </div>

                </div>
        </header>

        <section class="services-grid-layout">

            <!-- Card 1: Barangay Clearance -->
            <article class="srv-card srv-green">
                <div class="srv-card-header">
                    <div class="srv-icon"><i class="fa-regular fa-file-lines"></i></div>
                    <h3>Barangay Clearance</h3>
                </div>
                <div class="srv-card-body">
                    <p class="srv-desc">Official clearance certificate for employment, travel, and other legal purposes.</p>
                    <div class="srv-requirements">
                        <strong>Requirements:</strong>
                        <ul>
                            <li>Valid Government ID</li>
                            <li>Proof of Residency</li>
                            <li>Recent 2×2 Photo</li>
                        </ul>
                    </div>
                    <div class="srv-validity">
                        <i class="fa-regular fa-calendar-check"></i> Document Validity: <span>6 Months</span>
                    </div>
                    <div class="srv-meta">
                        <div class="srv-meta-row"><span>Processing Time:</span><strong>3-5 working days</strong></div>
                        <div class="srv-meta-row"><span>Fee:</span><strong>₱100.00</strong></div>
                    </div>
                    <a href="<?php echo $serviceRequestHref; ?>" class="srv-btn">Request Now</a>
                </div>
            </article>

            <!-- Card 2: Certificate of Residency -->
            <article class="srv-card srv-blue">
                <div class="srv-card-header">
                    <div class="srv-icon"><i class="fa-regular fa-file-lines"></i></div>
                    <h3>Certificate of Residency</h3>
                </div>
                <div class="srv-card-body">
                    <p class="srv-desc">Proof of residence document for various transactions and government requirements.</p>
                    <div class="srv-requirements">
                        <strong>Requirements:</strong>
                        <ul>
                            <li>Valid Government ID</li>
                            <li>Proof of Address</li>
                            <li>Barangay ID (if available)</li>
                        </ul>
                    </div>
                    <div class="srv-validity">
                        <i class="fa-regular fa-calendar-check"></i> Document Validity: <span>3 Months</span>
                    </div>
                    <div class="srv-meta">
                        <div class="srv-meta-row"><span>Processing Time:</span><strong>2-3 working days</strong></div>
                        <div class="srv-meta-row"><span>Fee:</span><strong>₱50.00</strong></div>
                    </div>
                    <a href="<?php echo $serviceRequestHref; ?>" class="srv-btn">Request Now</a>
                </div>
            </article>

            <!-- Card 3: Business Clearance -->
            <article class="srv-card srv-orange">
                <div class="srv-card-header">
                    <div class="srv-icon"><i class="fa-regular fa-file-lines"></i></div>
                    <h3>Business Clearance</h3>
                </div>
                <div class="srv-card-body">
                    <p class="srv-desc">Required clearance for business permits and commercial operations within the barangay.</p>
                    <div class="srv-requirements">
                        <strong>Requirements:</strong>
                        <ul>
                            <li>Business Permit Application</li>
                            <li>DTI/SEC Registration</li>
                            <li>Lease Contract or Proof of Ownership</li>
                            <li>Valid ID of Owner</li>
                        </ul>
                    </div>
                    <div class="srv-validity">
                        <i class="fa-regular fa-calendar-check"></i> Document Validity: <span>1 Year</span>
                    </div>
                    <div class="srv-meta">
                        <div class="srv-meta-row"><span>Processing Time:</span><strong>5-7 working days</strong></div>
                        <div class="srv-meta-row"><span>Fee:</span><strong>₱500.00</strong></div>
                    </div>
                    <a href="<?php echo $serviceRequestHref; ?>" class="srv-btn">Request Now</a>
                </div>
            </article>

            <!-- Card 4: Indigency Certificate -->
            <article class="srv-card srv-yellow">
                <div class="srv-card-header">
                    <div class="srv-icon"><i class="fa-regular fa-file-lines"></i></div>
                    <h3>Indigency Certificate</h3>
                </div>
                <div class="srv-card-body">
                    <p class="srv-desc">Certificate for financial assistance, medical aid, and social welfare programs.</p>
                    <div class="srv-requirements">
                        <strong>Requirements:</strong>
                        <ul>
                            <li>Valid Government ID</li>
                            <li>Barangay Residency Certificate</li>
                            <li>Supporting Documents (if applicable)</li>
                        </ul>
                    </div>
                    <div class="srv-validity">
                        <i class="fa-regular fa-calendar-check"></i> Document Validity: <span>3 Months</span>
                    </div>
                    <div class="srv-meta">
                        <div class="srv-meta-row"><span>Processing Time:</span><strong>2-3 working days</strong></div>
                        <div class="srv-meta-row"><span>Fee:</span><strong class="fee-free">Free</strong></div>
                    </div>
                    <a href="<?php echo $serviceRequestHref; ?>" class="srv-btn">Request Now</a>
                </div>
            </article>

            <!-- Card 5: Incident Report -->
            <article class="srv-card srv-cyan">
                <div class="srv-card-header">
                    <div class="srv-icon"><i class="fa-regular fa-file-lines"></i></div>
                    <h3>Incident Report</h3>
                </div>
                <div class="srv-card-body">
                    <p class="srv-desc">Official documentation of incidents and blotter reports for legal proceedings.</p>
                    <div class="srv-requirements">
                        <strong>Requirements:</strong>
                        <ul>
                            <li>Valid Government ID</li>
                            <li>Witness Information (if any)</li>
                            <li>Supporting Evidence</li>
                        </ul>
                    </div>
                    <div class="srv-validity">
                        <i class="fa-regular fa-calendar-check"></i> Document Validity: <span>Permanent Record</span>
                    </div>
                    <div class="srv-meta">
                        <div class="srv-meta-row"><span>Processing Time:</span><strong>1-2 working days</strong></div>
                        <div class="srv-meta-row"><span>Fee:</span><strong>₱50.00</strong></div>
                    </div>
                    <a href="<?php echo $serviceRequestHref; ?>" class="srv-btn">Request Now</a>
                </div>
            </article>

            <!-- Card 6: Barangay ID -->
            <article class="srv-card srv-violet">
                <div class="srv-card-header">
                    <div class="srv-icon"><i class="fa-regular fa-file-lines"></i></div>
                    <h3>Barangay ID</h3>
                </div>
                <div class="srv-card-body">
                    <p class="srv-desc">Official barangay identification card for residents of Barangay Makiling.</p>
                    <div class="srv-requirements">
                        <strong>Requirements:</strong>
                        <ul>
                            <li>Valid Government ID</li>
                            <li>Proof of Residency</li>
                            <li>2×2 Photo (2 copies)</li>
                        </ul>
                    </div>
                    <div class="srv-validity">
                        <i class="fa-regular fa-calendar-check"></i> Document Validity: <span>1 Year</span>
                    </div>
                    <div class="srv-meta">
                        <div class="srv-meta-row"><span>Processing Time:</span><strong>7-10 working days</strong></div>
                        <div class="srv-meta-row"><span>Fee:</span><strong>₱150.00</strong></div>
                    </div>
                    <a href="<?php echo $serviceRequestHref; ?>" class="srv-btn">Request Now</a>
                </div>
            </article>

            <!-- Card 7: Cedula -->
            <article class="srv-card srv-pink">
                <div class="srv-card-header">
                    <div class="srv-icon"><i class="fa-regular fa-file-lines"></i></div>
                    <h3>Cedula</h3>
                </div>
                <div class="srv-card-body">
                    <p class="srv-desc">Community Tax Certificate for residents and business entities.</p>
                    <div class="srv-requirements">
                        <strong>Requirements:</strong>
                        <ul>
                            <li>Valid Government ID</li>
                            <li>Proof of Income/Business</li>
                            <li>Previous Cedula (if renewing)</li>
                        </ul>
                    </div>
                    <div class="srv-validity">
                        <i class="fa-regular fa-calendar-check"></i> Document Validity: <span>1 Year</span>
                    </div>
                    <div class="srv-meta">
                        <div class="srv-meta-row"><span>Processing Time:</span><strong>Same day</strong></div>
                        <div class="srv-meta-row"><span>Fee:</span><strong class="fee-varies">Varies by income</strong></div>
                    </div>
                    <a href="<?php echo $serviceRequestHref; ?>" class="srv-btn">Request Now</a>
                </div>
            </article>

            <!-- Card 8: Good Moral Certificate -->
            <article class="srv-card srv-amber">
                <div class="srv-card-header">
                    <div class="srv-icon"><i class="fa-regular fa-file-lines"></i></div>
                    <h3>Good Moral Certificate</h3>
                </div>
                <div class="srv-card-body">
                    <p class="srv-desc">Certification of good moral character for employment and educational purposes.</p>
                    <div class="srv-requirements">
                        <strong>Requirements:</strong>
                        <ul>
                            <li>Valid Government ID</li>
                            <li>Barangay Clearance</li>
                            <li>Letter of Request</li>
                        </ul>
                    </div>
                    <div class="srv-validity">
                        <i class="fa-regular fa-calendar-check"></i> Document Validity: <span>6 Months</span>
                    </div>
                    <div class="srv-meta">
                        <div class="srv-meta-row"><span>Processing Time:</span><strong>3-5 working days</strong></div>
                        <div class="srv-meta-row"><span>Fee:</span><strong>₱75.00</strong></div>
                    </div>
                    <a href="<?php echo $serviceRequestHref; ?>" class="srv-btn">Request Now</a>
                </div>
            </article>

            <!-- Card 9: Permit to Construct -->
            <article class="srv-card srv-teal">
                <div class="srv-card-header">
                    <div class="srv-icon"><i class="fa-regular fa-file-lines"></i></div>
                    <h3>Permit to Construct</h3>
                </div>
                <div class="srv-card-body">
                    <p class="srv-desc">Building permit for minor construction, renovation, and repairs within the barangay.</p>
                    <div class="srv-requirements">
                        <strong>Requirements:</strong>
                        <ul>
                            <li>Building Plan</li>
                            <li>Land Title or Tax Declaration</li>
                            <li>Owner's Valid ID</li>
                            <li>Occupancy Permit (if applicable)</li>
                        </ul>
                    </div>
                    <div class="srv-validity">
                        <i class="fa-regular fa-calendar-check"></i> Document Validity: <span>1 Year</span>
                    </div>
                    <div class="srv-meta">
                        <div class="srv-meta-row"><span>Processing Time:</span><strong>7-14 working days</strong></div>
                        <div class="srv-meta-row"><span>Fee:</span><strong>₱300.00</strong></div>
                    </div>
                    <a href="<?php echo $serviceRequestHref; ?>" class="srv-btn">Request Now</a>
                </div>
            </article>

        </section>
    </main>

    <?php include '../includes/footer.php'; ?>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const navToggle = document.getElementById('navToggle');
            const navMenu = document.getElementById('navMenu');

            if (navToggle && navMenu) {
                navToggle.addEventListener('click', function() {
                    navMenu.classList.toggle('is-open');
                });
            }
        });
    </script>
</body>

</html>
