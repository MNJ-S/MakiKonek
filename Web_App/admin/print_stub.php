<?php
/**
 * print_stub.php
 * Resident-facing printable claim stub for a service request.
 * URL:  print_stub.php?ref=REFERENCE_NO
 * Also accepts: print_stub.php?request_id=123 (numeric fallback)
 *
 * NOTE: This page assumes the resident session key is $_SESSION['user_id']
 * (matching service_requests.user_id / user_profiles.user_id used elsewhere
 * in the system). Adjust the session key below if your resident auth uses
 * a different name (e.g. $_SESSION['resident_id']).
 */

session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login_resident.php");
    exit();
}

require_once __DIR__ . '/../includes/db_connect.php';

$current_user_id = (int)$_SESSION['user_id'];

$ref        = isset($_GET['ref']) ? trim($_GET['ref']) : '';
$request_id = isset($_GET['request_id']) ? (int)$_GET['request_id'] : 0;

$request = null;
$lookup_error = '';

if ($ref === '' && $request_id <= 0) {
    $lookup_error = 'No reference number or request ID was provided.';
} else {
    $base_query = "
        SELECT sr.*, dt.name AS document_type,
               p.first_name, p.middle_name, p.last_name, p.suffix
        FROM service_requests sr
        JOIN document_types dt ON sr.document_type_id = dt.document_type_id
        JOIN user_profiles p ON sr.user_id = p.user_id
        WHERE %s
        LIMIT 1
    ";

    if ($ref !== '') {
        $stmt = mysqli_prepare($conn, sprintf($base_query, 'sr.reference_no = ?'));
        mysqli_stmt_bind_param($stmt, "s", $ref);
    } else {
        $stmt = mysqli_prepare($conn, sprintf($base_query, 'sr.request_id = ?'));
        mysqli_stmt_bind_param($stmt, "i", $request_id);
    }

    mysqli_stmt_execute($stmt);
    $request = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
    mysqli_stmt_close($stmt);

    if (!$request) {
        $lookup_error = 'We couldn\'t find a service request that matches that reference number.';
    } elseif ((int)$request['user_id'] !== $current_user_id) {
        // Prevent a resident from viewing another resident's stub.
        $request = null;
        $lookup_error = 'This claim stub does not belong to your account.';
    }
}

/**
 * Picks the first non-empty value from a row given a list of possible
 * column names. Lets this page work even if the exact payment/amount
 * column name differs without altering the database structure.
 */
function pick(array $row, array $keys, $default = '') {
    foreach ($keys as $key) {
        if (!empty($row[$key])) {
            return $row[$key];
        }
    }
    return $default;
}

