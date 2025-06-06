<?php
session_start();
session_unset();
$host = "localhost";
$user = "root";
$pass = "";
$db = "eventplanner";

$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Para sa file uploads
$uploadDir = "uploads/";
$allowedExtensions = ['pdf', 'doc', 'docx'];

function uploadFile($fileInputName) {
    global $uploadDir, $allowedExtensions;

    if (isset($_FILES[$fileInputName]) && $_FILES[$fileInputName]['error'] == 0) {
        $fileName = basename($_FILES[$fileInputName]['name']);
        $fileExt = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

        if (!in_array($fileExt, $allowedExtensions)) {
            return false;
        }

        $targetFile = $uploadDir . uniqid() . "_" . $fileName;
        if (move_uploaded_file($_FILES[$fileInputName]['tmp_name'], $targetFile)) {
            return $targetFile;
        }
    }
    return false;
}

// 1. Check if budget approved for the proposal
if (!isset($_SESSION['proposal_id'])) {
    die("No proposal in progress. Please fill the form first.");
}
$proposal_id = $_SESSION['proposal_id'];

$stmt = $conn->prepare("SELECT budget_approved FROM proposals WHERE id = ?");
$stmt->bind_param("i", $proposal_id);
$stmt->execute();
$stmt->bind_result($budget_approved);
$stmt->fetch();
$stmt->close();

if (!$budget_approved) {
    die("Cannot submit proposal because budget is not approved yet.");
}

// 2. Validate form inputs
$department = $_POST['department'] ?? '';
$event_type = $_POST['event_type'] ?? '';
$date_range = $_POST['date_range'] ?? '';
$venue = $_POST['venue'] ?? '';
$time = $_POST['time'] ?? '';

if (!$department || !$event_type || !$date_range || !$venue || !$time) {
    die("Please fill all required fields.");
}

// 3. Upload files
$files = ['letter_attachment', 'constitution', 'reports', 'adviser_form', 'certification', 'financial'];
$filePaths = [];

foreach ($files as $file) {
    $uploadPath = uploadFile($file);
    if ($uploadPath === false) {
        die("Error uploading file for $file. Make sure it's PDF/DOC/DOCX.");
    }
    $filePaths[$file] = $uploadPath;
}

// 4. Update proposal and assign to VP
$stmt = $conn->prepare("UPDATE proposals SET 
    department=?, 
    event_type=?, 
    date_range=?, 
    venue=?, 
    time=?, 
    letter_attachment=?, 
    constitution=?, 
    reports=?, 
    adviser_form=?, 
    certification=?, 
    financial=?, 
    status='submitted', 
    level='VP', 
    submitted_at=NOW() 
    WHERE id=?");

$stmt->bind_param("sssssssssssi",
    $department,
    $event_type,
    $date_range,
    $venue,
    $time,
    $filePaths['letter_attachment'],
    $filePaths['constitution'],
    $filePaths['reports'],
    $filePaths['adviser_form'],
    $filePaths['certification'],
    $filePaths['financial'],
    $proposal_id
);

$stmt->execute();
$stmt->close();

// 5. Clear session and confirm
unset($_SESSION['form_data']);
unset($_SESSION['uploaded']);
unset($_SESSION['proposal_id']);
>>>>>>> 28d03f7 (proposal)

echo "<script>
    alert('Proposal submitted successfully.');
    window.location.href = 'proposal.php';
</script>";