<aside class="resident-sidebar">
    <p class="sidebar-label">Menu</p>
    <nav class="resident-menu" aria-label="Resident menu">
        <a class="<?php echo $activePage === 'dashboard' ? 'active' : ''; ?>" href="dashboard.php">
            <i class="fa-solid fa-house"></i>
            Dashboard
        </a>
        <a class="<?php echo $activePage === 'requests' ? 'active' : ''; ?>" href="requests.php">
            <i class="fa-regular fa-file-lines"></i>
            My Requests
        </a>
        <a class="<?php echo $activePage === 'reservations' ? 'active' : ''; ?>" href="reservations.php">
            <i class="fa-regular fa-calendar"></i>
            Reservations
        </a>
        <a class="<?php echo $activePage === 'notifications' ? 'active' : ''; ?>" href="notifications.php">
            <i class="fa-regular fa-bell"></i>
            Notifications
            <span class="count">3</span>
        </a>
        <a class="<?php echo $activePage === 'profile' ? 'active' : ''; ?>" href="profile.php">
            <i class="fa-regular fa-user"></i>
            Profile
        </a>
        <a class="<?php echo $activePage === 'settings' ? 'active' : ''; ?>" href="settings.php">
            <i class="fa-solid fa-gear"></i>
            Settings
        </a>
    </nav>
</aside>