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
            width: 800px;
            min-height: 1050px;
            margin: 0 auto;
            padding: 40px;
            background: #fff;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
        }

        /* Header section */
        .header-section {
            text-align: center;
            margin-bottom: 20px;
            position: relative;
        }

        .header-section h2 {
            margin: 0;
            font-size: 22px;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .header-section p {
            margin: 2px 0;
            font-size: 14px;
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
            width: 30%;
            border-right: 2px solid #000;
            padding: 15px 15px 15px 0;
            font-size: 11px;
            text-align: center;
        }

        .official-title {
            font-weight: bold;
            margin-top: 10px;
            text-transform: uppercase;
        }

        .official-role {
            font-style: normal;
            margin-bottom: 10px;
            font-size: 10px;
        }

        /* Right Column - Content */
        .right-content-panel {
            width: 70%;
            padding: 25px 0 25px 25px;
        }

        .document-title {
            text-align: center;
            font-size: 28px;
            font-weight: bold;
            text-transform: uppercase;
            margin-bottom: 30px;
            letter-spacing: 3px;
        }

        .salutation {
            font-weight: bold;
            font-size: 18px;
            margin-bottom: 20px;
            text-transform: uppercase;
        }

        .letter-body {
            font-size: 16px;
            line-height: 1.8;
            text-align: justify;
            text-indent: 50px;
            margin-bottom: 20px;
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

        .signature-block {
            margin-top: 80px;
            float: right;
            text-align: center;
            width: 250px;
        }

        .signature-line {
            font-weight: bold;
            text-transform: uppercase;
            margin-bottom: 2px;
            border-bottom: 1px solid #000;
            padding-bottom: 2px;
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
            }

            .print-toolbar {
                display: none !important;
            }

            .certificate-container {
                box-shadow: none;
                width: 100%;
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

    <!-- CERTIFICATE TEMPLATE -->
    <div class="certificate-container">

        <div class="header-section">
            <p>Republic of the Philippines</p>
            <p>Province of Laguna</p>
            <p>City of Calamba</p>
            <h2 style="margin-top: 5px;">BARANGAY MAKILING</h2>
            <p style="font-weight: bold; margin-top: 10px; font-size: 16px;">OFFICE OF THE PUNONG BARANGAY</p>
        </div>

        <div class="document-title" style="margin-top: 20px; margin-bottom: 10px;">CERTIFICATE OF INDIGENCY</div>

        <div class="layout-body">

            <!-- LEFT COLUMN: OFFICIALS -->
            <div class="left-officials-panel">
                <div class="official-title">HON. ALONTO Y. DICAY</div>
                <div class="official-role">PUNONG BARANGAY</div>

                <div class="official-title">HON. MANUEL B. BATAPA</div>
                <div class="official-role">BARANGAY KAGAWAD</div>

                <div class="official-title">HON. RICSON Q. CAGAS</div>
                <div class="official-role">BARANGAY KAGAWAD</div>

                <div class="official-title">HON. MARLYN S. POSADAS</div>
                <div class="official-role">BARANGAY KAGAWAD</div>

                <div class="official-title">HON. ROWENA L. KEIL</div>
                <div class="official-role">BARANGAY KAGAWAD</div>

                <div class="official-title">HON. JOEBEL D. POSADAS</div>
                <div class="official-role">BARANGAY KAGAWAD</div>

                <div class="official-title">HON. MALA A. MAYO, JR.</div>
                <div class="official-role">SK CHAIRMAN</div>

                <div style="margin-top: 40px;">
                    <img src="../assets/img/Barangay_Makiling_Seal.png" alt="Seal" style="width: 100px; height: 100px; opacity: 0.9;">
                </div>

                <p style="font-size: 9px; margin-top: 20px; font-weight: bold;">"WHEN WE WORK TOGETHER, OUR<br>SUCCESS WILL BE MUCH BETTER"</p>
            </div>

            <!-- RIGHT COLUMN: CONTENT -->
            <div class="right-content-panel">

                <div class="salutation">TO WHOM IT MAY CONCERN:</div>

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

                <div class="signature-block">
                    <div class="signature-line">HON. ALONTO Y. DICAY</div>
                    <div style="font-size: 14px;">Punong Barangay</div>
                </div>

                <div class="signature-block" style="float: left; margin-top: 100px; width: 220px;">
                    <div class="signature-line" contenteditable="true" style="border-bottom: 1px solid #000; margin-bottom: 2px;"><?php echo htmlspecialchars($fullname); ?></div>
                    <div style="font-size: 14px;" contenteditable="true">Applicant</div>
                </div>

            </div>
        </div>
    </div>

</body>

</html>