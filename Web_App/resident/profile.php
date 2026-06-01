<?php
session_start();

if (!isset($_SESSION['resident_id'])) {
    header("Location: ../login_reg.php");
    exit();
}

require_once __DIR__ . '/../includes/db_connect.php';

$resident_id = $_SESSION['resident_id'];
$success_message = '';
$error_message = '';

// --- PROCESSING FORM FORM SUBMISSION ---
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Basic profiling info
    $surname = mysqli_real_escape_string($conn, trim($_POST['surname']));
    $given_name = mysqli_real_escape_string($conn, trim($_POST['given_name']));
    $middle_name = mysqli_real_escape_string($conn, trim($_POST['middle_name']));
    $suffix = mysqli_real_escape_string($conn, trim($_POST['suffix']));
    $sex = mysqli_real_escape_string($conn, trim($_POST['sex']));
    $civil_status = mysqli_real_escape_string($conn, trim($_POST['civil_status_personal']));
    $birth_date = mysqli_real_escape_string($conn, trim($_POST['birth_date']));
    $birth_place = mysqli_real_escape_string($conn, trim($_POST['birth_place']));
    $religion = mysqli_real_escape_string($conn, trim($_POST['religion']));
    $nationality = mysqli_real_escape_string($conn, trim($_POST['nationality']));
    $mobile_number = mysqli_real_escape_string($conn, trim($_POST['mobile_number']));

    // Address fields
    $house_no = mysqli_real_escape_string($conn, trim($_POST['house_no']));
    $street = mysqli_real_escape_string($conn, trim($_POST['street']));
    $purok_no = mysqli_real_escape_string($conn, trim($_POST['purok_no']));
    $subdivision = mysqli_real_escape_string($conn, trim($_POST['subdivision']));

    // Government IDs & Extras
    $national_id = mysqli_real_escape_string($conn, trim($_POST['national_id']));
    $philhealth_no = mysqli_real_escape_string($conn, trim($_POST['philhealth_no']));
    $voters_id = mysqli_real_escape_string($conn, trim($_POST['voters_id']));
    $sss_no = mysqli_real_escape_string($conn, trim($_POST['sss_no']));
    $tin_no = mysqli_real_escape_string($conn, trim($_POST['tin_no']));
    $years_residency = mysqli_real_escape_string($conn, trim($_POST['years_residency']));
    $employed_status = mysqli_real_escape_string($conn, trim($_POST['employed_status']));
    $pagibig_no = mysqli_real_escape_string($conn, trim($_POST['pagibig_no']));

    // Emergency contact fields
    $emergency_name = mysqli_real_escape_string($conn, trim($_POST['emergency_name']));
    $emergency_relationship = mysqli_real_escape_string($conn, trim($_POST['emergency_relationship']));
    $emergency_contact = mysqli_real_escape_string($conn, trim($_POST['emergency_contact']));
    $emergency_address = mysqli_real_escape_string($conn, trim($_POST['emergency_address']));

    // Grab current avatar path to preserve it if no new file is uploaded
    $path_query = "SELECT avatar_path FROM user_profiles WHERE user_id = ? LIMIT 1";
    $p_stmt = mysqli_prepare($conn, $path_query);
    mysqli_stmt_bind_param($p_stmt, "i", $resident_id);
    mysqli_stmt_execute($p_stmt);
    $p_res = mysqli_stmt_get_result($p_stmt);
    $current_profile = mysqli_fetch_assoc($p_res);
    $avatar_path = $current_profile['avatar_path'] ?? '';

    // Handle profile image file upload
    if (isset($_FILES['profile_avatar']) && $_FILES['profile_avatar']['error'] === UPLOAD_ERR_OK) {
        $file_tmp = $_FILES['profile_avatar']['tmp_name'];
        $file_name = $_FILES['profile_avatar']['name'];
        $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));

        $allowed_extensions = ['jpg', 'jpeg', 'png'];
        if (in_array($file_ext, $allowed_extensions)) {
            $upload_dir = __DIR__ . '/../assets/uploads/avatars/';
            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0755, true);
            }

            $new_file_name = 'avatar_' . $resident_id . '_' . time() . '.' . $file_ext;
            $target_file = $upload_dir . $new_file_name;

            if (move_uploaded_file($file_tmp, $target_file)) {
                $avatar_path = 'assets/uploads/avatars/' . $new_file_name;
            }
        } else {
            $error_message = "Invalid file type. Only JPG, JPEG, and PNG files are allowed.";
        }
    }

    if (empty($error_message)) {
        // UPDATE THE CURRENT IDENTITY IN THE DATABASE
        $update_query = "
            UPDATE user_profiles SET 
                first_name = ?, last_name = ?, middle_name = ?, suffix = ?, sex = ?, civil_status = ?, 
                birth_date = ?, birth_place = ?, religion = ?, nationality = ?, mobile_number = ?, 
                house_no = ?, street = ?, purok_no = ?, subdivision = ?, national_id = ?, 
                philhealth_no = ?, voters_id = ?, sss_no = ?, tin_no = ?, years_residency = ?, 
                employed_status = ?, pagibig_no = ?, emergency_name = ?, emergency_relationship = ?, 
                emergency_contact = ?, emergency_address = ?, avatar_path = ?
            WHERE user_id = ?
        ";

        $stmt = mysqli_prepare($conn, $update_query);
        mysqli_stmt_bind_param(
            $stmt,
            "ssssssssssssssssssssssssssssi",
            $given_name,
            $surname,
            $middle_name,
            $suffix,
            $sex,
            $civil_status,
            $birth_date,
            $birth_place,
            $religion,
            $nationality,
            $mobile_number,
            $house_no,
            $street,
            $purok_no,
            $subdivision,
            $national_id,
            $philhealth_no,
            $voters_id,
            $sss_no,
            $tin_no,
            $years_residency,
            $employed_status,
            $pagibig_no,
            $emergency_name,
            $emergency_relationship,
            $emergency_contact,
            $emergency_address,
            $avatar_path,
            $resident_id
        );

        if (mysqli_stmt_execute($stmt)) {
            $success_message = "SAVED SUCCESSFULLY!";
        } else {
            $error_message = "FAILED TO UPDATE PROFILE. PLEASE TRY AGAIN.";
        }
    }
}

