<?php
session_start();

if (!isset($_SESSION['resident_id'])) {
    header('Location: ../login_reg.php');
    exit();
}

require_once __DIR__ . '/../includes/db_connect.php';

$resident_id = (int)$_SESSION['resident_id'];
$reference_no = trim((string)($_GET['ref'] ?? ''));
$request_id_input = $_GET['id'] ?? $_GET['request_id'] ?? null;
$request_id = filter_var($request_id_input, FILTER_VALIDATE_INT);
$request = null;
$error_message = '';

if ($reference_no === '' && !$request_id) {
    $error_message = 'No request reference or request ID was provided.';
} else {
    $query = "
        SELECT
            sr.request_id,
            sr.reference_no,
            sr.payment_method,
            sr.created_at,
            dt.name AS document_type,
            p.first_name,
            p.middle_name,
            p.last_name,
            p.suffix
        FROM service_requests sr
        JOIN document_types dt ON sr.document_type_id = dt.document_type_id
        JOIN user_profiles p ON sr.user_id = p.user_id
        WHERE sr.user_id = ?
          AND (
              (? <> '' AND sr.reference_no = ?)
              OR
              (? > 0 AND sr.request_id = ?)
          )
        LIMIT 1
    ";
    $stmt = mysqli_prepare($conn, $query);
    $request_id_value = $request_id ? (int)$request_id : 0;
    mysqli_stmt_bind_param(
        $stmt,
        'issii',
        $resident_id,
        $reference_no,
        $reference_no,
        $request_id_value,
        $request_id_value
    );
    mysqli_stmt_execute($stmt);
    $request = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt)) ?: null;

    if (!$request) {
        $error_message = 'The requested service record could not be found.';
    }
}

function claimStubName(array $request): string
{
    $reference = (string)$request['reference_no'];
    $submitted_name = $_SESSION['claim_stub_names'][$reference] ?? '';
    if (is_string($submitted_name) && trim($submitted_name) !== '') {
        return trim($submitted_name);
    }

    return trim(implode(' ', array_filter([
        $request['first_name'] ?? '',
        $request['middle_name'] ?? '',
        $request['last_name'] ?? '',
        $request['suffix'] ?? '',
    ])));
}

function claimStubPaymentMethod(?string $method): string
{
    return strtolower(trim((string)$method)) === 'online'
        ? 'Online Payment'
        : 'Cash on Pickup';
}

$requester_name = $request ? claimStubName($request) : '';
$payment_method = $request ? claimStubPaymentMethod($request['payment_method'] ?? '') : '';
$date_generated = $request && !empty($request['created_at'])
    ? date('F d, Y - h:i A', strtotime($request['created_at']))
    : '';
