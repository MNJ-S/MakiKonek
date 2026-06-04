<?php

$footerBase = $footerBase ?? '';
$footerAssetBase = $footerAssetBase ?? '../assets';
?>
<footer class="site-footer">
    <div class="footer-grid">
        <div>
            <img class="footer-logo" src="<?php echo $footerAssetBase; ?>/img/logo-makikonek.png" alt="MakiKonek Logo">
            <p>MakiKonek is your digital gateway to faster, easier, and more transparent barangay service.</p>
            
            <div class="social-row" style="display: flex; gap: 10px; align-items: center;">
                <style>
                    .social-row a {
                        display: inline-flex !important;
                        align-items: center !important;
                        justify-content: center !important;
                        width: 40px !important;
                        height: 40px !important;
                        background-color: #ffffff !important;
                        color: #043927 !important;
                        border-radius: 50% !important;
                        text-decoration: none !important;
                        font-weight: bold !important;
                        font-size: 18px !important;
                    }
                </style>
                <a href="#">f</a>
                <a href="#">●</a>
                <a href="#">☏</a>
                <a href="#">✉</a>
            </div>
        </div>

        <div>
            <h3>Quick Links</h3>
            <a href="<?php echo $footerBase; ?>index.php">Home</a>
            <a href="<?php echo $footerBase; ?>about.php">About</a>
            <a href="<?php echo $footerBase; ?>services.php">Services</a>
            <a href="<?php echo $footerBase; ?>announcements.php">Announcements</a>
            <a href="<?php echo $footerBase; ?>index.php#contact">Contact</a>
        </div>

        <div>
            <h3>Other Links</h3>
            <a href="#">Privacy Policy</a>
            <a href="#">Terms of Use</a>
            <a href="#">FAQ</a>
        </div>

        <div>
            <h3>Contact Us</h3>
            <p>Purok 1, Barangay Makiling, City of Calamba, Laguna</p>
            <p>(049) 123-4567</p>
            <p>makiling.barangay@gmail.com</p>
            <p>Monday - Friday<br>8:00 AM - 5:00 PM</p>
        </div>
    </div>

    <div class="copyright">
        &copy; 2024 Barangay Makiling. All rights reserved.
    </div>
</footer>