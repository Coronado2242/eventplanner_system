<?php
// Enable error reporting (optional for development)
ini_set('display_errors', 1);
error_reporting(E_ALL);

// DB connection
$host = "localhost";
$user = "root";
$pass = "";
$db   = "calendar";

$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Create table if it doesn't exist
$createTableSQL = "CREATE TABLE IF NOT EXISTS proposals (
    id INT AUTO_INCREMENT PRIMARY KEY,
    department VARCHAR(100),
    event_type VARCHAR(100),
    start_date DATE,
    end_date DATE,
    venue VARCHAR(255),
    time VARCHAR(50),
    adviser_form VARCHAR(255),
    certification VARCHAR(255),
    financial VARCHAR(255),
    constitution VARCHAR(255),
    reports VARCHAR(255),
    letter_attachment VARCHAR(255),
    status VARCHAR(50),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";
$conn->query($createTableSQL);

// Upload helper function
function uploadFile($fileInputName) {
    $targetDir = "uploads/";
    if (!is_dir($targetDir)) {
        mkdir($targetDir, 0777, true);
    }

    if (isset($_FILES[$fileInputName]) && $_FILES[$fileInputName]['error'] === UPLOAD_ERR_OK) {
        $filename = basename($_FILES[$fileInputName]["name"]);
        $cleanedName = preg_replace('/[^a-zA-Z0-9\._-]/', '_', $filename);
        $targetFile = $targetDir . time() . "_" . $cleanedName;

        if (move_uploaded_file($_FILES[$fileInputName]["tmp_name"], $targetFile)) {
            return $targetFile;
        }
    }
    return null;
}

// Get form values
$department    = $_POST['department'] ?? '';
$event_type    = $_POST['event_type'] ?? '';
$start_date    = $_POST['start_date'] ?? '';
$end_date      = $_POST['end_date'] ?? '';
$venue         = $_POST['venue'] ?? '';
$time          = $_POST['time'] ?? '';
$status        = "Pending";

// Upload attachments
$adviser_form  = uploadFile('adviser_form');
$certification = uploadFile('certification');
$financial     = uploadFile('financial');
$constitution  = uploadFile('constitution');
$reports       = uploadFile('reports');
$letter        = uploadFile('letter_attachment');

// Prepare and bind
$stmt = $conn->prepare("INSERT INTO proposals 
    (department, event_type, start_date, end_date, venue, time, adviser_form, certification, financial, constitution, reports, letter_attachment, status)
    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

if (!$stmt) {
    die("Prepare failed: " . $conn->error);
}

$stmt->bind_param(
    "sssssssssssss", 
    $department, $event_type, $start_date, $end_date, $venue, $time, 
    $adviser_form, $certification, $financial, $constitution, $reports, $letter, $status
);

// Execute and give feedback
if ($stmt->execute()) {
    echo "<p style='color: green; font-weight: bold;'>✔ Proposal submitted successfully!</p>";
    // Optional: redirect to form page with success flag
    // header("Location: proposal_form.php?success=1");
    // exit();
} else {
    echo "<p style='color: red;'>✖ Error submitting proposal: " . htmlspecialchars($stmt->error) . "</p>";
}

$stmt->close();
$conn->close();
?>
