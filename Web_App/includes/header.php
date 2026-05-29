<?php
/**
 * Header Component
 * Public navigation for MakiKonek portal
 */
$currentPage = basename($_SERVER['PHP_SELF']);
?>
<header class="site-header">
    <nav class="nav-shell" aria-label="Primary navigation">
        <a class="brand-link" href="index.php">
            <img src="../assets/img/logo-makikonek.png" alt="MakiKonek logo">
        </a>

        <div class="nav-menu">
            <a href="index.php" class="<?php echo $currentPage === 'index.php' ? 'active' : ''; ?>">Home</a>
            <a href="about.php" class="<?php echo $currentPage === 'about.php' ? 'active' : ''; ?>">About</a>
            <a href="services.php" class="<?php echo $currentPage === 'services.php' ? 'active' : ''; ?>">Services</a>
            <a href="index.php#announcements">Announcements</a>
            <a href="index.php#contact">Contact</a>
        </div>

        <a class="btn btn-small btn-primary nav-login" href="../login_reg.php">Login</a>
    </nav>
</header>
