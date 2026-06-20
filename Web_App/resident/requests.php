<?php
session_start();

if (!isset($_SESSION['resident_id'])) {
    header("Location: ../login_reg.php");
    exit();
}

require_once __DIR__ . '/../includes/db_connect.php';
require_once __DIR__ . '/../includes/prg_flash.php';
require_once __DIR__ . '/../includes/input_validation.php';

$payment_status_column_check = mysqli_query($conn, "SHOW COLUMNS FROM service_requests LIKE 'payment_status'");
if ($payment_status_column_check && mysqli_num_rows($payment_status_column_check) === 0) {
    mysqli_query($conn, "ALTER TABLE service_requests ADD COLUMN payment_status VARCHAR(30) DEFAULT 'Unpaid' AFTER payment_receipt_path");
}

$pageTitle = 'My Requests';
$activePage = 'requests';
$success_message = prgFlashPull('resident_requests');
$error_message = '';
$barangay_id_valid_until = (new DateTimeImmutable('today'))->modify('+1 year')->format('Y-m-d');

$resident_id = (int) $_SESSION['resident_id'];
$profile_query = "SELECT u.email, p.* FROM users u LEFT JOIN user_profiles p ON u.user_id = p.user_id WHERE u.user_id = ? LIMIT 1";
$p_stmt = mysqli_prepare($conn, $profile_query);
mysqli_stmt_bind_param($p_stmt, "i", $resident_id);
mysqli_stmt_execute($p_stmt);
$profile = mysqli_fetch_assoc(mysqli_stmt_get_result($p_stmt)) ?: [];

$pref_fname = htmlspecialchars($profile['first_name'] ?? '');
$pref_mname = htmlspecialchars($profile['middle_name'] ?? '');
$pref_lname = htmlspecialchars($profile['last_name'] ?? '');
$pref_suffix = htmlspecialchars($profile['suffix'] ?? '');
$pref_email = htmlspecialchars($profile['email'] ?? '');
$pref_phone = htmlspecialchars($profile['mobile_number'] ?? '');
$pref_bdate = htmlspecialchars($profile['birth_date'] ?? '');

$db_sex = strtoupper(trim($profile['sex'] ?? ''));
if ($db_sex === 'MALE') {
    $pref_gender = 'Male';
} elseif ($db_sex === 'FEMALE') {
    $pref_gender = 'Female';
} else {
    $pref_gender = 'Prefer not to say';
}

