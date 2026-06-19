<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header("Location: login_admin.php");
    exit();
}
require_once __DIR__ . '/../includes/db_connect.php';

if (!isset($_GET['req_id'])) {
    die("Error: No request ID specified.");
}

$req_id = (int)$_GET['req_id'];

$query = "
    SELECT sr.*, dt.name AS document_type,
           p.first_name, p.middle_name, p.last_name, p.suffix,
           p.birth_date, p.birth_place AS cedula_birthplace, p.employed_status AS occupation,
           p.sex AS gender,
           p.civil_status,
           p.mobile_number AS phone,
           CONCAT_WS(' ', p.house_no, p.street, 'Purok', p.purok_no, p.subdivision) AS full_address,
           rb.business_name, rb.business_location, rb.business_operator, rb.business_address, rb.business_nature,
           rc.construction_address, rc.construction_purpose, rc.construction_status, rc.construction_description,
           ced.cedula_type, ced.tax_year AS cedula_tax_year, ced.place_issued AS cedula_place_issued, ced.income_source AS cedula_income_source, ced.height AS cedula_height, ced.weight AS cedula_weight, ced.gross_income AS cedula_gross_income,
           rid.blood_type AS id_blood_type, rid.emergency_name AS id_emergency_name, rid.emergency_relationship AS id_emergency_relationship, rid.emergency_contact AS id_emergency_contact, rid.valid_until AS id_valid_until,
           rin.incident_date, rin.incident_time, rin.incident_location, rin.incident_persons, rin.incident_narrative, rin.incident_action, 
           rin.witness_name AS incident_witness_name, rin.witness_contact AS incident_witness_contact, rin.witness_address AS incident_witness_address,
           ugi_tin.id_number AS cedula_tin
    FROM service_requests sr
    JOIN document_types dt ON sr.document_type_id = dt.document_type_id
    JOIN user_profiles p ON sr.user_id = p.user_id
    LEFT JOIN request_business_clearances rb ON sr.request_id = rb.request_id
    LEFT JOIN request_construction_permits rc ON sr.request_id = rc.request_id
    LEFT JOIN request_cedulas ced ON sr.request_id = ced.request_id
    LEFT JOIN request_barangay_ids rid ON sr.request_id = rid.request_id
    LEFT JOIN request_incident_reports rin ON sr.request_id = rin.request_id
    LEFT JOIN user_government_ids ugi_tin ON sr.user_id = ugi_tin.user_id AND ugi_tin.id_type = 'TIN'
    WHERE sr.request_id = ? LIMIT 1
";

$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, "i", $req_id);
mysqli_stmt_execute($stmt);
$request = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));

if (!$request) {
    die("Error: Request record not found or data structure mismatch.");
}

$fullname = $request['first_name'] . ' ' . (!empty($request['middle_name']) ? substr($request['middle_name'], 0, 1) . '. ' : '') . $request['last_name'];
if (!empty($request['suffix'])) $fullname .= ' ' . $request['suffix'];

$bdate = new DateTime($request['birth_date']);
$today = new DateTime('today');
$age = $bdate->diff($today)->y;

$sex = strtolower($request['gender'] ?? '');
$civil_status = strtolower($request['civil_status']);
$purok = $request['full_address'];
$purpose = $request['purpose'];
$document_type = $request['document_type'];

$is_barangay_clearance = strcasecmp($document_type, 'Barangay Clearance') === 0;
$is_certificate_of_indigency = strcasecmp($document_type, 'Certificate of Indigency') === 0;
$is_certificate_of_residency = strcasecmp($document_type, 'Certificate of Residency') === 0;
$is_business_clearance = strcasecmp($document_type, 'Business Clearance') === 0;
$is_construction_permit = strcasecmp($document_type, 'Building/Construction Permit') === 0;
$is_cedula = strcasecmp($document_type, 'Cedula') === 0;
$is_barangay_id = strcasecmp($document_type, 'Barangay ID') === 0;
$is_incident_report = strcasecmp($document_type, 'Incident Report') === 0;

