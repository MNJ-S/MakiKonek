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

$query = "SELECT * FROM service_requests WHERE request_id = ? LIMIT 1";
$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, "i", $req_id);
mysqli_stmt_execute($stmt);
$request = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));

if (!$request) {
    die("Error: Request record not found.");
}

$fullname = $request['first_name'] . ' ' . (!empty($request['middle_name']) ? substr($request['middle_name'], 0, 1) . '. ' : '') . $request['last_name'];
if (!empty($request['suffix'])) $fullname .= ' ' . $request['suffix'];

$bdate = new DateTime($request['birth_date']);
$today = new DateTime('today');
$age = $bdate->diff($today)->y;

$sex = strtolower($request['gender']);
$civil_status = strtolower($request['civil_status']);
$purok = $request['address'];
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
$request_details = [];
if (!empty($request['request_details'])) {
    $decoded_details = json_decode($request['request_details'], true);
    if (is_array($decoded_details)) {
        $request_details = $decoded_details;
    }
}
$business_name = $request_details['business_name'] ?? '';
$business_location = $request_details['business_location'] ?? '';
$business_operator = $request_details['business_operator'] ?? $fullname;
$business_address = $request_details['business_address'] ?? $purok;
$business_nature = $request_details['business_nature'] ?? '';
$business_permit_for = $request_details['business_permit_for'] ?? $purpose;
$construction_address = $request_details['construction_address'] ?? $purok;
$construction_purpose = $request_details['construction_purpose'] ?? $purpose;
$construction_other_purpose = $request_details['construction_other_purpose'] ?? '';
$construction_status = $request_details['construction_status'] ?? '';
$construction_other_status = $request_details['construction_other_status'] ?? '';
$construction_description = $request_details['construction_description'] ?? '';
$cedula_type = $request_details['cedula_type'] ?? '';
$cedula_tax_year = $request_details['cedula_tax_year'] ?? date('Y');
$cedula_place_issued = $request_details['cedula_place_issued'] ?? 'Barangay Makiling';
$cedula_income_source = $request_details['cedula_income_source'] ?? '';
$cedula_tin = $request_details['cedula_tin'] ?? '';
$cedula_birthplace = $request_details['cedula_birthplace'] ?? '';
$cedula_height = $request_details['cedula_height'] ?? '';
$cedula_weight = $request_details['cedula_weight'] ?? '';
$cedula_gross_income = $request_details['cedula_gross_income'] ?? '';
$id_emergency_name = $request_details['id_emergency_name'] ?? '';
$id_emergency_relationship = $request_details['id_emergency_relationship'] ?? '';
$id_emergency_contact = $request_details['id_emergency_contact'] ?? '';
$id_blood_type = $request_details['id_blood_type'] ?? '';
$id_valid_until = $request_details['id_valid_until'] ?? '';
$incident_date = $request_details['incident_date'] ?? '';
$incident_time = $request_details['incident_time'] ?? '';
$incident_location = $request_details['incident_location'] ?? '';
$incident_persons = $request_details['incident_persons'] ?? '';
$incident_narrative = $request_details['incident_narrative'] ?? '';
$incident_action = $request_details['incident_action'] ?? '';
$incident_witness_name = $request_details['incident_witness_name'] ?? '';
$incident_witness_contact = $request_details['incident_witness_contact'] ?? '';
$incident_witness_address = $request_details['incident_witness_address'] ?? '';
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
    <style>
        /* A4 PAPER */
        body {
            font-family: 'Times New Roman', serif;
            margin: 0;
            padding: 20px;
            color: #000;
            background: #e9ecef;
        }

        .certificate-container {
            position: relative;
            width: 800px;
            min-height: 1050px;
            margin: 0 auto;
            padding: 36px 40px 42px;
            background: #fff;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
        }

        /* Header section */
        .header-section {
            text-align: center;
            margin-bottom: 18px;
            position: relative;
            min-height: 112px;
            padding: 8px 115px 0;
        }

        .header-logo {
            position: absolute;
            top: 18px;
            left: 28px;
            width: 82px;
            height: 82px;
            object-fit: contain;
        }

        .header-section h2 {
            margin: 0;
            font-size: 22px;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .header-section p {
            margin: 2px 0;
            font-size: 13px;
        }

        /* Document Layout */
        .layout-body {
            display: flex;
            margin-top: 10px;
            border-top: 2px solid #000;
            border-bottom: 2px solid #000;
        }

        /* Left Column - Officials */
        .left-officials-panel {
            display: flex;
            min-height: 690px;
            flex-direction: column;
            width: 30%;
            border-right: 2px solid #000;
            padding: 18px 13px 22px 0;
            font-size: 12px;
            text-align: center;
        }

        .officials-list {
            display: grid;
            gap: 4px;
            flex: 1 1 auto;
            align-content: start;
        }

        .official-group-label {
            margin: 2px 0 8px;
            border-bottom: 1px solid #000;
            padding-bottom: 5px;
            font-size: 10px;
            font-weight: bold;
            letter-spacing: 0.3px;
            text-transform: uppercase;
        }

        .official-group-label.sk {
            margin-top: 12px;
        }

        .official-title {
            font-weight: bold;
            margin-top: 3px;
            line-height: 1.16;
            text-transform: uppercase;
        }

        .official-role {
            font-style: normal;
            margin-bottom: 3px;
            font-size: 10px;
            line-height: 1.08;
            text-transform: uppercase;
        }

        .left-panel-footer {
            display: grid;
            gap: 14px;
            margin-top: 24px;
            padding: 0 10px;
        }

        .dry-seal-note {
            border: 1px solid #000;
            padding: 11px 8px;
            font-size: 8.2px;
            font-weight: bold;
            line-height: 1.35;
            text-transform: uppercase;
        }

        .barangay-motto {
            margin: 0;
            font-size: 8px;
            font-weight: bold;
            line-height: 1.25;
        }

        /* Right Column - Content */
        .right-content-panel {
            position: relative;
            overflow: hidden;
            width: 70%;
            padding: 28px 0 26px 24px;
        }

        .right-content-panel.full-width {
            width: 100%;
            padding: 28px 0 26px;
        }

        .content-watermark {
            position: absolute;
            top: 50%;
            left: 50%;
            z-index: 0;
            width: 430px;
            max-width: 88%;
            opacity: 0.055;
            transform: translate(-50%, -50%);
            pointer-events: none;
            user-select: none;
        }

        .right-content-panel > *:not(.content-watermark) {
            position: relative;
            z-index: 1;
        }

        .document-title {
            text-align: center;
            font-size: 27px;
            font-weight: bold;
            text-transform: uppercase;
            margin-bottom: 22px;
            letter-spacing: 4px;
        }

        .document-title.indigency-title {
            margin-top: 34px !important;
            margin-bottom: 42px !important;
            font-size: 31px;
            font-style: normal;
            text-decoration: none;
            letter-spacing: 1px;
        }

        .document-title.construction-title {
            font-size: 24px;
            letter-spacing: 1px;
            line-height: 1.25;
        }

        .salutation {
            font-weight: bold;
            font-size: 17px;
            margin-bottom: 26px;
            text-transform: uppercase;
        }

        .letter-body {
            font-size: 15.5px;
            line-height: 1.72;
            text-align: justify;
            text-indent: 50px;
            margin: 0 0 22px;
        }

        .editable {
            font-weight: bold;
            text-decoration: underline;
            background-color: #fff3cd;
            cursor: text;
            padding: 0 4px;
            border-radius: 3px;
            outline: none;
        }

        .editable:focus {
            background-color: #cff4fc;
            border-bottom: 2px solid #0dcaf0;
            text-decoration: none;
        }

        .signature-row {
            display: flex;
            align-items: flex-end;
            justify-content: space-between;
            gap: 36px;
            margin-top: 76px;
        }

        .signature-block {
            text-align: center;
            width: 235px;
        }

        .signature-block.official {
            width: 270px;
        }

        .signature-line {
            font-weight: bold;
            text-transform: uppercase;
            margin-bottom: 2px;
            border-bottom: 1px solid #000;
            padding: 0 8px 2px;
            font-size: 14px;
            line-height: 1.15;
            white-space: nowrap;
        }

        .signature-block.official .signature-line {
            font-size: 12.6px;
        }

        .signature-role {
            font-size: 13px;
        }

        .clearance-signature-row {
            margin-top: 58px;
        }

        .clearance-extra {
            margin-top: 34px;
            width: 330px;
        }

        .tax-field {
            display: grid;
            grid-template-columns: 132px 10px 1fr;
            align-items: end;
            gap: 7px;
            margin-bottom: 7px;
            font-size: 13.5px;
        }

        .tax-field .blank-line {
            display: block;
            height: 15px;
            border-bottom: 1px solid #000;
        }

        .thumbmark-group {
            display: grid;
            width: 210px;
            grid-template-columns: 1fr 1fr;
            margin-top: 34px;
            border: 1px solid #000;
        }

        .thumbmark-box {
            height: 76px;
        }

        .thumbmark-box + .thumbmark-box {
            border-left: 1px solid #000;
        }

        .thumbmark-labels {
            display: grid;
            width: 210px;
            grid-template-columns: 1fr 1fr;
            margin-top: 6px;
            font-size: 12px;
            font-weight: bold;
            text-align: center;
        }

        .thumbmark-note {
            width: 210px;
            font-size: 12px;
            font-weight: bold;
            text-align: center;
        }

        .residency-content {
            position: relative;
            overflow: hidden;
            min-height: auto;
            padding: 0;
        }

        .residency-content .content-watermark {
            width: 430px;
        }

        .residency-salutation {
            margin: 0 0 28px;
            font-size: 17px;
            font-weight: bold;
            text-transform: uppercase;
        }

        .residency-body {
            margin: 0 0 22px;
            font-size: 15.5px;
            line-height: 1.72;
            text-align: justify;
            text-indent: 50px;
        }

        .residency-body.no-indent {
            text-indent: 0;
        }

        .residency-signatures {
            display: flex;
            align-items: flex-end;
            justify-content: space-between;
            gap: 34px;
            margin-top: 72px;
        }

        .specimen-signature {
            width: 210px;
            font-size: 14px;
        }

        .specimen-line {
            display: block;
            width: 160px;
            margin-top: 44px;
            border-bottom: 1px solid #000;
        }

        .residency-official-signature {
            width: 260px;
            text-align: center;
            font-size: 13px;
        }

        .residency-official-signature strong {
            display: block;
            border-bottom: 1px solid #000;
            font-size: 12.6px;
            text-transform: uppercase;
        }

        .indigency-content {
            position: relative;
            overflow: hidden;
            min-height: auto;
            padding: 0;
        }

        .indigency-content .content-watermark {
            width: 430px;
            opacity: 0.085;
        }

        .indigency-salutation {
            margin: 0 0 28px;
            font-family: Arial, sans-serif;
            font-size: 16px;
            font-style: normal;
            text-transform: none;
        }

        .indigency-body {
            position: relative;
            z-index: 1;
            margin: 0 0 18px;
            font-family: Arial, sans-serif;
            font-size: 15.5px;
            line-height: 1.65;
            text-align: justify;
            text-indent: 42px;
        }

        .indigency-body.issued {
            margin-top: 36px;
        }

        .indigency-emphasis {
            font-weight: bold;
        }

        .indigency-signature {
            position: relative;
            z-index: 1;
            width: 260px;
            margin: 64px 0 0 auto;
            text-align: center;
            font-family: Arial, sans-serif;
            font-size: 13px;
        }

        .indigency-signature strong {
            display: block;
            border-bottom: 1px solid #000;
            font-size: 12.6px;
            text-transform: uppercase;
        }

        .business-content {
            position: relative;
            z-index: 1;
            font-family: Arial, sans-serif;
        }

        .business-intro {
            margin: 0 0 12px;
            font-size: 12px;
        }

        .business-certify {
            margin: 0 0 22px;
            font-size: 12px;
            font-weight: 700;
            text-align: center;
        }

        .business-fields {
            display: grid;
            gap: 10px;
            width: 78%;
            margin: 0 auto 18px;
            text-align: center;
        }

        .business-line-value {
            min-height: 20px;
            border-bottom: 1px solid #000;
            font-size: 13px;
            font-weight: bold;
            line-height: 1.3;
        }

        .business-line-label {
            margin-top: -8px;
            font-size: 10px;
        }

        .business-paragraph {
            margin: 0 0 13px;
            font-size: 11.5px;
            line-height: 1.45;
            text-align: justify;
        }

        .business-check-row {
            display: grid;
            grid-template-columns: 54px 1fr;
            gap: 12px;
            margin: 0 0 8px;
            font-size: 11px;
            line-height: 1.35;
        }

        .business-check {
            display: flex;
            align-items: flex-start;
            justify-content: center;
            gap: 8px;
            font-weight: bold;
        }

        .business-issued {
            margin: 14px 0 30px;
            font-size: 11.5px;
            font-weight: bold;
            text-align: center;
        }

        .business-signature {
            width: 250px;
            margin: 0 0 18px auto;
            text-align: center;
            font-family: Arial, sans-serif;
            font-size: 12px;
        }

        .business-signature strong {
            display: block;
            border-bottom: 1px solid #000;
            font-size: 12.5px;
            text-transform: uppercase;
        }

        .business-note {
            margin: 0 0 12px;
            font-size: 10px;
            font-style: italic;
        }

        .business-paid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 26px;
            font-size: 10px;
        }

        .business-paid h4 {
            margin: 0 0 8px;
            font-size: 10px;
        }

        .business-paid-row {
            display: grid;
            grid-template-columns: 86px 1fr;
            align-items: end;
            gap: 8px;
            margin-bottom: 4px;
        }

        .business-paid-line {
            display: block;
            height: 13px;
            border-bottom: 1px solid #000;
        }

        .construction-content {
            position: relative;
            z-index: 1;
            font-size: 13px;
            line-height: 1.28;
        }

        .construction-lead {
            margin: 0 0 14px;
            text-align: justify;
            text-indent: 34px;
        }

        .construction-lead strong {
            font-size: 16px;
            letter-spacing: 3px;
        }

        .construction-info {
            display: grid;
            grid-template-columns: 146px 10px 1fr;
            gap: 6px;
            margin-bottom: 3px;
            font-size: 13px;
        }

        .construction-info strong {
            font-size: 14px;
        }

        .construction-section-title {
            margin: 16px 0 6px;
            font-size: 14px;
            font-weight: bold;
        }

        .construction-purpose-grid,
        .construction-status-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 2px 28px;
            margin-bottom: 12px;
            font-size: 13px;
        }

        .construction-mark {
            display: inline-block;
            min-width: 44px;
            border-bottom: 1px solid #000;
            text-align: center;
            font-weight: bold;
        }

        .construction-other-line {
            display: inline-block;
            min-width: 150px;
            border-bottom: 1px solid #000;
        }

        .construction-conditions {
            margin: 0;
            padding: 0;
            list-style: none;
            font-size: 13px;
        }

        .construction-conditions li {
            display: grid;
            grid-template-columns: 60px 1fr;
            gap: 8px;
            margin-bottom: 2px;
        }

        .construction-note {
            margin: 12px 0 14px;
            text-align: justify;
            text-indent: 34px;
        }

        .construction-issued {
            margin: 0 0 54px;
            font-size: 13px;
            text-align: justify;
        }

        .construction-issued strong {
            font-size: 16px;
            letter-spacing: 1px;
        }

        .construction-signature {
            width: 255px;
            margin-left: auto;
            text-align: center;
        }

        .construction-signature strong {
            display: block;
            border-bottom: 1px solid #000;
            font-size: 12.6px;
            text-transform: uppercase;
        }

        .summary-document {
            position: relative;
            z-index: 1;
            font-size: 14px;
            line-height: 1.55;
        }

        .summary-document h3 {
            margin: 0 0 18px;
            font-size: 17px;
            text-align: center;
            text-transform: uppercase;
        }

        .summary-grid {
            display: grid;
            grid-template-columns: 155px 10px 1fr;
            gap: 8px;
            margin-bottom: 8px;
        }

        .summary-grid strong {
            text-transform: uppercase;
        }

        .summary-box {
            min-height: 92px;
            margin: 10px 0 18px;
            border: 1px solid #000;
            padding: 10px;
            text-align: justify;
        }

        .id-card-preview {
            display: grid;
            grid-template-columns: 118px 1fr;
            gap: 18px;
            margin: 14px 0 24px;
            border: 2px solid #000;
            padding: 16px;
        }

        .id-photo-box {
            display: grid;
            height: 120px;
            place-items: center;
            border: 1px solid #000;
            font-size: 12px;
            text-align: center;
        }

        .summary-signature {
            width: 255px;
            margin: 62px 0 0 auto;
            text-align: center;
        }

        .summary-signature strong {
            display: block;
            border-bottom: 1px solid #000;
            font-size: 12.6px;
            text-transform: uppercase;
        }

        .form-document-table {
            display: grid;
            border: 2px solid #000;
            font-family: Arial, sans-serif;
            font-size: 10.5px;
        }

        .form-row {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            border-bottom: 1px solid #000;
        }

        .form-row:last-child {
            border-bottom: 0;
        }

        .form-cell {
            min-height: 44px;
            border-right: 1px solid #000;
            padding: 5px;
        }

        .form-cell:last-child {
            border-right: 0;
        }

        .form-cell.full {
            grid-column: 1 / -1;
        }

        .form-cell.tall {
            min-height: 112px;
        }

        .form-label {
            display: block;
            margin-bottom: 8px;
            font-size: 8.5px;
            font-weight: bold;
            text-transform: uppercase;
        }

        .barangay-id-cards {
            display: grid;
            gap: 14px;
            font-family: Arial, sans-serif;
        }

        .barangay-id-card {
            overflow: hidden;
            border: 2px solid #1d2d4f;
            border-radius: 8px;
            min-height: 238px;
            background: linear-gradient(135deg, #ffffff 0%, #ffffff 55%, #e9eef8 56%, #f7f9fe 100%);
        }

        .barangay-id-card-header {
            padding: 10px 12px 6px;
            text-align: center;
            font-size: 10px;
            line-height: 1.15;
        }

        .barangay-id-card-header strong {
            display: block;
            font-size: 14px;
            text-transform: uppercase;
        }

        .barangay-id-front-body {
            display: grid;
            grid-template-columns: 112px 1fr;
            gap: 12px;
            padding: 6px 14px 12px;
        }

        .barangay-id-photo {
            display: grid;
            height: 112px;
            place-items: center;
            border: 1px solid #777;
            background: #f8fafc;
            font-size: 11px;
            text-align: center;
        }

        .barangay-id-name {
            align-self: end;
            border-bottom: 3px solid #1d2d4f;
            padding-bottom: 5px;
            font-size: 20px;
            font-weight: 900;
            text-align: center;
            text-transform: uppercase;
        }

        .barangay-id-role {
            margin-top: 8px;
            background: #1d4ea3;
            color: #fff;
            padding: 6px;
            font-size: 12px;
            font-weight: bold;
            text-align: center;
            text-transform: uppercase;
        }

        .barangay-id-address {
            margin-top: 10px;
            border-bottom: 1px solid #000;
            padding-bottom: 4px;
            font-size: 10px;
            text-align: center;
            text-transform: uppercase;
        }

        .barangay-id-info-table {
            margin: 14px;
            border: 2px solid #1d4ea3;
            font-size: 10px;
        }

        .barangay-id-info-row {
            display: grid;
            grid-template-columns: 118px 1fr;
            border-bottom: 1px solid #1d4ea3;
        }

        .barangay-id-info-row:last-child {
            border-bottom: 0;
        }

        .barangay-id-info-row span {
            padding: 5px;
        }

        .barangay-id-info-row span:first-child {
            background: #1d4ea3;
            color: #fff;
            font-weight: bold;
            text-transform: uppercase;
        }

        .barangay-id-emergency {
            margin: 12px 14px;
            text-align: center;
            font-size: 10px;
        }

        .barangay-id-valid {
            margin: 12px auto 30px;
            width: 220px;
            border-bottom: 1px solid #000;
            text-align: center;
            font-weight: bold;
            text-transform: uppercase;
        }

        .id-print-page {
            width: 820px;
            margin: 0 auto;
            padding: 28px;
            background: #fff;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
            font-family: Arial, sans-serif;
        }

        .id-print-grid {
            display: flex;
            flex-wrap: wrap;
            gap: 18px;
            align-items: flex-start;
            justify-content: center;
        }

        .actual-id-card {
            position: relative;
            overflow: hidden;
            width: 3.375in;
            height: 2.125in;
            border: 1.5px solid #1f2937;
            border-radius: 8px;
            background: linear-gradient(135deg, #ffffff 0%, #ffffff 56%, #e8eefb 57%, #f7f9ff 100%);
            color: #111827;
            box-sizing: border-box;
        }

        .actual-id-card::before {
            content: "";
            position: absolute;
            inset: auto -32px -48px auto;
            width: 180px;
            height: 180px;
            border-radius: 999px;
            background: rgba(29, 78, 163, 0.08);
            pointer-events: none;
        }

        .actual-id-header {
            position: relative;
            z-index: 1;
            display: grid;
            grid-template-columns: 42px 1fr;
            gap: 7px;
            align-items: center;
            padding: 8px 10px 4px;
            text-align: center;
            font-size: 6.8px;
            line-height: 1.1;
        }

        .actual-id-logo {
            width: 38px;
            height: 38px;
            object-fit: contain;
        }

        .actual-id-header strong {
            display: block;
            margin-top: 2px;
            font-size: 10px;
            text-transform: uppercase;
        }

        .actual-id-body {
            position: relative;
            z-index: 1;
            display: grid;
            grid-template-columns: 78px 1fr;
            gap: 9px;
            padding: 3px 11px 8px;
        }

        .actual-id-photo {
            display: grid;
            height: 78px;
            place-items: center;
            border: 1px solid #9ca3af;
            background: #f8fafc;
            font-size: 8px;
            text-align: center;
        }

        .actual-id-name {
            margin-top: 11px;
            border-bottom: 2px solid #1d4ea3;
            padding-bottom: 3px;
            font-size: 14px;
            font-weight: 900;
            line-height: 1.05;
            text-align: center;
            text-transform: uppercase;
        }

        .actual-id-role {
            margin-top: 5px;
            background: #1d4ea3;
            color: #fff;
            padding: 4px 6px;
            font-size: 8.5px;
            font-weight: bold;
            text-align: center;
            text-transform: uppercase;
        }

        .actual-id-number {
            margin-top: 6px;
            font-size: 7px;
            text-align: center;
        }

        .actual-id-address {
            position: absolute;
            left: 11px;
            right: 11px;
            bottom: 8px;
            z-index: 1;
            border-top: 1px solid #1f2937;
            padding-top: 3px;
            font-size: 7px;
            text-align: center;
            text-transform: uppercase;
        }

        .actual-id-back-header {
            background: #1d4ea3;
            color: #fff;
            padding: 4px;
            font-size: 7px;
            font-weight: bold;
            text-align: center;
            text-transform: uppercase;
        }

        .actual-id-info {
            position: relative;
            z-index: 1;
            margin: 8px 12px 6px;
            border: 1.5px solid #1d4ea3;
            font-size: 7px;
        }

        .actual-id-info-row {
            display: grid;
            grid-template-columns: 82px 1fr;
            border-bottom: 1px solid #1d4ea3;
        }

        .actual-id-info-row:last-child {
            border-bottom: 0;
        }

        .actual-id-info-row span {
            padding: 3px 4px;
        }

        .actual-id-info-row span:first-child {
            background: #1d4ea3;
            color: #fff;
            font-weight: bold;
            text-transform: uppercase;
        }

        .actual-id-emergency {
            position: relative;
            z-index: 1;
            margin: 8px 12px 6px;
            font-size: 7px;
            line-height: 1.25;
            text-align: center;
        }

        .actual-id-valid {
            position: relative;
            z-index: 1;
            width: 150px;
            margin: 8px auto 13px;
            border-bottom: 1px solid #111827;
            padding-bottom: 2px;
            font-size: 9px;
            font-weight: bold;
            text-align: center;
            text-transform: uppercase;
        }

        .actual-id-signature {
            position: absolute;
            left: 20px;
            right: 20px;
            bottom: 9px;
            z-index: 1;
            text-align: center;
            font-size: 7px;
        }

        .actual-id-signature strong {
            display: block;
            border-bottom: 1px solid #111827;
            font-size: 8px;
            text-transform: uppercase;
        }

        /* Interactive Toolbar (Hides when printing) */
        .print-toolbar {
            width: 800px;
            margin: 0 auto 20px auto;
            background: #212529;
            color: white;
            padding: 15px 20px;
            border-radius: 8px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .print-btn {
            background: #3f9f25;
            color: white;
            border: none;
            padding: 8px 16px;
            font-weight: bold;
            border-radius: 4px;
            cursor: pointer;
            font-size: 15px;
        }

        .print-btn:hover {
            background: #2e6f40;
        }

        @media print {
            body {
                background: #fff;
                padding: 0;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }

            .print-toolbar {
                display: none !important;
            }

            .certificate-container {
                box-shadow: none;
                width: 100%;
                padding: 0;
            }

            .id-print-page {
                box-shadow: none;
                width: auto;
                padding: 0;
            }

            .editable {
                background-color: transparent !important;
                border: none !important;
            }
        }
    </style>
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

    <!-- CERTIFICATE TEMPLATE -->
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
            <!-- LEFT COLUMN: OFFICIALS -->
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

            <!-- RIGHT COLUMN: CONTENT -->
            <div class="right-content-panel <?php echo ($is_incident_report || $is_cedula) ? 'full-width' : ''; ?>">
                <img class="content-watermark" src="../assets/img/Barangay_Makiling_Seal.png" alt="" aria-hidden="true">

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
                        <span class="editable" contenteditable="true"><?php echo htmlspecialchars($age_phrase); ?></span>,
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
