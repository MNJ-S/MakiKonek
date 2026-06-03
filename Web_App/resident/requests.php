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

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // FETCH FORM
    $document_type = mysqli_real_escape_string($conn, $_POST['document_type'] ?? 'Barangay Clearance');
    $first_name = strtoupper(mysqli_real_escape_string($conn, trim($_POST['first_name'])));
    $middle_name = strtoupper(mysqli_real_escape_string($conn, trim($_POST['middle_name'])));
    $last_name = strtoupper(mysqli_real_escape_string($conn, trim($_POST['last_name'])));
    $suffix = strtoupper(mysqli_real_escape_string($conn, trim($_POST['suffix'])));
    $email = mysqli_real_escape_string($conn, trim($_POST['email']));
    $phone = mysqli_real_escape_string($conn, trim($_POST['phone']));
    $birth_date = mysqli_real_escape_string($conn, $_POST['birth_date']);
    $gender = strtoupper(mysqli_real_escape_string($conn, $_POST['gender']));
    $civil_status = strtoupper(mysqli_real_escape_string($conn, $_POST['civil_status']));
    $address = strtoupper(mysqli_real_escape_string($conn, trim($_POST['address'])));
    $province = strtoupper(mysqli_real_escape_string($conn, trim($_POST['province'])));
    $city = strtoupper(mysqli_real_escape_string($conn, trim($_POST['city'])));
    $barangay = strtoupper(mysqli_real_escape_string($conn, trim($_POST['barangay'])));
    $purpose = strtoupper(mysqli_real_escape_string($conn, trim($_POST['purpose'])));
    $occupation = strtoupper(mysqli_real_escape_string($conn, trim($_POST['occupation'])));
    $document_fee = mysqli_real_escape_string($conn, $_POST['document_fee'] ?? 'P50.00');

    // VALID ID
    $id_path = '';
    if (isset($_FILES['valid_id']) && $_FILES['valid_id']['error'] === UPLOAD_ERR_OK) {
        $file_tmp = $_FILES['valid_id']['tmp_name'];
        $file_name = $_FILES['valid_id']['name'];
        $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));

        $allowed_extensions = ['jpg', 'jpeg', 'png', 'pdf'];
        if (in_array($file_ext, $allowed_extensions)) {
            $upload_dir = __DIR__ . '/../assets/uploads/requirements/';
            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0755, true);
            }

            $new_file_name = 'id_' . $resident_id . '_' . time() . '.' . $file_ext;
            if (move_uploaded_file($file_tmp, $upload_dir . $new_file_name)) {
                $id_path = 'assets/uploads/requirements/' . $new_file_name;
            }
        } else {
            $error_message = "Invalid file type. Only JPG, PNG, and PDF files are allowed.";
        }
    } else {
        $error_message = "You must upload a copy of a valid identification card.";
    }

    // PASOK SA DATABASE
    if (empty($error_message)) {
        $reference_no = 'MK-' . strtoupper(substr(uniqid(), 7, 6));

        $insert_query = "
            INSERT INTO service_requests (
                user_id, reference_no, document_type, first_name, middle_name, last_name, suffix, 
                email, phone, birth_date, gender, civil_status, address, province, city, barangay, 
                purpose, occupation, document_fee, id_path
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ";

        $stmt = mysqli_prepare($conn, $insert_query);
        mysqli_stmt_bind_param(
            $stmt,
            "isssssssssssssssssss",
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
            $id_path
        );

        if (mysqli_stmt_execute($stmt)) {
            $success_message = "Your request has been submitted under Reference Number: " . $reference_no;
        } else {
            $error_message = "Database processing error. Failed to submit request.";
        }
    }
}

