<?php
$host = "localhost";
$user = "root";
$pass = "";
$db = "calendar"; 


$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// File upload function
function uploadFile($fileInputName) {
    $targetDir = "uploads/";
    $filename = basename($_FILES[$fileInputName]["name"]);
    $targetFile = $targetDir . time() . "_" . $filename;
    move_uploaded_file($_FILES[$fileInputName]["tmp_name"], $targetFile);
    return $targetFile;
}

// inputs
$department = $_POST['department'];
$event_type = $_POST['event_type'];
$start_date = $_POST['start_date'];
$end_date = $_POST['end_date'];
$venue = $_POST['venue'];
$time = $_POST['time'];

// Upload files
$adviser_form = uploadFile('adviser_form');
$certification = uploadFile('certification');
$financial = uploadFile('financial');
$constitution = uploadFile('constitution');
$reports = uploadFile('reports');
$letter = uploadFile('letter_attachment');

// Insert to database
$stmt = $conn->prepare("INSERT INTO proposals 
(department, event_type, start_date, end_date, venue, time, adviser_form, certification, financial, constitution, reports, letter_attachment, status)
VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'Pending')");
$stmt->bind_param("ssssssssssss", $department, $event_type, $start_date, $end_date, $venue, $time, $adviser_form, $certification, $financial, $constitution, $reports, $letter);

if ($stmt->execute()) {
    header("Location: proposal_form.php?success=1");
} else {
    echo "Error: " . $stmt->error;
}

$conn->close();
?>
