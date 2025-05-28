<?php
session_start();
$host = "localhost";
$user = "root";
$pass = "";
$db = "eventplanner";
$conn = new mysqli($host, $user, $pass, $db);

// Save form data to session
$_SESSION['form_data'] = $_POST;

// Upload and store files
$uploads = [];
foreach (['letter_attachment', 'constitution', 'reports', 'adviser_form', 'certification', 'financial'] as $field) {
    if (!empty($_FILES[$field]['name'])) {
        $filename = time() . '_' . basename($_FILES[$field]['name']);
        $target = "uploads/" . $filename;
        move_uploaded_file($_FILES[$field]['tmp_name'], $target);
        $uploads[$field] = $target;
    }
}
$_SESSION['uploaded'] = $uploads;

// Extract form data
$department = $_POST['department'];
$event_type = $_POST['event_type'];
$venue = $_POST['venue'];
$time = $_POST['time'];
$dateRange = explode(' to ', str_replace(' - ', ' to ', $_POST['date_range']));
$start_date = isset($dateRange[0]) ? date('Y-m-d', strtotime($dateRange[0])) : null;
$end_date = isset($dateRange[1]) ? date('Y-m-d', strtotime($dateRange[1])) : $start_date;

// Insert into DB
$stmt = $conn->prepare("INSERT INTO proposals 
(department, event_type, start_date, end_date, venue, time, adviser_form, certification, financial, constitution, reports, letter_attachment, status, budget_approved) 
VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'pending', 0)");
$stmt->bind_param("ssssssssssss",
    $department, $event_type, $start_date, $end_date, $venue, $time,
    $uploads['adviser_form'], $uploads['certification'], $uploads['financial'],
    $uploads['constitution'], $uploads['reports'], $uploads['letter_attachment']);
$stmt->execute();

$_SESSION['proposal_id'] = $stmt->insert_id;
$stmt->close();
$conn->close();

// Show alert and return to form
echo "<script>
    alert('Successful requesting budget, waiting for the budget.');
    window.location.href = 'proposal.php';
</script>";
