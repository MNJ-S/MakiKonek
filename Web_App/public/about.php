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
    <title>About Us | MakiKonek Digital Service Portal</title>
    <link rel="stylesheet" href="../assets/css/home.css?v=20260613a">
    <link rel="stylesheet" href="../assets/css/about.css?v=20260613a">
    <link rel="stylesheet" href="../assets/css/header.css?v=20260613d">
    <link rel="stylesheet" href="../assets/css/footer.css?v=20260613b">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" rel="stylesheet">
    <script defer src="../assets/js/public.js?v=20260528g"></script>
</head>

<body>
    <?php include '../includes/header.php'; ?>

    <!-- wrapper ID para hindi maapektuhan o magalaw ang global code sa index section -->
    <main id="about-custom-scope">

        <!-- 1. HERO BANNER SECTION & METRIC CARDS -->
        <section class="about-hero-block site-hero">
            <div class="hero-inner-frame">
                <div class="hero-logos">
                    <img src="../assets/img/Barangay_Makiling_Seal.png" alt="Barangay Seal" class="hero-logo-seal">
                    <img src="../assets/img/Barangay_Makiling_SK.jpg" alt="SK Logo" class="hero-logo-sk">
                </div>

                <span class="hero-tag">About Barangay Makiling</span>
                <h1>A Community Rooted in Service</h1>
                <p class="hero-subline">Serving over 11,000 residents through transparent governance, community programs, and digital public services.</p>

                <!-- simplified hero: metrics removed for a cleaner layout -->
            </div>
        </section>

        <!-- 2. THE CHRONOLOGICAL STORY TIMELINE  -->
        <section class="story-timeline-section">
            <div class="timeline-center-header">
                <h2>Our Story</h2>
                <p class="timeline-subline">From San Isidro to Makiling - the journey of our barangay</p>
            </div>

            <div class="timeline-cards-grid">
                <div class="timeline-story-card">
                    <div class="story-number-circle bg-timeline-green">1</div>
                    <h3>San Isidro Era</h3>
                    <p>Named after San Isidro, the patron saint of farmers, when the forest was cleared and fertile farmlands emerged.</p>
                </div>

                <div class="timeline-story-card">
                    <div class="story-number-circle bg-timeline-blue">2</div>
                    <h3>Transformation</h3>
                    <p>The name gradually changed as travelers noticed the many slopes (kiling) of Mount Makiling visible from the area.</p>
                </div>

                <div class="timeline-story-card">
                    <div class="story-number-circle bg-timeline-orange">3</div>
                    <h3>Modern Makiling</h3>
                    <p>Today, a progressive barangay serving 11,669 residents with modern digital services and community programs.</p>
                </div>
            </div>
        </section>

        <!-- 3. MISSION & LOCAL SPECIFICATIONS SPLIT WRAPPER -->
        <section class="mission-specs-split">
            <div class="mission-big-card">
                <div class="mission-card-header">
                    <i class="fa-solid fa-bullseye"></i>
                    <h2>Mission</h2>
                </div>
                <p>Magkaroon ng malayang komunikasyon, kooperasyon at pagkakaisa sa hanay ng mga namumuno sa Barangay at mga mamamayan na nasasakupan nito upang mabilis at maayos na maisakatuparan ang 8 Point Vision ng aming Barangay.</p>
            </div>

            <div class="specs-list-container">
                <div class="spec-inline-item border-green">
                    <div class="spec-icon text-green"><i class="fa-solid fa-location-dot"></i></div>
                    <div class="spec-details">
                        <h4>Location</h4>
                        <p>6.5 km from Poblacion, Calamba • 30 mins travel time</p>
                    </div>
                </div>
                <div class="spec-inline-item border-blue">
                    <div class="spec-icon text-blue"><i class="fa-solid fa-building"></i></div>
                    <div class="spec-details">
                        <h4>Classification</h4>
                        <p>Rural Barangay • Growth Management Zone 1 & 2</p>
                    </div>
                </div>
                <div class="spec-inline-item border-orange">
                    <div class="spec-icon text-orange"><i class="fa-solid fa-heart"></i></div>
                    <div class="spec-details">
                        <h4>Patron Saint</h4>
                        <p>San Isidro, Patron of Farmers • Fiesta: May 15</p>
                    </div>
                </div>
                <div class="spec-inline-item border-purple">
                    <div class="spec-icon text-purple"><i class="fa-solid fa-circle-info"></i></div>
                    <div class="spec-details">
                        <h4>Territory</h4>
                        <p>5 Puroks • Bounded by Tulo, Sto. Tomas, Saimsim, Ulango</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- 4. 8-POINT VISION CONTAINER -->
        <section class="vision-grid-section">
            <div class="vision-center-header">
                <div class="vision-eye-icon"><i class="fa-solid fa-eye"></i></div>
                <h2>8 Point Vision</h2>
                <p class="vision-quote">"Hangarin ng Barangay Makiling na maging"</p>
            </div>

            <div class="vision-8-grid">
                <!-- M -->
                <div class="vision-card bg-v-green">
                    <div class="vision-letter text-v-green">M</div>
                    <h4>Maunlad at progresibo</h4>
                    <p>na tutulong sa pag-angat ng antas ng pamumuhay ng kaniyang mamamayan</p>
                </div>
                <!-- A -->
                <div class="vision-card bg-v-blue">
                    <div class="vision-letter text-v-blue">A</div>
                    <h4>Asensado</h4>
                    <p>ay maykakayahan na ibigay ang mabilis at kumpletong pangunahing serbisyo sa lahat ng sector ng kaniyang komunidad</p>
                </div>
                <!-- K -->
                <div class="vision-card bg-v-orange">
                    <div class="vision-letter text-v-orange">K</div>
                    <h4>Katulong</h4>
                    <p>ng mga mamamayan sa pagpapaunlad ng kanilang kabuhayan</p>
                </div>
                <!-- I -->
                <div class="vision-card bg-v-yellow">
                    <div class="vision-letter text-v-yellow">I</div>
                    <h4>Instrumento</h4>
                    <p>sa pagiging, maka-Diyos, makabayan at makakalikasan ng kaniyang mga residente</p>
                </div>
                <!-- L -->
                <div class="vision-card bg-v-cyan">
                    <div class="vision-letter text-v-cyan">L</div>
                    <h4>Lahad at tapat</h4>
                    <p>sa pamumuno at pagpapatupad ng mga proyekto at programa nito</p>
                </div>
                <!-- I -->
                <div class="vision-card bg-v-purple">
                    <div class="vision-letter text-v-purple">I</div>
                    <h4>Isang Barangay</h4>
                    <p>na mapayapa at kaaya-aya para sa mga taong mananahan dito</p>
                </div>
                <!-- N -->
                <div class="vision-card bg-v-magenta">
                    <div class="vision-letter text-v-magenta">N</div>
                    <h4>Nagkakaisang komunidad</h4>
                    <p>na nakikilahok sa bawat proyekto at programa ng Gobyerno</p>
                </div>
                <!-- G -->
                <div class="vision-card bg-v-darkorange">
                    <div class="vision-letter text-v-darkorange">G</div>
                    <h4>Gobyerno</h4>
                    <p>na maglilingkod ng may pananagutan sa kanyang mamamayan</p>
                </div>
            </div>
        </section>

        <!-- 5. GOVERNING OFFICIALS SECTION -->
        <section class="leaders-section">
            <div class="leaders-title-block">
                <div class="leaders-top-icon"><i class="fa-solid fa-award"></i></div>
                <h2>Our Leaders</h2>
                <p class="leaders-subtitle">Dedicated officials serving Barangay Makiling</p>
            </div>

            <div class="council-group">
                <h3 class="group-heading">Barangay Council (Sangguniang Barangay)</h3>

                <div class="leaders-grid-5-cols">
                    <!-- Row 1 -->
                    <div class="leader-card">
                        <div class="leader-avatar"><img src="../assets/img/officials/official-aigrette.webp" alt="Aigrette Panganiban Lajara" loading="lazy" decoding="async"></div>
                        <h4 class="leader-name">Aigrette Panganiban Lajara</h4>
                        <span class="leader-position text-green">Barangay Captain</span>
                    </div>
                    <div class="leader-card">
                        <div class="leader-avatar"><img src="../assets/img/officials/official-teona.webp" alt="Teona Lizardo Noprada" loading="lazy" decoding="async"></div>
                        <h4 class="leader-name">Teona Lizardo Noprada</h4>
                        <span class="leader-position text-green">Barangay Secretary</span>
                    </div>
                    <div class="leader-card">
                        <div class="leader-avatar"><img src="../assets/img/officials/official-rubie.webp" alt="Rubie Alcantara Olaes" loading="lazy" decoding="async"></div>
                        <h4 class="leader-name">Rubie Alcantara Olaes</h4>
                        <span class="leader-position text-green">Barangay Treasurer</span>
                    </div>
                    <div class="leader-card">
                        <div class="leader-avatar"><img src="../assets/img/officials/official-hermano.webp" alt="Hermano Medalla De Chavez" loading="lazy" decoding="async"></div>
                        <h4 class="leader-name">Hermano Medalla De Chavez</h4>
                        <span class="leader-position text-green">Kagawad</span>
                    </div>
                    <div class="leader-card">
                        <div class="leader-avatar"><img src="../assets/img/officials/official-virgilio.webp" alt="Virgilio Torres Lopez" loading="lazy" decoding="async"></div>
                        <h4 class="leader-name">Virgilio Torres Lopez</h4>
                        <span class="leader-position text-green">Kagawad</span>
                    </div>

                    <div class="leader-card">
                        <div class="leader-avatar"><img src="../assets/img/officials/official-diomedes.webp" alt="Diomedes Nemes Austria" loading="lazy" decoding="async"></div>
                        <h4 class="leader-name">Diomedes Nemes Austria</h4>
                        <span class="leader-position text-green">Kagawad</span>
                    </div>
                    <div class="leader-card">
                        <div class="leader-avatar"><img src="../assets/img/officials/official-rizal.webp" alt="Rizal Mercado Pascual" loading="lazy" decoding="async"></div>
                        <h4 class="leader-name">Rizal Mercado Pascual</h4>
                        <span class="leader-position text-green">Kagawad</span>
                    </div>
                    <div class="leader-card">
                        <div class="leader-avatar"><img src="../assets/img/officials/official-freddie.webp" alt="Freddie Balansay Noprada" loading="lazy" decoding="async"></div>
                        <h4 class="leader-name">Freddie Balansay Noprada</h4>
                        <span class="leader-position text-green">Kagawad</span>
                    </div>
                    <div class="leader-card">
                        <div class="leader-avatar"><img src="../assets/img/officials/official-marcelo.webp" alt="Marcelo Atienza Molinyawe" loading="lazy" decoding="async"></div>
                        <h4 class="leader-name">Marcelo Atienza Molinyawe</h4>
                        <span class="leader-position text-green">Kagawad</span>
                    </div>
                    <div class="leader-card">
                        <div class="leader-avatar"><img src="../assets/img/officials/official-antonio.webp" alt="Antonio Hempesalla Medalla" loading="lazy" decoding="async"></div>
                        <h4 class="leader-name">Antonio Hempesalla Medalla</h4>
                        <span class="leader-position text-green">Kagawad</span>
                    </div>
                </div>
            </div>


            <div class="council-group">
                <h3 class="group-heading">Sangguniang Kabataan (SK) Council</h3>

                <div class="leaders-grid-4-cols">
                    <!-- Row 1 -->
                    <div class="leader-card">
                        <div class="leader-avatar"><img src="../assets/img/officials/official-aaron.webp" alt="Aaron Klyne Macasadia Magsino" loading="lazy" decoding="async"></div>
                        <h4 class="leader-name">Aaron Klyne Macasadia Magsino</h4>
                        <span class="leader-position text-purple">SK Chairman</span>
                    </div>
                    <div class="leader-card">
                        <div class="leader-avatar"><img src="../assets/img/officials/official-christian.webp" alt="Christian Heplan Perez" loading="lazy" decoding="async"></div>
                        <h4 class="leader-name">Christian Heplan Perez</h4>
                        <span class="leader-position text-purple">SK Kagawad</span>
                    </div>
                    <div class="leader-card">
                        <div class="leader-avatar"><img src="../assets/img/officials/official-john-paul.webp" alt="John Paul De Castro Evangelista" loading="lazy" decoding="async"></div>
                        <h4 class="leader-name">John Paul De Castro Evangelista</h4>
                        <span class="leader-position text-purple">SK Kagawad</span>
                    </div>
                    <div class="leader-card">
                        <div class="leader-avatar"><img src="../assets/img/officials/official-mark-harold.webp" alt="Mark Harold Alferez Burgos" loading="lazy" decoding="async"></div>
                        <h4 class="leader-name">Mark Harold Alferez Burgos</h4>
                        <span class="leader-position text-purple">SK Kagawad</span>
                    </div>

                    <div class="leader-card">
                        <div class="leader-avatar"><img src="../assets/img/officials/official-dhanna.webp" alt="Dhanna Marie Macasadia Montes" loading="lazy" decoding="async"></div>
                        <h4 class="leader-name">Dhanna Marie Macasadia Montes</h4>
                        <span class="leader-position text-purple">SK Kagawad</span>
                    </div>
                    <div class="leader-card">
                        <div class="leader-avatar"><img src="../assets/img/officials/official-jaz-elle.webp" alt="Jaz Elle Carpio Alvarez" loading="lazy" decoding="async"></div>
                        <h4 class="leader-name">Jaz Elle Carpio Alvarez</h4>
                        <span class="leader-position text-purple">SK Kagawad</span>
                    </div>
                    <div class="leader-card">
                        <div class="leader-avatar"><img src="../assets/img/officials/official-ellaine.webp" alt="Ellaine Buena Egloria" loading="lazy" decoding="async"></div>
                        <h4 class="leader-name">Ellaine Buena Egloria</h4>
                        <span class="leader-position text-purple">SK Kagawad</span>
                    </div>
                    <div class="leader-card">
                        <div class="leader-avatar"><img src="../assets/img/officials/official-jhenie-lee.webp" alt="Jhenie Lee Siman Laude" loading="lazy" decoding="async"></div>
                        <h4 class="leader-name">Jhenie Lee Siman Laude</h4>
                        <span class="leader-position text-purple">SK Kagawad</span>
                    </div>
                </div>
            </div>
        </section>

        <!-- 6. EDUCATION & GROWTH OPPORTUNITIES SECTION -->
        <section class="education-growth-section">
            <div class="education-center-header">
                <div class="education-cap-icon"><i class="fa-solid fa-graduation-cap"></i></div>
                <h2>Education & Growth</h2>
                <p class="education-subline">Building a brighter future through education</p>
            </div>

            <div class="education-split-grid">
                <div class="education-panel-card bg-edu-lightgreen">
                    <div class="panel-icon text-edu-green"><i class="fa-solid fa-graduation-cap"></i></div>
                    <h3>Pre-Elementary & Elementary</h3>
                    <ul class="education-list bullet-green">
                        <li>Makiling Day Care Center</li>
                        <li>Makiling Elementary School</li>
                        <li>San Isidro Parish Elementary School</li>
                        <li>Joy Christian Lighthouse Academy</li>
                    </ul>
                </div>

                <div class="education-panel-card bg-edu-lightblue">
                    <div class="panel-icon text-edu-blue"><i class="fa-solid fa-graduation-cap"></i></div>
                    <h3>Secondary & Tertiary</h3>
                    <ul class="education-list bullet-blue">
                        <li>Makiling National Highschool</li>
                        <li>Lyceum of the Philippines University Laguna</li>
                    </ul>
                </div>
            </div>
        </section>

        <!-- 7. GROWING COMMUNITY SECTION -->
        <section class="growing-community-container">
            <div class="growing-community-card">
                <div class="gc-header-row">
                    <i class="fa-solid fa-chart-line"></i>
                    <h2>Growing Community</h2>
                </div>
                <p class="gc-subtext">From 2,000 residents in 1980 to 11,669 in 2018 - almost 6x growth!</p>

                <div class="gc-chart" aria-label="Population growth line graph">
                    <svg viewBox="0 0 900 210" role="img" aria-labelledby="gc-chart-title gc-chart-desc">
                        <title id="gc-chart-title">Barangay Makiling population growth</title>
                        <desc id="gc-chart-desc">Population increased from 2,000 residents in 1980 to 11,669 residents in 2018.</desc>
                        <defs>
                            <linearGradient id="gcLineGradient" x1="0" x2="1" y1="0" y2="0">
                                <stop offset="0%" stop-color="#ffffff" />
                                <stop offset="100%" stop-color="#d8fff0" />
                            </linearGradient>
                            <linearGradient id="gcAreaGradient" x1="0" x2="0" y1="0" y2="1">
                                <stop offset="0%" stop-color="#ffffff" stop-opacity="0.28" />
                                <stop offset="100%" stop-color="#ffffff" stop-opacity="0" />
                            </linearGradient>
                        </defs>
                        <path class="gc-chart-area" d="M20 168 L143 145 L266 129 L389 115 L512 82 L635 75 L758 20 L880 5 L880 190 L20 190 Z" />
                        <path class="gc-chart-grid" d="M20 190 H880 M20 130 H880 M20 70 H880" />
                        <path class="gc-chart-line" d="M20 168 L143 145 L266 129 L389 115 L512 82 L635 75 L758 20 L880 5" />
                        <g class="gc-chart-points">
                            <circle cx="20" cy="168" r="5" />
                            <circle cx="143" cy="145" r="5" />
                            <circle cx="266" cy="129" r="5" />
                            <circle cx="389" cy="115" r="5" />
                            <circle cx="512" cy="82" r="5" />
                            <circle cx="635" cy="75" r="5" />
                            <circle cx="758" cy="20" r="5" />
                            <circle cx="880" cy="5" r="5" />
                        </g>
                    </svg>
                </div>

                <div class="gc-data-display-row">
                    <div class="gc-stat-node">
                        <h3>2,000</h3>
                        <span>1980</span>
                    </div>
                    <div class="gc-stat-node">
                        <h3>3,382</h3>
                        <span>1990</span>
                    </div>
                    <div class="gc-stat-node">
                        <h3>4,326</h3>
                        <span>1995</span>
                    </div>
                    <div class="gc-stat-node">
                        <h3>5,130</h3>
                        <span>2000</span>
                    </div>
                    <div class="gc-stat-node">
                        <h3>7,100</h3>
                        <span>2007</span>
                    </div>
                    <div class="gc-stat-node">
                        <h3>7,510</h3>
                        <span>2010</span>
                    </div>
                    <div class="gc-stat-node">
                        <h3>10,760</h3>
                        <span>2015</span>
                    </div>
                    <div class="gc-stat-node">
                        <h3>11,669</h3>
                        <span>2018</span>
                    </div>
                </div>
            </div>
        </section>

    </main>

    <?php include '../includes/footer.php'; ?>
</body>

</html>
