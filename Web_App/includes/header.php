<?php

$currentPage = basename($_SERVER['PHP_SELF']);
$navBase = $navBase ?? '';
$assetBase = $assetBase ?? '../assets';
$loginHref = '../login_reg.php';
$isResidentHeader = $isResidentHeader ?? isset($_SESSION['resident_id']);
$residentProfileHref = $residentProfileHref ?? 'profile.php';
$residentLogoutHref = $residentLogoutHref ?? 'logout.php';

// username default as Resident
$header_username = isset($_SESSION['resident_username']) ? $_SESSION['resident_username'] : 'Resident';
$residentNotifications = $residentNotifications ?? [
    [
        'title' => 'Community Assembly Meeting',
        'message' => 'Quarterly barangay assembly is scheduled this Saturday at 2:00 PM.',
        'time' => '2 hours ago',
        'type' => 'Announcement',
        'icon' => 'fa-regular fa-bell',
        'unread' => true,
    ],
    [
        'title' => 'Document Request Approved',
        'message' => 'Your Barangay Clearance request is ready for pickup.',
        'time' => '1 day ago',
        'type' => 'Request update',
        'icon' => 'fa-regular fa-circle-check',
        'unread' => true,
    ],
    [
        'title' => 'Health and Wellness Program',
        'message' => 'Free health check-up is available at the barangay health center.',
        'time' => '3 days ago',
        'type' => 'Advisory',
        'icon' => 'fa-solid fa-kit-medical',
        'unread' => false,
    ],
];
$residentUnreadCount = count(array_filter($residentNotifications, function ($notification) {
    return !empty($notification['unread']);
}));
?>
<header class="site-header">
    <nav class="nav-shell" aria-label="Primary navigation">
        <a class="brand-link" href="<?php echo $navBase; ?>index.php">
            <img src="<?php echo $assetBase; ?>/img/logo-makikonek.png" alt="MakiKonek logo">
        </a>

        <div class="nav-menu" data-nav-menu>
            <a href="<?php echo $navBase; ?>index.php" class="<?php echo $currentPage === 'index.php' ? 'active' : ''; ?>">Home</a>
            <a href="<?php echo $navBase; ?>about.php" class="<?php echo $currentPage === 'about.php' ? 'active' : ''; ?>">About</a>
            <a href="<?php echo $navBase; ?>services.php" class="<?php echo $currentPage === 'services.php' ? 'active' : ''; ?>">Services</a>
            <a href="<?php echo $navBase; ?>announcements.php" class="<?php echo $currentPage === 'announcements.php' ? 'active' : ''; ?>">Announcements</a>
            <a href="<?php echo $navBase; ?>index.php#contact">Contact</a>
        </div>

    
        <?php if ($isResidentHeader): ?>
            <div class="resident-header-actions">
                <div class="resident-notification-wrap" data-notification-root>
                    <button class="resident-notification" type="button" aria-label="Open notifications" aria-expanded="false" aria-controls="resident-notification-panel" data-notification-toggle>
                        <i class="fa-regular fa-bell"></i>
                        <span class="resident-notification-badge" data-notification-badge <?php echo $residentUnreadCount === 0 ? 'hidden' : ''; ?>>
                            <?php echo $residentUnreadCount; ?>
                        </span>
                    </button>

                    <section class="resident-notification-panel" id="resident-notification-panel" aria-label="Notifications" data-notification-panel hidden>
                        <div class="notification-panel-header">
                            <div>
                                <h2>Notifications</h2>
                                <p><span data-notification-count><?php echo $residentUnreadCount; ?></span> unread updates</p>
                            </div>
                            <button class="notification-mark-read" type="button" data-mark-all-read <?php echo $residentUnreadCount === 0 ? 'disabled' : ''; ?>>
                                Mark all as read
                            </button>
                        </div>

                        <div class="notification-panel-list">
                            <?php foreach ($residentNotifications as $notification): ?>
                                <article class="notification-panel-item <?php echo !empty($notification['unread']) ? 'is-unread' : 'is-read'; ?>" data-notification-item data-read="<?php echo !empty($notification['unread']) ? 'false' : 'true'; ?>">
                                    <span class="notification-panel-icon" aria-hidden="true">
                                        <i class="<?php echo htmlspecialchars($notification['icon']); ?>"></i>
                                    </span>
                                    <div class="notification-panel-copy">
                                        <div class="notification-panel-title-row">
                                            <h3><?php echo htmlspecialchars($notification['title']); ?></h3>
                                            <span><?php echo htmlspecialchars($notification['time']); ?></span>
                                        </div>
                                        <p><?php echo htmlspecialchars($notification['message']); ?></p>
                                        <small><?php echo htmlspecialchars($notification['type']); ?></small>
                                    </div>
                                </article>
                            <?php endforeach; ?>
                        </div>
                    </section>
                </div>
                <a class="resident-profile-link" href="<?php echo htmlspecialchars($residentProfileHref); ?>" aria-label="Open resident profile">
                    <span class="resident-header-avatar" aria-hidden="true">
                        <i class="fa-regular fa-user"></i>
                    </span>
                    <span class="resident-header-user">
                        <strong><?php echo htmlspecialchars($header_username); ?></strong>
                        <small>Resident</small>
                    </span>
                </a>

                <a class="resident-logout" href="<?php echo htmlspecialchars($residentLogoutHref); ?>">Logout</a>
            </div>
        <?php else: ?>
            <a class="btn btn-small btn-primary nav-login" href="<?php echo $loginHref; ?>">Login</a>
        <?php endif; ?>
    </nav>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            var navToggle = document.querySelector('.nav-toggle');
            var navMenu = document.querySelector('[data-nav-menu]');

            if (navToggle && navMenu && !navMenu.dataset.navInit) {
                navToggle.addEventListener('click', function() {
                    var isOpen = navMenu.classList.toggle('is-open');
                    navToggle.setAttribute('aria-expanded', String(isOpen));
                });

                navMenu.querySelectorAll('a').forEach(function(link) {
                    link.addEventListener('click', function() {
                        navMenu.classList.remove('is-open');
                        navToggle.setAttribute('aria-expanded', 'false');
                    });
                });

                navMenu.dataset.navInit = 'true';
            }

            document.querySelectorAll('[data-notification-root]').forEach(function(root) {
                if (root.dataset.notificationInit) {
                    return;
                }

                var toggle = root.querySelector('[data-notification-toggle]');
                var panel = root.querySelector('[data-notification-panel]');
                var badge = root.querySelector('[data-notification-badge]');
                var unreadText = root.querySelector('[data-notification-count]');
                var markAllRead = root.querySelector('[data-mark-all-read]');
                var items = root.querySelectorAll('[data-notification-item]');

                if (!toggle || !panel) {
                    return;
                }

                var updateUnread = function() {
                    var unreadCount = root.querySelectorAll('[data-notification-item][data-read="false"]').length;
                    var countBadges = document.querySelectorAll('[data-notification-sidebar-count]');

                    if (badge) {
                        badge.textContent = unreadCount;
                        badge.hidden = unreadCount === 0;
                    }

                    countBadges.forEach(function(countBadge) {
                        countBadge.textContent = unreadCount;
                        countBadge.hidden = unreadCount === 0;
                    });

                    if (unreadText) {
                        unreadText.textContent = unreadCount;
                    }

                    if (markAllRead) {
                        markAllRead.disabled = unreadCount === 0;
                    }
                };

                var closePanel = function() {
                    panel.hidden = true;
                    root.classList.remove('is-open');
                    toggle.setAttribute('aria-expanded', 'false');
                };

                var openPanel = function() {
                    panel.hidden = false;
                    root.classList.add('is-open');
                    toggle.setAttribute('aria-expanded', 'true');
                };

                toggle.addEventListener('click', function(event) {
                    event.stopPropagation();
                    panel.hidden ? openPanel() : closePanel();
                });

                items.forEach(function(item) {
                    item.addEventListener('click', function() {
                        item.dataset.read = 'true';
                        item.classList.remove('is-unread');
                        item.classList.add('is-read');
                        updateUnread();
                    });
                });

                if (markAllRead) {
                    markAllRead.addEventListener('click', function() {
                        items.forEach(function(item) {
                            item.dataset.read = 'true';
                            item.classList.remove('is-unread');
                            item.classList.add('is-read');
                        });
                        updateUnread();
                    });
                }

                document.addEventListener('click', function(event) {
                    if (!root.contains(event.target)) {
                        closePanel();
                    }
                });

                document.addEventListener('keydown', function(event) {
                    if (event.key === 'Escape') {
                        closePanel();
                    }
                });

                updateUnread();
                root.dataset.notificationInit = 'true';
            });
        });
    </script>
</header>
