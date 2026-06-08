<?php
$currentPage = basename($_SERVER['PHP_SELF']);
$admin_username = $_SESSION['admin_username'] ?? 'Admin';
$admin_role = $_SESSION['admin_role'] ?? 'Staff';
?>
<style>
    .sidebar {
        width: 280px;
        position: fixed;
        top: 0;
        left: 0;
        height: 100vh;
        background-color: #102b21;
        border-right: 1px solid #3f9f25;
        z-index: 1000;
    }
    .sidebar .nav-link {
        color: #d7eddc;
        margin-bottom: 5px;
        border-radius: 8px;
        transition: background-color 180ms ease, color 180ms ease;
    }
    .sidebar .nav-link:hover,
    .sidebar .nav-link:focus {
        background-color: rgba(63, 159, 37, 0.18);
        color: #ffffff;
    }
    .sidebar .nav-link.active {
        background-color: #3f9f25;
        color: #ffffff;
    }
    .sidebar .nav-link.active i {
        color: #ffffff;
    }
    .sidebar hr {
        border-color: rgba(63, 159, 37, 0.35);
    }
    .sidebar .dropdown-toggle::after {
        color: #ffffff;
    }
</style>
<nav class="sidebar p-3 d-flex flex-column">
    <a href="dashboard.php" class="d-flex align-items-center mb-4 text-white text-decoration-none">
        <span class="fs-4 fw-bold">MakiKonek Admin</span>
    </a>
    <ul class="nav nav-pills flex-column mb-auto">
        <li class="mb-1 text-uppercase text-white small fw-bold px-3">Operations</li>
        <li class="nav-item">
            <a href="dashboard.php" class="nav-link <?php echo $currentPage === 'dashboard.php' ? 'active' : ''; ?>">
                <i class="bi bi-speedometer2 me-2"></i> Dashboard
            </a>
        </li>
        <li>
            <a href="manage_requests.php" class="nav-link <?php echo $currentPage === 'manage_requests.php' ? 'active' : ''; ?>">
                <i class="bi bi-file-earmark-text me-2"></i> Service Requests
            </a>
        </li>
        <li>
            <a href="manage_residents.php" class="nav-link <?php echo $currentPage === 'manage_residents.php' ? 'active' : ''; ?>">
                <i class="bi bi-people me-2"></i> Residents
            </a>
        </li>
        <li>
            <a href="manage_appointments.php" class="nav-link <?php echo $currentPage === 'manage_appointments.php' ? 'active' : ''; ?>">
                <i class="bi bi-calendar-event me-2"></i> Appointments
            </a>
        </li>

        <!-- SECURITY: Only Super Admins see these links -->
        <?php if ($admin_role === 'Super Admin'): ?>
            <li class="mt-3 mb-1 text-uppercase text-white small fw-bold px-3">System Control</li>
            <li>
                <a href="manage_officials.php" class="nav-link <?php echo $currentPage === 'manage_officials.php' ? 'active' : ''; ?>">
                    <i class="bi bi-person-badge me-2"></i> Manage Officials
                </a>
            </li>
            <li>
                <a href="manage_staff.php" class="nav-link <?php echo $currentPage === 'manage_staff.php' ? 'active' : ''; ?>">
                    <i class="bi bi-shield-lock me-2"></i> Manage Staff
                </a>
            </li>
        <?php endif; ?>
    </ul>
    <hr style="color: #3f9f25;">
    <div class="dropdown">
        <a href="#" class="d-flex align-items-center text-white text-decoration-none dropdown-toggle" data-bs-toggle="dropdown">
            <i class="bi bi-person-circle fs-4 me-2"></i>
            <strong><?php echo htmlspecialchars($admin_username); ?></strong>
        </a>
        <ul class="dropdown-menu dropdown-menu-dark text-small shadow">
            <li><a class="dropdown-item text-danger" href="logout.php">Sign out</a></li>
        </ul>
    </div>
</nav>