$auto_download = $request && isset($_GET['download']) && $_GET['download'] === '1';
$pdf_filename = $request
    ? 'MakiKonek-Claim-Stub-' . preg_replace('/[^A-Za-z0-9_-]/', '-', $request['reference_no']) . '.pdf'
    : 'MakiKonek-Claim-Stub.pdf';
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Claim Stub | MakiKonek</title>
    <link rel="icon" href="../assets/img/Barangay_Makiling_Seal.png" type="image/png">
    <style>
        :root {
            --green: #08783f;
            --green-dark: #075c34;
            --green-soft: #e9f7ef;
            --blue: #2458a6;
            --blue-soft: #edf4ff;
            --ink: #10213c;
            --muted: #64748b;
            --line: #dbe5df;
        }

        * {
            box-sizing: border-box;
        }

        body {
            min-height: 100vh;
            margin: 0;
            color: var(--ink);
            background:
                radial-gradient(circle at 10% 10%, rgba(8, 120, 63, 0.08), transparent 28%),
                radial-gradient(circle at 90% 90%, rgba(36, 88, 166, 0.08), transparent 30%),
                #f5f8f6;
            font-family: Arial, Helvetica, sans-serif;
        }

        .page-shell {
            width: min(940px, calc(100% - 32px));
            margin: 30px auto;
        }

        .toolbar {
            display: flex;
            justify-content: space-between;
            gap: 12px;
            margin-bottom: 18px;
        }

        .toolbar a,
        .toolbar button {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-height: 44px;
            padding: 0 20px;
            border: 1px solid var(--green);
            border-radius: 12px;
            color: var(--green);
            background: #fff;
            font: inherit;
            font-weight: 800;
            text-decoration: none;
            cursor: pointer;
        }

        .toolbar button {
            color: #fff;
            background: var(--green);
        }

        .stub {
            position: relative;
            overflow: hidden;
            border: 1px solid #cfe1d6;
            border-radius: 24px;
            background: #fff;
            box-shadow: 0 24px 60px rgba(16, 33, 60, 0.12);
        }

        .stub::before {
            position: absolute;
            inset: 0 auto 0 0;
            width: 10px;
            background: linear-gradient(180deg, var(--green), var(--blue));
            content: "";
        }

        .stub-header {
            display: grid;
            grid-template-columns: auto 1fr auto;
            gap: 18px;
            align-items: center;
            padding: 28px 34px 24px 42px;
            border-bottom: 1px dashed #b8c9c0;
            background: linear-gradient(110deg, #fff 52%, var(--blue-soft));
        }

        .stub-logo {
            width: 86px;
            height: 68px;
            object-fit: contain;
        }

        .brand-copy small,
        .brand-copy strong {
            display: block;
        }

        .brand-copy small {
            margin-bottom: 4px;
            color: var(--muted);
            font-size: 12px;
            font-weight: 700;
            letter-spacing: 0.08em;
            text-transform: uppercase;
        }

        .brand-copy strong {
            color: var(--green-dark);
            font-size: 28px;
            line-height: 1.05;
        }

        .claim-label {
            padding: 12px 16px;
            border-radius: 999px;
            color: #fff;
            background: var(--blue);
            font-size: 13px;
            font-weight: 900;
            letter-spacing: 0.08em;
            text-transform: uppercase;
        }

        .stub-body {
            padding: 30px 34px 34px 42px;
        }

        .stub-intro {
            display: flex;
            justify-content: space-between;
            gap: 20px;
            align-items: end;
            margin-bottom: 24px;
        }

        .stub-intro h1 {
            margin: 0 0 6px;
            font-size: 30px;
        }

        .stub-intro p {
            margin: 0;
            color: var(--muted);
            font-size: 14px;
        }

        .reference {
            min-width: 210px;
            padding: 13px 18px;
            border: 1px solid #b9d8c5;
            border-radius: 14px;
            background: var(--green-soft);
            text-align: center;
        }

        .reference span,
        .reference strong {
            display: block;
        }

        .reference span {
            color: var(--green-dark);
            font-size: 11px;
            font-weight: 800;
            letter-spacing: 0.08em;
            text-transform: uppercase;
        }

        .reference strong {
            margin-top: 4px;
            color: var(--ink);
            font-size: 21px;
            letter-spacing: 0.04em;
        }

        .detail-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 14px;
        }

        .detail {
            min-height: 94px;
            padding: 17px 18px;
            border: 1px solid var(--line);
            border-radius: 15px;
            background: #fbfdfc;
        }

        .detail span,
        .detail strong {
            display: block;
        }

        .detail span {
            margin-bottom: 8px;
            color: var(--muted);
            font-size: 11px;
            font-weight: 800;
            letter-spacing: 0.07em;
            text-transform: uppercase;
        }

        .detail strong {
            font-size: 18px;
            line-height: 1.25;
        }

        .stub-footer {
            display: flex;
            justify-content: space-between;
            gap: 20px;
            align-items: center;
            margin-top: 24px;
            padding: 18px 20px;
            border-radius: 15px;
            color: #fff;
            background: linear-gradient(100deg, var(--green-dark), var(--green), var(--blue));
        }

        .stub-footer strong,
        .stub-footer span {
            display: block;
        }

        .stub-footer strong {
            margin-bottom: 3px;
            font-size: 15px;
        }

        .stub-footer span {
            font-size: 12px;
            opacity: 0.9;
        }

        .seal {
            width: 54px;
            height: 54px;
            object-fit: contain;
            filter: drop-shadow(0 4px 8px rgba(0, 0, 0, 0.18));
        }

        .error-card {
            padding: 50px 30px;
            border: 1px solid #f1caca;
            border-radius: 20px;
            background: #fff;
            box-shadow: 0 18px 50px rgba(16, 33, 60, 0.1);
            text-align: center;
        }

        .error-card h1 {
            margin: 0 0 10px;
            color: #9f2d2d;
        }

        .error-card p {
            margin: 0;
            color: var(--muted);
        }

        @media (max-width: 680px) {
            .stub-header {
                grid-template-columns: auto 1fr;
            }

            .claim-label {
                grid-column: 1 / -1;
                justify-self: start;
            }

            .stub-intro {
                align-items: stretch;
                flex-direction: column;
            }

            .reference {
                min-width: 0;
            }

            .detail-grid {
                grid-template-columns: 1fr;
            }
        }

        @page {
            size: A4 portrait;
            margin: 14mm;
        }

        @media print {
            body {
                min-height: auto;
                background: #fff;
            }

            .page-shell {
                width: 100%;
                margin: 0;
            }

            .toolbar {
                display: none !important;
            }

            .stub {
                box-shadow: none;
                break-inside: avoid;
            }
        }
    </style>
