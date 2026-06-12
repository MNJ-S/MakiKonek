<?php
session_start();

if (!isset($_SESSION['admin_id']) || $_SESSION['admin_role'] !== 'Super Admin') {
    header("Location: dashboard.php");
    exit();
}

require_once __DIR__ . '/../includes/db_connect.php';
require_once __DIR__ . '/../includes/prg_flash.php';

date_default_timezone_set('Asia/Manila');

$success_message = prgFlashPull('admin_officials');
$error_message = '';

function adminOfficialEscape(?string $value): string
{
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

function adminOfficialName(array $row): string
{
    return trim(($row['first_name'] ?? '') . ' ' . ($row['last_name'] ?? '')) ?: ($row['username'] ?? 'Official');
}

function adminOfficialPortrait($seed): string
{
    $portrait_map = [
        'Aigrette Panganiban Lajara' => 'aigrette',
        'Teona Lizardo Noprada' => 'teona',
        'Rubie Alcantara Olaes' => 'rubie',
        'Hermano Medalla De Chavez' => 'hermano',
        'Virgilio Torres Lopez' => 'virgilio',
        'Diomedes Nemes Austria' => 'diomedes',
        'Rizal Mercado Pascual' => 'rizal',
        'Freddie Balansay Noprada' => 'freddie',
        'Marcelo Atienza Molinyawe' => 'marcelo',
        'Antonio Hempesalla Medalla' => 'antonio',
        'Aaron Klyne Macasadia Magsino' => 'aaron',
        'Christian Heplan Perez' => 'christian',
        'John Paul De Castro Evangelista' => 'john-paul',
        'Mark Harold Alferez Burgos' => 'mark-harold',
        'Dhanna Marie Macasadia Montes' => 'dhanna',
        'Jaz Elle Carpio Alvarez' => 'jaz-elle',
        'Ellaine Buena Egloria' => 'ellaine',
        'Jhenie Lee Siman Laude' => 'jhenie-lee',
    ];

    if (isset($portrait_map[(string)$seed])) {
        return '../assets/img/officials/official-' . $portrait_map[(string)$seed] . '.webp';
    }

    $index = ((abs(crc32((string)$seed)) % 20) + 1);
    return sprintf('../assets/img/officials/official-%02d.webp', $index);
}

function adminOfficialGroup(string $role): string
{
    return strtoupper($role) === 'SK' ? 'Sangguniang Kabataan' : 'Barangay Officials';
}

function adminOfficialSplitName(string $full_name): array
{
    $parts = preg_split('/\s+/', trim($full_name));
    $last_name = array_pop($parts);
    return [trim(implode(' ', $parts)), $last_name ?: $full_name];
}

function seedAboutPageOfficials(mysqli $conn): void
{
    $roster = [
        ['Aigrette Panganiban Lajara', 'Barangay Captain', 'Barangay Council', 'Opisyal'],
        ['Teona Lizardo Noprada', 'Barangay Secretary', 'Barangay Council', 'Opisyal'],
        ['Rubie Alcantara Olaes', 'Barangay Treasurer', 'Barangay Council', 'Opisyal'],
        ['Hermano Medalla De Chavez', 'Kagawad', 'Barangay Council', 'Opisyal'],
        ['Virgilio Torres Lopez', 'Kagawad', 'Barangay Council', 'Opisyal'],
        ['Diomedes Nemes Austria', 'Kagawad', 'Barangay Council', 'Opisyal'],
        ['Rizal Mercado Pascual', 'Kagawad', 'Barangay Council', 'Opisyal'],
        ['Freddie Balansay Noprada', 'Kagawad', 'Barangay Council', 'Opisyal'],
        ['Marcelo Atienza Molinyawe', 'Kagawad', 'Barangay Council', 'Opisyal'],
        ['Antonio Hempesalla Medalla', 'Kagawad', 'Barangay Council', 'Opisyal'],
        ['Aaron Klyne Macasadia Magsino', 'SK Chairman', 'Sangguniang Kabataan Council', 'SK'],
        ['Christian Heplan Perez', 'SK Kagawad', 'Sangguniang Kabataan Council', 'SK'],
        ['John Paul De Castro Evangelista', 'SK Kagawad', 'Sangguniang Kabataan Council', 'SK'],
        ['Mark Harold Alferez Burgos', 'SK Kagawad', 'Sangguniang Kabataan Council', 'SK'],
        ['Dhanna Marie Macasadia Montes', 'SK Kagawad', 'Sangguniang Kabataan Council', 'SK'],
        ['Jaz Elle Carpio Alvarez', 'SK Kagawad', 'Sangguniang Kabataan Council', 'SK'],
        ['Ellaine Buena Egloria', 'SK Kagawad', 'Sangguniang Kabataan Council', 'SK'],
        ['Jhenie Lee Siman Laude', 'SK Kagawad', 'Sangguniang Kabataan Council', 'SK'],
    ];

    $exists_stmt = mysqli_prepare($conn, "
        SELECT u.user_id
        FROM users u
        JOIN user_profiles p ON u.user_id = p.user_id
        JOIN barangay_officials bo ON u.user_id = bo.user_id
        WHERE p.first_name = ? AND p.last_name = ? AND bo.position = ?
        LIMIT 1
    ");
    $email_stmt = mysqli_prepare($conn, "SELECT user_id FROM users WHERE email = ? LIMIT 1");
    $insert_user_stmt = mysqli_prepare($conn, "INSERT INTO users (username, email, password, role) VALUES (?, ?, ?, ?)");
    $insert_profile_stmt = mysqli_prepare($conn, "INSERT INTO user_profiles (user_id, first_name, last_name, mobile_number, date_registration) VALUES (?, ?, ?, ?, CURRENT_DATE())");
    $insert_official_stmt = mysqli_prepare($conn, "INSERT INTO barangay_officials (user_id, position, committee, term_start, term_end) VALUES (?, ?, ?, '2023-06-30', '2026-06-30')");

    if (!$exists_stmt || !$email_stmt || !$insert_user_stmt || !$insert_profile_stmt || !$insert_official_stmt) {
        return;
    }

    foreach ($roster as $index => [$full_name, $position, $committee, $role]) {
        [$first_name, $last_name] = adminOfficialSplitName($full_name);

        mysqli_stmt_bind_param($exists_stmt, "sss", $first_name, $last_name, $position);
        mysqli_stmt_execute($exists_stmt);
        $existing = mysqli_stmt_get_result($exists_stmt);
        if ($existing && mysqli_num_rows($existing) > 0) {
            continue;
        }

        $slug = strtolower(preg_replace('/[^a-z0-9]+/i', '.', $full_name));
        $slug = trim($slug, '.');
        $username = 'official_' . str_pad((string)($index + 1), 2, '0', STR_PAD_LEFT);
        $email = $slug . '@makiling.gov.ph';
        $password = 'makikonek2026';
        $mobile = '0917 000 ' . str_pad((string)($index + 1), 4, '0', STR_PAD_LEFT);

        mysqli_stmt_bind_param($email_stmt, "s", $email);
        mysqli_stmt_execute($email_stmt);
        $email_existing = mysqli_stmt_get_result($email_stmt);

        if ($email_existing && ($user = mysqli_fetch_assoc($email_existing))) {
            $user_id = (int)$user['user_id'];
        } else {
            mysqli_stmt_bind_param($insert_user_stmt, "ssss", $username, $email, $password, $role);
            mysqli_stmt_execute($insert_user_stmt);
            $user_id = mysqli_insert_id($conn);

            mysqli_stmt_bind_param($insert_profile_stmt, "isss", $user_id, $first_name, $last_name, $mobile);
            mysqli_stmt_execute($insert_profile_stmt);
        }

        mysqli_stmt_bind_param($insert_official_stmt, "iss", $user_id, $position, $committee);
        mysqli_stmt_execute($insert_official_stmt);
    }
}

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['archive_official'])) {
    $user_id = (int)$_POST['user_id'];
    $stmt = mysqli_prepare($conn, "UPDATE barangay_officials SET is_active = 0 WHERE user_id = ?");
    mysqli_stmt_bind_param($stmt, "i", $user_id);

    if (mysqli_stmt_execute($stmt)) {
        prgRedirect(
            'manage_officials.php',
            'admin_officials',
            'Official account archived successfully.'
        );
    } else {
        $error_message = "Failed to archive official.";
    }
}

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['create_official'])) {
    $official_role = trim($_POST['official_role'] ?? 'Opisyal');
    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $first_name = trim($_POST['first_name'] ?? '');
    $last_name = trim($_POST['last_name'] ?? '');
    $position = trim($_POST['position'] ?? '');
    $committee = trim($_POST['committee'] ?? '');
    $term_start = trim($_POST['term_start'] ?? '');
    $term_end = trim($_POST['term_end'] ?? '');

    if ($username === '' || $email === '' || $password === '' || $first_name === '' || $last_name === '' || $position === '' || $term_start === '') {
        $error_message = "Please complete the required official account details.";
    } else {
        mysqli_begin_transaction($conn);
        try {
            $insert_user = "INSERT INTO users (username, email, password, role) VALUES (?, ?, ?, ?)";
            $stmt_user = mysqli_prepare($conn, $insert_user);
            mysqli_stmt_bind_param($stmt_user, "ssss", $username, $email, $password, $official_role);
            mysqli_stmt_execute($stmt_user);
            $new_user_id = mysqli_insert_id($conn);

            $insert_profile = "INSERT INTO user_profiles (user_id, first_name, last_name) VALUES (?, ?, ?)";
            $stmt_profile = mysqli_prepare($conn, $insert_profile);
            mysqli_stmt_bind_param($stmt_profile, "iss", $new_user_id, $first_name, $last_name);
            mysqli_stmt_execute($stmt_profile);

            $insert_official = "INSERT INTO barangay_officials (user_id, position, committee, term_start, term_end) VALUES (?, ?, ?, ?, ?)";
            $stmt_official = mysqli_prepare($conn, $insert_official);
            $term_end_value = $term_end !== '' ? $term_end : null;
            mysqli_stmt_bind_param($stmt_official, "issss", $new_user_id, $position, $committee, $term_start, $term_end_value);
            mysqli_stmt_execute($stmt_official);

            mysqli_commit($conn);
            prgRedirect(
                'manage_officials.php',
                'admin_officials',
                "Successfully created {$official_role} account for {$first_name}."
            );
        } catch (Exception $e) {
            mysqli_rollback($conn);
            $error_message = "System error: Could not create official account.";
        }
    }
}

