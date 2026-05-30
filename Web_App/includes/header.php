<?php
/**
 * Header Component
 * Public navigation for MakiKonek portal
 */
$currentPage = basename($_SERVER['PHP_SELF']);
$navBase = $navBase ?? '';
$assetBase = $assetBase ?? '../assets';
$loginHref = $loginHref ?? '../login_reg.php';
$isResidentHeader = $isResidentHeader ?? false;
?>
<header class="site-header">
    <nav class="nav-shell" aria-label="Primary navigation">
        <a class="brand-link" href="<?php echo $navBase; ?>index.php">
            <img src="<?php echo $assetBase; ?>/img/logo-makikonek.png" alt="MakiKonek logo">
        </a>

        <div class="nav-menu">
            <a href="<?php echo $navBase; ?>index.php" class="<?php echo $currentPage === 'index.php' ? 'active' : ''; ?>">Home</a>
            <a href="<?php echo $navBase; ?>about.php" class="<?php echo $currentPage === 'about.php' ? 'active' : ''; ?>">About</a>
            <a href="<?php echo $navBase; ?>services.php" class="<?php echo $currentPage === 'services.php' ? 'active' : ''; ?>">Services</a>
            <a href="<?php echo $navBase; ?>announcements.php" class="<?php echo $currentPage === 'announcements.php' ? 'active' : ''; ?>">Announcements</a>
            <a href="<?php echo $navBase; ?>index.php#contact">Contact</a>
        </div>

        <?php if ($isResidentHeader): ?>
            <div class="resident-header-actions">
                <button class="resident-notification" type="button" aria-label="Notifications">
                    <i class="fa-regular fa-bell"></i>
                </button>
                <span class="resident-header-avatar" aria-hidden="true">
                    <i class="fa-regular fa-user"></i>
                </span>
                <span class="resident-header-user">
                    <strong>Juan Dela Cruz</strong>
                    <small>Resident</small>
                </span>
                <a class="resident-logout" href="<?php echo $loginHref; ?>">Logout</a>
            </div>
        <?php else: ?>
            <a class="btn btn-small btn-primary nav-login" href="<?php echo $loginHref; ?>">Login</a>
        <?php endif; ?>
    </nav>
</header>