$pref_civil = htmlspecialchars(ucfirst(strtolower($profile['civil_status'] ?? '')));
$pref_address = htmlspecialchars(trim(implode(' ', array_filter([$profile['house_no'] ?? '', $profile['street'] ?? '', !empty($profile['purok_no']) ? 'Purok ' . $profile['purok_no'] : '', $profile['subdivision'] ?? '']))));
$documentGroups = [
    'Certificates' => [
        ['name' => 'Barangay Clearance', 'fee' => 'P50.00', 'time' => '1-2 working days'],
        ['name' => 'Certificate of Indigency', 'fee' => 'Free', 'time' => '1-2 working days'],
        ['name' => 'Certificate of Residency', 'fee' => 'P20.00', 'time' => '1-2 working days'],
        ['name' => 'Good Moral Certificate', 'fee' => 'P30.00', 'time' => '1-2 working days'],
    ],
    'Business' => [
        ['name' => 'Business Clearance', 'fee' => 'P100.00', 'time' => '2-3 working days'],
    ],
    'Permits' => [
        ['name' => 'Building/Construction Permit', 'fee' => 'P500.00', 'time' => '3-5 working days'],
        ['name' => 'Cedula', 'fee' => 'Based on LGU', 'time' => 'Same day processing'],
    ],
    'Others' => [
        ['name' => 'Barangay ID', 'fee' => 'P100.00', 'time' => '2-3 working days'],
        ['name' => 'Incident Report', 'fee' => 'P50.00', 'time' => '1-2 working days'],
    ],
];
$documentFees = [];
foreach ($documentGroups as $documents) {
    foreach ($documents as $document) {
        $documentFees[$document['name']] = (float)preg_replace('/[^0-9.]/', '', $document['fee']);
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $document_type = trim($_POST['document_type'] ?? '');
    $document_fee = $documentFees[$document_type] ?? 0.0;
    $payment_method = trim($_POST['payment_method'] ?? 'cash');
    $first_name = trim($_POST['first_name'] ?? '');
    $middle_name = trim($_POST['middle_name'] ?? '');
    $last_name = trim($_POST['last_name'] ?? '');
    $suffix = trim($_POST['suffix'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $birth_date = trim($_POST['birth_date'] ?? '');
    $gender = trim($_POST['gender'] ?? '');
    $civil_status = trim($_POST['civil_status'] ?? '');
    $address = trim($_POST['address'] ?? '');
    $province = trim($_POST['province'] ?? '');
    $city = trim($_POST['city'] ?? '');
    $barangay = trim($_POST['barangay'] ?? '');
    $purpose = trim($_POST['purpose'] ?? '');
    $occupation = trim($_POST['occupation'] ?? '');
    $id_path = '';
    $payment_receipt_path = null;

    $business_name = $business_location = $business_operator = $business_address = $business_nature = $business_permit_for = null;
    $construction_address = $construction_purpose = $construction_other_purpose = $construction_status = $construction_other_status = $construction_description = null;
    $cedula_type = $cedula_tax_year = $cedula_place_issued = $cedula_income_source = $cedula_tin = $cedula_birthplace = $cedula_height = $cedula_weight = $cedula_gross_income = null;
    $id_emergency_name = $id_emergency_relationship = $id_emergency_contact = $id_blood_type = $id_valid_until = null;
    $incident_date = $incident_time = $incident_location = $incident_persons = $incident_narrative = $incident_action = $incident_witness_name = $incident_witness_contact = $incident_witness_address = null;

    if ($document_type === 'Business Clearance') {
        $business_name = trim($_POST['business_name'] ?? '');
        $business_location = trim($_POST['business_location'] ?? '');
        $business_operator = trim($_POST['business_operator'] ?? '');
        $business_address = trim($_POST['business_address'] ?? '');
        $business_nature = trim($_POST['business_nature'] ?? '');
        $business_permit_for = trim($_POST['business_permit_for'] ?? '');

        if ($business_name === '' || $business_location === '' || $business_operator === '' || $business_address === '' || $business_nature === '') {
            $error_message = 'Please complete all required business clearance fields.';
        } else if ($purpose === '') {
            $purpose = $business_permit_for !== '' ? $business_permit_for : 'BUSINESS PERMIT APPLICATION';
        }
    }

    if ($document_type === 'Building/Construction Permit') {
        $construction_address = trim($_POST['construction_address'] ?? '');
        $construction_purpose = trim($_POST['construction_purpose'] ?? '');
        $construction_other_purpose = trim($_POST['construction_other_purpose'] ?? '');
        $construction_status = trim($_POST['construction_status'] ?? '');
        $construction_other_status = trim($_POST['construction_other_status'] ?? '');
        $construction_description = trim($_POST['construction_description'] ?? '');

        if ($construction_address === '' || $construction_purpose === '' || $construction_status === '') {
            $error_message = 'Please complete all required construction permit fields.';
        } else if ($purpose === '') {
            $purpose = $construction_purpose === 'Others' && $construction_other_purpose !== '' ? $construction_other_purpose : $construction_purpose;
        }
    }

    if ($document_type === 'Cedula') {
        $cedula_type = trim($_POST['cedula_type'] ?? '');
        $cedula_tax_year = trim($_POST['cedula_tax_year'] ?? '');
        $cedula_place_issued = trim($_POST['cedula_place_issued'] ?? '');
        $cedula_income_source = trim($_POST['cedula_income_source'] ?? '');
        $cedula_tin = trim($_POST['cedula_tin'] ?? '');
        $cedula_birthplace = trim($_POST['cedula_birthplace'] ?? '');
        $cedula_height = trim($_POST['cedula_height'] ?? '');
        $cedula_weight = trim($_POST['cedula_weight'] ?? '');
        $cedula_gross_income = trim($_POST['cedula_gross_income'] ?? '');

        if ($cedula_type === '' || $cedula_tax_year === '' || $cedula_place_issued === '' || $cedula_birthplace === '' || $cedula_height === '' || $cedula_weight === '' || $cedula_gross_income === '') {
            $error_message = 'Please complete all required cedula fields.';
        } else if ($purpose === '') {
            $purpose = 'COMMUNITY TAX CERTIFICATE';
        }
    }

    if ($document_type === 'Barangay ID') {
        $id_emergency_name = trim($_POST['id_emergency_name'] ?? '');
        $id_emergency_relationship = trim($_POST['id_emergency_relationship'] ?? '');
        $id_emergency_contact = trim($_POST['id_emergency_contact'] ?? '');
        $id_blood_type = trim($_POST['id_blood_type'] ?? '');
        $id_valid_until = $barangay_id_valid_until;

        if ($id_emergency_name === '' || $id_emergency_relationship === '' || $id_emergency_contact === '') {
            $error_message = 'Please complete all required Barangay ID fields.';
        } else if ($purpose === '') {
            $purpose = 'BARANGAY IDENTIFICATION';
        }
    }

    if ($document_type === 'Incident Report') {
        $incident_date = trim($_POST['incident_date'] ?? '');
        $incident_time = trim($_POST['incident_time'] ?? '');
        $incident_location = trim($_POST['incident_location'] ?? '');
        $incident_persons = trim($_POST['incident_persons'] ?? '');
        $incident_narrative = trim($_POST['incident_narrative'] ?? '');
        $incident_action = trim($_POST['incident_action'] ?? '');
        $incident_witness_name = trim($_POST['incident_witness_name'] ?? '');
        $incident_witness_contact = trim($_POST['incident_witness_contact'] ?? '');
        $incident_witness_address = trim($_POST['incident_witness_address'] ?? '');

        if ($incident_date === '' || $incident_time === '' || $incident_location === '' || $incident_narrative === '') {
            $error_message = 'Please complete all required incident report fields.';
        } else if ($purpose === '') {
            $purpose = 'INCIDENT DOCUMENTATION';
        }
    }

    if ($error_message === '' && !array_key_exists($document_type, $documentFees)) {
        $error_message = 'Please choose a valid document type.';
    } elseif ($error_message === '' && ($first_name === '' || $last_name === '' || $email === '' || $phone === '' || $birth_date === '' || $gender === '' || $civil_status === '' || $address === '' || $province === '' || $city === '' || $barangay === '' || $purpose === '')) {
        $error_message = 'Please complete all required request fields.';
    } elseif ($error_message === '' && (!inputIsName($first_name) || !inputIsName($middle_name, true) || !inputIsName($last_name) || !inputIsName($suffix, true))) {
        $error_message = 'Names may contain letters, spaces, hyphens, and periods only.';
    } elseif ($error_message === '' && (!filter_var($email, FILTER_VALIDATE_EMAIL) || !inputLength($email, 254))) {
        $error_message = 'Please enter a valid email address.';
    } elseif ($error_message === '' && !inputIsPhone($phone)) {
        $error_message = 'Phone number must use a valid Philippine mobile format (for example, 09123456789).';
    } elseif ($error_message === '' && (!inputIsDate($birth_date) || $birth_date > date('Y-m-d'))) {
        $error_message = 'Please enter a valid birth date that is not in the future.';
    } elseif ($error_message === '' && (!in_array($gender, ['Male', 'Female', 'Prefer not to say'], true) || !in_array($civil_status, ['Single', 'Married', 'Widowed', 'Separated'], true))) {
        $error_message = 'Please choose valid personal information options.';
    } elseif ($error_message === '' && !in_array($payment_method, ['online', 'cash'], true) && $document_fee > 0) {
        $error_message = 'Please choose a valid payment method.';
    } elseif ($error_message === '' && empty($_POST['legal_agreement_declaration'])) {
        $error_message = 'You must accept the Terms and Privacy Policy before submitting.';
    } elseif ($error_message === '' && $document_type === 'Business Clearance' && !inputIsName((string)$business_operator)) {
        $error_message = 'Business operator name may contain letters, spaces, hyphens, and periods only.';
    } elseif ($error_message === '' && $document_type === 'Cedula' && (!inputIsInteger((string)$cedula_tax_year, 1900, (int)date('Y') + 1) || !preg_match('/^\d+(?:[.\'\"]\d+)?$/', (string)$cedula_height) || !is_numeric($cedula_weight) || (float)$cedula_weight <= 0 || !preg_match('/^\d+(?:\.\d{1,2})?$/', (string)$cedula_gross_income) || (float)$cedula_gross_income < 0 || !inputIsNumericId((string)$cedula_tin, true, 12))) {
        $error_message = 'Enter valid numeric cedula measurements, tax year, income, and TIN.';
    } elseif ($error_message === '' && $document_type === 'Barangay ID' && (!inputIsName((string)$id_emergency_name) || !inputIsName((string)$id_emergency_relationship) || !inputIsPhone((string)$id_emergency_contact))) {
        $error_message = 'Enter valid emergency contact details.';
    } elseif ($error_message === '' && $document_type === 'Incident Report' && (!inputIsDate((string)$incident_date) || $incident_date > date('Y-m-d') || !inputIsTime((string)$incident_time))) {
        $error_message = 'Incident date and time must be valid, and the incident date cannot be in the future.';
    } elseif ($error_message === '' && $incident_witness_name !== null && !inputIsName((string)$incident_witness_name, true)) {
        $error_message = 'Witness name may contain letters, spaces, hyphens, and periods only.';
    } elseif ($error_message === '' && $incident_witness_contact !== null && !inputIsPhone((string)$incident_witness_contact, true)) {
        $error_message = 'Witness contact number must use a valid Philippine mobile format.';
    } elseif ($error_message === '' && empty($_FILES['valid_id']['name'])) {
        $error_message = 'Please upload a valid ID.';
    } elseif ($error_message === '' && $payment_method === 'online' && empty($_FILES['payment_receipt']['name'])) {
        $error_message = 'Please upload your payment receipt for online transactions.';
    } elseif ($error_message === '') {

        // VALID ID
        $upload_dir = __DIR__ . '/../assets/uploads/requirements/';
        if (!is_dir($upload_dir)) mkdir($upload_dir, 0755, true);

        $extension = strtolower(pathinfo($_FILES['valid_id']['name'], PATHINFO_EXTENSION));
        $file_error = inputUploadedFileError($_FILES['valid_id'], ['jpg', 'jpeg', 'png', 'pdf'], ['image/jpeg', 'image/png', 'application/pdf']);
        if ($file_error !== null) {
            $error_message = $file_error . ' Valid ID must be a JPG, PNG, or PDF file.';
        } else {
            $file_name = 'id_' . $resident_id . '_' . time() . '.' . $extension;
            if (move_uploaded_file($_FILES['valid_id']['tmp_name'], $upload_dir . $file_name)) {
                $id_path = 'assets/uploads/requirements/' . $file_name;
            } else {
                $error_message = 'Failed to upload valid ID.';
            }
        }

        // PAID RECEIPT
        if ($error_message === '' && $payment_method === 'online') {
            $receipt_dir = __DIR__ . '/../assets/uploads/receipts/';
            if (!is_dir($receipt_dir)) mkdir($receipt_dir, 0755, true);

            $receipt_ext = strtolower(pathinfo($_FILES['payment_receipt']['name'], PATHINFO_EXTENSION));
            $receipt_error = inputUploadedFileError($_FILES['payment_receipt'], ['jpg', 'jpeg', 'png', 'pdf'], ['image/jpeg', 'image/png', 'application/pdf']);
            if ($receipt_error !== null) {
                $error_message = $receipt_error . ' Receipt must be a JPG, PNG, or PDF file.';
            } else {
                $receipt_name = 'receipt_' . $resident_id . '_' . time() . '.' . $receipt_ext;
                if (move_uploaded_file($_FILES['payment_receipt']['tmp_name'], $receipt_dir . $receipt_name)) {
                    $payment_receipt_path = 'assets/uploads/receipts/' . $receipt_name;
                } else {
                    $error_message = 'Failed to upload payment receipt.';
                }
            }
        }
    }

    if ($error_message === '') {
        $reference_no = 'MK-' . strtoupper(substr(uniqid(), -6));

        mysqli_begin_transaction($conn);
        try {
            // 1. Get document_type_id
            $type_query = "SELECT document_type_id FROM document_types WHERE name = ? LIMIT 1";
            $stmt_type = mysqli_prepare($conn, $type_query);
            mysqli_stmt_bind_param($stmt_type, "s", $document_type);
            mysqli_stmt_execute($stmt_type);
            $type_result = mysqli_stmt_get_result($stmt_type);
            $type_row = mysqli_fetch_assoc($type_result);
            $document_type_id = $type_row ? $type_row['document_type_id'] : 0;
            $payment_status = 'Unpaid';

            if ((float)$document_fee <= 0) {
                $payment_status = 'No Fee';
            } elseif ($payment_method === 'online') {
                $payment_status = 'Receipt Submitted';
            } elseif ($payment_method === 'cash') {
                $payment_status = 'Unpaid';
            }

            $insert_base = "INSERT INTO service_requests (
                user_id, document_type_id, reference_no, purpose, document_fee, 
                payment_method, payment_receipt_path, payment_status, id_path, status
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'Pending')";

            $stmt_base = mysqli_prepare($conn, $insert_base);
            mysqli_stmt_bind_param($stmt_base, "iisssssss", $resident_id, $document_type_id, $reference_no, $purpose, $document_fee, $payment_method, $payment_receipt_path, $payment_status, $id_path);
            mysqli_stmt_execute($stmt_base);

            $new_request_id = mysqli_insert_id($conn);

            if ($document_type === 'Business Clearance') {
                $insert_biz = "INSERT INTO request_business_clearances (request_id, business_name, business_location, business_operator, business_address, business_nature) VALUES (?, ?, ?, ?, ?, ?)";
                $stmt_biz = mysqli_prepare($conn, $insert_biz);
                mysqli_stmt_bind_param($stmt_biz, "isssss", $new_request_id, $business_name, $business_location, $business_operator, $business_address, $business_nature);
                mysqli_stmt_execute($stmt_biz);
            } elseif ($document_type === 'Building/Construction Permit') {
                $insert_con = "INSERT INTO request_construction_permits (request_id, construction_address, construction_purpose, construction_status, construction_description) VALUES (?, ?, ?, ?, ?)";
                $stmt_con = mysqli_prepare($conn, $insert_con);
                mysqli_stmt_bind_param($stmt_con, "issss", $new_request_id, $construction_address, $construction_purpose, $construction_status, $construction_description);
                mysqli_stmt_execute($stmt_con);
            } elseif ($document_type === 'Cedula') {
                $insert_cedula = "INSERT INTO request_cedulas (request_id, cedula_type, tax_year, place_issued, income_source, height, weight, gross_income) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
                $stmt_ced = mysqli_prepare($conn, $insert_cedula);
                mysqli_stmt_bind_param($stmt_ced, "issssssd", $new_request_id, $cedula_type, $cedula_tax_year, $cedula_place_issued, $cedula_income_source, $cedula_height, $cedula_weight, $cedula_gross_income);
                mysqli_stmt_execute($stmt_ced);
            } elseif ($document_type === 'Barangay ID') {
                $insert_id = "INSERT INTO request_barangay_ids (request_id, blood_type, emergency_name, emergency_relationship, valid_until) VALUES (?, ?, ?, ?, ?)";
                $stmt_id = mysqli_prepare($conn, $insert_id);
                mysqli_stmt_bind_param($stmt_id, "issss", $new_request_id, $id_blood_type, $id_emergency_name, $id_emergency_relationship, $id_valid_until);
                mysqli_stmt_execute($stmt_id);
            } elseif ($document_type === 'Incident Report') {
                $insert_inc = "INSERT INTO request_incident_reports (request_id, incident_date, incident_time, incident_location, incident_persons, incident_narrative, incident_action, witness_name, witness_contact, witness_address) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
                $stmt_inc = mysqli_prepare($conn, $insert_inc);
                mysqli_stmt_bind_param($stmt_inc, "isssssssss", $new_request_id, $incident_date, $incident_time, $incident_location, $incident_persons, $incident_narrative, $incident_action, $incident_witness_name, $incident_witness_contact, $incident_witness_address);
                mysqli_stmt_execute($stmt_inc);
            }

            $resident_name = trim($first_name . ' ' . $last_name);
            createAdminNotification(
                $conn,
                'New Service Request',
                $resident_name . ' submitted a ' . $document_type . ' request (' . $reference_no . ').',
                'Service Request',
                'bi-file-earmark-text',
                'manage_requests.php'
            );

            mysqli_commit($conn);
            $_SESSION['claim_stub_names'][$reference_no] = trim(implode(' ', array_filter([
                $first_name,
                $middle_name,
                $last_name,
                $suffix,
            ])));
            header('Location: print_stub.php?ref=' . rawurlencode($reference_no) . '&download=1');
            exit();
        } catch (Exception $e) {
            mysqli_rollback($conn);
            $error_message = 'Database error. Failed to submit request.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle; ?> | MakiKonek</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/header.css?v=20260613e">
    <link rel="icon" href="../assets/img/Barangay_Makiling_Seal.png" type="image/png">
    <link rel="stylesheet" href="../assets/css/footer.css?v=20260613b">
    <link rel="stylesheet" href="../assets/css/resident.css?v=20260613a">
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
            <header class="page-heading">
                <h1>My Requests</h1>
                <p>Submit a new document request</p>
            </header>

            <?php if ($success_message): ?>
                <div class="alert alert-success"><?php echo htmlspecialchars($success_message); ?></div>
            <?php endif; ?>

            <?php if ($error_message): ?>
                <div class="alert alert-danger"><?php echo htmlspecialchars($error_message); ?></div>
            <?php endif; ?>

            <section class="request-layout">
                <aside class="request-card">
                    <h2>Document Type</h2>
                    <p>Select the type of document you need</p>

                    <?php foreach ($documentGroups as $groupName => $documents): ?>
                        <div class="document-group">
                            <strong><?php echo htmlspecialchars($groupName); ?></strong>
                            <?php foreach ($documents as $document): ?>
                                <button
                                    class="document-option"
                                    type="button"
                                    data-document-name="<?php echo htmlspecialchars($document['name']); ?>"
                                    data-document-fee="<?php echo htmlspecialchars($document['fee']); ?>"
                                    data-document-time="<?php echo htmlspecialchars($document['time']); ?>">
                                    <i class="fa-regular fa-file-lines"></i>
                                    <span class="document-copy">
                                        <strong><?php echo htmlspecialchars($document['name']); ?></strong>
                                        <small>Fee: <?php echo htmlspecialchars($document['fee']); ?></small>
                                    </span>
                                </button>
                            <?php endforeach; ?>
                        </div>
                    <?php endforeach; ?>
                </aside>

                <section class="request-card">
                    <div class="empty-request" data-empty-state>
                        <div>
                            <i class="fa-regular fa-file-lines"></i>
                            <h2>Select a document type</h2>
                            <p>Choose a document type from the list on the left to continue</p>
                        </div>
                    </div>

                    <form class="request-form" action="requests.php" method="POST" enctype="multipart/form-data" data-request-form>
                        <input type="hidden" name="document_type" data-document-type-input>
                        <input type="hidden" name="document_fee" data-document-fee-input>

                        <h2>Request Details</h2>
                        <p>Complete the form for <span data-selected-document>your selected document</span></p>

                        <div class="form-grid">
                            <div class="field">
                                <label for="first_name">First Name *</label>
                                <input id="first_name" name="first_name" type="text" maxlength="60" pattern="[A-Za-zÀ-ÖØ-öø-ÿÑñ .-]+" data-input="name" value="<?php echo $pref_fname; ?>" required>
                            </div>
                            <div class="field">
                                <label for="middle_name">Middle Name</label>
                                <input id="middle_name" name="middle_name" type="text" maxlength="60" pattern="[A-Za-zÀ-ÖØ-öø-ÿÑñ .-]*" data-input="name" value="<?php echo $pref_mname; ?>">
                            </div>
                            <div class="field">
                                <label for="last_name">Last Name *</label>
                                <input id="last_name" name="last_name" type="text" maxlength="60" pattern="[A-Za-zÀ-ÖØ-öø-ÿÑñ .-]+" data-input="name" value="<?php echo $pref_lname; ?>" required>
                            </div>
                            <div class="field">
                                <label for="suffix">Suffix</label>
                                <input id="suffix" name="suffix" type="text" maxlength="10" pattern="[A-Za-zÀ-ÖØ-öø-ÿÑñ .-]*" data-input="name" value="<?php echo $pref_suffix; ?>">
                            </div>
                            <div class="field">
                                <label for="email">Email Address *</label>
                                <input id="email" name="email" type="email" maxlength="254" value="<?php echo $pref_email; ?>" placeholder="example@email.com" required>
                            </div>
                            <div class="field">
                                <label for="phone">Phone Number *</label>
                                <input id="phone" name="phone" type="tel" inputmode="numeric" maxlength="11" pattern="09[0-9]{9}" data-input="phone" value="<?php echo $pref_phone; ?>" placeholder="09171234567" required>
                            </div>
                            <div class="field">
                                <label for="birth_date">Birth Date *</label>
                                <input id="birth_date" name="birth_date" type="date" max="<?php echo date('Y-m-d'); ?>" value="<?php echo $pref_bdate; ?>" required>
                            </div>
                            <div class="field">
                                <label for="gender">Gender *</label>
                                <select id="gender" name="gender" required>
                                    <option value="">Select gender</option>
                                    <option value="Female" <?php echo ($pref_gender == 'Female') ? 'selected' : ''; ?>>Female</option>
                                    <option value="Male" <?php echo ($pref_gender == 'Male') ? 'selected' : ''; ?>>Male</option>
                                    <option value="Prefer not to say" <?php echo ($pref_gender == 'Prefer not to say') ? 'selected' : ''; ?>>Prefer not to say</option>
                                </select>
                            </div>
                            <div class="field">
                                <label for="civil_status">Civil Status *</label>
                                <select id="civil_status" name="civil_status" required>
                                    <option value="">Select status</option>
                                    <option <?php echo ($pref_civil == 'Single') ? 'selected' : ''; ?>>Single</option>
                                    <option <?php echo ($pref_civil == 'Married') ? 'selected' : ''; ?>>Married</option>
                                    <option <?php echo ($pref_civil == 'Widowed') ? 'selected' : ''; ?>>Widowed</option>
                                    <option <?php echo ($pref_civil == 'Separated') ? 'selected' : ''; ?>>Separated</option>
                                </select>
                            </div>
                            <div class="field full">
                                <label for="address">Full Address *</label>
                                <input id="address" name="address" type="text" value="<?php echo $pref_address; ?>" placeholder="Enter full address" required>
                            </div>
                            <div class="field">
                                <label for="province">Province *</label>
                                <input id="province" name="province" type="text" value="Laguna" required>
                            </div>
                            <div class="field">
                                <label for="city">City/Municipality *</label>
                                <input id="city" name="city" type="text" value="Calamba" required>
                            </div>
                            <div class="field">
                                <label for="barangay">Barangay *</label>
                                <input id="barangay" name="barangay" type="text" value="Makiling" required>
                            </div>

                            <div class="field full service-extra business-extra" data-service-extra="Business Clearance" style="display:none;">
                                <h3>Business Clearance Details</h3>
                                <p>Provide the business information that will appear on the clearance.</p>
                            </div>

                            <div class="field service-extra business-extra" data-service-extra="Business Clearance" style="display:none;">
                                <label for="business_name">Business Name / Trade Activity *</label>
                                <input id="business_name" name="business_name" type="text" placeholder="Enter business or trade name">
                            </div>
                            <div class="field service-extra business-extra" data-service-extra="Business Clearance" style="display:none;">
                                <label for="business_location">Business Location *</label>
                                <input id="business_location" name="business_location" type="text" placeholder="Enter location">
                            </div>
                            <div class="field service-extra business-extra" data-service-extra="Business Clearance" style="display:none;">
                                <label for="business_operator">Operator / Manager *</label>
                                <input id="business_operator" name="business_operator" type="text" maxlength="120" pattern="[A-Za-zÀ-ÖØ-öø-ÿÑñ .-]+" data-input="name" placeholder="Enter operator or manager">
                            </div>
                            <div class="field service-extra business-extra" data-service-extra="Business Clearance" style="display:none;">
                                <label for="business_nature">Nature / Type of Business *</label>
                                <input id="business_nature" name="business_nature" type="text" placeholder="e.g. Sari-sari store, online shop">
                            </div>
                            <div class="field full service-extra business-extra" data-service-extra="Business Clearance" style="display:none;">
                                <label for="business_address">Business Address *</label>
                                <input id="business_address" name="business_address" type="text" placeholder="Enter complete business address">
                            </div>
                            <div class="field full service-extra business-extra" data-service-extra="Business Clearance" style="display:none;">
                                <label for="business_permit_for">Permit / Application Purpose</label>
                                <input id="business_permit_for" name="business_permit_for" type="text" placeholder="e.g. Mayor's Permit application" data-optional="true">
                            </div>

                            <div class="field full service-extra construction-extra" data-service-extra="Building/Construction Permit" style="display:none;">
                                <h3>Construction Permit Details</h3>
                                <p>Provide the project information that will appear on the permit.</p>
                            </div>

                            <div class="field full service-extra construction-extra" data-service-extra="Building/Construction Permit" style="display:none;">
                                <label for="construction_address">Project / Construction Address *</label>
                                <input id="construction_address" name="construction_address" type="text" placeholder="Enter project location">
                            </div>
                            <div class="field service-extra construction-extra" data-service-extra="Building/Construction Permit" style="display:none;">
                                <label for="construction_purpose">Specific Purpose *</label>
                                <select id="construction_purpose" name="construction_purpose">
                                    <option value="">Select purpose</option>
                                    <option value="Electrical Works">Electrical Works</option>
                                    <option value="Pipe Lying">Pipe Lying</option>
                                    <option value="Excavation">Excavation</option>
                                    <option value="Drainage / Sewerage">Drainage / Sewerage</option>
                                    <option value="Others">Others</option>
                                </select>
                            </div>
                            <div class="field service-extra construction-extra" data-service-extra="Building/Construction Permit" style="display:none;">
                                <label for="construction_other_purpose">Other Purpose</label>
                                <input id="construction_other_purpose" name="construction_other_purpose" type="text" placeholder="Specify other purpose" data-optional="true">
                            </div>
                            <div class="field service-extra construction-extra" data-service-extra="Building/Construction Permit" style="display:none;">
                                <label for="construction_status">Construction Status *</label>
                                <select id="construction_status" name="construction_status">
                                    <option value="">Select status</option>
                                    <option value="Not yet started">Not yet started</option>
                                    <option value="Already Started">Already Started</option>
                                    <option value="Already Constructed">Already Constructed</option>
                                    <option value="Others">Others</option>
                                </select>
                            </div>
                            <div class="field service-extra construction-extra" data-service-extra="Building/Construction Permit" style="display:none;">
                                <label for="construction_other_status">Other Status</label>
                                <input id="construction_other_status" name="construction_other_status" type="text" placeholder="Specify other status" data-optional="true">
                            </div>
                            <div class="field full service-extra construction-extra" data-service-extra="Building/Construction Permit" style="display:none;">
                                <label for="construction_description">Project Description</label>
                                <textarea id="construction_description" name="construction_description" rows="3" placeholder="Briefly describe the proposed work" data-optional="true"></textarea>
                            </div>

                            <div class="field full service-extra cedula-extra" data-service-extra="Cedula" style="display:none;">
                                <h3>Cedula Details</h3>
                                <p>Provide information for the community tax certificate request.</p>
                            </div>
                            <div class="field service-extra cedula-extra" data-service-extra="Cedula" style="display:none;">
                                <label for="cedula_type">Cedula Type *</label>
                                <select id="cedula_type" name="cedula_type">
                                    <option value="">Select type</option>
                                    <option value="Individual">Individual</option>
                                    <option value="Business">Business</option>
                                </select>
                            </div>
                            <div class="field service-extra cedula-extra" data-service-extra="Cedula" style="display:none;">
                                <label for="cedula_tax_year">Tax Year *</label>
                                <input id="cedula_tax_year" name="cedula_tax_year" type="number" min="1900" max="<?php echo (int)date('Y') + 1; ?>" step="1" data-input="digits" data-max-digits="4" placeholder="2026">
                            </div>
                            <div class="field service-extra cedula-extra" data-service-extra="Cedula" style="display:none;">
                                <label for="cedula_place_issued">Place Issued *</label>
                                <input id="cedula_place_issued" name="cedula_place_issued" type="text" placeholder="e.g. Barangay Makiling">
                            </div>
                            <div class="field service-extra cedula-extra" data-service-extra="Cedula" style="display:none;">
                                <label for="cedula_birthplace">Birthplace *</label>
                                <input id="cedula_birthplace" name="cedula_birthplace" type="text" placeholder="City/Municipality">
                            </div>
                            <div class="field service-extra cedula-extra" data-service-extra="Cedula" style="display:none;">
                                <label for="cedula_height">Height *</label>
                                <input id="cedula_height" name="cedula_height" type="text" inputmode="decimal" maxlength="8" pattern="[0-9]+(?:[.&quot;'][0-9]+)?" placeholder="e.g. 5'2&quot;">
                            </div>
                            <div class="field service-extra cedula-extra" data-service-extra="Cedula" style="display:none;">
                                <label for="cedula_weight">Weight *</label>
                                <input id="cedula_weight" name="cedula_weight" type="number" min="1" max="500" step="0.1" placeholder="e.g. 50">
                            </div>
                            <div class="field service-extra cedula-extra" data-service-extra="Cedula" style="display:none;">
                                <label for="cedula_gross_income">Gross Annual Income *</label>
                                <input id="cedula_gross_income" name="cedula_gross_income" type="number" min="0" step="0.01" placeholder="Previous year's income">
                            </div>
                            <div class="field service-extra cedula-extra" data-service-extra="Cedula" style="display:none;">
                                <label for="cedula_income_source">Income / Business Source</label>
                                <input id="cedula_income_source" name="cedula_income_source" type="text" placeholder="e.g. Employment, business" data-optional="true">
                            </div>
                            <div class="field service-extra cedula-extra" data-service-extra="Cedula" style="display:none;">
                                <label for="cedula_tin">TIN No.</label>
                                <input id="cedula_tin" name="cedula_tin" type="text" inputmode="numeric" maxlength="15" pattern="[0-9]{0,12}" data-input="numeric-id" data-max-digits="12" placeholder="456-789-123-000" data-optional="true">
                            </div>

                            <div class="field full service-extra barangay-id-extra" data-service-extra="Barangay ID" style="display:none;">
                                <h3>Barangay ID Details</h3>
                                <p>Provide emergency contact details for the Barangay ID request.</p>
                            </div>
                            <div class="field service-extra barangay-id-extra" data-service-extra="Barangay ID" style="display:none;">
                                <label for="id_emergency_name">Emergency Contact Name *</label>
                                <input id="id_emergency_name" name="id_emergency_name" type="text" maxlength="120" pattern="[A-Za-zÀ-ÖØ-öø-ÿÑñ .-]+" data-input="name" placeholder="Enter full name">
                            </div>
                            <div class="field service-extra barangay-id-extra" data-service-extra="Barangay ID" style="display:none;">
                                <label for="id_emergency_relationship">Relationship *</label>
                                <input id="id_emergency_relationship" name="id_emergency_relationship" type="text" maxlength="60" pattern="[A-Za-zÀ-ÖØ-öø-ÿÑñ .-]+" data-input="name" placeholder="e.g. Parent, sibling">
                            </div>
                            <div class="field service-extra barangay-id-extra" data-service-extra="Barangay ID" style="display:none;">
                                <label for="id_emergency_contact">Emergency Contact Number *</label>
                                <input id="id_emergency_contact" name="id_emergency_contact" type="tel" inputmode="numeric" maxlength="11" pattern="09[0-9]{9}" data-input="phone" placeholder="09171234567">
                            </div>
                            <div class="field service-extra barangay-id-extra" data-service-extra="Barangay ID" style="display:none;">
                                <label for="id_blood_type">Blood Type</label>
                                <input id="id_blood_type" name="id_blood_type" type="text" placeholder="Optional" data-optional="true">
                            </div>
                            <div class="field service-extra barangay-id-extra" data-service-extra="Barangay ID" style="display:none;">
                                <label for="id_valid_until">Valid Until *</label>
                                <input id="id_valid_until" name="id_valid_until" type="date" value="<?php echo htmlspecialchars($barangay_id_valid_until); ?>" readonly aria-readonly="true">
                            </div>

                            <div class="field full service-extra incident-extra" data-service-extra="Incident Report" style="display:none;">
                                <h3>Incident Report Details</h3>
                                <p>Provide the incident details for the official report.</p>
                            </div>
                            <div class="field service-extra incident-extra" data-service-extra="Incident Report" style="display:none;">
                                <label for="incident_date">Incident Date *</label>
                                <input id="incident_date" name="incident_date" type="date" max="<?php echo date('Y-m-d'); ?>">
                            </div>
                            <div class="field service-extra incident-extra" data-service-extra="Incident Report" style="display:none;">
                                <label for="incident_time">Incident Time *</label>
                                <input id="incident_time" name="incident_time" type="time">
                            </div>
                            <div class="field full service-extra incident-extra" data-service-extra="Incident Report" style="display:none;">
                                <label for="incident_location">Incident Location *</label>
                                <input id="incident_location" name="incident_location" type="text" placeholder="Where did the incident happen?">
                            </div>
                            <div class="field full service-extra incident-extra" data-service-extra="Incident Report" style="display:none;">
                                <label for="incident_persons">Persons Involved / Witnesses</label>
                                <input id="incident_persons" name="incident_persons" type="text" placeholder="Optional" data-optional="true">
                            </div>
                            <div class="field full service-extra incident-extra" data-service-extra="Incident Report" style="display:none;">
                                <label for="incident_narrative">Incident Narrative *</label>
                                <textarea id="incident_narrative" name="incident_narrative" rows="4" placeholder="Briefly describe what happened"></textarea>
                            </div>
                            <div class="field full service-extra incident-extra" data-service-extra="Incident Report" style="display:none;">
                                <label for="incident_action">Requested Action</label>
                                <input id="incident_action" name="incident_action" type="text" placeholder="Optional" data-optional="true">
                            </div>
                            <div class="field full service-extra incident-extra" data-service-extra="Incident Report" style="display:none;">
                                <h3>Witness Information</h3>
                                <p>Optional, if a witness is available.</p>
                            </div>
                            <div class="field service-extra incident-extra" data-service-extra="Incident Report" style="display:none;">
                                <label for="incident_witness_name">Witness Full Name</label>
                                <input id="incident_witness_name" name="incident_witness_name" type="text" maxlength="120" pattern="[A-Za-zÀ-ÖØ-öø-ÿÑñ .-]*" data-input="name" placeholder="Optional" data-optional="true">
                            </div>
                            <div class="field service-extra incident-extra" data-service-extra="Incident Report" style="display:none;">
                                <label for="incident_witness_contact">Witness Contact Number</label>
                                <input id="incident_witness_contact" name="incident_witness_contact" type="tel" inputmode="numeric" maxlength="11" pattern="09[0-9]{9}" data-input="phone" placeholder="09171234567" data-optional="true">
                            </div>
                            <div class="field full service-extra incident-extra" data-service-extra="Incident Report" style="display:none;">
                                <label for="incident_witness_address">Witness Address</label>
                                <input id="incident_witness_address" name="incident_witness_address" type="text" placeholder="Optional" data-optional="true">
                            </div>

                            <div class="field-group-5050">

                                <div class="field valid-id-field">
                                    <label for="valid_id">Upload Valid ID *</label>
                                    <div class="upload-box">
                                        <i class="fas fa-cloud-upload-alt"></i>
                                        <p>Click to upload or drag and drop</p>
                                        <p class="upload-sub">SVG, PNG, JPG or PDF (max. 5 MB uploaded)</p>
                                        <input type="file" id="valid_id" name="valid_id" accept="image/jpeg,image/png,application/pdf" required style="opacity: 0; position: absolute; z-index: -1; width: 1px; height: 1px;">
                                        <label for="valid_id" class="choose-file-btn">Choose File</label>
                                    </div>
                                </div>

                                <div class="right-fields-stack">
                                    <div class="field">
                                        <label for="purpose">Purpose *</label>
                                        <input type="text" id="purpose" name="purpose" placeholder="Enter purpose" required>
                                    </div>
                                    <div class="field">
                                        <label for="occupation">Occupation</label>
                                        <input type="text" id="occupation" name="occupation" placeholder="Enter occupation">
                                    </div>
                                </div>

                            </div>
                        </div>

                        <div class="field full remarks-field-block">
                            <label for="request_remarks">Note / Remarks</label>
                            <textarea id="request_remarks" name="request_remarks" rows="3" placeholder="Any additional note or special instructions for your request..." data-optional="true"></textarea>
                        </div>

                        <div class="payment-main-wrapper-5050">

                            <div class="payment-left-column">

                                <div class="field payment-method-sub-block">
                                    <label class="section-subtitle">Payment Method *</label>
                                    <div class="radio-group-payment">
                                        <label class="radio-label">
                                            <input type="radio" name="payment_method" value="online" checked>
                                            Pay Online / Pay Now
                                        </label>
                                        <label class="radio-label">
                                            <input type="radio" name="payment_method" value="cash">
                                            Pay Cash upon Pickup
                                        </label>
                                    </div>
                                </div>

                                <div class="field receipt-upload-sub-block">
                                    <label for="payment_receipt">Upload Payment Receipt *</label>
                                    <div class="upload-box compact-upload">
                                        <i class="fas fa-cloud-upload-alt"></i>
                                        <p>Click to upload or drag and drop</p>
                                        <p class="upload-sub">SVG, PNG, JPG or PDF (max. 5 MB uploaded)</p>
                                        <input type="file" id="payment_receipt" name="payment_receipt" accept="image/jpeg,image/png,application/pdf" style="opacity: 0; position: absolute; z-index: -1; width: 1px; height: 1px;">
                                        <label for="payment_receipt" class="choose-file-btn">Choose File</label>
                                    </div>

                                    <a href="#" class="sample-link">
                                        <i class="fas fa-eye"></i> View Sample Receipt
                                    </a>
                                </div>
                            </div>

                            <div class="payment-right-column">
                                <div class="field qr-code-sub-block">
                                    <label>Scan QR Code to Pay</label>
                                    <div class="qr-image-wrapper">
                                        <img src="../assets/img/qr_code.jpg" alt="Payment QR Code" class="qr-img">
                                    </div>
                                </div>
                            </div>

                        </div>

                        <div class="fee-box">
                            <span>
                                Document Fee:
                                <small>Processing time: <span data-processing-time>1-2 working days</span></small>
                            </span>
                            <strong data-selected-fee>P50.00</strong>
                        </div>

                        <div class="legal-agreement-wrapper">
                            <label class="legal-checkbox-container">
                                <input type="checkbox" id="legal_agreement_declaration" name="legal_agreement_declaration" required>
                                <span class="custom-ui-checkbox"></span>
                                <span class="legal-text-content">
                                    <span class="required-asterisk">*</span> I've read and accept the
                                    <a href="javascript:void(0)" id="openTermsLink" class="legal-hyperlink">Terms & Conditions</a>
                                    and
                                    <a href="javascript:void(0)" id="openPrivacyLink" class="legal-hyperlink">Privacy Policy</a>.
                                </span>
                            </label>
                        </div>

                        <div class="form-actions">
                            <button class="cancel-btn" type="button" data-clear-request>Cancel</button>
                            <button class="submit-btn" type="submit">Submit Request</button>
                        </div>
                    </form>
                </section>
            </section>
        </main>
    </div>

    <div id="receiptModal" class="receipt-modal-overlay">
        <div class="receipt-modal-content">
            <button type="button" class="modal-back-btn">
                <i class="fas fa-arrow-left"></i> Back to Form
            </button>
            <div class="modal-image-wrapper">
                <img src="../assets/img/sample_receipt.png" alt="Sample GCash Receipt">
            </div>
        </div>
    </div>
    <div id="toast-notification-container" class="toast-container">
        <?php if (!empty($success_message)): ?>
            <div class="toast-notification success">
                <i class="fas fa-check-circle"></i> <?php echo htmlspecialchars($success_message); ?>
            </div>
        <?php endif; ?>

        <?php if (!empty($error_message)): ?>
            <div class="toast-notification error">
                <i class="fas fa-exclamation-circle"></i> <?php echo htmlspecialchars($error_message); ?>
            </div>
        <?php endif; ?>
    </div>

    <div id="termsModal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 9999; justify-content: center; align-items: center; padding: 20px;">
        <div style="background: white; width: min(100%, 700px); height: 85vh; border-radius: var(--radius); padding: 24px; display: flex; flex-direction: column; box-shadow: var(--shadow);">
            
            <h2 style="margin-top: 0; color: var(--green-dark); border-bottom: 2px solid #eee; padding-bottom: 10px; font-size: 20px; font-weight: 600; margin-bottom: 15px;">Terms & Conditions</h2>
            
            <iframe src="../public/terms_conditions.php" style="width: 100%; flex: 1; border: none; margin-bottom: 15px; border-radius: 4px;"></iframe>
            
            <div style="text-align: center; border-top: 2px solid #eee; padding-top: 15px;">
                <button id="closeTermsModalBtn" type="button" style="background-color: var(--green-dark); color: white; border: none; padding: 12px 32px; font-size: 14px; font-weight: 600; border-radius: var(--radius); cursor: pointer;">Go Back</button>
            </div>
        </div>
    </div>

    <div id="privacyModal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 9999; justify-content: center; align-items: center; padding: 20px;">
        <div style="background: white; width: min(100%, 700px); height: 85vh; border-radius: var(--radius); padding: 24px; display: flex; flex-direction: column; box-shadow: var(--shadow);">
            
            <h2 style="margin-top: 0; color: var(--green-dark); border-bottom: 2px solid #eee; padding-bottom: 10px; font-size: 20px; font-weight: 600; margin-bottom: 15px;">Privacy Policy</h2>
            
            <iframe src="../public/privacy_policy.php" style="width: 100%; flex: 1; border: none; margin-bottom: 15px; border-radius: 4px;"></iframe>
            
            <div style="text-align: center; border-top: 2px solid #eee; padding-top: 15px;">
                <button id="closePrivacyModalBtn" type="button" style="background-color: var(--green-dark); color: white; border: none; padding: 12px 32px; font-size: 14px; font-weight: 600; border-radius: var(--radius); cursor: pointer;">Go Back</button>
            </div>
        </div>
    </div>
    <?php
    $footerBase = '../public/';
    $footerAssetBase = '../assets';
    include __DIR__ . '/../includes/footer.php';
    ?>
    <script src="../assets/js/input-validation.js?v=20260620a"></script>
    <script src="../assets/js/resident.js?v=20260530a"></script> 
</body>

</html>
