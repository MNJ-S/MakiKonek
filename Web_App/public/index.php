<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MakiKonek | Barangay Makiling Digital Service Portal</title>
    <link rel="stylesheet" href="../assets/css/home.css?v=20260530a">
    <link rel="stylesheet" href="../assets/css/header.css?v=20260529e">
    <link rel="stylesheet" href="../assets/css/footer.css?v=20260529e">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" rel="stylesheet">
    <script defer src="../assets/js/public.js?v=20260530a"></script>
</head>

<body>
    <?php include '../includes/header.php'; ?>

    <main>
        <!-- Hero section -->
        <section class="hero-section" aria-labelledby="hero-title">
            <div class="hero-media" role="img" aria-label="Mt. Makiling landscape and Barangay Makiling community"></div>
            <div class="hero-overlay"></div>
            <div class="hero-content">
                <div class="hero-copy">
                    <p class="eyebrow">Digital Service Portal</p>
                    <h1 id="hero-title">
                        <span class="brand-word"><span class="maki">Maki</span><span class="konek">Konek</span></span>
                        sa Barangay Makiling Online
                    </h1>
                    <p>Mag-request ng dokumento, i-track ang aplikasyon, at manatiling updated sa isang digital platform.</p>
                    <div class="hero-actions">
                        <a class="btn btn-primary" href="../login_reg.php">Request Service</a>
                        <a class="btn btn-glass" href="#announcements">Browse Announcements</a>
                    </div>
                    <div class="hero-status" aria-label="Quick portal highlights">
                        <article>
                            <strong>5</strong>
                            <span>Available services</span>
                        </article>
                        <article>
                            <strong>24/7</strong>
                            <span>Online access</span>
                        </article>
                        <article>
                            <strong>Active</strong>
                            <span>Announcements</span>
                        </article>
                    </div>
                </div>


            </div>
            <div class="hero-wave" aria-hidden="true"></div>
        </section>

        <!-- About preview -->
        <section class="section intro-band" id="about">
            <div class="section-heading compact">
                <p class="eyebrow">Barangay Makiling</p>
                <h2>Digital access for a growing community</h2>
                <p>Barangay Makiling is a rural growth area of Calamba with five puroks, local schools, public service offices, and community programs guided by transparent and responsive barangay leadership.</p>
            </div>
        </section>

        <!-- Services overview -->
        <section class="section service-section" id="services">
            <div class="service-toolbar">
                <div>
                    <p class="eyebrow">Available Services</p>
                    <h2>Online Barangay Services</h2>
                    <p>Fast, convenient, and reliable services for every resident of Barangay Makiling.</p>
                </div>
                <form class="service-search" action="#services" role="search">
                    <label for="service_search">Search services</label>
                    <input id="service_search" type="search" placeholder="Search services...">
                    <button type="submit">Search</button>
                </form>
            </div>

            <div class="service-grid">
                <article class="service-card">
                    <div class="service-icon green">▣</div>
                    <h3>Barangay Clearance</h3>
                    <p>Request a Barangay Clearance online.</p>
                    <a href="../login_reg.php">Learn more</a>
                </article>
                <article class="service-card">
                    <div class="service-icon blue">⌂</div>
                    <h3>Certificate of Residency</h3>
                    <p>Get proof of residency for official transactions.</p>
                    <a href="../login_reg.php">Learn more</a>
                </article>
                <article class="service-card">
                    <div class="service-icon orange">●</div>
                    <h3>Indigency Certificate</h3>
                    <p>Request an Indigency Certificate for assistance programs.</p>
                    <a href="../login_reg.php">Learn more</a>
                </article>
                <article class="service-card">
                    <div class="service-icon green">▦</div>
                    <h3>Event Permit</h3>
                    <p>Apply for permits for community and private events.</p>
                    <a href="../login_reg.php">Learn more</a>
                </article>
                <article class="service-card">
                    <div class="service-icon violet">▤</div>
                    <h3>Business Permit</h3>
                    <p>Apply for business permits and barangay registration.</p>
                    <a href="../login_reg.php">Learn more</a>
                </article>
                <article class="service-card">
                    <div class="service-icon blue">•••</div>
                    <h3>Other Services</h3>
                    <p>Explore additional services offered by the barangay.</p>
                    <a href="../login_reg.php">View all</a>
                </article>
            </div>

            <div class="process-section">
                <div class="section-heading split compact-left">
                    <div>
                        <p class="eyebrow">How it works</p>
                        <h2>Simple request process</h2>
                    </div>
                </div>
                <div class="process-grid">
                    <article><span>1</span><strong>Choose a Service</strong>
                        <p>Select the service you need from the list.</p>
                    </article>
                    <article><span>2</span><strong>Fill Out the Form</strong>
                        <p>Enter your details and upload required documents.</p>
                    </article>
                    <article><span>3</span><strong>Submit Request</strong>
                        <p>Review and submit your request for processing.</p>
                    </article>
                    <article><span>4</span><strong>Wait for Updates</strong>
                        <p>Receive status updates from the barangay office.</p>
                    </article>
                    <article><span>5</span><strong>Claim Result</strong>
                        <p>Claim your document once processing is complete.</p>
                    </article>
                </div>
            </div>
        </section>

        <!-- Announcements -->
        <section class="section announcement-section" id="announcements">
            <div class="section-heading split">
                <div>
                    <p class="eyebrow">Latest Announcements</p>
                    <h2>Stay Updated</h2>
                </div>
                <a href="announcements.php" class="text-link">View All Announcements</a>
            </div>

            <div class="announcement-grid">
                <article class="announcement-card">
                    <span class="badge green">Announcement</span>
                    <h3>Schedule of Barangay Assembly</h3>
                    <p>Please be informed that the Barangay Assembly will be held on May 25, 2026 at the Barangay Hall.</p>
                    <time datetime="2026-05-18">May 18, 2026</time>
                </article>
                <article class="announcement-card">
                    <span class="badge blue">News</span>
                    <h3>Clean-Up Drive This Saturday</h3>
                    <p>Join us this May 30, 2026 for our community clean-up drive. Together, let us keep Makiling clean.</p>
                    <time datetime="2026-05-16">May 16, 2026</time>
                </article>
                <article class="announcement-card">
                    <span class="badge yellow">Advisory</span>
                    <h3>Road Maintenance on Main Street</h3>
                    <p>Please be advised of road maintenance on May 22 to 23, 2026. Thank you for your cooperation.</p>
                    <time datetime="2026-05-15">May 15, 2026</time>
                </article>
            </div>
        </section>

        <!-- Calendar -->
        <section class="section calendar-section">
            <div class="section-heading">
                <h2>Calendar of Activities</h2>
                <p>Stay updated with upcoming community events, programs, and important dates.</p>
            </div>

            <div class="calendar-panel">
                <div class="calendar-top">
                    <h3 data-calendar-title>May 2026</h3>
                    <div class="calendar-controls" aria-label="Calendar controls">
                        <button type="button" data-calendar-today>Today</button>
                        <button type="button" data-calendar-prev>Prev</button>
                        <button type="button" data-calendar-next>Next</button>
                    </div>
                </div>
                <div class="calendar-grid" data-calendar-grid aria-label="Activities calendar">
                </div>
            </div>
        </section>

        <!-- Emergency hotlines -->
        <section class="section hotline-section" id="contact">
            <div class="section-heading">
                <h2>Emergency Hotlines</h2>
                <p>Quick access to emergency contacts and services.</p>
            </div>

            <div class="hotline-grid">
                <article class="hotline-card police">
                    <span class="hotline-icon">P</span>
                    <h3>Police</h3>
                    <p>Calamba Police Station</p>
                    <strong>(049) 545 1694</strong>
                </article>
                <article class="hotline-card fire">
                    <span class="hotline-icon">F</span>
                    <h3>Fire Station</h3>
                    <p>Calamba Fire Station</p>
                    <strong>(049) 545 1695<br>0945 490 4131</strong>
                </article>
                <article class="hotline-card health">
                    <span class="hotline-icon">H</span>
                    <h3>Health Center</h3>
                    <p>Makiling Health Center</p>
                    <strong>+63 963 786 6650</strong>
                </article>
                <article class="hotline-card disaster">
                    <span class="hotline-icon">D</span>
                    <h3>Disaster Response</h3>
                    <p>Calamba City CDRRMD</p>
                    <strong>(049) 545 4119<br>+63 917 148 9813</strong>
                </article>
            </div>

            <aside class="reminder-box" aria-label="Emergency reminders">
                <h3>Emergency Reminders</h3>
                <ul>
                    <li>Stay calm and assess the situation.</li>
                    <li>Provide clear location information.</li>
                    <li>Keep emergency numbers saved.</li>
                    <li>In fire, evacuate immediately.</li>
                    <li>Follow authority instructions.</li>
                    <li>Provide patient condition details.</li>
                </ul>
            </aside>
        </section>

        <!-- Concern CTA and transparency preview -->
        <section class="section cta-section" id="transparency">
            <div class="concern-cta">
                <div class="cta-image"></div>
                <div>
                    <p class="eyebrow">Public assistance</p>
                    <h2>Have a concern or suggestion?</h2>
                    <p>We are here to listen and help.</p>
                </div>
                <a class="btn btn-secondary" href="../login_reg.php">Send a Message</a>
            </div>
        </section>
    </main>

    <?php include '../includes/footer.php'; ?>
</body>

</html>