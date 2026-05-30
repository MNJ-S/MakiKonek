<?php
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
        ['name' => 'Building/Construction Permit', 'fee' => 'P500.00', 'time' => '3-5 working days'],
        ['name' => 'Cedula', 'fee' => 'Based on LGU', 'time' => 'Same day processing'],
    ],
    'Others' => [
        ['name' => 'Barangay ID', 'fee' => 'P100.00', 'time' => '2-3 working days'],
        ['name' => 'Incident Report', 'fee' => 'P50.00', 'time' => '1-2 working days'],
    ],
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle; ?> | MakiKonek</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/footer.css?v=20260529e">
    <link rel="stylesheet" href="../assets/css/resident.css?v=20260530a">
</head>
<body class="resident-page">
    <?php include __DIR__ . '/partials/resident_topbar.php'; ?>

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
                                    data-document-time="<?php echo htmlspecialchars($document['time']); ?>"
                                >
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

                    <form class="request-form" data-request-form>
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
                            <div class="field full">
                                <label for="valid_id">Valid ID *</label>
                                <div class="upload-box">
                                    <div>
                                        <i class="fa-solid fa-arrow-up-from-bracket"></i>
                                        <span>Click to upload or drag and drop</span>
                                        <small>SVG, PNG, JPG or PDF (max. 5 MB uploaded)</small>
                                        <br>
                                        <button type="button">Choose File</button>
                                    </div>
                                </div>
                            </div>
                            <div class="field">
                                <label for="purpose">Purpose *</label>
                                <input id="purpose" name="purpose" type="text" placeholder="Enter purpose" required>
                            </div>
                            <div class="field">
                                <label for="occupation">Occupation</label>
                                <input id="occupation" name="occupation" type="text" placeholder="Enter occupation">
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

    <?php include __DIR__ . '/partials/resident_footer.php'; ?>
    <script src="../assets/js/resident.js?v=20260530a"></script>
</body>
</html>
