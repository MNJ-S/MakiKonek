<?php

session_start();

if (!isset($_SESSION['resident_id'])) {
    header("Location: ../login_reg.php");
    exit();
}

require_once __DIR__ . '/../includes/db_connect.php';

$pageTitle = 'My Requests';
$activePage = 'requests';
$success_message = '';
$error_message = '';

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

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $resident_id = (int) $_SESSION['resident_id'];
    $document_type = trim($_POST['document_type'] ?? '');
    $document_fee = trim($_POST['document_fee'] ?? '');
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
    $request_details = null;
    $id_path = '';

    if ($document_type === 'Business Clearance') {
        $business_name = trim($_POST['business_name'] ?? '');
        $business_location = trim($_POST['business_location'] ?? '');
        $business_operator = trim($_POST['business_operator'] ?? '');
        $business_address = trim($_POST['business_address'] ?? '');
        $business_nature = trim($_POST['business_nature'] ?? '');
        $business_permit_for = trim($_POST['business_permit_for'] ?? '');

        if ($business_name === '' || $business_location === '' || $business_operator === '' || $business_address === '' || $business_nature === '') {
            $error_message = 'Please complete all required business clearance fields.';
        } else {
            $request_details = json_encode([
                'business_name' => $business_name,
                'business_location' => $business_location,
                'business_operator' => $business_operator,
                'business_address' => $business_address,
                'business_nature' => $business_nature,
                'business_permit_for' => $business_permit_for,
            ]);

            if ($purpose === '') {
                $purpose = $business_permit_for !== '' ? $business_permit_for : 'BUSINESS PERMIT APPLICATION';
            }
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
        } else {
            $request_details = json_encode([
                'construction_address' => $construction_address,
                'construction_purpose' => $construction_purpose,
                'construction_other_purpose' => $construction_other_purpose,
                'construction_status' => $construction_status,
                'construction_other_status' => $construction_other_status,
                'construction_description' => $construction_description,
            ]);

            if ($purpose === '') {
                $purpose = $construction_purpose === 'Others' && $construction_other_purpose !== ''
                    ? $construction_other_purpose
                    : $construction_purpose;
            }
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
        } else {
            $request_details = json_encode([
                'cedula_type' => $cedula_type,
                'cedula_tax_year' => $cedula_tax_year,
                'cedula_place_issued' => $cedula_place_issued,
                'cedula_income_source' => $cedula_income_source,
                'cedula_tin' => $cedula_tin,
                'cedula_birthplace' => $cedula_birthplace,
                'cedula_height' => $cedula_height,
                'cedula_weight' => $cedula_weight,
                'cedula_gross_income' => $cedula_gross_income,
            ]);

            if ($purpose === '') {
                $purpose = 'COMMUNITY TAX CERTIFICATE';
            }
        }
    }

    if ($document_type === 'Barangay ID') {
        $id_emergency_name = trim($_POST['id_emergency_name'] ?? '');
        $id_emergency_relationship = trim($_POST['id_emergency_relationship'] ?? '');
        $id_emergency_contact = trim($_POST['id_emergency_contact'] ?? '');
        $id_blood_type = trim($_POST['id_blood_type'] ?? '');
        $id_valid_until = trim($_POST['id_valid_until'] ?? '');

        if ($id_emergency_name === '' || $id_emergency_relationship === '' || $id_emergency_contact === '' || $id_valid_until === '') {
            $error_message = 'Please complete all required Barangay ID fields.';
        } else {
            $request_details = json_encode([
                'id_emergency_name' => $id_emergency_name,
                'id_emergency_relationship' => $id_emergency_relationship,
                'id_emergency_contact' => $id_emergency_contact,
                'id_blood_type' => $id_blood_type,
                'id_valid_until' => $id_valid_until,
            ]);

            if ($purpose === '') {
                $purpose = 'BARANGAY IDENTIFICATION';
            }
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
        } else {
            $request_details = json_encode([
                'incident_date' => $incident_date,
                'incident_time' => $incident_time,
                'incident_location' => $incident_location,
                'incident_persons' => $incident_persons,
                'incident_narrative' => $incident_narrative,
                'incident_action' => $incident_action,
                'incident_witness_name' => $incident_witness_name,
                'incident_witness_contact' => $incident_witness_contact,
                'incident_witness_address' => $incident_witness_address,
            ]);

            if ($purpose === '') {
                $purpose = 'INCIDENT DOCUMENTATION';
            }
        }
    }

    if ($error_message === '' && ($document_type === '' || $first_name === '' || $last_name === '' || $email === '' || $phone === '' || $birth_date === '' || $gender === '' || $civil_status === '' || $address === '' || $province === '' || $city === '' || $barangay === '' || $purpose === '')) {
        $error_message = 'Please complete all required request fields.';
    } elseif ($error_message === '' && empty($_FILES['valid_id']['name'])) {
        $error_message = 'Please upload a valid ID.';
    } elseif ($error_message === '') {
        $upload_dir = __DIR__ . '/../assets/uploads/requirements/';
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0755, true);
        }

        $extension = strtolower(pathinfo($_FILES['valid_id']['name'], PATHINFO_EXTENSION));
        $allowed_extensions = ['jpg', 'jpeg', 'png', 'pdf'];

        if (!in_array($extension, $allowed_extensions, true)) {
            $error_message = 'Valid ID must be a JPG, PNG, or PDF file.';
        } else {
            $file_name = 'id_' . $resident_id . '_' . time() . '.' . $extension;
            $target_file = $upload_dir . $file_name;

            if (move_uploaded_file($_FILES['valid_id']['tmp_name'], $target_file)) {
                $id_path = 'assets/uploads/requirements/' . $file_name;
            } else {
                $error_message = 'Failed to upload valid ID.';
            }
        }
    }

    if ($error_message === '') {
        $reference_no = 'MK-' . strtoupper(substr(uniqid(), -6));
        $insert = "INSERT INTO service_requests (
            user_id, reference_no, document_type, first_name, middle_name, last_name, suffix,
            email, phone, birth_date, gender, civil_status, address, province, city, barangay,
            purpose, occupation, document_fee, id_path, request_details
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

        $stmt = mysqli_prepare($conn, $insert);
        mysqli_stmt_bind_param(
            $stmt,
            "issssssssssssssssssss",
            $resident_id,
            $reference_no,
            $document_type,
            $first_name,
            $middle_name,
            $last_name,
            $suffix,
            $email,
            $phone,
            $birth_date,
            $gender,
            $civil_status,
            $address,
            $province,
            $city,
            $barangay,
            $purpose,
            $occupation,
            $document_fee,
            $id_path,
            $request_details
        );

        if (mysqli_stmt_execute($stmt)) {
            $success_message = 'Your request has been submitted under Reference Number: ' . $reference_no;
        } else {
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
                                <input id="first_name" name="first_name" type="text" value="Juan" required>
                            </div>
                            <div class="field">
                                <label for="middle_name">Middle Name</label>
                                <input id="middle_name" name="middle_name" type="text" value="Santos">
                            </div>
                            <div class="field">
                                <label for="last_name">Last Name *</label>
                                <input id="last_name" name="last_name" type="text" value="Dela Cruz" required>
                            </div>
                            <div class="field">
                                <label for="suffix">Suffix</label>
                                <input id="suffix" name="suffix" type="text">
                            </div>
                            <div class="field">
                                <label for="email">Email Address *</label>
                                <input id="email" name="email" type="email" placeholder="example@email.com" required>
                            </div>
                            <div class="field">
                                <label for="phone">Phone Number *</label>
                                <input id="phone" name="phone" type="tel" placeholder="+63 912 345 6789" required>
                            </div>
                            <div class="field">
                                <label for="birth_date">Birth Date *</label>
                                <input id="birth_date" name="birth_date" type="date" required>
                            </div>
                            <div class="field">
                                <label for="gender">Gender *</label>
                                <select id="gender" name="gender" required>
                                    <option value="">Select gender</option>
                                    <option>Female</option>
                                    <option>Male</option>
                                    <option>Prefer not to say</option>
                                </select>
                            </div>
                            <div class="field">
                                <label for="civil_status">Civil Status *</label>
                                <select id="civil_status" name="civil_status" required>
                                    <option value="">Select status</option>
                                    <option>Single</option>
                                    <option>Married</option>
                                    <option>Widowed</option>
                                    <option>Separated</option>
                                </select>
                            </div>
                            <div class="field full">
                                <label for="address">Full Address *</label>
                                <input id="address" name="address" type="text" placeholder="Enter full address" required>
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
                                <input id="business_operator" name="business_operator" type="text" placeholder="Enter operator or manager">
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
                                <input id="cedula_tax_year" name="cedula_tax_year" type="text" placeholder="e.g. 2026">
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
                                <input id="cedula_height" name="cedula_height" type="text" placeholder="e.g. 5'2&quot;">
                            </div>
                            <div class="field service-extra cedula-extra" data-service-extra="Cedula" style="display:none;">
                                <label for="cedula_weight">Weight *</label>
                                <input id="cedula_weight" name="cedula_weight" type="text" placeholder="e.g. 50 kg">
                            </div>
                            <div class="field service-extra cedula-extra" data-service-extra="Cedula" style="display:none;">
                                <label for="cedula_gross_income">Gross Annual Income *</label>
                                <input id="cedula_gross_income" name="cedula_gross_income" type="text" placeholder="Previous year's income">
                            </div>
                            <div class="field service-extra cedula-extra" data-service-extra="Cedula" style="display:none;">
                                <label for="cedula_income_source">Income / Business Source</label>
                                <input id="cedula_income_source" name="cedula_income_source" type="text" placeholder="e.g. Employment, business" data-optional="true">
                            </div>
                            <div class="field service-extra cedula-extra" data-service-extra="Cedula" style="display:none;">
                                <label for="cedula_tin">TIN No.</label>
                                <input id="cedula_tin" name="cedula_tin" type="text" placeholder="Optional" data-optional="true">
                            </div>

                            <div class="field full service-extra barangay-id-extra" data-service-extra="Barangay ID" style="display:none;">
                                <h3>Barangay ID Details</h3>
                                <p>Provide emergency contact details for the Barangay ID request.</p>
                            </div>
                            <div class="field service-extra barangay-id-extra" data-service-extra="Barangay ID" style="display:none;">
                                <label for="id_emergency_name">Emergency Contact Name *</label>
                                <input id="id_emergency_name" name="id_emergency_name" type="text" placeholder="Enter full name">
                            </div>
                            <div class="field service-extra barangay-id-extra" data-service-extra="Barangay ID" style="display:none;">
                                <label for="id_emergency_relationship">Relationship *</label>
                                <input id="id_emergency_relationship" name="id_emergency_relationship" type="text" placeholder="e.g. Parent, sibling">
                            </div>
                            <div class="field service-extra barangay-id-extra" data-service-extra="Barangay ID" style="display:none;">
                                <label for="id_emergency_contact">Emergency Contact Number *</label>
                                <input id="id_emergency_contact" name="id_emergency_contact" type="tel" placeholder="+63 912 345 6789">
                            </div>
                            <div class="field service-extra barangay-id-extra" data-service-extra="Barangay ID" style="display:none;">
                                <label for="id_blood_type">Blood Type</label>
                                <input id="id_blood_type" name="id_blood_type" type="text" placeholder="Optional" data-optional="true">
                            </div>
                            <div class="field service-extra barangay-id-extra" data-service-extra="Barangay ID" style="display:none;">
                                <label for="id_valid_until">Valid Until *</label>
                                <input id="id_valid_until" name="id_valid_until" type="date">
                            </div>

                            <div class="field full service-extra incident-extra" data-service-extra="Incident Report" style="display:none;">
                                <h3>Incident Report Details</h3>
                                <p>Provide the incident details for the official report.</p>
                            </div>
                            <div class="field service-extra incident-extra" data-service-extra="Incident Report" style="display:none;">
                                <label for="incident_date">Incident Date *</label>
                                <input id="incident_date" name="incident_date" type="date">
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
                                <input id="incident_witness_name" name="incident_witness_name" type="text" placeholder="Optional" data-optional="true">
                            </div>
                            <div class="field service-extra incident-extra" data-service-extra="Incident Report" style="display:none;">
                                <label for="incident_witness_contact">Witness Contact Number</label>
                                <input id="incident_witness_contact" name="incident_witness_contact" type="tel" placeholder="Optional" data-optional="true">
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
                                        <input type="file" id="valid_id" name="valid_id" accept="image/*,.pdf" required style="display:none;">
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
                                        <input type="file" id="payment_receipt" name="payment_receipt" accept="image/*,application/pdf" style="display:none;">
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

    <?php
    $footerBase = '../public/';
    $footerAssetBase = '../assets';
    include __DIR__ . '/../includes/footer.php';
    ?>
    <script src="../assets/js/resident.js?v=20260530a"></script>
</body>

</html>