$business_name = $request['business_name'] ?? '';
$business_location = $request['business_location'] ?? '';
$business_operator = $request['business_operator'] ?? $fullname;
$business_address = $request['business_address'] ?? $purok;
$business_nature = $request['business_nature'] ?? '';
$business_permit_for = $request['business_permit_for'] ?? $purpose;
$construction_address = $request['construction_address'] ?? $purok;
$construction_purpose = $request['construction_purpose'] ?? $purpose;
$construction_other_purpose = $request['construction_other_purpose'] ?? '';
$construction_status = $request['construction_status'] ?? '';
$construction_other_status = $request['construction_other_status'] ?? '';
$construction_description = $request['construction_description'] ?? '';
$cedula_type = $request['cedula_type'] ?? '';
$cedula_tax_year = $request['cedula_tax_year'] ?? date('Y');
$cedula_place_issued = $request['cedula_place_issued'] ?? 'Barangay Makiling';
$cedula_income_source = $request['cedula_income_source'] ?? '';
$cedula_tin = $request['cedula_tin'] ?? '';
$cedula_birthplace = $request['cedula_birthplace'] ?? '';
$cedula_height = $request['cedula_height'] ?? '';
$cedula_weight = $request['cedula_weight'] ?? '';
$cedula_gross_income = $request['cedula_gross_income'] ?? '';
$id_emergency_name = $request['id_emergency_name'] ?? '';
$id_emergency_relationship = $request['id_emergency_relationship'] ?? '';
$id_emergency_contact = $request['id_emergency_contact'] ?? '';
$id_blood_type = $request['id_blood_type'] ?? '';
$id_valid_until = $request['id_valid_until'] ?? '';
$incident_date = $request['incident_date'] ?? '';
$incident_time = $request['incident_time'] ?? '';
$incident_location = $request['incident_location'] ?? '';
$incident_persons = $request['incident_persons'] ?? '';
$incident_narrative = $request['incident_narrative'] ?? '';
$incident_action = $request['incident_action'] ?? '';
$incident_witness_name = $request['incident_witness_name'] ?? '';
$incident_witness_contact = $request['incident_witness_contact'] ?? '';
$incident_witness_address = $request['incident_witness_address'] ?? '';
$age_phrase = $age >= 18 ? 'of legal age' : $age . ' years old';
$pronoun_object = $sex === 'female' ? 'her' : ($sex === 'male' ? 'his' : 'their');

if ($is_barangay_clearance) {
    $document_title = 'BARANGAY CLEARANCE';
} elseif ($is_business_clearance) {
    $document_title = 'BARANGAY BUSINESS CLEARANCE';
} elseif ($is_construction_permit) {
    $document_title = 'PERMIT FOR CONSTRUCTION AND/OR CIVIL WORKS';
} elseif ($is_cedula) {
    $document_title = 'COMMUNITY TAX CERTIFICATE';
} elseif ($is_barangay_id) {
    $document_title = 'BARANGAY IDENTIFICATION';
} elseif ($is_incident_report) {
    $document_title = 'INCIDENT REPORT';
} elseif ($is_certificate_of_residency) {
    $document_title = 'CERTIFICATE OF RESIDENCY';
} else {
    $document_title = 'CERTIFICATE OF INDIGENCY';
}

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Print Document | MakiKonek</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="icon" href="../assets/img/Barangay_Makiling_Seal.png" type="image/png">
    <link rel="stylesheet" href="../assets/css/print_document.css">
</head>

