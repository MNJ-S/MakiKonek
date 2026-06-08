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
    <title>Announcements | MakiKonek Digital Service Portal</title>
    <link rel="stylesheet" href="../assets/css/home.css?v=20260529h">
    <link rel="stylesheet" href="../assets/css/header.css?v=20260608b">
    <link rel="stylesheet" href="../assets/css/footer.css?v=20260529e">
    <link rel="stylesheet" href="../assets/css/announcements.css?v=20260530c">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">
    <script defer src="../assets/js/announcements.js?v=20260529a"></script>
</head>

<body>
    <?php include '../includes/header.php'; ?>

    <main class="ann-page">
        <section class="ann-hero" aria-labelledby="ann-title">
            <div class="ann-hero-copy">
                <!-- <nav class="ann-breadcrumb" aria-label="Breadcrumb">
                    <a href="index.php"><i class="fa-solid fa-house"></i></a>
                    <span>/</span>
                    <span>Announcements</span>
                </nav> -->
                <h1 id="ann-title">Latest <span>Announcements</span> and <strong>News</strong></h1>
                <p>Stay updated on important announcements, programs, advisories, events, and activities in Barangay Makiling.</p>
            </div>

            <div class="ann-hero-art">
                <div class="ann-mountain"></div>
                <article class="featured-announcement" aria-label="Featured announcement">
                    <span>Featured Announcement</span>
                    <h2>Barangay Cleanup Drive 2026</h2>
                    <p>Join us on May 30, 2026 at the Covered Court. Assembly starts at 6:00 AM.</p>
                    <div>
                        <small><i class="fa-regular fa-clock"></i> 6:00 AM</small>
                        <small><i class="fa-solid fa-location-dot"></i> Covered Court</small>
                    </div>
                    <a href="#latest-announcements">Read More</a>
                </article>
            </div>
        </section>

        <section class="ann-tools" aria-label="Search and filters">
            <form class="ann-search" role="search">
                <i class="fa-solid fa-magnifying-glass" aria-hidden="true"></i>
                <label for="announcement-search">Search announcements</label>
                <input id="announcement-search" type="search" placeholder="Search announcements...">
            </form>

            <div class="ann-filters" aria-label="Announcement categories">
                <button class="active" type="button" data-category="all">All</button>
                <button type="button" data-category="anunsyo">Announcements</button>
                <button type="button" data-category="programa">Programs</button>
                <button type="button" data-category="abiso">Advisories</button>
                <button type="button" data-category="events">Events</button>
            </div>
        </section>

        <div class="ann-layout">
            <section class="ann-feed" aria-labelledby="latest-announcements">
                <div class="section-title">
                    <h2 id="latest-announcements">Latest Announcements</h2>
                </div>

                <article class="announcement-post" data-category="programa">
                    <div class="post-media cleanup-poster" role="img" aria-label="Barangay cleanup drive poster">
                        <i class="fa-solid fa-broom"></i>
                        <strong>Community Cleanup</strong>
                        <span>Program</span>
                    </div>
                    <div class="post-body">
                        <div class="post-top">
                            <span class="tag green">Programs</span>
                            <button type="button" aria-label="Save announcement"><i class="fa-regular fa-bookmark"></i></button>
                        </div>
                        <h3>Barangay Cleanup Drive 2026</h3>
                        <p>Tara, makiisa sa paglilinis ng ating komunidad para sa mas malinis at ligtas na Barangay Makiling.</p>
                        <div class="post-meta">
                            <span><i class="fa-regular fa-calendar"></i> May 30, 2026</span>
                            <span><i class="fa-regular fa-clock"></i> 6:00 AM</span>
                            <span><i class="fa-solid fa-location-dot"></i> Covered Court</span>
                        </div>
                    </div>
                </article>

                <article class="announcement-post" data-category="abiso">
                    <div class="post-media power-poster" role="img" aria-label="Scheduled power interruption poster">
                        <i class="fa-solid fa-bolt"></i>
                        <strong>Power Advisory</strong>
                        <span>Advisory</span>
                    </div>
                    <div class="post-body">
                        <div class="post-top">
                            <span class="tag orange">Advisory</span>
                            <button type="button" aria-label="Save announcement"><i class="fa-regular fa-bookmark"></i></button>
                        </div>
                        <h3>Scheduled Power Interruption</h3>
                        <p>Magkakaroon ng power interruption sa ilang bahagi ng Barangay Makiling sa darating na Sabado.</p>
                        <div class="post-meta">
                            <span><i class="fa-regular fa-calendar"></i> May 31, 2026</span>
                            <span><i class="fa-regular fa-clock"></i> 9:00 AM - 3:00 PM</span>
                            <span><i class="fa-solid fa-location-dot"></i> Zone 1, 2, 3</span>
                        </div>
                    </div>
                </article>

                <article class="announcement-post" data-category="programa">
                    <div class="post-media food-poster" role="img" aria-label="Food pack distribution poster">
                        <i class="fa-solid fa-box-open"></i>
                        <strong>Community Assistance</strong>
                        <span>Program</span>
                    </div>
                    <div class="post-body">
                        <div class="post-top">
                            <span class="tag green">Programs</span>
                            <button type="button" aria-label="Save announcement"><i class="fa-regular fa-bookmark"></i></button>
                        </div>
                        <h3>Pamamahagi ng Food Packs</h3>
                        <p>Para sa mga benepisyaryo ng 4Ps at senior citizens. Dalhin ang valid ID para sa verification.</p>
                        <div class="post-meta">
                            <span><i class="fa-regular fa-calendar"></i> June 3, 2026</span>
                            <span><i class="fa-regular fa-clock"></i> 1:00 PM</span>
                            <span><i class="fa-solid fa-location-dot"></i> Barangay Hall</span>
                        </div>
                    </div>
                </article>

                <article class="announcement-post" data-category="events">
                    <div class="post-media flag-poster" role="img" aria-label="Independence day event poster">
                        <i class="fa-solid fa-flag"></i>
                        <strong>Kalayaan Day</strong>
                        <span>Event</span>
                    </div>
                    <div class="post-body">
                        <div class="post-top">
                            <span class="tag purple">Events</span>
                            <button type="button" aria-label="Save announcement"><i class="fa-regular fa-bookmark"></i></button>
                        </div>
                        <h3>Kalayaan Day Community Program</h3>
                        <p>Ipagdiwang natin ang Araw ng Kalayaan kasama ang buong komunidad ng Barangay Makiling.</p>
                        <div class="post-meta">
                            <span><i class="fa-regular fa-calendar"></i> June 12, 2026</span>
                            <span><i class="fa-regular fa-clock"></i> 7:00 AM</span>
                            <span><i class="fa-solid fa-location-dot"></i> Barangay Plaza</span>
                        </div>
                    </div>
                </article>

                <div class="ann-pagination" aria-label="Pagination">
                    <button type="button" class="active">1</button>
                    <button type="button">2</button>
                    <button type="button">3</button>
                    <button type="button" aria-label="Next page"><i class="fa-solid fa-chevron-right"></i></button>
                </div>
            </section>

            <aside class="ann-sidebar" aria-label="Community sidebar">
                <section class="sidebar-block">
                    <div class="section-title">
                        <h2>Facebook Pages</h2>
                    </div>

                    <article class="fb-card">
                        <div class="fb-heading">
                            <img src="../assets/img/Barangay_Makiling_Seal.png" alt="Barangay Makiling seal">
                            <div>
                                <strong>Sangguniang Barangay ng Makiling</strong>
                                <span>@BarangayMakilingOfficial</span>
                                <small>Official community page</small>
                            </div>
                            <i class="fa-brands fa-facebook"></i>
                        </div>
                        <img class="fb-cover" src="../assets/img/Barangay_Makiling_Cover.jpg" alt="Barangay Makiling Facebook cover photo">
                        <a href="https://www.facebook.com/profile.php?id=100008385673390" target="_blank" rel="noopener">Visit Page <i class="fa-solid fa-arrow-up-right-from-square"></i></a>
                    </article>

                    <article class="fb-card">
                        <div class="fb-heading">
                            <img src="../assets/img/Barangay_Makiling_SK.jpg" alt="SK Makiling logo">
                            <div>
                                <strong>Angat SK ng Makiling</strong>
                                <span>@kabataangmakiling</span>
                                <small>Youth council updates</small>
                            </div>
                            <i class="fa-brands fa-facebook"></i>
                        </div>
                        <img class="fb-cover" src="https://scontent.fmnl7-1.fna.fbcdn.net/v/t39.30808-6/533643470_595910850267538_7007377488613180364_n.jpg?stp=cp6_dst-jpg_tt6&_nc_cat=107&ccb=1-7&_nc_sid=cc71e4&_nc_eui2=AeHIUdCqPfNoXPRFZv4tvA8YkdlyveRxgdaR2XK95HGB1nz1SgtIGujHGwaaqlhvSinprzH-Sgak0qFcregYNjSJ&_nc_ohc=dAWHbDyfAtYQ7kNvwHfaDa1&_nc_oc=Adp1Ndez0CnW8UrFQXZHJbPfp_F1gg5Rlfo2uL1zjeB0MHAq6pic9IAZ9iWeIMLQgJw&_nc_zt=23&_nc_ht=scontent.fmnl7-1.fna&_nc_gid=plI-mXKvN97Aw3q0G1f0Jw&_nc_ss=7b2a8&oh=00_Af9Wsmkfpflw8ot_1g2aB_brbT7C2Yyq_HIACMxJe7yvig&oe=6A277DB4" alt="SK Makiling Facebook cover photo">
                        <a href="https://www.facebook.com/kabataangmakiling" target="_blank" rel="noopener">Visit Page <i class="fa-solid fa-arrow-up-right-from-square"></i></a>
                    </article>
                </section>

                <section class="subscribe-card">
                    <div class="section-title">
                        <h2>Stay Updated</h2>
                    </div>
                    <button type="button">
                        <span><i class="fa-solid fa-bell"></i></span>
                        <div>
                            <strong>Enable notifications</strong>
                            <small>Receive alerts for newly posted announcements.</small>
                        </div>
                        <i class="fa-solid fa-chevron-right"></i>
                    </button>
                    <button type="button">
                        <span><i class="fa-regular fa-envelope"></i></span>
                        <div>
                            <strong>Subscribe to updates</strong>
                            <small>Get regular community updates by email.</small>
                        </div>
                        <i class="fa-solid fa-chevron-right"></i>
                    </button>
                    <div class="emergency-list">
                        <strong>Emergency Contacts</strong>
                        <span><i class="fa-solid fa-phone"></i> Barangay Hotline: (049) 123-4567</span>
                        <span><i class="fa-solid fa-kit-medical"></i> Health Center: (049) 545-1695</span>
                        <span><i class="fa-solid fa-shield-halved"></i> Barangay Tanod: 0917-123-4567</span>
                    </div>
                    <a href="../signup.php">Subscribe</a>
                </section>
            </aside>
        </div>
    </main>

    <?php include '../includes/footer.php'; ?>
</body>

</html>
