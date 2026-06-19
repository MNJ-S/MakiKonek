document.addEventListener('DOMContentLoaded', function() {
    const notifButton = document.querySelector('.dashboard-notification');
    const notifBody = document.getElementById('admin-notification-body');
    const notifCountText = document.getElementById('admin-notif-count-text');
    const badgeCount = document.getElementById('admin-notif-badge');
    const markAllReadButton = document.getElementById('markAllReadBtn');
    const ajaxUrl = 'includes/ajax_get_notifications.php';

    if (!notifButton || !notifBody) {
        return;
    }

    fetchNotifications(true);
    window.setInterval(function() {
        fetchNotifications(true);
    }, 60000);

    notifButton.parentElement.addEventListener('show.bs.dropdown', function() {
        notifBody.innerHTML = '<div class="p-4 text-center text-muted"><div class="spinner-border spinner-border-sm mb-2" role="status"></div><br><small>Loading updates...</small></div>';
        fetchNotifications(false);
    });

    if (markAllReadButton) {
        markAllReadButton.addEventListener('click', function() {
            updateNotifications({ action: 'mark_all_read' }).then(function(result) {
                if (result && result.success) {
                    updateUI(result, false);
                }
            });
        });
    }

    notifBody.addEventListener('click', function(event) {
        const item = event.target.closest('.notification-item');
        if (!item) {
            return;
        }

        event.preventDefault();
        const notificationId = Number(item.dataset.id || 0);
        const destination = item.getAttribute('href') || '#';

        updateNotifications({
            action: 'mark_read',
            notification_id: notificationId
        }).finally(function() {
            if (destination !== '#') {
                window.location.href = destination;
            } else {
                fetchNotifications(false);
            }
        });
    });

    function fetchNotifications(isInitialLoad) {
        return fetch(ajaxUrl, {
            credentials: 'same-origin',
            headers: { 'Accept': 'application/json' }
        })
            .then(parseResponse)
            .then(function(result) {
                if (result.success) {
                    updateUI(result, isInitialLoad);
                }
                return result;
            })
            .catch(function(error) {
                console.error('Error fetching notifications:', error);
                if (!isInitialLoad) {
                    notifBody.innerHTML = '<div class="p-3 text-center text-danger"><small>Failed to load notifications.</small></div>';
                }
            });
    }

    function updateNotifications(payload) {
        return fetch(ajaxUrl, {
            method: 'POST',
            credentials: 'same-origin',
            headers: {
                'Accept': 'application/json',
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(payload)
        })
            .then(parseResponse)
            .catch(function(error) {
                console.error('Error updating notifications:', error);
                return null;
            });
    }

    function parseResponse(response) {
        return response.json().then(function(result) {
            if (!response.ok) {
                throw new Error(result.message || 'Notification request failed.');
            }
            return result;
        });
    }

    function updateUI(result, isInitialLoad) {
        if (badgeCount) {
            if (result.unread_count > 0) {
                badgeCount.textContent = result.unread_count > 9 ? '9+' : result.unread_count;
                badgeCount.style.display = 'flex';
            } else {
                badgeCount.style.display = 'none';
            }
        }

        if (markAllReadButton) {
            markAllReadButton.style.display = result.unread_count > 0 ? 'inline-block' : 'none';
            markAllReadButton.disabled = result.unread_count === 0;
        }

        if (isInitialLoad) {
            return;
        }

        if (notifCountText) {
            notifCountText.textContent = `${result.unread_count} unread update${result.unread_count !== 1 ? 's' : ''}`;
        }

        if (result.data.length === 0) {
            notifBody.innerHTML = `
                <div class="p-4 text-center text-muted">
                    <i class="bi bi-bell-slash fs-2 mb-2 d-block opacity-50"></i>
                    <small>You're all caught up!<br>No notifications yet.</small>
                </div>`;
            return;
        }

        let html = '';
        result.data.forEach(function(item) {
            const dateObj = new Date(String(item.created_at).replace(' ', 'T') + '+08:00');
            const formattedDate = Number.isNaN(dateObj.getTime())
                ? ''
                : dateObj.toLocaleDateString('en-PH', {
                    month: 'short',
                    day: 'numeric',
                    hour: '2-digit',
                    minute: '2-digit'
                });
            const unreadClass = item.is_read === 0 ? 'unread' : '';
            const linkHref = safeActionUrl(item.action_url);
            const iconClass = /^[a-z0-9 -]+$/i.test(item.icon || '') ? item.icon : 'bi-bell';
            const type = String(item.type || 'System');
            let iconBgClass = 'icon-green-light';

            if (type.toUpperCase() === 'SYSTEM' || type.toUpperCase() === 'ALERT') {
                iconBgClass = 'bg-danger-subtle text-danger';
            } else if (type.toUpperCase() === 'ACCOUNT') {
                iconBgClass = 'bg-primary-subtle text-primary';
            }

            html += `
                <a href="${escapeHtml(linkHref)}" class="notification-item ${unreadClass}" data-id="${Number(item.notification_id)}">
                    <div class="notification-icon ${iconBgClass}">
                        <i class="bi ${escapeHtml(iconClass)}"></i>
                    </div>
                    <div class="notification-content">
                        <div class="notification-content-header">
                            <strong>${escapeHtml(item.title)}</strong>
                            <time>${escapeHtml(formattedDate)}</time>
                        </div>
                        <p>${escapeHtml(item.message)}</p>
                        <span class="notification-badge">${escapeHtml(type.toUpperCase())}</span>
                    </div>
                </a>`;
        });

        notifBody.innerHTML = html;
    }

    function escapeHtml(value) {
        return String(value == null ? '' : value)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }

    function safeActionUrl(value) {
        const url = String(value || '');
        return /^[a-z0-9_./?=&%-]+$/i.test(url) && !url.startsWith('//') ? url : '#';
    }
});
