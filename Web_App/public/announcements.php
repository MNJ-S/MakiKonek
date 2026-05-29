<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Announcements | MakiKonek Digital Service Portal</title>
    <link rel="stylesheet" href="../assets/css/home.css?v=20260529h">
    <link rel="stylesheet" href="../assets/css/header.css?v=20260529e">
    <link rel="stylesheet" href="../assets/css/footer.css?v=20260529e">
    <link rel="stylesheet" href="../assets/css/announcements.css?v=20260529a">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">
    <script defer src="../assets/js/announcements.js?v=20260529a"></script>
</head>
<body>
    <?php include '../includes/header.php'; ?>

    <main class="ann-page">
        <section class="ann-hero" aria-labelledby="ann-title">
            <div class="ann-hero-copy">
                <nav class="ann-breadcrumb" aria-label="Breadcrumb">
                    <a href="index.php"><i class="fa-solid fa-house"></i></a>
                    <span>/</span>
                    <span>Announcements</span>
                </nav>
                <h1 id="ann-title">Mga <span>Anunsyo</span> at <strong>Balita</strong></h1>
                <p>Manatiling updated sa mahahalagang anunsyo, programa, abiso, events, at aktibidad ng Barangay Makiling.</p>
            </div>

            <div class="ann-hero-art" aria-hidden="true">
                <div class="ann-mountain"></div>
                <div class="megaphone">
                    <i class="fa-solid fa-bullhorn"></i>
                </div>
                <span class="float-badge bell"><i class="fa-solid fa-bell"></i></span>
                <span class="float-badge doc"><i class="fa-solid fa-file-lines"></i></span>
            </div>
        </section>

        <section class="ann-tools" aria-label="Search and filters">
            <form class="ann-search" role="search">
                <i class="fa-solid fa-magnifying-glass" aria-hidden="true"></i>
                <label for="announcement-search">Maghanap ng anunsyo</label>
                <input id="announcement-search" type="search" placeholder="Maghanap ng anunsyo...">
            </form>

            <div class="ann-filters" aria-label="Announcement categories">
                <button class="active" type="button" data-category="all">Lahat</button>
                <button type="button" data-category="anunsyo">Anunsyo</button>
                <button type="button" data-category="programa">Mga Programa</button>
                <button type="button" data-category="abiso">Abiso</button>
                <button type="button" data-category="events">Events</button>
            </div>
        </section>

        <div class="ann-layout">
            <section class="ann-feed" aria-labelledby="latest-announcements">
                <div class="section-title">
                    <h2 id="latest-announcements">Pinakabagong Anunsyo</h2>
                </div>

                <article class="announcement-post" data-category="programa">
                    <div class="post-media cleanup-poster" role="img" aria-label="Barangay cleanup drive poster"></div>
                    <div class="post-body">
                        <div class="post-top">
                            <span class="tag green">Mga Programa</span>
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
                    <div class="post-media power-poster" role="img" aria-label="Scheduled power interruption poster"></div>
                    <div class="post-body">
                        <div class="post-top">
                            <span class="tag blue">Abiso</span>
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
                    <div class="post-media food-poster" role="img" aria-label="Food pack distribution poster"></div>
                    <div class="post-body">
                        <div class="post-top">
                            <span class="tag green">Mga Programa</span>
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
                    <div class="post-media flag-poster" role="img" aria-label="Independence day event poster"></div>
                    <div class="post-body">
                        <div class="post-top">
                            <span class="tag blue">Events</span>
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
                                <strong>Barangay Makiling</strong>
                                <span>@BarangayMakilingOfficial</span>
                                <small>Official community page</small>
                            </div>
                            <i class="fa-brands fa-facebook"></i>
                        </div>
                        <div class="fb-preview">
                            <img src="../assets/img/Barangay_Makiling_Cover.jpg" alt="Barangay Makiling community cover">
                            <img src="../assets/img/hero-mt-makiling.png" alt="Mt. Makiling">
                            <img src="../assets/img/Barangay_Makiling_Seal.png" alt="Barangay Makiling seal preview">
                        </div>
                        <a href="https://www.facebook.com/profile.php?id=100008385673390" target="_blank" rel="noopener">Bisitahin ang Page <i class="fa-solid fa-arrow-up-right-from-square"></i></a>
                    </article>

                    <article class="fb-card">
                        <div class="fb-heading">
                            <img src="../assets/img/Barangay_Makiling_SK.jpg" alt="SK Makiling logo">
                            <div>
                                <strong>SK Makiling Official</strong>
                                <span>@kabataangmakiling</span>
                                <small>Youth council updates</small>
                            </div>
                            <i class="fa-brands fa-facebook"></i>
                        </div>
                        <div class="fb-preview">
                            <img src="../assets/img/Barangay_Makiling_SK.jpg" alt="SK Makiling logo preview">
                            <img src="../assets/img/Barangay_Makiling_Cover.jpg" alt="Community event preview">
                            <img src="../assets/img/hero-mt-makiling.png" alt="Mt. Makiling preview">
                        </div>
                        <a href="https://www.facebook.com/kabataangmakiling" target="_blank" rel="noopener">Bisitahin ang Page <i class="fa-solid fa-arrow-up-right-from-square"></i></a>
                    </article>
                </section>

                <section class="subscribe-card">
                    <div class="section-title">
                        <h2>Huwag Palampasin</h2>
                    </div>
                    <button type="button">
                        <span><i class="fa-solid fa-bell"></i></span>
                        <strong>I-enable ang notifications</strong>
                        <small>Makatanggap ng updates sa mga bagong anunsyo.</small>
                        <i class="fa-solid fa-chevron-right"></i>
                    </button>
                    <button type="button">
                        <span><i class="fa-regular fa-envelope"></i></span>
                        <strong>Mag-subscribe sa updates</strong>
                        <small>Ilagay ang iyong email para sa regular na updates.</small>
                        <i class="fa-solid fa-chevron-right"></i>
                    </button>
                    <a href="../signup.php">Mag-subscribe</a>
                </section>
            </aside>
        </div>
    </main>

    <?php include '../includes/footer.php'; ?>
</body>
</html>