if ($request) {
    $fullname = trim(
        $request['first_name'] . ' ' .
        (!empty($request['middle_name']) ? substr($request['middle_name'], 0, 1) . '. ' : '') .
        $request['last_name'] .
        (!empty($request['suffix']) ? ' ' . $request['suffix'] : '')
    );

    $document_type   = $request['document_type'] ?? 'N/A';
    $reference_no    = $request['reference_no'] ?? ('REQ-' . str_pad((string)$request['request_id'], 6, '0', STR_PAD_LEFT));
    $status          = ucwords(str_replace('_', ' ', $request['status'] ?? 'Pending'));
    $payment_method  = pick($request, ['payment_method', 'mode_of_payment', 'payment_mode', 'payment_type'], 'Not specified');
    $amount          = pick($request, ['amount', 'fee', 'total_amount', 'payment_amount', 'total_fee'], '');
    $date_generated  = date('F d, Y g:i A');
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Claim Stub | MakiKonek</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="icon" href="../assets/img/Barangay_Makiling_Seal.png" type="image/png">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@500;700;800&family=Inter:wght@400;500;600&family=IBM+Plex+Mono:wght@500;600&display=swap" rel="stylesheet">

    <style>
        :root {
            --mk-green: #0E9F6E;
            --mk-green-dark: #0B7A55;
            --mk-blue: #1565D8;
            --mk-blue-dark: #0F4AA8;
            --mk-ink: #1A2333;
            --mk-muted: #64748B;
            --mk-line: #DCE3E8;
            --mk-paper: #FFFFFF;
            --mk-bg: #EEF4F2;
            --mk-amber: #B7791F;
        }

        * { box-sizing: border-box; }

        body {
            margin: 0;
            padding: 0;
            background: var(--mk-bg);
            font-family: 'Inter', sans-serif;
            color: var(--mk-ink);
        }

        /* ---------- Toolbar (hidden on print) ---------- */
        .print-toolbar {
            position: sticky;
            top: 0;
            z-index: 10;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
            padding: 14px 24px;
            background: var(--mk-ink);
            color: #fff;
        }

        .print-toolbar h5 {
            margin: 0;
            font-family: 'Manrope', sans-serif;
            font-weight: 700;
            font-size: 16px;
        }

        .print-toolbar small {
            color: #A9B4C4;
            font-size: 12.5px;
        }

        .toolbar-actions {
            display: flex;
            gap: 10px;
        }

        .toolbar-actions button {
            display: inline-flex;
            align-items: center;
            gap: 7px;
            border: none;
            border-radius: 8px;
            padding: 9px 16px;
            font-size: 14px;
            font-weight: 600;
            font-family: 'Inter', sans-serif;
            cursor: pointer;
            transition: transform .12s ease, opacity .12s ease;
        }

        .toolbar-actions button:active { transform: scale(0.97); }

        .btn-back {
            background: transparent;
            color: #E2E8F0;
            border: 1px solid #3B4658 !important;
        }
        .btn-back:hover { background: #232E42; }

        .btn-print {
            background: linear-gradient(90deg, var(--mk-green), var(--mk-blue));
            color: #fff;
        }
        .btn-print:hover { opacity: 0.92; }

        /* ---------- Page / stub layout ---------- */
        .stub-page {
            display: flex;
            justify-content: center;
            padding: 44px 20px 60px;
        }

        .claim-stub {
            width: 100%;
            max-width: 720px;
            background: var(--mk-paper);
            border-radius: 22px;
            overflow: hidden;
            box-shadow: 0 18px 40px -16px rgba(15, 35, 60, 0.28);
            border: 1px solid var(--mk-line);
        }

        .stub-header {
            display: flex;
            align-items: center;
            gap: 14px;
            padding: 20px 26px;
            background: linear-gradient(100deg, var(--mk-green-dark), var(--mk-blue-dark));
            color: #fff;
        }

        .stub-logo {
            width: 46px;
            height: 46px;
            border-radius: 50%;
            background: #fff;
            object-fit: contain;
            padding: 4px;
            flex-shrink: 0;
        }

        .stub-header-text { flex: 1; min-width: 0; }

        .stub-header-text strong {
            display: block;
            font-family: 'Manrope', sans-serif;
            font-weight: 800;
            font-size: 19px;
            letter-spacing: 0.2px;
        }

        .stub-header-text span {
            display: block;
            font-size: 12.5px;
            color: #DCEFE7;
            margin-top: 2px;
        }

        .stub-badge {
            flex-shrink: 0;
            background: rgba(255, 255, 255, 0.16);
            border: 1px solid rgba(255, 255, 255, 0.4);
            color: #fff;
            font-family: 'Manrope', sans-serif;
            font-weight: 700;
            font-size: 11.5px;
            letter-spacing: 1.4px;
            padding: 7px 12px;
            border-radius: 999px;
        }

        .stub-status-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 12px 26px;
            background: #F7FAF9;
            border-bottom: 1px dashed var(--mk-line);
            font-size: 12.5px;
            color: var(--mk-muted);
        }

        .status-pill {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            font-weight: 700;
            font-size: 12px;
            padding: 4px 11px;
            border-radius: 999px;
            background: #E6F7EF;
            color: var(--mk-green-dark);
            text-transform: capitalize;
        }

        .status-pill::before {
            content: "";
            width: 6px;
            height: 6px;
            border-radius: 50%;
            background: currentColor;
        }

        /* ---------- Body: main info + perforation + side tab ---------- */
        .stub-body {
            display: grid;
            grid-template-columns: 1fr auto auto;
            align-items: stretch;
        }

        .stub-main {
            padding: 26px 26px 22px;
        }

        .stub-row {
            display: grid;
            grid-template-columns: 160px 1fr;
            gap: 10px;
            padding: 11px 0;
            border-bottom: 1px solid #EEF1F4;
        }
        .stub-row:last-child { border-bottom: none; }

        .stub-label {
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.6px;
            color: var(--mk-muted);
            align-self: center;
        }

        .stub-value {
            font-size: 15.5px;
            font-weight: 600;
            color: var(--mk-ink);
            align-self: center;
            word-break: break-word;
        }

        .stub-value.muted-note {
            font-size: 12.5px;
            font-weight: 500;
            color: var(--mk-muted);
        }

        /* perforation divider */
        .stub-perforation {
            position: relative;
            width: 1px;
            background-image: linear-gradient(var(--mk-line) 60%, transparent 0%);
            background-position: top;
            background-size: 2px 14px;
            background-repeat: repeat-y;
            margin: 0 2px;
        }

        .stub-perforation::before,
        .stub-perforation::after {
            content: "";
            position: absolute;
            left: 50%;
            transform: translateX(-50%);
            width: 22px;
            height: 22px;
            border-radius: 50%;
            background: var(--mk-bg);
            border: 1px solid var(--mk-line);
        }
        .stub-perforation::before { top: -11px; }
        .stub-perforation::after { bottom: -11px; }

        .stub-side {
            width: 92px;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            gap: 14px;
            padding: 22px 10px;
            background: #F7FAF9;
        }

        .stub-side-text {
            writing-mode: vertical-rl;
            transform: rotate(180deg);
            display: flex;
            align-items: center;
            gap: 10px;
            font-family: 'IBM Plex Mono', monospace;
            white-space: nowrap;
        }

        .stub-side-label {
            font-size: 10.5px;
            font-weight: 600;
            letter-spacing: 2px;
            color: var(--mk-muted);
        }

        .stub-side-ref {
            font-size: 17px;
            font-weight: 700;
            color: var(--mk-blue-dark);
        }

        .stub-barcode {
            width: 18px;
            height: 64px;
            background: repeating-linear-gradient(
                90deg,
                var(--mk-ink) 0px,
                var(--mk-ink) 2px,
                transparent 2px,
                transparent 4px,
                var(--mk-ink) 4px,
                var(--mk-ink) 5px,
                transparent 5px,
                transparent 8px
            );
            opacity: 0.75;
        }

        .stub-footer {
            padding: 16px 26px 22px;
            border-top: 1px dashed var(--mk-line);
            font-size: 12.5px;
            line-height: 1.55;
            color: var(--mk-muted);
        }

        .stub-footer strong { color: var(--mk-ink); }

        /* ---------- Error state ---------- */
        .error-card {
            width: 100%;
            max-width: 480px;
            background: var(--mk-paper);
            border-radius: 22px;
            border: 1px solid var(--mk-line);
            padding: 40px 32px;
            text-align: center;
            box-shadow: 0 18px 40px -16px rgba(15, 35, 60, 0.18);
        }

        .error-icon {
            width: 56px;
            height: 56px;
            border-radius: 50%;
            background: #FDECEC;
            color: #C0392B;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            margin: 0 auto 18px;
        }

        .error-card h3 {
            font-family: 'Manrope', sans-serif;
            font-size: 18px;
            margin: 0 0 8px;
        }

        .error-card p {
            font-size: 14px;
            color: var(--mk-muted);
            margin: 0 0 22px;
            line-height: 1.6;
        }

        .error-card .btn-back {
            background: var(--mk-ink);
            color: #fff;
            border: none !important;
        }

        /* ---------- Print rules ---------- */
        @media print {
            .no-print { display: none !important; }
            body { background: #fff; }
            .stub-page { padding: 0; }
            .claim-stub { box-shadow: none; border: 1px solid #ccc; max-width: 100%; }
        }

        @page {
            size: auto;
            margin: 10mm;
        }
    </style>
</head>

<body>

    <div class="print-toolbar no-print">
        <div>
            <h5><i class="bi bi-receipt"></i> Claim Stub</h5>
            <small><?php echo $request ? 'Reference No. ' . htmlspecialchars($reference_no) : 'Service Request Claim Stub'; ?></small>
        </div>
        <div class="toolbar-actions">
            <button class="btn-back" onclick="window.history.back()"><i class="bi bi-arrow-left"></i> Back</button>
            <?php if ($request): ?>
                <button class="btn-print" onclick="window.print()"><i class="bi bi-printer-fill"></i> Print Stub</button>
            <?php endif; ?>
        </div>
    </div>

    <div class="stub-page">

        <?php if ($request): ?>
            <div class="claim-stub">

                <div class="stub-header">
                    <img class="stub-logo" src="../assets/img/makikonek_logo.png" alt="MakiKonek"
                        onerror="this.onerror=null;this.src='../assets/img/Barangay_Makiling_Seal.png';">
                    <div class="stub-header-text">
                        <strong>MakiKonek</strong>
                        <span>Barangay Makiling &middot; City of Calamba, Laguna</span>
                    </div>
                    <div class="stub-badge">CLAIM STUB</div>
                </div>

                <div class="stub-status-row">
                    <span>Present this stub when claiming your document</span>
                    <span class="status-pill"><?php echo htmlspecialchars($status); ?></span>
                </div>

                <div class="stub-body">
                    <div class="stub-main">
                        <div class="stub-row">
                            <span class="stub-label">Requester Name</span>
                            <span class="stub-value"><?php echo htmlspecialchars($fullname); ?></span>
                        </div>
                        <div class="stub-row">
                            <span class="stub-label">Type of Request</span>
                            <span class="stub-value"><?php echo htmlspecialchars($document_type); ?></span>
                        </div>
                        <div class="stub-row">
                            <span class="stub-label">Reference Number</span>
                            <span class="stub-value"><?php echo htmlspecialchars($reference_no); ?></span>
                        </div>
                        <div class="stub-row">
                            <span class="stub-label">Date Generated</span>
                            <span class="stub-value"><?php echo htmlspecialchars($date_generated); ?></span>
                        </div>
                        <div class="stub-row">
                            <span class="stub-label">Payment Method</span>
                            <span class="stub-value">
                                <?php echo htmlspecialchars($payment_method); ?><?php if ($amount !== ''): ?>
                                    <span class="muted-note"> &middot; ₱<?php echo htmlspecialchars(number_format((float)$amount, 2)); ?></span>
                                <?php endif; ?>
                            </span>
                        </div>
                    </div>

                    <div class="stub-perforation"></div>

                    <div class="stub-side">
                        <div class="stub-barcode"></div>
                        <div class="stub-side-text">
                            <span class="stub-side-label">REF NO.</span>
                            <span class="stub-side-ref"><?php echo htmlspecialchars($reference_no); ?></span>
                        </div>
                    </div>
                </div>

                <div class="stub-footer">
                    <strong>Note:</strong> This claim stub serves as proof of your service request. Please bring a valid ID
                    matching the requester name above when claiming your document at the Barangay Hall.
                </div>
            </div>

        <?php else: ?>
            <div class="error-card">
                <div class="error-icon"><i class="bi bi-exclamation-triangle"></i></div>
                <h3>Claim Stub Not Found</h3>
                <p><?php echo htmlspecialchars($lookup_error); ?></p>
                <button class="btn-back" onclick="window.history.back()" style="width:100%; justify-content:center; display:inline-flex; align-items:center; gap:7px; padding:11px 16px; border-radius:8px; font-weight:600; cursor:pointer;">
                    <i class="bi bi-arrow-left"></i> Go Back
                </button>
            </div>
        <?php endif; ?>

    </div>

</body>

</html>
