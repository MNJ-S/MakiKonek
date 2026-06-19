<div class="dropdown dropdown-notification-wrapper">
    <button class="dashboard-notification" type="button" aria-label="Notifications" data-bs-toggle="dropdown" aria-expanded="false" data-bs-auto-close="outside">
        <i class="bi bi-bell"></i>
        <span id="admin-notif-badge" style="display: none;">0</span>
    </button>

    <div class="dropdown-menu dropdown-menu-end notification-dropdown">
        <div class="notification-header">
            <div>
                <h6>Notifications</h6>
                <small id="admin-notif-count-text">0 unread updates</small>
            </div>
            <button type="button" class="mark-read-btn" id="markAllReadBtn" style="display: none;">Mark all as read</button>
        </div>

        <div class="notification-body" id="admin-notification-body">
            <div class="p-3 text-center text-muted"><small>Loading...</small></div>
        </div>
    </div>
</div>