seedAboutPageOfficials($conn);

$fetch_query = "
    SELECT u.user_id, u.username, u.email, u.role, u.created_at,
           p.first_name, p.last_name, p.mobile_number,
           bo.position, bo.committee, bo.term_start, bo.term_end
    FROM users u
    JOIN user_profiles p ON u.user_id = p.user_id
    JOIN barangay_officials bo ON u.user_id = bo.user_id
    WHERE bo.is_active = 1
    ORDER BY
        CASE WHEN UPPER(u.role) = 'SK' THEN 2 ELSE 1 END,
        CASE
            WHEN bo.position LIKE '%Captain%' THEN 1
            WHEN bo.position LIKE '%Chair%' THEN 2
            WHEN bo.position LIKE '%Secretary%' THEN 3
            WHEN bo.position LIKE '%Treasurer%' THEN 4
            ELSE 5
        END,
        bo.position ASC,
        p.last_name ASC
";
$officials_result = mysqli_query($conn, $fetch_query);
$officials = [];
if ($officials_result) {
    while ($row = mysqli_fetch_assoc($officials_result)) {
        $officials[] = $row;
    }
}

$barangay_officials = array_values(array_filter($officials, fn($row) => strtoupper($row['role']) !== 'SK'));
$sk_officials = array_values(array_filter($officials, fn($row) => strtoupper($row['role']) === 'SK'));
$total_officials = count($officials);
$barangay_count = count($barangay_officials);
$sk_count = count($sk_officials);
$ending_terms = array_values(array_filter($officials, function ($row) {
    return !empty($row['term_end']) && strtotime($row['term_end']) !== false && strtotime($row['term_end']) <= strtotime('+90 days');
}));
$featured = $officials[0] ?? null;
?>
<!DOCTYPE html>
<html lang="en" data-bs-theme="light">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Officials | MakiKonek</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <link rel="stylesheet" href="../assets/css/admin.css?v=20260612v">
</head>

