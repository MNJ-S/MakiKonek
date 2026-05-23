<?php
session_start();

// THE GATEKEEPER
if (!isset($_SESSION['admin_id'])) {
    header("Location: login_admin.php");
    exit();
}

require_once __DIR__ . '/../includes/db_connect.php';

// Fetch all residents by joining the users and user_profiles tables
$query = "
    SELECT u.user_id, u.email, u.created_at, 
           p.first_name, p.last_name, p.purok_address, p.contact_number 
    FROM users u
    INNER JOIN user_profiles p ON u.user_id = p.user_id
    ORDER BY u.created_at DESC
";
$result = mysqli_query($conn, $query);
?>

<!DOCTYPE html>
<html lang="en" data-bs-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Residents | MakiKonek</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <style>
        body { background-color: #121212; }
        .main-content { min-height: 100vh; padding: 2rem; }
        .custom-card { background-color: #1e1e1e; border: 1px solid #333; border-radius: 8px; }
    </style>
</head>
<body>

<div class="container-fluid">
    <main class="main-content mx-auto" style="max-width: 1200px;">
        
        <div class="d-flex justify-content-between align-items-center mb-4 pb-2 border-bottom border-secondary">
            <h2 class="fw-bold text-white"><i class="bi bi-people text-success me-2"></i> Resident Directory</h2>
            <a href="dashboard.php" class="btn btn-outline-light"><i class="bi bi-arrow-left me-1"></i> Dashboard</a>
        </div>

        <div class="custom-card p-4 shadow-sm">
            <div class="table-responsive">
                <table class="table table-dark table-hover align-middle border-secondary">
                    <thead class="table-active">
                        <tr>
                            <th>Full Name</th>
                            <th>Purok Address</th>
                            <th>Contact Number</th>
                            <th>Email Address</th>
                            <th>Registration Date</th>
                            <th class="text-center">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (mysqli_num_rows($result) > 0): ?>
                            <?php while ($row = mysqli_fetch_assoc($result)): ?>
                                <tr>
                                    <td class="fw-bold"><?php echo htmlspecialchars($row['first_name'] . ' ' . $row['last_name']); ?></td>
                                    <td><?php echo htmlspecialchars($row['purok_address']); ?></td>
                                    <td><?php echo htmlspecialchars($row['contact_number']); ?></td>
                                    <td class="text-muted"><?php echo htmlspecialchars($row['email']); ?></td>
                                    <td class="small"><?php echo date("M d, Y", strtotime($row['created_at'])); ?></td>
                                    <td class="text-center">
                                        <button class="btn btn-sm btn-primary" title="View Full Profile">
                                            <i class="bi bi-eye"></i> View
                                        </button>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="6" class="text-center text-muted py-4">No residents registered yet.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

    </main>
</div>
</body>
</html>