// --- ACTIVE DATABASE FETCH FOR CURRENT DATA ---
$fetch_query = "
    SELECT u.email, u.created_at, p.* FROM users u 
    INNER JOIN user_profiles p ON u.user_id = p.user_id 
    WHERE u.user_id = ? LIMIT 1
";

$f_stmt = mysqli_prepare($conn, $fetch_query);
mysqli_stmt_bind_param($f_stmt, "i", $resident_id);
mysqli_stmt_execute($f_stmt);
$resident_data = mysqli_fetch_assoc(mysqli_stmt_get_result($f_stmt));

$pageTitle = 'Profile';
$activePage = 'profile';
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle; ?> | MakiKonek</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/header.css?v=20260530a">
    <link rel="stylesheet" href="../assets/css/footer.css?v=20260529e">
    <link rel="stylesheet" href="../assets/css/resident.css?v=20260530a">
</head>

<body class="resident-page">
    <?php
    $navBase = '../public/';
    $assetBase = '../assets';
    $loginHref = '../login_reg.php';
    $isResidentHeader = true;
    include __DIR__ . '/../includes/header.php';
    ?>

    <div class="resident-shell">
        <?php include __DIR__ . '/partials/resident_sidebar.php'; ?>

        <main class="resident-main">
            <form action="profile.php" method="POST" enctype="multipart/form-data" class="profile-layout-wrapper">

                <div class="profile-left-panel">
                    <div class="avatar-card-container">
                        <div class="avatar-placeholder-svg" style="position: relative; width: 120px; height: 120px; background: #e2e8f0; display: flex; align-items: center; justify-content: center; border-radius: 6px; overflow: hidden;">
                            <?php if (!empty($resident_data['avatar_path'])): ?>
                                <img id="avatar-preview" src="../<?php echo htmlspecialchars($resident_data['avatar_path']); ?>" alt="Avatar Preview" style="width:100%; height:100%; object-fit:cover; border-radius:6px;">
                                <i id="avatar-icon" class="fa-regular fa-user" style="display: none;"></i>
                            <?php else: ?>
                                <img id="avatar-preview" src="" alt="Avatar Preview" style="display:none; width:100%; height:100%; object-fit:cover; border-radius:6px;">
                                <i id="avatar-icon" class="fa-regular fa-user" style="font-size: 2.5rem; color: #a0aec0;"></i>
                            <?php endif; ?>
                        </div>

                        <input type="file" id="real-file-input" name="profile_avatar" accept="image/*" style="display: none;">
                        <label for="real-file-input" class="upload-avatar-action" style="cursor: pointer; display: flex; align-items: center; justify-content: center; gap: 5px;">
                            <i class="fa-solid fa-upload"></i> Upload
                        </label>
                    </div>

                    <div class="emergency-contact-box">
                        <h3>In Case of Emergency</h3>
                        <div class="field full"><label>Full Name</label><input type="text" name="emergency_name" value="<?php echo htmlspecialchars($resident_data['emergency_name'] ?? ''); ?>"></div>
                        <div class="field full"><label>Relationship</label><input type="text" name="emergency_relationship" value="<?php echo htmlspecialchars($resident_data['emergency_relationship'] ?? ''); ?>"></div>
                        <div class="field full"><label>Contact No.</label><input type="text" name="emergency_contact" value="<?php echo htmlspecialchars($resident_data['emergency_contact'] ?? ''); ?>"></div>
                        <div class="field full"><label>Address</label><input type="text" name="emergency_address" value="<?php echo htmlspecialchars($resident_data['emergency_address'] ?? ''); ?>"></div>
                    </div>
                </div>

                <div class="profile-right-panel">
                    <fieldset class="profile-form-section">
                        <legend>Personal Information</legend>
                        <div class="profile-input-grid">
                            <div class="field"><label>Surname</label><input type="text" name="surname" value="<?php echo htmlspecialchars($resident_data['last_name'] ?? ''); ?>" oninput="this.value = this.value.toUpperCase()"></div>
                            <div class="field"><label>Given Name</label><input type="text" name="given_name" value="<?php echo htmlspecialchars($resident_data['first_name'] ?? ''); ?>" oninput="this.value = this.value.toUpperCase()"></div>
                            <div class="field"><label>Middle Name</label><input type="text" name="middle_name" value="<?php echo htmlspecialchars($resident_data['middle_name'] ?? ''); ?>" oninput="this.value = this.value.toUpperCase()"></div>
                            <div class="field"><label>Suffix</label><input type="text" name="suffix" value="<?php echo htmlspecialchars($resident_data['suffix'] ?? ''); ?>" oninput="this.value = this.value.toUpperCase()"></div>
                            <div class="field">
                                <label>Sex</label>
                                <select name="sex" style="width: 100%; padding: 8px; border: 1px solid #ccc; border-radius: 4px;">
                                    <option value="" disabled <?php echo empty($resident_data['sex']) ? 'selected' : ''; ?>>SELECT</option>
                                    <option value="MALE" <?php echo ($resident_data['sex'] ?? '') === 'MALE' ? 'selected' : ''; ?>>MALE</option>
                                    <option value="FEMALE" <?php echo ($resident_data['sex'] ?? '') === 'FEMALE' ? 'selected' : ''; ?>>FEMALE</option>
                                    <option value="PREFER NOT TO SAY" <?php echo ($resident_data['sex'] ?? '') === 'PREFER NOT TO SAY' ? 'selected' : ''; ?>>PREFER NOT TO SAY</option>
                                </select>
                            </div>
                            <div class="field">
                                <label>Civil Status</label>
                                <select name="civil_status_personal" style="width: 100%; padding: 8px; border: 1px solid #ccc; border-radius: 4px;">
                                    <option value="" disabled <?php echo empty($resident_data['civil_status']) ? 'selected' : ''; ?>>SELECT</option>
                                    <option value="SINGLE" <?php echo ($resident_data['civil_status'] ?? '') === 'SINGLE' ? 'selected' : ''; ?>>SINGLE</option>
                                    <option value="MARRIED" <?php echo ($resident_data['civil_status'] ?? '') === 'MARRIED' ? 'selected' : ''; ?>>MARRIED</option>
                                    <option value="WIDOWED" <?php echo ($resident_data['civil_status'] ?? '') === 'WIDOWED' ? 'selected' : ''; ?>>WIDOWED</option>
                                    <option value="SEPARATED" <?php echo ($resident_data['civil_status'] ?? '') === 'SEPARATED' ? 'selected' : ''; ?>>SEPARATED</option>
                                </select>
                            </div>
                            <div class="field"><label>Birth Date</label><input type="date" name="birth_date" value="<?php echo htmlspecialchars($resident_data['birth_date'] ?? ''); ?>"></div>
                            <div class="field"><label>Birth Place</label><input type="text" name="birth_place" value="<?php echo htmlspecialchars($resident_data['birth_place'] ?? ''); ?>" oninput="this.value = this.value.toUpperCase()"></div>
                            <div class="field"><label>Religion</label><input type="text" name="religion" value="<?php echo htmlspecialchars($resident_data['religion'] ?? ''); ?>" oninput="this.value = this.value.toUpperCase()"></div>
                            <div class="field"><label>Nationality</label><input type="text" name="nationality" value="<?php echo htmlspecialchars($resident_data['nationality'] ?? ''); ?>" oninput="this.value = this.value.toUpperCase()"></div>
                            <div class="field double-wide"><label>Email</label><input type="email" name="email" value="<?php echo htmlspecialchars($resident_data['email'] ?? ''); ?>" disabled style="background:#edf2f7; cursor:not-allowed;"></div>
                            <div class="field double-wide"><label>Mobile Number</label><input type="text" name="mobile_number" value="<?php echo htmlspecialchars($resident_data['mobile_number'] ?? ''); ?>"></div>
                        </div>
                    </fieldset>

                    <fieldset class="profile-form-section">
                        <legend>Address</legend>
                        <div class="profile-input-grid">
                            <div class="field double-wide"><label>House No.</label><input type="text" name="house_no" value="<?php echo htmlspecialchars($resident_data['house_no'] ?? ''); ?>" oninput="this.value = this.value.toUpperCase()"></div>
                            <div class="field double-wide"><label>Street</label><input type="text" name="street" value="<?php echo htmlspecialchars($resident_data['street'] ?? ''); ?>" oninput="this.value = this.value.toUpperCase()"></div>
                            <div class="field double-wide"><label>Purok No.</label><input type="text" name="purok_no" value="<?php echo htmlspecialchars($resident_data['purok_no'] ?? ''); ?>" oninput="this.value = this.value.toUpperCase()"></div>
                            <div class="field double-wide"><label>Subdivision</label><input type="text" name="subdivision" value="<?php echo htmlspecialchars($resident_data['subdivision'] ?? ''); ?>" oninput="this.value = this.value.toUpperCase()"></div>
                        </div>
                    </fieldset>

                    <fieldset class="profile-form-section">
                        <legend>Other Details</legend>
                        <div class="profile-input-grid">
                            <div class="field double-wide"><label>National ID No.</label><input type="text" name="national_id" value="<?php echo htmlspecialchars($resident_data['national_id'] ?? ''); ?>"></div>
                            <div class="field double-wide"><label>Philhealth No.</label><input type="text" name="philhealth_no" value="<?php echo htmlspecialchars($resident_data['philhealth_no'] ?? ''); ?>"></div>
                            <div class="field double-wide"><label>Voter's ID No.</label><input type="text" name="voters_id" value="<?php echo htmlspecialchars($resident_data['voters_id'] ?? ''); ?>"></div>
                            <div class="field double-wide"><label>SSS No.</label><input type="text" name="sss_no" value="<?php echo htmlspecialchars($resident_data['sss_no'] ?? ''); ?>"></div>
                            <div class="field double-wide"><label>TIN No.</label><input type="text" name="tin_no" value="<?php echo htmlspecialchars($resident_data['tin_no'] ?? ''); ?>"></div>
                            <div class="field double-wide"><label>Years of Residency</label><input type="text" name="years_residency" value="<?php echo htmlspecialchars($resident_data['years_residency'] ?? ''); ?>"></div>
                            <div class="field double-wide"><label>Date of Registration</label><input type="text" name="date_registration" value="<?php echo date('M d, Y', strtotime($resident_data['created_at'])); ?>" disabled style="background:#edf2f7;" oninput="this.value = this.value.toUpperCase()"></div>
                            <div class="field double-wide">
                                <label>Employed?</label>
                                <select name="employed_status" style="width: 100%; padding: 8px; border: 1px solid #ccc; border-radius: 4px;">
                                    <option value="" disabled <?php echo empty($resident_data['employed_status']) ? 'selected' : ''; ?>>SELECT</option>
                                    <option value="YES" <?php echo ($resident_data['employed_status'] ?? '') === 'YES' ? 'selected' : ''; ?>>YES</option>
                                    <option value="NO" <?php echo ($resident_data['employed_status'] ?? '') === 'NO' ? 'selected' : ''; ?>>NO</option>
                                    <option value="STUDENT" <?php echo ($resident_data['employed_status'] ?? '') === 'STUDENT' ? 'selected' : ''; ?>>STUDENT</option>
                                </select>
                            </div>
                            <div class="field double-wide"><label>Pag-ibig No.</label><input type="text" name="pagibig_no" value="<?php echo htmlspecialchars($resident_data['pagibig_no'] ?? ''); ?>"></div>
                        </div>
                    </fieldset>

                    <div class="profile-action-row" style="display:flex; flex-direction:column; gap:15px; align-items:flex-start;">
                        <button type="submit" class="save-profile-btn">Save Changes</button>

                        <!-- NOTIFICATION -->
                        <div id="notification-container" style="position: fixed; bottom: 30px; right: 30px; z-index: 9999; display: flex; flex-direction: column; gap: 10px;">
                            <?php if (!empty($success_message)): ?>
                                <div class="toast-notification" style="background-color:#dcfce7; border-left: 5px solid #22c55e; color:#15803d; padding:15px 25px; border-radius:6px; font-size:14px; font-weight:bold; box-shadow: 0 10px 25px rgba(0,0,0,0.1); animation: slideIn 0.3s ease-out forwards; transition: opacity 0.5s ease;">
                                    <?php echo $success_message; ?>
                                </div>
                            <?php endif; ?>
                            <?php if (!empty($error_message)): ?>
                                <div class="toast-notification" style="background-color:#fee2e2; border-left: 5px solid #ef4444; color:#b91c1c; padding:15px 25px; border-radius:6px; font-size:14px; font-weight:bold; box-shadow: 0 10px 25px rgba(0,0,0,0.1); animation: slideIn 0.3s ease-out forwards; transition: opacity 0.5s ease;">
                                    <?php echo $error_message; ?>
                                </div>
                            <?php endif; ?>
                        </div>

                        <style>
                            @keyframes slideIn {
                                from {
                                    transform: translateX(120%);
                                    opacity: 0;
                                }

                                to {
                                    transform: translateX(0);
                                    opacity: 1;
                                }
                            }
                        </style>
                    </div>
                </div>
            </form>
        </main>
    </div>

    <?php
    $footerBase = '../public/';
    $footerAssetBase = '../assets';
    include __DIR__ . '/../includes/footer.php';
    ?>

    <script>
        // PREVIEW HANDLER
        document.getElementById('real-file-input').addEventListener('change', function(event) {
            const input = event.target;
            if (input.files && input.files[0]) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    const preview = document.getElementById('avatar-preview');
                    const icon = document.getElementById('avatar-icon');
                    preview.src = e.target.result;
                    preview.style.display = 'block';
                    if (icon) icon.style.display = 'none';
                }
                reader.readAsDataURL(input.files[0]);
            }
        });

        document.addEventListener('DOMContentLoaded', function() {
            const notifications = document.querySelectorAll('.toast-notification');

            notifications.forEach(function(notification) {
                setTimeout(function() {
                    notification.style.opacity = '0';
                    setTimeout(function() {
                        notification.remove();
                    }, 500);
                }, 5000);
            });
        });
    </script>
</body>

</html>