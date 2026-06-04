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