</head>

<body>
    <main class="page-shell">
        <nav class="toolbar" aria-label="Claim stub actions">
            <a href="requests.php">&larr; Back to My Requests</a>
            <?php if ($request): ?>
                <button type="button" id="printStubButton">Print Stub</button>
            <?php endif; ?>
        </nav>

        <?php if (!$request): ?>
            <section class="error-card">
                <h1>Claim Stub Unavailable</h1>
                <p><?php echo htmlspecialchars($error_message); ?></p>
            </section>
        <?php else: ?>
            <article class="stub" id="claimStub">
                <header class="stub-header">
                    <img class="stub-logo" src="../assets/img/iconlogo-makikonek.png" alt="MakiKonek">
                    <div class="brand-copy">
                        <small>Barangay Makiling Digital Services</small>
                        <strong>MakiKonek</strong>
                    </div>
                    <span class="claim-label">Official Claim Stub</span>
                </header>

                <div class="stub-body">
                    <div class="stub-intro">
                        <div>
                            <h1>Service Request Claim Stub</h1>
                            <p>Present this stub when following up or claiming your requested document.</p>
                        </div>
                        <div class="reference">
                            <span>Reference Number</span>
                            <strong><?php echo htmlspecialchars($request['reference_no']); ?></strong>
                        </div>
                    </div>

                    <section class="detail-grid" aria-label="Request details">
                        <div class="detail">
                            <span>Name of Requester</span>
                            <strong><?php echo htmlspecialchars($requester_name); ?></strong>
                        </div>
                        <div class="detail">
                            <span>Type of Request</span>
                            <strong><?php echo htmlspecialchars($request['document_type']); ?></strong>
                        </div>
                        <div class="detail">
                            <span>Date Generated</span>
                            <strong><?php echo htmlspecialchars($date_generated); ?></strong>
                        </div>
                        <div class="detail">
                            <span>Payment Method</span>
                            <strong><?php echo htmlspecialchars($payment_method); ?></strong>
                        </div>
                    </section>

                    <footer class="stub-footer">
                        <div>
                            <strong>Keep this claim stub for your records.</strong>
                            <span>Verify request updates through your MakiKonek resident account.</span>
                        </div>
                        <img class="seal" src="../assets/img/Barangay_Makiling_Seal.png" alt="Barangay Makiling Seal">
                    </footer>
                </div>
            </article>
        <?php endif; ?>
    </main>

    <?php if ($request): ?>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const stub = document.getElementById('claimStub');
                const printButton = document.getElementById('printStubButton');
                const autoDownload = <?php echo $auto_download ? 'true' : 'false'; ?>;
                const filename = <?php echo json_encode($pdf_filename); ?>;

                function downloadPdf() {
                    if (!stub || typeof html2pdf === 'undefined') {
                        window.print();
                        return;
                    }

                    html2pdf().set({
                        margin: 8,
                        filename: filename,
                        image: {
                            type: 'jpeg',
                            quality: 0.98
                        },
                        html2canvas: {
                            scale: 2,
                            useCORS: true,
                            backgroundColor: '#ffffff'
                        },
                        jsPDF: {
                            unit: 'mm',
                            format: 'a4',
                            orientation: 'portrait'
                        },
                        pagebreak: {
                            mode: ['avoid-all']
                        }
                    }).from(stub).save();
                }

                printButton.addEventListener('click', function() {
                    window.print();
                });

                if (autoDownload) {
                    window.addEventListener('load', function() {
                        window.setTimeout(downloadPdf, 300);
                    }, {
                        once: true
                    });
                }
            });
        </script>
    <?php endif; ?>
</body>

</html>