<body>

    <div class="print-toolbar">
        <div>
            <h5 style="margin: 0; font-family: sans-serif;"><i class="bi bi-pencil-square text-warning"></i> Document Editor Mode</h5>
            <small style="font-family: sans-serif; color: #adb5bd;">Click on any highlighted text below to edit it before printing.</small>
        </div>
        <button class="print-btn" onclick="window.print()"><i class="bi bi-printer-fill"></i> Save as PDF / Print</button>
    </div>

    <?php if ($is_barangay_id): ?>
        <div class="id-print-page">
            <div class="id-print-grid">
                <div class="actual-id-card">
                    <div class="actual-id-header">
                        <img class="actual-id-logo" src="../assets/img/Barangay_Makiling_Seal.png" alt="Barangay Makiling Seal">
                        <div>
                            Republic of the Philippines<br>
                            Province of Laguna<br>
                            City of Calamba
                            <strong>Barangay Makiling</strong>
                        </div>
                    </div>

                    <div class="actual-id-body">
                        <div class="actual-id-photo">2x2<br>Photo</div>
                        <div>
                            <div class="actual-id-name editable" contenteditable="true"><?php echo htmlspecialchars($fullname); ?></div>
                            <div class="actual-id-role">Resident</div>
                            <div class="actual-id-number">Barangay ID No. <span class="editable" contenteditable="true"><?php echo htmlspecialchars($request['reference_no']); ?></span></div>
                        </div>
                    </div>

                    <div class="actual-id-address editable" contenteditable="true"><?php echo htmlspecialchars($purok); ?></div>
                </div>

                <div class="actual-id-card">
                    <div class="actual-id-back-header">Personal Information</div>
                    <div class="actual-id-info">
                        <div class="actual-id-info-row"><span>Date of Birth</span><span class="editable" contenteditable="true"><?php echo htmlspecialchars(date('F d, Y', strtotime($request['birth_date']))); ?></span></div>
                        <div class="actual-id-info-row"><span>Sex</span><span class="editable" contenteditable="true"><?php echo htmlspecialchars($request['gender']); ?></span></div>
                        <div class="actual-id-info-row"><span>Civil Status</span><span class="editable" contenteditable="true"><?php echo htmlspecialchars($civil_status); ?></span></div>
                        <div class="actual-id-info-row"><span>Contact No.</span><span class="editable" contenteditable="true"><?php echo htmlspecialchars($request['phone']); ?></span></div>
                        <div class="actual-id-info-row"><span>Blood Type</span><span class="editable" contenteditable="true"><?php echo htmlspecialchars($id_blood_type); ?></span></div>
                    </div>

                    <div class="actual-id-emergency">
                        IN CASE OF EMERGENCY, PLEASE CONTACT:<br>
                        <strong class="editable" contenteditable="true"><?php echo htmlspecialchars($id_emergency_name); ?> - <?php echo htmlspecialchars($id_emergency_relationship); ?></strong><br>
                        <span class="editable" contenteditable="true"><?php echo htmlspecialchars($id_emergency_contact); ?></span>
                    </div>

                    <div class="actual-id-back-header">In case of loss, please return to this barangay</div>
                    <div class="actual-id-valid editable" contenteditable="true">
                        <?php echo $id_valid_until ? htmlspecialchars(date('F d, Y', strtotime($id_valid_until))) : ''; ?>
                    </div>

                    <div class="actual-id-signature">
                        <strong>HON. AIGRETTE PANGANIBAN LAJARA</strong>
                        <span>Punong Barangay</span>
                    </div>
                </div>
            </div>
        </div>
    <?php else: ?>

        <div class="certificate-container">

            <div class="header-section">
                <img class="header-logo" src="../assets/img/Barangay_Makiling_Seal.png" alt="Barangay Makiling Seal">
                <p>Republic of the Philippines</p>
                <p>Province of Laguna</p>
                <p>City of Calamba</p>
                <h2 style="margin-top: 5px;">BARANGAY MAKILING</h2>
                <p style="font-weight: bold; margin-top: 10px; font-size: 16px;">OFFICE OF THE PUNONG BARANGAY</p>
            </div>

            <div class="document-title <?php echo $is_certificate_of_indigency ? 'indigency-title' : ''; ?> <?php echo $is_construction_permit ? 'construction-title' : ''; ?>" style="margin-top: 20px; margin-bottom: 10px;"><?php echo htmlspecialchars($document_title); ?></div>

            <div class="layout-body <?php echo ($is_incident_report || $is_cedula) ? 'incident-layout' : ''; ?>">

                <?php if (!$is_incident_report && !$is_cedula): ?>
                    <div class="left-officials-panel">
                        <div class="officials-list">
                            <div class="official-title">HON. AIGRETTE PANGANIBAN LAJARA</div>
                            <div class="official-role">Barangay Captain</div>

                            <div class="official-title">TEONA LIZARDO NOPRADA</div>
                            <div class="official-role">Barangay Secretary</div>

                            <div class="official-title">RUBIE ALCANTARA OLAES</div>
                            <div class="official-role">Barangay Treasurer</div>

                            <div class="official-title">HON. HERMANO MEDALLA DE CHAVEZ</div>
                            <div class="official-role">Kagawad</div>

                            <div class="official-title">HON. VIRGILIO TORRES LOPEZ</div>
                            <div class="official-role">Kagawad</div>

                            <div class="official-title">HON. DIOMEDES NEMES AUSTRIA</div>
                            <div class="official-role">Kagawad</div>

                            <div class="official-title">HON. RIZAL MERCADO PASCUAL</div>
                            <div class="official-role">Kagawad</div>

                            <div class="official-title">HON. FREDDIE BALANSAY NOPRADA</div>
                            <div class="official-role">Kagawad</div>

                            <div class="official-title">HON. MARCELO ATIENZA MOLINYAWE</div>
                            <div class="official-role">Kagawad</div>

                            <div class="official-title">HON. ANTONIO HEMPESALLA MEDALLA</div>
                            <div class="official-role">Kagawad</div>

                            <div class="official-title">HON. AARON KLYNE MACASADIA MAGSINO</div>
                            <div class="official-role">SK Chairman</div>

                        </div>

                        <div class="left-panel-footer">
                            <div class="dry-seal-note">Not valid without the official dry seal of Barangay Makiling.</div>
                            <p class="barangay-motto">"WHEN WE WORK TOGETHER, OUR<br>SUCCESS WILL BE MUCH BETTER"</p>
                        </div>
                    </div>
                <?php endif; ?>

                <div class="right-content-panel <?php echo ($is_incident_report || $is_cedula) ? 'full-width' : ''; ?>">
                    <img class="content-watermark" src="../assets/img/Barangay_Makiling_Seal.png" alt="" aria-hidden="true">

                    <!-- Community Tax Certificate Application Form -->
                    <?php if ($is_cedula): ?>
                        <div class="summary-document">
                            <h3>Community Tax Certificate Application Form</h3>
                            <div class="form-document-table">
                                <div class="form-row">
                                    <div class="form-cell"><span class="form-label">Last Name</span><span class="editable" contenteditable="true"><?php echo htmlspecialchars($request['last_name']); ?></span></div>
                                    <div class="form-cell"><span class="form-label">First Name</span><span class="editable" contenteditable="true"><?php echo htmlspecialchars($request['first_name']); ?></span></div>
                                    <div class="form-cell"><span class="form-label">Middle Name</span><span class="editable" contenteditable="true"><?php echo htmlspecialchars($request['middle_name']); ?></span></div>
                                </div>
                                <div class="form-row">
                                    <div class="form-cell full"><span class="form-label">Address: Purok, Barangay, Municipality/City</span><span class="editable" contenteditable="true"><?php echo htmlspecialchars($purok); ?>, Barangay Makiling, Calamba City</span></div>
                                </div>
                                <div class="form-row">
                                    <div class="form-cell"><span class="form-label">Birthdate</span><span class="editable" contenteditable="true"><?php echo htmlspecialchars(date('F d, Y', strtotime($request['birth_date']))); ?></span></div>
                                    <div class="form-cell"><span class="form-label">Birthplace</span><span class="editable" contenteditable="true"><?php echo htmlspecialchars($cedula_birthplace); ?></span></div>
                                    <div class="form-cell"><span class="form-label">Civil Status</span><span class="editable" contenteditable="true"><?php echo htmlspecialchars($civil_status); ?></span></div>
                                </div>
                                <div class="form-row">
                                    <div class="form-cell"><span class="form-label">Height</span><span class="editable" contenteditable="true"><?php echo htmlspecialchars($cedula_height); ?></span></div>
                                    <div class="form-cell"><span class="form-label">Weight</span><span class="editable" contenteditable="true"><?php echo htmlspecialchars($cedula_weight); ?></span></div>
                                    <div class="form-cell"><span class="form-label">Occupation</span><span class="editable" contenteditable="true"><?php echo htmlspecialchars($request['occupation']); ?></span></div>
                                </div>
                                <div class="form-row">
                                    <div class="form-cell"><span class="form-label">Cedula Type</span><span class="editable" contenteditable="true"><?php echo htmlspecialchars($cedula_type); ?></span></div>
                                    <div class="form-cell"><span class="form-label">Tax Year</span><span class="editable" contenteditable="true"><?php echo htmlspecialchars($cedula_tax_year); ?></span></div>
                                    <div class="form-cell"><span class="form-label">TIN No.</span><span class="editable" contenteditable="true"><?php echo htmlspecialchars($cedula_tin); ?></span></div>
                                </div>
                                <div class="form-row">
                                    <div class="form-cell"><span class="form-label">Gross Annual Income</span><span class="editable" contenteditable="true"><?php echo htmlspecialchars($cedula_gross_income); ?></span></div>
                                    <div class="form-cell"><span class="form-label">Income / Business Source</span><span class="editable" contenteditable="true"><?php echo htmlspecialchars($cedula_income_source); ?></span></div>
                                    <div class="form-cell"><span class="form-label">Place Issued</span><span class="editable" contenteditable="true"><?php echo htmlspecialchars($cedula_place_issued); ?></span></div>
                                </div>
                            </div>

                            <div class="summary-signature">
                                <strong>HON. AIGRETTE PANGANIBAN LAJARA</strong>
                                <span>Punong Barangay</span>
                            </div>
                        </div>
                    <?php elseif ($is_barangay_id): ?>
                        <div class="summary-document">
                            <h3>Barangay ID Card Preview</h3>
                            <div class="barangay-id-cards">
                                <div class="barangay-id-card">
                                    <div class="barangay-id-card-header">
                                        Republic of the Philippines<br>
                                        Province of Laguna<br>
                                        City of Calamba
                                        <strong>Barangay Makiling</strong>
                                    </div>
                                    <div class="barangay-id-front-body">
                                        <div class="barangay-id-photo">2x2<br>Photo</div>
                                        <div>
                                            <div class="barangay-id-name editable" contenteditable="true"><?php echo htmlspecialchars($fullname); ?></div>
                                            <div class="barangay-id-role">Resident</div>
                                            <div class="barangay-id-address editable" contenteditable="true"><?php echo htmlspecialchars($purok); ?></div>
                                            <small>Complete Address</small>
                                        </div>
                                    </div>
                                </div>

                                <div class="barangay-id-card">
                                    <div class="barangay-id-info-table">
                                        <div class="barangay-id-info-row"><span>Date of Birth</span><span class="editable" contenteditable="true"><?php echo htmlspecialchars(date('F d, Y', strtotime($request['birth_date']))); ?></span></div>
                                        <div class="barangay-id-info-row"><span>Sex</span><span class="editable" contenteditable="true"><?php echo htmlspecialchars($request['gender']); ?></span></div>
                                        <div class="barangay-id-info-row"><span>Civil Status</span><span class="editable" contenteditable="true"><?php echo htmlspecialchars($civil_status); ?></span></div>
                                        <div class="barangay-id-info-row"><span>Contact No.</span><span class="editable" contenteditable="true"><?php echo htmlspecialchars($request['phone']); ?></span></div>
                                        <div class="barangay-id-info-row"><span>Blood Type</span><span class="editable" contenteditable="true"><?php echo htmlspecialchars($id_blood_type); ?></span></div>
                                    </div>
                                    <div class="barangay-id-emergency">
                                        IN CASE OF EMERGENCY, PLEASE CONTACT:<br>
                                        <strong class="editable" contenteditable="true"><?php echo htmlspecialchars($id_emergency_name); ?> - <?php echo htmlspecialchars($id_emergency_relationship); ?></strong><br>
                                        <span class="editable" contenteditable="true"><?php echo htmlspecialchars($id_emergency_contact); ?></span>
                                    </div>
                                    <div class="barangay-id-valid editable" contenteditable="true">
                                        <?php echo $id_valid_until ? htmlspecialchars(date('F d, Y', strtotime($id_valid_until))) : ''; ?>
                                    </div>
                                    <div class="summary-signature" style="margin-top:0;">
                                        <strong>HON. AIGRETTE PANGANIBAN LAJARA</strong>
                                        <span>Punong Barangay</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php elseif ($is_incident_report): ?>
                        <div class="summary-document">
                            <h3>Barangay Incident Report</h3>
                            <div class="form-document-table">
                                <div class="form-row">
                                    <div class="form-cell full"><span class="form-label">Reporting Party Full Name</span><span class="editable" contenteditable="true"><?php echo htmlspecialchars($fullname); ?></span></div>
                                </div>
                                <div class="form-row">
                                    <div class="form-cell"><span class="form-label">Contact Number</span><span class="editable" contenteditable="true"><?php echo htmlspecialchars($request['phone']); ?></span></div>
                                    <div class="form-cell"><span class="form-label">Address</span><span class="editable" contenteditable="true"><?php echo htmlspecialchars($purok); ?></span></div>
                                    <div class="form-cell"><span class="form-label">Date of Report</span><span class="editable" contenteditable="true"><?php echo date('F d, Y'); ?></span></div>
                                </div>
                                <div class="form-row">
                                    <div class="form-cell"><span class="form-label">Date of Incident</span><span class="editable" contenteditable="true"><?php echo htmlspecialchars($incident_date); ?></span></div>
                                    <div class="form-cell"><span class="form-label">Time of Incident</span><span class="editable" contenteditable="true"><?php echo htmlspecialchars($incident_time); ?></span></div>
                                    <div class="form-cell"><span class="form-label">Persons Involved</span><span class="editable" contenteditable="true"><?php echo htmlspecialchars($incident_persons); ?></span></div>
                                </div>
                                <div class="form-row">
                                    <div class="form-cell full"><span class="form-label">Incident Location</span><span class="editable" contenteditable="true"><?php echo htmlspecialchars($incident_location); ?></span></div>
                                </div>
                                <div class="form-row">
                                    <div class="form-cell full tall"><span class="form-label">Brief Description of Incident</span><span class="editable" contenteditable="true"><?php echo htmlspecialchars($incident_narrative); ?></span></div>
                                </div>
                                <div class="form-row">
                                    <div class="form-cell full"><span class="form-label">Witness Full Name</span><span class="editable" contenteditable="true"><?php echo htmlspecialchars($incident_witness_name); ?></span></div>
                                </div>
                                <div class="form-row">
                                    <div class="form-cell"><span class="form-label">Witness Contact Number</span><span class="editable" contenteditable="true"><?php echo htmlspecialchars($incident_witness_contact); ?></span></div>
                                    <div class="form-cell" style="grid-column: span 2;"><span class="form-label">Witness Address</span><span class="editable" contenteditable="true"><?php echo htmlspecialchars($incident_witness_address); ?></span></div>
                                </div>
                                <div class="form-row">
                                    <div class="form-cell tall"><span class="form-label">Signature</span><br><br><br><small>Reporting Party's Signature</small></div>
                                    <div class="form-cell tall" style="grid-column: span 2;"><span class="form-label">Barangay Official's Action / Remarks</span><span class="editable" contenteditable="true"><?php echo htmlspecialchars($incident_action); ?></span></div>
                                </div>
                            </div>
                        </div>
                    <?php elseif ($is_construction_permit): ?>
                        <div class="construction-content">
                            <p class="construction-lead">
                                <strong>THIS IS TO CERTIFY</strong>, that the Office of the Punong Barangay of Barangay Makiling,
                                Calamba City, Laguna, interposes no objection to the grant of permit for construction and/or other
                                civil works to the applicant whose name and other personal circumstances appearing below.
                            </p>

                            <div class="construction-info">
                                <strong>Name of Applicant</strong><span>:</span><span class="editable" contenteditable="true"><?php echo htmlspecialchars($fullname); ?></span>
                            </div>
                            <div class="construction-info">
                                <strong>Address</strong><span>:</span><span class="editable" contenteditable="true"><?php echo htmlspecialchars($construction_address); ?></span>
                            </div>

                            <div class="construction-section-title">Specific Purpose of Application</div>
                            <div class="construction-purpose-grid">
                                <span><span class="construction-mark"><?php echo $construction_purpose === 'Electrical Works' ? 'X' : ''; ?></span> Electrical Works</span>
                                <span><span class="construction-mark"><?php echo $construction_purpose === 'Excavation' ? 'X' : ''; ?></span> Excavation</span>
                                <span><span class="construction-mark"><?php echo $construction_purpose === 'Pipe Lying' ? 'X' : ''; ?></span> Pipe Lying</span>
                                <span><span class="construction-mark"><?php echo $construction_purpose === 'Drainage / Sewerage' ? 'X' : ''; ?></span> Drainage / Sewerage</span>
                                <span>
                                    <span class="construction-mark"><?php echo $construction_purpose === 'Others' ? 'X' : ''; ?></span>
                                    Others (Specify) <span class="construction-other-line editable" contenteditable="true"><?php echo htmlspecialchars($construction_other_purpose); ?></span>
                                </span>
                            </div>

                            <div class="construction-section-title">Construction:</div>
                            <div class="construction-status-grid">
                                <span><span class="construction-mark"><?php echo $construction_status === 'Not yet started' ? 'X' : ''; ?></span> Not yet started</span>
                                <span><span class="construction-mark"><?php echo $construction_status === 'Already Constructed' ? 'X' : ''; ?></span> Already Constructed</span>
                                <span><span class="construction-mark"><?php echo $construction_status === 'Already Started' ? 'X' : ''; ?></span> Already Started</span>
                                <span>
                                    <span class="construction-mark"><?php echo $construction_status === 'Others' ? 'X' : ''; ?></span>
                                    Others <span class="construction-other-line editable" contenteditable="true"><?php echo htmlspecialchars($construction_other_status); ?></span>
                                </span>
                            </div>

                            <?php if (!empty($construction_description)): ?>
                                <p class="construction-note">
                                    Project description: <span class="editable" contenteditable="true"><?php echo htmlspecialchars($construction_description); ?></span>
                                </p>
                            <?php endif; ?>

                            <div class="construction-section-title">Applicant agrees to observe and fully comply with the following conditions:</div>
                            <ul class="construction-conditions">
                                <li><span class="construction-mark"></span><span>Structure to be constructed</span></li>
                                <li><span class="construction-mark"></span><span>Avenues, waterways or any government property</span></li>
                                <li><span class="construction-mark"></span><span>Provisions of the National Building Code shall be fully executed.</span></li>
                                <li><span class="construction-mark"></span><span>Full restoration of excavated streets, sidewalks, drainage system, etc.</span></li>
                                <li><span class="construction-mark"></span><span>Installation of proper safety and precautionary signs.</span></li>
                                <li><span class="construction-mark"></span><span>Non storage of harmful chemicals or substances that may cause health hazardous result to environmental dangers.</span></li>
                                <li><span class="construction-mark"></span><span>Vehicle parking shall be provided but shall not exceed property limits.</span></li>
                                <li><span class="construction-mark"></span><span>Others <span class="construction-other-line"></span></span></li>
                            </ul>

                            <p class="construction-note">
                                Non-compliance of any or all of the foregoing conditions shall result to the revocation of this
                                Barangay Clearance without prejudice to the filing of legal action of the applicant.
                            </p>

                            <p class="construction-issued">
                                <strong>ISSUED</strong> this <span class="editable" contenteditable="true"><?php echo date('jS'); ?></span>
                                day of <span class="editable" contenteditable="true"><?php echo date('F'); ?></span>
                                <span class="editable" contenteditable="true"><?php echo date('Y'); ?></span> at Barangay Makiling,
                                Calamba City, Laguna, Philippines.
                            </p>

                            <div class="construction-signature">
                                <strong>HON. AIGRETTE PANGANIBAN LAJARA</strong>
                                <span>Punong Barangay</span>
                            </div>
                        </div>
                    <?php elseif ($is_business_clearance): ?>
                        <div class="business-content">
                            <p class="business-intro">To whom it may concern:</p>
                            <p class="business-certify">This is to certify that the business or trade activity described below:</p>

                            <div class="business-fields">
                                <div>
                                    <div class="business-line-value editable" contenteditable="true"><?php echo htmlspecialchars($business_name); ?></div>
                                    <div class="business-line-label">(Business Name or Trade Activity)</div>
                                </div>
                                <div>
                                    <div class="business-line-value editable" contenteditable="true"><?php echo htmlspecialchars($business_location); ?></div>
                                    <div class="business-line-label">(Location)</div>
                                </div>
                                <div>
                                    <div class="business-line-value editable" contenteditable="true"><?php echo htmlspecialchars($business_operator); ?></div>
                                    <div class="business-line-label">(Operator / Manager)</div>
                                </div>
                                <div>
                                    <div class="business-line-value editable" contenteditable="true"><?php echo htmlspecialchars($business_address); ?></div>
                                    <div class="business-line-label">(Address)</div>
                                </div>
                            </div>

                            <p class="business-paragraph">
                                Proposed to be established in this Barangay and is being applied for a Barangay Clearance to be used in securing a corresponding Mayor's Permit has been found to be:
                            </p>

                            <div class="business-check-row">
                                <div class="business-check"><span>X</span><span>/</span></div>
                                <div>In conformity with the provisions of existing Barangay Ordinances, rules, and regulations being enforced in this Barangay;</div>
                            </div>
                            <div class="business-check-row">
                                <div class="business-check"><span>X</span><span>/</span></div>
                                <div>Not among those businesses or trade activities with pending cases and/or being banned to be established in this barangay;</div>
                            </div>

                            <p class="business-paragraph">
                                In view of the foregoing, this barangay through the undersigned,
                            </p>

                            <div class="business-check-row">
                                <div class="business-check"><span>X</span><span>/</span></div>
                                <div>
                                    Interposes no objection for the issuance of the corresponding Mayor's Permit being applied for
                                    <span class="editable" contenteditable="true"><?php echo htmlspecialchars($business_permit_for); ?></span>.
                                </div>
                            </div>

                            <p class="business-paragraph">
                                PERMIT, HOWEVER, is subject for cancellation if the specific purpose granted by the Barangay Council is not consonant with the actual operation of the business.
                            </p>

                            <p class="business-issued">
                                Issued this <span class="editable" contenteditable="true"><?php echo date('jS'); ?></span> day of
                                <span class="editable" contenteditable="true"><?php echo date('F, Y'); ?></span> at Barangay Makiling, Calamba City, Laguna.
                            </p>

                            <div class="business-signature">
                                <strong>HON. AIGRETTE PANGANIBAN LAJARA</strong>
                                <span>Punong Barangay</span>
                            </div>

                            <p class="business-note">(Note: Not valid without Barangay Dry Seal)</p>

                            <div class="business-paid">
                                <div>
                                    <h4>Paid Under:</h4>
                                    <div class="business-paid-row"><span>O.R. NO.:</span><span class="business-paid-line" contenteditable="true"></span></div>
                                    <div class="business-paid-row"><span>DATE PAID:</span><span class="business-paid-line" contenteditable="true"></span></div>
                                    <div class="business-paid-row"><span>PLACE:</span><span class="business-paid-line" contenteditable="true"></span></div>
                                </div>
                                <div>
                                    <h4>&nbsp;</h4>
                                    <div class="business-paid-row"><span>TIN NO.:</span><span class="business-paid-line" contenteditable="true"></span></div>
                                    <div class="business-paid-row"><span>DATE ISSUED:</span><span class="business-paid-line" contenteditable="true"></span></div>
                                    <div class="business-paid-row"><span>PLACE ISSUED:</span><span class="business-paid-line" contenteditable="true"></span></div>
                                </div>
                            </div>
                        </div>
                    <?php elseif ($is_certificate_of_residency): ?>
                        <div class="residency-content">
                            <div class="residency-salutation">TO WHOM IT MAY CONCERN:</div>

                            <p class="residency-body">
                                This is to certify that <span class="editable" contenteditable="true"><?php echo htmlspecialchars($fullname); ?></span>,
                                <span class="editable" contenteditable="true"><?php echo $age; ?> years old</span>,
                                <span class="editable" contenteditable="true"><?php echo htmlspecialchars($civil_status); ?></span>,
                                Filipino citizen, whose specimen signature appears below, is a
                                <strong>PERMANENT RESIDENT</strong> of
                                <span class="editable" contenteditable="true"><?php echo htmlspecialchars($purok); ?></span>,
                                Barangay Makiling, Calamba City, Laguna.
                            </p>

                            <p class="residency-body">
                                Based on records of this office, the above-named person has been residing at
                                <span class="editable" contenteditable="true"><?php echo htmlspecialchars($purok); ?></span>,
                                Barangay Makiling, Calamba City, Laguna.
                            </p>

                            <p class="residency-body">
                                This <strong>CERTIFICATION</strong> is being issued upon the request of the above-named person for
                                <span class="editable" contenteditable="true"><?php echo htmlspecialchars($purpose); ?></span>
                                or whatever legal purpose it may serve.
                            </p>

                            <p class="residency-body">
                                Issued this <span class="editable" contenteditable="true"><?php echo date('jS'); ?></span> day of
                                <span class="editable" contenteditable="true"><?php echo date('F, Y'); ?></span> at Barangay Makiling,
                                Calamba City, Laguna, Philippines.
                            </p>

                            <div class="residency-signatures">
                                <div class="specimen-signature">
                                    <span>Specimen Signature:</span>
                                    <span class="specimen-line"></span>
                                </div>

                                <div class="residency-official-signature">
                                    <strong>HON. AIGRETTE PANGANIBAN LAJARA</strong>
                                    <span>Punong Barangay</span>
                                </div>
                            </div>
                        </div>
                    <?php elseif ($is_certificate_of_indigency): ?>
                        <div class="indigency-content">
                            <div class="indigency-salutation">To whom it may concern:</div>

                            <p class="indigency-body">
                                This is to certify that <span class="editable" contenteditable="true"><?php echo htmlspecialchars($fullname); ?></span> is a
                                bonafide resident of
                                <span class="editable" contenteditable="true"><?php echo htmlspecialchars($purok); ?></span>,
                                Barangay Makiling, Calamba City. Certify further that the above-named person is one of the
                                <span class="indigency-emphasis">indigents</span> in our barangay.
                            </p>

                            <p class="indigency-body">
                                That the above-mentioned person is living in this barangay since
                                <span class="editable" contenteditable="true">________</span> up to present.
                            </p>

                            <p class="indigency-body">
                                This certification is hereby issued upon the request of the above-mentioned person in connection with
                                <span class="editable" contenteditable="true"><?php echo htmlspecialchars($purpose); ?></span>
                                or for whatever legal purpose it may serve <?php echo htmlspecialchars($pronoun_object); ?> best.
                            </p>

                            <p class="indigency-body issued">
                                Issued this <span class="editable" contenteditable="true"><?php echo date('jS'); ?></span> day of
                                <span class="editable" contenteditable="true"><?php echo date('F'); ?></span>,
                                <span class="editable" contenteditable="true"><?php echo date('Y'); ?></span> at Barangay Makiling,
                                Calamba City.
                            </p>

                            <div class="indigency-signature">
                                <strong>HON. AIGRETTE PANGANIBAN LAJARA</strong>
                                <span>Punong Barangay</span>
                            </div>
                        </div>
                    <?php else: ?>
                        <div class="salutation">TO WHOM IT MAY CONCERN:</div>

                        <?php if ($is_barangay_clearance): ?>
                            <p class="letter-body">
                                This is to certify that <span class="editable" contenteditable="true"><?php echo htmlspecialchars($fullname); ?></span>,
                                Filipino, <span class="editable" contenteditable="true"><?php echo $age; ?> years old</span>,
                                <span class="editable" contenteditable="true"><?php echo htmlspecialchars($civil_status); ?></span>,
                                and a resident of <span class="editable" contenteditable="true"><?php echo htmlspecialchars($purok); ?></span>,
                                Barangay Makiling, Calamba City, Laguna.
                            </p>

                            <p class="letter-body">
                                This further certifies that the above-named person is of good moral character, a law-abiding citizen,
                                and has never been convicted of any crime involving moral turpitude nor been a member of any
                                subversive organization which seeks to overthrow our government.
                            </p>

                            <p class="letter-body">
                                Issued this <span class="editable" contenteditable="true"><?php echo date('jS'); ?></span> day of
                                <span class="editable" contenteditable="true"><?php echo date('F, Y'); ?></span> upon request of the
                                above-named for <span class="editable" contenteditable="true"><?php echo htmlspecialchars($purpose); ?></span>
                                and for whatever legal purpose it may serve.
                            </p>

                            <div class="signature-row clearance-signature-row">
                                <div class="signature-block applicant">
                                    <div class="signature-line" contenteditable="true"><?php echo htmlspecialchars($fullname); ?></div>
                                    <div class="signature-role" contenteditable="true">Signature</div>
                                </div>

                                <div class="signature-block official">
                                    <div class="signature-line">HON. AIGRETTE PANGANIBAN LAJARA</div>
                                    <div class="signature-role">Punong Barangay</div>
                                </div>
                            </div>

                            <div class="clearance-extra">
                                <div class="tax-field">
                                    <span>Community Tax Cert. No.</span>
                                    <span>:</span>
                                    <span class="blank-line" contenteditable="true"></span>
                                </div>
                                <div class="tax-field">
                                    <span>Date of Issue</span>
                                    <span>:</span>
                                    <span class="blank-line" contenteditable="true"></span>
                                </div>
                                <div class="tax-field">
                                    <span>Place of Issue</span>
                                    <span>:</span>
                                    <span class="blank-line" contenteditable="true"></span>
                                </div>

                                <div class="thumbmark-group">
                                    <div class="thumbmark-box"></div>
                                    <div class="thumbmark-box"></div>
                                </div>
                                <div class="thumbmark-labels">
                                    <span>Left</span>
                                    <span>Right</span>
                                </div>
                                <div class="thumbmark-note">(Thumbmark)</div>
                            </div>
                        <?php else: ?>
                            <p class="letter-body">
                                This is to certify that <span class="editable" contenteditable="true"><?php echo htmlspecialchars($fullname); ?></span>,
                                <span class="editable" contenteditable="true"><?php echo $age; ?> years old</span>, Filipino,
                                <span class="editable" contenteditable="true"><?php echo htmlspecialchars($sex); ?></span>,
                                <span class="editable" contenteditable="true"><?php echo htmlspecialchars($civil_status); ?></span>,
                                is a bonafide resident of <span class="editable" contenteditable="true"><?php echo htmlspecialchars($purok); ?></span>,
                                Barangay Makiling, Calamba, Laguna.
                            </p>

                            <p class="letter-body">
                                This is to certify further that the above-named belongs to an indigent family of this barangay.
                            </p>

                            <p class="letter-body">
                                This certification is issued upon the request of the interested party for the requirement for
                                <span class="editable" contenteditable="true"><?php echo htmlspecialchars($purpose); ?></span>
                                or whatever legal intents and purposes it may serve them best.
                            </p>

                            <p class="letter-body">
                                Given this <span class="editable" contenteditable="true"><?php echo date('jS'); ?></span> day of
                                <span class="editable" contenteditable="true"><?php echo date('F Y'); ?></span> at Barangay Makiling,
                                Calamba City, Laguna, Philippines.
                            </p>

                            <div class="signature-row">
                                <div class="signature-block applicant">
                                    <div class="signature-line" contenteditable="true"><?php echo htmlspecialchars($fullname); ?></div>
                                    <div class="signature-role" contenteditable="true">Applicant</div>
                                </div>

                                <div class="signature-block official">
                                    <div class="signature-line">HON. AIGRETTE PANGANIBAN LAJARA</div>
                                    <div class="signature-role">Punong Barangay</div>
                                </div>
                            </div>
                        <?php endif; ?>
                    <?php endif; ?>

                </div>
            </div>
        </div>
    <?php endif; ?>

</body>

</html>