<body class="dashboard-body">
    <?php include __DIR__ . '/partials/admin_sidebar.php'; ?>

    <main class="main-content admin-modern-page officials-main">
        <header class="admin-modern-header">
            <div>
                <h1>Officials</h1>
                <p>Manage barangay and Sangguniang Kabataan officials.</p>
            </div>
            <div class="admin-modern-actions">
                <div class="dashboard-date-card">
                    <i class="bi bi-calendar3"></i>
                    <span>
                        <strong><?php echo date('F d, Y'); ?></strong>
                        <small><?php echo date('l, h:i A'); ?></small>
                    </span>
                </div>
                <button class="dashboard-notification" type="button" aria-label="Notifications">
                    <i class="bi bi-bell"></i>
                    <?php if (count($ending_terms) > 0): ?><span><?php echo min(9, count($ending_terms)); ?></span><?php endif; ?>
                </button>
                <a href="#official-create-card" class="admin-primary-btn"><i class="bi bi-plus-lg"></i> Add Official</a>
            </div>
        </header>

        <?php if ($success_message): ?>
            <div class="alert alert-success alert-dismissible fade show shadow-sm"><i class="bi bi-check-circle me-2"></i><?php echo adminOfficialEscape($success_message); ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
        <?php endif; ?>
        <?php if ($error_message): ?>
            <div class="alert alert-danger alert-dismissible fade show shadow-sm"><i class="bi bi-exclamation-triangle me-2"></i><?php echo adminOfficialEscape($error_message); ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
        <?php endif; ?>

        <section class="official-kpi-grid" aria-label="Officials analytics">
            <article class="admin-kpi-panel">
                <span><i class="bi bi-people"></i></span>
                <div><p>Barangay Officials</p><strong><?php echo $barangay_count; ?></strong><small>Active officials</small></div>
            </article>
            <article class="admin-kpi-panel admin-kpi-blue">
                <span><i class="bi bi-person-hearts"></i></span>
                <div><p>SK Officials</p><strong><?php echo $sk_count; ?></strong><small>Active youth council</small></div>
            </article>
            <article class="admin-kpi-panel">
                <span><i class="bi bi-person-badge"></i></span>
                <div><p>Total Officials</p><strong><?php echo $total_officials; ?></strong><small>Across all groups</small></div>
            </article>
        </section>

        <nav class="admin-filter-pills" aria-label="Official group filters">
            <button type="button" class="is-active" data-official-filter="all">All Officials</button>
            <button type="button" data-official-filter="barangay">Barangay Officials</button>
            <button type="button" data-official-filter="sk">SK Officials</button>
        </nav>

        <section class="officials-layout">
            <div class="officials-directory">
                <?php if ($featured): ?>
                    <article class="official-feature-card" data-official-group="<?php echo strtoupper($featured['role']) === 'SK' ? 'sk' : 'barangay'; ?>">
                        <img src="<?php echo adminOfficialPortrait(adminOfficialName($featured)); ?>" alt="<?php echo adminOfficialEscape(adminOfficialName($featured)); ?>" width="112" height="112" loading="lazy" decoding="async">
                        <div>
                            <span><?php echo adminOfficialEscape($featured['position']); ?></span>
                            <h2><?php echo adminOfficialEscape(adminOfficialName($featured)); ?></h2>
                            <p><?php echo adminOfficialEscape(adminOfficialGroup($featured['role'])); ?></p>
                            <small><i class="bi bi-envelope"></i><?php echo adminOfficialEscape($featured['email']); ?></small>
                            <small><i class="bi bi-calendar2-check"></i><?php echo adminOfficialEscape(date('M d, Y', strtotime($featured['term_start']))); ?> - <?php echo !empty($featured['term_end']) ? adminOfficialEscape(date('M d, Y', strtotime($featured['term_end']))) : 'Present'; ?></small>
                        </div>
                        <button type="button" class="official-view-btn" data-name="<?php echo adminOfficialEscape(adminOfficialName($featured)); ?>" data-position="<?php echo adminOfficialEscape($featured['position']); ?>" data-role="<?php echo adminOfficialEscape(adminOfficialGroup($featured['role'])); ?>" data-email="<?php echo adminOfficialEscape($featured['email']); ?>" data-committee="<?php echo adminOfficialEscape($featured['committee'] ?: 'General Administration'); ?>" data-term="<?php echo adminOfficialEscape(date('M d, Y', strtotime($featured['term_start'])) . ' - ' . (!empty($featured['term_end']) ? date('M d, Y', strtotime($featured['term_end'])) : 'Present')); ?>">View Profile</button>
                    </article>
                <?php endif; ?>

                <article class="admin-card official-list-card">
                    <div class="admin-card-heading">
                        <div>
                            <h2>Official Directory</h2>
                            <p>Active officials from the current database records.</p>
                        </div>
                        <select id="officialPositionFilter" aria-label="Filter by position">
                            <option value="all">All Positions</option>
                            <?php foreach (array_unique(array_map(fn($row) => $row['position'], $officials)) as $position): ?>
                                <option value="<?php echo adminOfficialEscape($position); ?>"><?php echo adminOfficialEscape($position); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <?php if (!empty($officials)): ?>
                        <div class="official-card-grid">
                            <?php foreach ($officials as $row):
                                $group = strtoupper($row['role']) === 'SK' ? 'sk' : 'barangay';
                            ?>
                                <article class="official-person-card" data-official-group="<?php echo $group; ?>" data-official-position="<?php echo adminOfficialEscape($row['position']); ?>">
                                    <img src="<?php echo adminOfficialPortrait(adminOfficialName($row)); ?>" alt="<?php echo adminOfficialEscape(adminOfficialName($row)); ?>" width="74" height="74" loading="lazy" decoding="async">
                                    <span><?php echo adminOfficialEscape($row['position']); ?></span>
                                    <h3><?php echo adminOfficialEscape(adminOfficialName($row)); ?></h3>
                                    <p><?php echo adminOfficialEscape($row['committee'] ?: adminOfficialGroup($row['role'])); ?></p>
                                    <button type="button" class="official-view-btn" data-name="<?php echo adminOfficialEscape(adminOfficialName($row)); ?>" data-position="<?php echo adminOfficialEscape($row['position']); ?>" data-role="<?php echo adminOfficialEscape(adminOfficialGroup($row['role'])); ?>" data-email="<?php echo adminOfficialEscape($row['email']); ?>" data-committee="<?php echo adminOfficialEscape($row['committee'] ?: 'General Administration'); ?>" data-term="<?php echo adminOfficialEscape(date('M d, Y', strtotime($row['term_start'])) . ' - ' . (!empty($row['term_end']) ? date('M d, Y', strtotime($row['term_end'])) : 'Present')); ?>">View Profile</button>
                                    <form action="manage_officials.php" method="POST" onsubmit="return confirm('Archive this official account?');">
                                        <input type="hidden" name="user_id" value="<?php echo (int)$row['user_id']; ?>">
                                        <button type="submit" name="archive_official" aria-label="Archive official"><i class="bi bi-archive"></i></button>
                                    </form>
                                </article>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <div class="admin-empty-state">
                            <i class="bi bi-person-badge"></i>
                            <strong>No active officials found.</strong>
                            <span>Add an official account to build the directory.</span>
                        </div>
                    <?php endif; ?>
                </article>
            </div>

            <aside class="officials-side-stack">
                <article class="admin-card official-create-card" id="official-create-card">
                    <div class="admin-card-heading">
                        <div>
                            <h2>Add Official</h2>
                            <p>Create an account and assign a position.</p>
                        </div>
                    </div>
                    <form action="manage_officials.php" method="POST" class="admin-form-grid">
                        <label>Given Name<input type="text" name="first_name" required></label>
                        <label>Surname<input type="text" name="last_name" required></label>
                        <label>Username<input type="text" name="username" required></label>
                        <label>Email<input type="email" name="email" required></label>
                        <label>Password<input type="password" name="password" required></label>
                        <label>Role<select name="official_role"><option value="Opisyal">Barangay Opisyal</option><option value="SK">SK Official</option></select></label>
                        <label>Position<input type="text" name="position" required></label>
                        <label>Committee<input type="text" name="committee"></label>
                        <label>Term Start<input type="date" name="term_start" required></label>
                        <label>Term End<input type="date" name="term_end"></label>
                        <button type="submit" name="create_official">Create Official</button>
                    </form>
                </article>

                <article class="admin-card">
                    <div class="admin-card-heading">
                        <div>
                            <h2>Term Reminders</h2>
                            <p>Terms ending within 90 days.</p>
                        </div>
                    </div>
                    <div class="official-reminder-list">
                        <?php if (!empty($ending_terms)): ?>
                            <?php foreach (array_slice($ending_terms, 0, 5) as $row): ?>
                                <div>
                                    <img src="<?php echo adminOfficialPortrait(adminOfficialName($row)); ?>" alt="" width="42" height="42" loading="lazy" decoding="async">
                                    <span><strong><?php echo adminOfficialEscape(adminOfficialName($row)); ?></strong><small>Term ends on <?php echo adminOfficialEscape(date('M d, Y', strtotime($row['term_end']))); ?></small></span>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="official-muted-note">No urgent term reminders.</div>
                        <?php endif; ?>
                    </div>
                </article>
            </aside>
        </section>
    </main>

    <aside class="admin-side-drawer" id="officialDrawer" aria-label="Official profile">
        <div class="admin-side-drawer-header">
            <div>
                <span>Official Profile</span>
                <h2 id="drawerOfficialName">Official</h2>
            </div>
            <button type="button" id="officialDrawerClose" aria-label="Close profile"><i class="bi bi-x-lg"></i></button>
        </div>
        <dl>
            <div><dt>Position</dt><dd id="drawerOfficialPosition"></dd></div>
            <div><dt>Group</dt><dd id="drawerOfficialRole"></dd></div>
            <div><dt>Email</dt><dd id="drawerOfficialEmail"></dd></div>
            <div><dt>Committee</dt><dd id="drawerOfficialCommittee"></dd></div>
            <div><dt>Term</dt><dd id="drawerOfficialTerm"></dd></div>
        </dl>
    </aside>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const drawer = document.getElementById('officialDrawer');
            document.querySelectorAll('.official-view-btn').forEach(button => {
                button.addEventListener('click', function() {
                    document.getElementById('drawerOfficialName').textContent = this.dataset.name;
                    document.getElementById('drawerOfficialPosition').textContent = this.dataset.position;
                    document.getElementById('drawerOfficialRole').textContent = this.dataset.role;
                    document.getElementById('drawerOfficialEmail').textContent = this.dataset.email;
                    document.getElementById('drawerOfficialCommittee').textContent = this.dataset.committee;
                    document.getElementById('drawerOfficialTerm').textContent = this.dataset.term;
                    drawer.classList.add('is-open');
                });
            });

            document.getElementById('officialDrawerClose').addEventListener('click', function() {
                drawer.classList.remove('is-open');
            });

            const groupButtons = document.querySelectorAll('[data-official-filter]');
            const positionFilter = document.getElementById('officialPositionFilter');

            function applyFilters() {
                const activeGroup = document.querySelector('[data-official-filter].is-active')?.dataset.officialFilter || 'all';
                const position = positionFilter.value;
                document.querySelectorAll('[data-official-group]').forEach(card => {
                    const groupMatches = activeGroup === 'all' || card.dataset.officialGroup === activeGroup;
                    const positionMatches = !card.dataset.officialPosition || position === 'all' || card.dataset.officialPosition === position;
                    card.hidden = !(groupMatches && positionMatches);
                });
            }

            groupButtons.forEach(button => {
                button.addEventListener('click', function() {
                    groupButtons.forEach(item => item.classList.remove('is-active'));
                    this.classList.add('is-active');
                    applyFilters();
                });
            });

            positionFilter.addEventListener('change', applyFilters);
        });
    </script>
</body>

</html>
