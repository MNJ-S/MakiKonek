<?php
session_start();

if (!isset($_SESSION['admin_id'])) {
    header("Location: login_admin.php");
    exit();
}

require_once __DIR__ . '/../includes/db_connect.php';

date_default_timezone_set('Asia/Manila');

$success_message = '';
$error_message = '';
$upload_dir = __DIR__ . '/../assets/uploads/announcements';
$upload_public_path = '../assets/uploads/announcements/';

if (!is_dir($upload_dir)) {
    mkdir($upload_dir, 0775, true);
}

mysqli_query($conn, "
    CREATE TABLE IF NOT EXISTS announcements (
        announcement_id INT AUTO_INCREMENT PRIMARY KEY,
        title VARCHAR(180) NOT NULL,
        category VARCHAR(40) NOT NULL DEFAULT 'Announcement',
        summary VARCHAR(255) DEFAULT NULL,
        body TEXT NOT NULL,
        event_date DATE DEFAULT NULL,
        event_time VARCHAR(80) DEFAULT NULL,
        location VARCHAR(160) DEFAULT NULL,
        cover_image VARCHAR(255) DEFAULT NULL,
        status VARCHAR(20) NOT NULL DEFAULT 'Published',
        created_by INT DEFAULT NULL,
        created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci
");

function adminAnnouncementEscape(?string $value): string
{
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

function adminAnnouncementCategoryClass(string $category): string
{
    $normalized = strtolower($category);
    if ($normalized === 'program') return 'admin-ann-program';
    if ($normalized === 'advisory') return 'admin-ann-advisory';
    if ($normalized === 'event') return 'admin-ann-event';
    return 'admin-ann-default';
}

function adminAnnouncementExcerpt(string $value, int $limit = 120): string
{
    $clean = trim(strip_tags($value));
    return strlen($clean) > $limit ? substr($clean, 0, $limit - 3) . '...' : $clean;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create_announcement'])) {
    $title = trim($_POST['title'] ?? '');
    $category = trim($_POST['category'] ?? 'Announcement');
    $summary = trim($_POST['summary'] ?? '');
    $body = trim($_POST['body'] ?? '');
    $event_date = trim($_POST['event_date'] ?? '');
    $event_time = trim($_POST['event_time'] ?? '');
    $location = trim($_POST['location'] ?? '');
    $status = trim($_POST['status'] ?? 'Published');
    $cover_image = null;

    if ($title === '' || $body === '') {
        $error_message = 'Please add an announcement title and content.';
    } else {
        if (!empty($_FILES['cover_image']['name']) && is_uploaded_file($_FILES['cover_image']['tmp_name'])) {
            $extension = strtolower(pathinfo($_FILES['cover_image']['name'], PATHINFO_EXTENSION));
            $allowed = ['jpg', 'jpeg', 'png', 'webp'];
            if (in_array($extension, $allowed, true)) {
                $filename = 'announcement_' . time() . '_' . random_int(1000, 9999) . '.' . $extension;
                $target = $upload_dir . '/' . $filename;
                if (move_uploaded_file($_FILES['cover_image']['tmp_name'], $target)) {
                    $cover_image = $upload_public_path . $filename;
                }
            }
        }

        $event_date_value = $event_date !== '' ? $event_date : null;
        $stmt = mysqli_prepare($conn, "
            INSERT INTO announcements
                (title, category, summary, body, event_date, event_time, location, cover_image, status, created_by)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        $admin_id = (int)$_SESSION['admin_id'];
        mysqli_stmt_bind_param($stmt, "sssssssssi", $title, $category, $summary, $body, $event_date_value, $event_time, $location, $cover_image, $status, $admin_id);

        if (mysqli_stmt_execute($stmt)) {
            $success_message = 'Announcement created successfully.';
        } else {
            $error_message = 'Could not create announcement.';
        }
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_announcement_status'])) {
    $announcement_id = (int)($_POST['announcement_id'] ?? 0);
    $status = trim($_POST['status'] ?? 'Draft');
    if ($announcement_id > 0 && in_array($status, ['Published', 'Draft', 'Archived'], true)) {
        $stmt = mysqli_prepare($conn, "UPDATE announcements SET status = ? WHERE announcement_id = ?");
        mysqli_stmt_bind_param($stmt, "si", $status, $announcement_id);
        $success_message = mysqli_stmt_execute($stmt) ? 'Announcement status updated.' : 'Could not update announcement.';
    }
}

$announcements_result = mysqli_query($conn, "SELECT * FROM announcements ORDER BY created_at DESC, announcement_id DESC");
$announcements = [];
if ($announcements_result) {
    while ($row = mysqli_fetch_assoc($announcements_result)) {
        $announcements[] = $row;
    }
}

$published_count = count(array_filter($announcements, fn($row) => $row['status'] === 'Published'));
$draft_count = count(array_filter($announcements, fn($row) => $row['status'] === 'Draft'));
$this_month_count = count(array_filter($announcements, fn($row) => date('Y-m', strtotime($row['created_at'])) === date('Y-m')));
?>
<!DOCTYPE html>
<html lang="en" data-bs-theme="light">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Announcements | MakiKonek</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <link rel="stylesheet" href="../assets/css/admin.css?v=20260608n">
</head>

<body class="dashboard-body">
    <?php include __DIR__ . '/partials/admin_sidebar.php'; ?>

    <main class="main-content admin-modern-page announcements-admin-main">
        <header class="admin-modern-header">
            <div>
                <h1>Announcements</h1>
                <p>Create and manage public barangay announcements.</p>
            </div>
            <div class="admin-modern-actions">
                <div class="dashboard-date-card">
                    <i class="bi bi-calendar3"></i>
                    <span>
                        <strong><?php echo date('F d, Y'); ?></strong>
                        <small><?php echo date('l, h:i A'); ?></small>
                    </span>
                </div>
                <a href="#announcementComposer" class="admin-primary-btn"><i class="bi bi-plus-lg"></i> Create Announcement</a>
            </div>
        </header>

        <?php if ($success_message): ?>
            <div class="alert alert-success alert-dismissible fade show shadow-sm"><i class="bi bi-check-circle me-2"></i><?php echo adminAnnouncementEscape($success_message); ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
        <?php endif; ?>
        <?php if ($error_message): ?>
            <div class="alert alert-danger alert-dismissible fade show shadow-sm"><i class="bi bi-exclamation-triangle me-2"></i><?php echo adminAnnouncementEscape($error_message); ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
        <?php endif; ?>

        <section class="official-kpi-grid" aria-label="Announcement summary">
            <article class="admin-kpi-panel">
                <span><i class="bi bi-megaphone"></i></span>
                <div><p>Published</p><strong><?php echo $published_count; ?></strong><small>Visible to residents</small></div>
            </article>
            <article class="admin-kpi-panel admin-kpi-blue">
                <span><i class="bi bi-pencil-square"></i></span>
                <div><p>Drafts</p><strong><?php echo $draft_count; ?></strong><small>Needs final review</small></div>
            </article>
            <article class="admin-kpi-panel">
                <span><i class="bi bi-calendar-event"></i></span>
                <div><p>This Month</p><strong><?php echo $this_month_count; ?></strong><small>Created announcements</small></div>
            </article>
        </section>

        <section class="announcement-admin-layout">
            <article class="admin-card announcement-composer-card" id="announcementComposer">
                <div class="admin-card-heading">
                    <div>
                        <h2>Create Announcement</h2>
                        <p>Publish advisories, programs, events, and barangay updates.</p>
                    </div>
                </div>
                <form action="manage_announcements.php" method="POST" enctype="multipart/form-data" class="announcement-composer-form">
                    <label>Title<input type="text" name="title" placeholder="Enter announcement title" required></label>
                    <label>Category<select name="category"><option>Announcement</option><option>Program</option><option>Advisory</option><option>Event</option></select></label>
                    <label class="span-2">Short Summary<input type="text" name="summary" placeholder="One-line summary for cards"></label>
                    <label class="span-2">Content<textarea name="body" rows="7" placeholder="Write the announcement details..." required></textarea></label>
                    <label>Date<input type="date" name="event_date"></label>
                    <label>Time<input type="text" name="event_time" placeholder="e.g. 8:00 AM - 12:00 PM"></label>
                    <label>Location<input type="text" name="location" placeholder="Barangay Hall"></label>
                    <label>Status<select name="status"><option>Published</option><option>Draft</option></select></label>
                    <label class="span-2">Cover Image<input type="file" name="cover_image" accept="image/png,image/jpeg,image/webp"></label>
                    <button type="submit" name="create_announcement"><i class="bi bi-send"></i> Publish Announcement</button>
                </form>
            </article>

            <article class="admin-card announcement-list-card">
                <div class="admin-card-heading">
                    <div>
                        <h2>Announcement Records</h2>
                        <p>All announcements stored in the database.</p>
                    </div>
                </div>
                <div class="announcement-admin-list">
                    <?php if (!empty($announcements)): ?>
                        <?php foreach ($announcements as $row): ?>
                            <article>
                                <div class="announcement-admin-media <?php echo adminAnnouncementCategoryClass($row['category']); ?>">
                                    <?php if (!empty($row['cover_image'])): ?>
                                        <img src="<?php echo adminAnnouncementEscape($row['cover_image']); ?>" alt="">
                                    <?php else: ?>
                                        <i class="bi bi-megaphone"></i>
                                    <?php endif; ?>
                                </div>
                                <div>
                                    <span><?php echo adminAnnouncementEscape($row['category']); ?> · <?php echo adminAnnouncementEscape($row['status']); ?></span>
                                    <h3><?php echo adminAnnouncementEscape($row['title']); ?></h3>
                                    <p><?php echo adminAnnouncementEscape($row['summary'] ?: adminAnnouncementExcerpt($row['body'])); ?></p>
                                    <small><?php echo adminAnnouncementEscape(date('M d, Y', strtotime($row['created_at']))); ?></small>
                                </div>
                                <form action="manage_announcements.php" method="POST">
                                    <input type="hidden" name="announcement_id" value="<?php echo (int)$row['announcement_id']; ?>">
                                    <select name="status">
                                        <option value="Published" <?php echo $row['status'] === 'Published' ? 'selected' : ''; ?>>Published</option>
                                        <option value="Draft" <?php echo $row['status'] === 'Draft' ? 'selected' : ''; ?>>Draft</option>
                                        <option value="Archived" <?php echo $row['status'] === 'Archived' ? 'selected' : ''; ?>>Archived</option>
                                    </select>
                                    <button type="submit" name="update_announcement_status">Save</button>
                                </form>
                            </article>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="admin-empty-state">
                            <i class="bi bi-megaphone"></i>
                            <strong>No announcements yet.</strong>
                            <span>Create the first public announcement.</span>
                        </div>
                    <?php endif; ?>
                </div>
            </article>
        </section>
    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>
