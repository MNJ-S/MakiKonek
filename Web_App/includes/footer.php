<?php

$footerBase = $footerBase ?? '';
$footerAssetBase = $footerAssetBase ?? '../assets';
?>
<footer class="site-footer">
    <div class="footer-grid">
        <div>
            <img class="footer-logo" src="<?php echo $footerAssetBase; ?>/img/logo-makikonek.png" alt="MakiKonek Logo">
            <p>MakiKonek is your digital gateway to faster, easier, and more transparent barangay service.</p>
            
            <div class="social-row">
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
            <a href="http://localhost/MakiKonek/MakiKonek/Web_App/public/privacy_policy.php" target="_blank" class="text-white text-decoration-none opacity-75">Privacy Policy</a></li>
            <a href="http://localhost/MakiKonek/MakiKonek/Web_App/public/terms_conditions.php" target="_blank" class="text-white text-decoration-none opacity-75">Terms and Conditions</a></li>
            <a href="#" class="text-white text-decoration-none opacity-75">FAQ</a>
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
