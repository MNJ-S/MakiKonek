<?php
session_start();

if (!isset($_SESSION['resident_id'])) {
    header("Location: ../login_reg.php");
    exit();
}

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
            <form action="" method="POST" enctype="multipart/form-data" class="profile-layout-wrapper">

                <div class="profile-left-panel">
                    <div class="avatar-card-container">
                        <div class="avatar-placeholder-svg">
                            <img id="avatar-preview" src="" alt="Avatar Preview" style="display:none; width:100%; height:100%; object-fit:cover; border-radius:6px;">
                            <i id="avatar-icon" class="fa-regular fa-user"></i>
                        </div>

                        <input type="file" id="real-file-input" name="profile_avatar" accept="image/*" style="display: none;">

                        <button type="button" class="upload-avatar-action" id="upload-trigger-btn">
                            <i class="fa-solid fa-upload"></i> Upload
                        </button>
                    </div>

                    <div class="emergency-contact-box">
                        <h3>In Case of Emergency</h3>
                        <div class="field full">
                            <label>Full Name</label>
                            <input type="text" name="emergency_name" value="Maria Dela Cruz">
                        </div>
                        <div class="field full">
                            <label>Relationship</label>
                            <input type="text" name="emergency_relationship" value="Mother">
                        </div>
                        <div class="field full">
                            <label>Contact No.</label>
                            <input type="text" name="emergency_contact" value="0917-987-6543">
                        </div>
                        <div class="field full">
                            <label>Address</label>
                            <input type="text" name="emergency_address" value="123 Main Street, Purok 1">
                        </div>
                    </div>
                </div>

                <div class="profile-right-panel">

                    <fieldset class="profile-form-section">
                        <legend>Personal Information</legend>
                        <div class="profile-input-grid">
                            <div class="field"><label>Surname</label><input type="text" name="surname" value="Dela Cruz"></div>
                            <div class="field"><label>Given Name</label><input type="text" name="given_name" value="Juan"></div>
                            <div class="field"><label>Middle Name</label><input type="text" name="middle_name" value="Santos"></div>
                            <div class="field"><label>Suffix</label><input type="text" name="suffix" value=""></div>
                            <div class="field"><label>Sex</label><input type="text" name="sex" value=""></div>
                            <div class="field"><label>Civil Status</label><input type="text" name="civil_status_personal" value=""></div>
                            <div class="field"><label>Birth Date</label><input type="date" name="birth_date" value=""></div>
                            <div class="field"><label>Birth Place</label><input type="text" name="birth_place" value="Los Baños"></div>
                            <div class="field"><label>Religion</label><input type="text" name="religion" value="Roman Catholic"></div>
                            <div class="field"><label>Nationality</label><input type="text" name="nationality" value="Filipino"></div>
                            <div class="field double-wide"><label>Email</label><input type="email" name="email" value="juan.delacruz@email.com"></div>
                            <div class="field double-wide"><label>Mobile Number</label><input type="text" name="mobile_number" value="0917-123-4567"></div>
                        </div>
                    </fieldset>

                    <fieldset class="profile-form-section">
                        <legend>Address</legend>
                        <div class="profile-input-grid">
                            <div class="field double-wide"><label>House No.</label><input type="text" name="house_no" value="123"></div>
                            <div class="field double-wide"><label>Street</label><input type="text" name="street" value="Main Street"></div>
                            <div class="field double-wide"><label>Purok No.</label><input type="text" name="purok_no" value=""></div>
                            <div class="field double-wide"><label>Subdivision</label><input type="text" name="subdivision" value=""></div>
                        </div>
                    </fieldset>

                    <fieldset class="profile-form-section">
                        <legend>Other Details</legend>
                        <div class="profile-input-grid">
                            <div class="field double-wide"><label>National ID No.</label><input type="text" name="national_id" value=""></div>
                            <div class="field double-wide"><label>Philhealth No.</label><input type="text" name="philhealth_no" value=""></div>
                            <div class="field double-wide"><label>Voter's ID No.</label><input type="text" name="voters_id" value=""></div>
                            <div class="field double-wide"><label>SSS No.</label><input type="text" name="sss_no" value=""></div>
                            <div class="field double-wide"><label>Civil Status</label><input type="text" name="civil_status_other" value=""></div>
                            <div class="field double-wide"><label>TIN No.</label><input type="text" name="tin_no" value=""></div>
                            <div class="field double-wide"><label>Years of Residency</label><input type="text" name="years_residency" value="5"></div>
                            <div class="field double-wide"><label>Date of Registration</label><input type="text" name="date_registration" value=""></div>
                            <div class="field double-wide"><label>Employed?</label><input type="text" name="employed_status" value=""></div>
                            <div class="field double-wide"><label>Pag-ibig No.</label><input type="text" name="pagibig_no" value=""></div>
                        </div>
                    </fieldset>

                    <div class="profile-action-row">
                        <button type="submit" class="save-profile-btn">Save Changes</button>
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

    <script src="../assets/js/resident.js?v=20260530a"></script>
</body>

</html>