$pageTitle = 'My Requests';
$activePage = 'requests';

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
        ['name' => 'Building Permit', 'fee' => 'P500.00', 'time' => '3-5 working days'],
        ['name' => 'Cedula', 'fee' => 'Based on LGU', 'time' => 'Same day processing'],
    ]
];
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

                    <form class="request-form" action="requests.php" method="POST" enctype="multipart/form-data" data-request-form style="display:none;">
                        <input type="hidden" name="document_type" id="hidden_doc_type">
                        <input type="hidden" name="document_fee" id="hidden_doc_fee">

                        <h2>Request Details</h2>
                        <p>Complete the form for <span id="display-selected-doc" style="font-weight:bold; color:#0b6d36;"></span></p>

                        <div class="form-grid">
                            <div class="field"><label>First Name *</label><input type="text" name="first_name" required oninput="this.value = this.value.toUpperCase()"></div>
                            <div class="field"><label>Middle Name</label><input type="text" name="middle_name" oninput="this.value = this.value.toUpperCase()"></div>
                            <div class="field"><label>Last Name *</label><input type="text" name="last_name" required oninput="this.value = this.value.toUpperCase()"></div>
                            <div class="field"><label>Suffix</label><input type="text" name="suffix" oninput="this.value = this.value.toUpperCase()"></div>
                            <div class="field"><label>Email Address *</label><input type="email" name="email" required></div>
                            <div class="field"><label>Phone Number *</label><input type="tel" name="phone" required></div>
                            <div class="field"><label>Birth Date *</label><input type="date" name="birth_date" required></div>
                            <div class="field">
                                <label>Gender *</label>
                                <select name="gender" required>
                                    <option value="">Select gender</option>
                                    <option value="MALE">Male</option>
                                    <option value="FEMALE">Female</option>
                                    <option value="PREFER NOT TO SAY">Prefer not to say</option>
                                </select>
                            </div>
                            <div class="field">
                                <label>Civil Status *</label>
                                <select name="civil_status" required>
                                    <option value="">Select status</option>
                                    <option value="SINGLE">Single</option>
                                    <option value="MARRIED">Married</option>
                                    <option value="WIDOWED">Widowed</option>
                                    <option value="SEPARATED">Separated</option>
                                </select>
                            </div>
                            <div class="field full"><label>Full Address *</label><input type="text" name="address" required oninput="this.value = this.value.toUpperCase()"></div>
                            <div class="field"><label>Province *</label><input type="text" name="province" value="LAGUNA" required oninput="this.value = this.value.toUpperCase()"></div>
                            <div class="field"><label>City/Municipality *</label><input type="text" name="city" value="CALAMBA" required oninput="this.value = this.value.toUpperCase()"></div>
                            <div class="field"><label>Barangay *</label><input type="text" name="barangay" value="MAKILING" required oninput="this.value = this.value.toUpperCase()"></div>

                            <div class="field full">
                                <label>Upload Valid ID *</label>
                                <input type="file" name="valid_id" accept="image/*,.pdf" required>
                            </div>

                            <div class="field"><label>Purpose *</label><input type="text" name="purpose" required oninput="this.value = this.value.toUpperCase()"></div>
                            <div class="field"><label>Occupation</label><input type="text" name="occupation" oninput="this.value = this.value.toUpperCase()"></div>
                        </div>

                        <div class="fee-box">
                            <span>Document Fee: <small>Processing time: <span data-processing-time>1-2 working days</span></small></span>
                            <strong id="display-fee">P0.00</strong>
                        </div>

                        <div class="form-actions">
                            <button class="cancel-btn" type="button" id="cancel-request-btn">Cancel</button>
                            <button class="submit-btn" type="submit">Submit Request</button>
                        </div>
                    </form>
                </section>
            </section>
        </main>
    </div>

    <div id="notification-container" style="position: fixed; bottom: 30px; right: 30px; z-index: 9999; display: flex; flex-direction: column; gap: 10px;">
        <?php if (!empty($success_message)): ?>
            <div class="toast-notification" style="background-color:#dcfce7; border-left: 5px solid #22c55e; color:#15803d; padding:15px 25px; border-radius:6px; font-size:14px; font-weight:bold; box-shadow: 0 10px 25px rgba(0,0,0,0.1); transition: opacity 0.5s ease;">
                <?php echo $success_message; ?>
            </div>
        <?php endif; ?>
        <?php if (!empty($error_message)): ?>
            <div class="toast-notification" style="background-color:#fee2e2; border-left: 5px solid #ef4444; color:#b91c1c; padding:15px 25px; border-radius:6px; font-size:14px; font-weight:bold; box-shadow: 0 10px 25px rgba(0,0,0,0.1); transition: opacity 0.5s ease;">
                <?php echo $error_message; ?>
            </div>
        <?php endif; ?>
    </div>

    <?php
    $footerBase = '../public/';
    $footerAssetBase = '../assets';
    include __DIR__ . '/../includes/footer.php';
    ?>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const options = document.querySelectorAll('.document-option');
            const emptyState = document.querySelector('[data-empty-state]');
            const form = document.querySelector('[data-request-form]');

            options.forEach(btn => {
                btn.addEventListener('click', function() {
                    options.forEach(b => b.classList.remove('active'));
                    this.classList.add('active');
                    // UPDATE
                    const name = this.dataset.documentName;
                    const fee = this.dataset.documentFee;

                    document.getElementById('hidden_doc_type').value = name;
                    document.getElementById('hidden_doc_fee').value = fee;
                    document.getElementById('display-selected-doc').textContent = name;
                    document.getElementById('display-fee').textContent = fee;

                    emptyState.style.display = 'none';
                    form.style.display = 'block';
                });
            });

            document.getElementById('cancel-request-btn').addEventListener('click', function() {
                form.reset();
                form.style.style.display = 'none';
                emptyState.style.display = 'block';
                options.forEach(b => b.classList.remove('active'));
            });

            const notifications = document.querySelectorAll('.toast-notification');
            notifications.forEach(n => {
                setTimeout(() => {
                    n.style.opacity = '0';
                    setTimeout(() => n.remove(), 500);
                }, 5000);
            });
        });
    </script>
</body>

</html