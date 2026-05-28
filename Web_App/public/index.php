<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MakiKonek | Barangay Makiling Digital Service Portal</title>
    <link rel="stylesheet" href="../assets/css/home.css?v=20260528g">
    <script defer src="../assets/js/public.js?v=20260528g"></script>
</head>
<body>
    <!-- Public navigation -->
    <header class="site-header" id="home">
        <nav class="nav-shell" aria-label="Primary navigation">
            <a class="brand-link" href="#home" aria-label="MakiKonek home">
                <img src="../assets/img/logo-makikonek.png" alt="MakiKonek logo">
            </a>

            <button class="nav-toggle" type="button" aria-label="Open navigation" aria-expanded="false">
                <span></span>
                <span></span>
                <span></span>
            </button>

            <div class="nav-menu" data-nav-menu>
                <a href="index.php" class="active">Home</a>
                <a href="about.php">About</a>
                <a href="services.php">Services</a>
                <a href="#announcements">Announcements</a>
                <a href="#contact">Contact</a>
            </div>

            <a class="btn btn-small btn-primary nav-login" href="../login_reg.php">Login</a>
        </nav>
    </header>

    <main>
        <!-- Hero section -->
        <section class="hero-section" aria-labelledby="hero-title">
            <div class="hero-media" role="img" aria-label="Mt. Makiling landscape and Barangay Makiling community"></div>
            <div class="hero-overlay"></div>
            <div class="hero-content">
                <p class="eyebrow">Welcome to</p>
                <h1 id="hero-title">MakiKonek</h1>
                <p>Your easy access to Barangay Makiling services, announcements, emergency contacts, and community information.</p>
                <div class="hero-actions">
                    <a class="btn btn-primary" href="../login_reg.php">Request a Service</a>
                    <a class="btn btn-light" href="#announcements">View Announcements</a>
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
                    <p class="eyebrow">Serbisyong Makukuha</p>
                    <h2>Mga Serbisyong Barangay Online</h2>
                    <p>Mabilis, madali, at maaasahang serbisyo para sa bawat residente ng Barangay Makiling.</p>
                </div>
                <form class="service-search" action="#services" role="search">
                    <label for="service_search">Maghanap ng serbisyo</label>
                    <input id="service_search" type="search" placeholder="Maghanap ng serbisyo...">
                    <button type="submit">Search</button>
                </form>
            </div>

            <div class="service-grid">
                <article class="service-card">
                    <div class="service-icon green">▣</div>
                    <h3>Barangay Clearance</h3>
                    <p>Mag-request ng Barangay Clearance online.</p>
                    <a href="../login_reg.php">Alamin pa</a>
                </article>
                <article class="service-card">
                    <div class="service-icon blue">⌂</div>
                    <h3>Certificate of Residency</h3>
                    <p>Kumuha ng patunay ng pagka-residente.</p>
                    <a href="../login_reg.php">Alamin pa</a>
                </article>
                <article class="service-card">
                    <div class="service-icon orange">●</div>
                    <h3>Indigency Certificate</h3>
                    <p>Mag-request ng Indigency Certificate.</p>
                    <a href="../login_reg.php">Alamin pa</a>
                </article>
                <article class="service-card">
                    <div class="service-icon green">▦</div>
                    <h3>Event Permit</h3>
                    <p>Mag-apply ng permit para sa inyong mga event.</p>
                    <a href="../login_reg.php">Alamin pa</a>
                </article>
                <article class="service-card">
                    <div class="service-icon violet">▤</div>
                    <h3>Business Permit</h3>
                    <p>Mag-apply para sa business permit at registration.</p>
                    <a href="../login_reg.php">Alamin pa</a>
                </article>
                <article class="service-card">
                    <div class="service-icon blue">•••</div>
                    <h3>Iba Pang Serbisyo</h3>
                    <p>Tuklasin ang iba pang serbisyong handog namin.</p>
                    <a href="../login_reg.php">Tingnan lahat</a>
                </article>
            </div>

            <div class="process-section">
                <div class="section-heading split compact-left">
                    <div>
                        <p class="eyebrow">Paano ito gumagana?</p>
                        <h2>Simple ang pag-request</h2>
                    </div>
                </div>
                <div class="process-grid">
                    <article><span>1</span><strong>Piliin ang Serbisyo</strong><p>Pumili ng kailangan mo mula sa listahan.</p></article>
                    <article><span>2</span><strong>Mag-fill up ng Form</strong><p>Sagutan ang detalye at i-upload ang dokumento.</p></article>
                    <article><span>3</span><strong>Ipadala ang Request</strong><p>I-review at isumite para sa pagsusuri.</p></article>
                    <article><span>4</span><strong>Hintayin ang Update</strong><p>Makakatanggap ka ng status update.</p></article>
                    <article><span>5</span><strong>Kunin ang Resulta</strong><p>Kunin ang dokumento kapag nakumpleto na.</p></article>
                </div>
            </div>
        </section>

        <!-- Announcements -->
        <section class="section announcement-section" id="announcements">
            <div class="section-heading split">
                <div>
                    <p class="eyebrow">Latest announcements</p>
                    <h2>Stay Updated</h2>
                </div>
                <a href="#announcements" class="text-link">View All Announcements</a>
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
                    <h3>May 2026</h3>
                    <div class="calendar-controls" aria-label="Calendar controls">
                        <button type="button">Today</button>
                        <button type="button">Prev</button>
                        <button type="button">Next</button>
                    </div>
                </div>
                <div class="calendar-grid" aria-label="May 2026 calendar">
                    <div class="day-label">Sun</div><div class="day-label">Mon</div><div class="day-label">Tue</div><div class="day-label">Wed</div><div class="day-label">Thu</div><div class="day-label">Fri</div><div class="day-label">Sat</div>
                    <div class="day muted"></div><div class="day muted"></div><div class="day muted"></div><div class="day muted"></div><div class="day muted"></div>
                    <div class="day"><span>1</span></div>
                    <div class="day"><span>2</span><strong class="event green">Community Clean-up<br>6:00 AM</strong><strong class="event green">Zumba Session<br>4:00 PM</strong></div>
                    <div class="day"><span>3</span></div>
                    <div class="day"><span>4</span><strong class="event green">Basketball League<br>5:00 PM</strong></div>
                    <div class="day"><span>5</span></div><div class="day"><span>6</span></div><div class="day"><span>7</span></div><div class="day"><span>8</span></div>
                    <div class="day"><span>9</span><strong class="event green">Town Hall Meeting<br>3:00 PM</strong></div>
                    <div class="day"><span>10</span></div>
                    <div class="day"><span>11</span><strong class="event green">Free Check-up<br>9:30 AM</strong></div>
                    <div class="day"><span>12</span></div><div class="day"><span>13</span></div><div class="day"><span>14</span></div><div class="day"><span>15</span></div>
                    <div class="day"><span>16</span><strong class="event green">Summer Festival<br>8:00 AM</strong></div>
                    <div class="day"><span>17</span></div>
                    <div class="day"><span>18</span><strong class="event green">Cooking Workshop<br>1:00 PM</strong></div>
                    <div class="day"><span>19</span></div><div class="day"><span>20</span></div><div class="day"><span>21</span></div>
                    <div class="day"><span>22</span><strong class="event blue">Environment Day<br>7:00 AM</strong><strong class="event blue">Tree Planting<br>8:00 AM</strong></div>
                    <div class="day"><span>23</span></div><div class="day"><span>24</span></div>
                    <div class="day"><span>25</span><strong class="event blue">Barangay Assembly<br>2:00 PM</strong></div>
                    <div class="day"><span>26</span><strong class="event blue">Youth Council<br>4:00 PM</strong></div>
                    <div class="day"><span>27</span><strong class="event blue">Senior Program<br>10:00 AM</strong></div>
                    <div class="day today"><span>28</span></div><div class="day"><span>29</span></div><div class="day"><span>30</span></div><div class="day"><span>31</span></div>
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
                    <strong>Available at barangay hall</strong>
                </article>
                <article class="hotline-card disaster">
                    <span class="hotline-icon">D</span>
                    <h3>Disaster Response</h3>
                    <p>Calamba City CDRRMD</p>
                    <strong>(049) 545 4119<br>0917 148 9813</strong>
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

    <!-- Footer -->
    <footer class="site-footer">
        <div class="footer-grid">
            <div>
                <img class="footer-logo" src="../assets/img/logo-makikonek.png" alt="MakiKonek logo">
                <p>MakiKonek is your digital gateway to faster, easier, and more transparent barangay service.</p>
                <div class="social-row" aria-label="Social links">
                    <a href="#home" aria-label="Facebook">f</a>
                    <a href="#home" aria-label="Messenger">m</a>
                    <a href="#home" aria-label="Email">@</a>
                    <a href="#home" aria-label="Website">w</a>
                </div>
            </div>
            <div>
                <h3>Quick Links</h3>
                <a href="index.php">Home</a>
                <a href="about.php">About</a>
                <a href="services.php">Services</a>
                <a href="#announcements">Announcements</a>
                <a href="#transparency">Transparency</a>
                <a href="#contact">Contact</a>
            </div>
            <div>
                <h3>Other Links</h3>
                <a href="#home">Privacy Policy</a>
                <a href="#home">Terms of Use</a>
                <a href="#home">FAQ</a>
                <a href="#home">Sitemap</a>
            </div>
            <div>
                <h3>Contact Us</h3>
                <p>Purok 1, Barangay Makiling, City of Calamba, Laguna</p>
                <p>(049) 123-4567</p>
                <p>makiling.barangay@gmail.com</p>
                <p>Monday - Friday<br>8:00 AM - 5:00 PM</p>
            </div>
        </div>
        <p class="copyright">© 2026 Barangay Makiling. All rights reserved.</p>
    </footer>
</body>
</html>
