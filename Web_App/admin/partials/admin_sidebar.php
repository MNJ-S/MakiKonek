<?php
$currentPage = basename($_SERVER['PHP_SELF']);
$admin_username = $_SESSION['admin_username'] ?? 'Admin';
$admin_role = $_SESSION['admin_role'] ?? 'Staff';
$admin_initials = strtoupper(substr(preg_replace('/[^A-Za-z0-9]/', '', $admin_username), 0, 2));
if ($admin_initials === '') {
    $admin_initials = 'AD';
}
?>
<nav class="sidebar admin-shell-sidebar">
    <a href="dashboard.php" class="admin-brand" aria-label="MakiKonek Admin Dashboard">
        <span class="admin-brand-lockup">
            <img src="../assets/img/logo2-makikonek.png" alt="MakiKonek">
            <small>Admin Panel</small>
        </span>
    </a>

    <div class="admin-nav-stack">
        <section class="admin-nav-group">
            <p>Operations</p>
            <a href="dashboard.php" class="nav-link <?php echo $currentPage === 'dashboard.php' ? 'active' : ''; ?>">
                <i class="bi bi-grid-1x2-fill"></i> Dashboard
            </a>
            <a href="manage_requests.php" class="nav-link <?php echo $currentPage === 'manage_requests.php' ? 'active' : ''; ?>">
                <i class="bi bi-file-earmark-text"></i> Service Requests
            </a>
            <a href="manage_residents.php" class="nav-link <?php echo $currentPage === 'manage_residents.php' ? 'active' : ''; ?>">
                <i class="bi bi-person"></i> Residents
            </a>
            <a href="manage_appointments.php" class="nav-link <?php echo $currentPage === 'manage_appointments.php' ? 'active' : ''; ?>">
                <i class="bi bi-calendar3"></i> Appointments
            </a>
        </section>

        <section class="admin-nav-group">
            <p>Content</p>
            <a href="manage_officials.php" class="nav-link <?php echo $currentPage === 'manage_officials.php' ? 'active' : ''; ?>">
                <i class="bi bi-person-badge"></i> Officials
            </a>
            <a href="manage_announcements.php" class="nav-link <?php echo $currentPage === 'manage_announcements.php' ? 'active' : ''; ?>">
                <i class="bi bi-megaphone"></i> Announcements
            </a>
        </section>

        <?php if ($admin_role === 'Super Admin'): ?>
            <section class="admin-nav-group">
                <p>Administration</p>
                <a href="manage_staff.php" class="nav-link <?php echo $currentPage === 'manage_staff.php' ? 'active' : ''; ?>">
                    <i class="bi bi-people"></i> Staff Management
                </a>
                <a href="#" class="nav-link">
                    <i class="bi bi-gear"></i> Settings
                </a>
            </section>
        <?php endif; ?>
    </div>

    <div class="admin-sidebar-bottom">
        <div class="admin-profile-card">
            <span class="admin-avatar"><?php echo htmlspecialchars($admin_initials); ?></span>
            <span>
                <strong><?php echo htmlspecialchars($admin_username); ?></strong>
                <small><?php echo htmlspecialchars($admin_role === 'Super Admin' ? 'Super Administrator' : $admin_role); ?></small>
            </span>
            <a href="logout.php" class="admin-profile-action" title="Sign out" aria-label="Sign out">
                <i class="bi bi-box-arrow-right"></i>
            </a>
        </div>
    </div>
</nav>
