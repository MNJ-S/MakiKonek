<?php

$currentPage = basename($_SERVER['PHP_SELF']);
$navBase = $navBase ?? '';
$assetBase = $assetBase ?? '../assets';
$loginHref = '../login_reg.php';
$isResidentHeader = $isResidentHeader ?? isset($_SESSION['resident_id']);
$residentProfileHref = $residentProfileHref ?? 'profile.php';
$residentLogoutHref = $residentLogoutHref ?? 'logout.php';

// Prefer the resident's given name in the header, then fall back to the username.
$header_username = isset($_SESSION['resident_username']) ? $_SESSION['resident_username'] : 'Resident';

$residentNotifications = [];
$residentUnreadCount = 0;

if ($isResidentHeader && isset($conn) && isset($_SESSION['resident_id'])) {
    $res_id = (int)$_SESSION['resident_id'];

    $name_query = "SELECT first_name FROM user_profiles WHERE user_id = ? LIMIT 1";
    $name_stmt = mysqli_prepare($conn, $name_query);
    if ($name_stmt) {
        mysqli_stmt_bind_param($name_stmt, "i", $res_id);
        mysqli_stmt_execute($name_stmt);
        $name_result = mysqli_stmt_get_result($name_stmt);
        if ($row = mysqli_fetch_assoc($name_result)) {
            $first_name = trim((string)($row['first_name'] ?? ''));
            if ($first_name !== '') {
                $header_username = function_exists('mb_convert_case')
                    ? mb_convert_case($first_name, MB_CASE_TITLE, 'UTF-8')
                    : ucwords(strtolower($first_name));
            }
        }
        mysqli_stmt_close($name_stmt);
    }

    // Fetch top 10 notifications for the dropdown
    $notif_query = "SELECT notification_id, title, message, type, icon, is_read, created_at FROM user_notifications WHERE user_id = ? ORDER BY created_at DESC LIMIT 10";
    $notif_stmt = mysqli_prepare($conn, $notif_query);
    if ($notif_stmt) {
        mysqli_stmt_bind_param($notif_stmt, "i", $res_id);
        mysqli_stmt_execute($notif_stmt);
        $notif_result = mysqli_stmt_get_result($notif_stmt);
        while ($row = mysqli_fetch_assoc($notif_result)) {
            $time_diff = max(0, time() - strtotime($row['created_at']));
            if ($time_diff < 60) {
                $time_str = "Just now";
            } elseif ($time_diff < 3600) {
                $mins = floor($time_diff / 60);
                $time_str = $mins . " min" . ($mins > 1 ? "s" : "") . " ago";
            } elseif ($time_diff < 86400) {
                $hours = floor($time_diff / 3600);
                $time_str = $hours . " hour" . ($hours > 1 ? "s" : "") . " ago";
            } else {
                $days = floor($time_diff / 86400);
                $time_str = $days . " day" . ($days > 1 ? "s" : "") . " ago";
            }
            
            $residentNotifications[] = [
                'id' => $row['notification_id'],
                'title' => $row['title'],
                'message' => $row['message'],
                'time' => $time_str,
                'type' => $row['type'],
                'icon' => $row['icon'],
                'unread' => !$row['is_read'],
            ];
        }
        mysqli_stmt_close($notif_stmt);
    }
    
    // Fetch total unread count for badges
    $unread_query = "SELECT COUNT(*) as unread_count FROM user_notifications WHERE user_id = ? AND is_read = 0";
    $unread_stmt = mysqli_prepare($conn, $unread_query);
    if ($unread_stmt) {
        mysqli_stmt_bind_param($unread_stmt, "i", $res_id);
        mysqli_stmt_execute($unread_stmt);
        $unread_result = mysqli_stmt_get_result($unread_stmt);
        if ($row = mysqli_fetch_assoc($unread_result)) {
            $residentUnreadCount = (int)$row['unread_count'];
        }
        mysqli_stmt_close($unread_stmt);
    }
}
?>
<header class="site-header">
    <nav class="nav-shell" aria-label="Primary navigation">
        <a class="brand-link" href="<?php echo $navBase; ?>index.php">
            <img src="<?php echo $assetBase; ?>/img/logo2-makikonek.png" alt="MakiKonek logo">
        </a>

        <button class="nav-toggle" type="button" aria-label="Toggle navigation menu" aria-expanded="false">
            <span></span>
            <span></span>
            <span></span>
        </button>

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

                var updateUnread = function(decrement = 0, setZero = false) {
                    var countBadges = document.querySelectorAll('[data-notification-sidebar-count]');
                    var currentCount = parseInt(badge ? badge.textContent : '0') || 0;
                    
                    if (setZero) {
                        currentCount = 0;
                    } else if (decrement > 0) {
                        currentCount = Math.max(0, currentCount - decrement);
                    }
                    // If no decrement and not setZero, we just use the current badge text
                    // which is initialized accurately from PHP

                    if (badge) {
                        badge.textContent = currentCount;
                        badge.hidden = currentCount === 0;
                    }

                    countBadges.forEach(function(countBadge) {
                        countBadge.textContent = currentCount;
                        countBadge.hidden = currentCount === 0;
                    });

                    if (unreadText) {
                        unreadText.textContent = currentCount;
                    }

                    if (markAllRead) {
                        markAllRead.disabled = currentCount === 0;
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
                        if (item.dataset.read === 'false') {
                            item.dataset.read = 'true';
                            item.classList.remove('is-unread');
                            item.classList.add('is-read');
                            updateUnread(1, false);
                        }
                    });
                });

                if (markAllRead) {
                    markAllRead.addEventListener('click', function() {
                        items.forEach(function(item) {
                            item.dataset.read = 'true';
                            item.classList.remove('is-unread');
                            item.classList.add('is-read');
                        });
                        updateUnread(0, true);
                        
                        // Sync UI on the main notifications page if present
                        var pageCards = document.querySelectorAll('.page-notification-card');
                        pageCards.forEach(function(card) {
                            card.style.fontWeight = 'normal';
                            card.style.opacity = '0.8';
                        });
                        var pageMarkAllBtn = document.querySelector('.mark-read-btn');
                        if (pageMarkAllBtn) {
                            pageMarkAllBtn.style.display = 'none';
                        }
                        
                        // Send request to backend
                        fetch('../resident/mark_notifications_read.php', {
                            method: 'POST'
                        }).catch(err => console.error(err));
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
