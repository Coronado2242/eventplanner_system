<?php
session_start();
$host = "localhost";
$user = "root";
$pass = "";
$db = "eventplanner";
$conn = new mysqli($host, $user, $pass, $db);

// Only process if form fields are set
if (isset($_POST['department'], $_POST['event_type'], $_POST['date_range'], $_POST['venue'], $_POST['time'])) {

    // Optional: Save to session only for sticky values
    $_SESSION['form_data'] = $_POST;

    // Upload and store files
    $uploads = [];
    foreach (['letter_attachment', 'constitution', 'reports', 'adviser_form', 'certification', 'financial'] as $field) {
        if (!empty($_FILES[$field]['name'])) {
            $filename = time() . '_' . basename($_FILES[$field]['name']);
            $target = "uploads/" . $filename;
            move_uploaded_file($_FILES[$field]['tmp_name'], $target);
            $uploads[$field] = $target;
        } else {
            $uploads[$field] = null; // Ensure it's defined
        }
    }

    $_SESSION['uploaded'] = $uploads;

    $department = $_POST['department'];
    $event_type = $_POST['event_type'];
    $venue = $_POST['venue'];
    $time = $_POST['time'];

    // Fix: parse date range safely
    $dates = preg_split("/ to | - /", $_POST['date_range']);
    $start_date = isset($dates[0]) ? date('Y-m-d', strtotime(trim($dates[0]))) : null;
    $end_date = isset($dates[1]) ? date('Y-m-d', strtotime(trim($dates[1]))) : $start_date;

    $stmt = $conn->prepare("INSERT INTO proposals 
        (department, event_type, start_date, end_date, venue, time, adviser_form, certification, financial, constitution, reports, letter_attachment, status, budget_approved) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'pending', 0)");

    $stmt->bind_param("ssssssssssss",
        $department, $event_type, $start_date, $end_date, $venue, $time,
        $uploads['adviser_form'], $uploads['certification'], $uploads['financial'],
        $uploads['constitution'], $uploads['reports'], $uploads['letter_attachment']);

    if ($stmt->execute()) {
        $_SESSION['proposal_id'] = $stmt->insert_id;
        echo "<script>alert('Successful requesting budget, waiting for the budget.'); window.location.href = 'proposal.php';</script>";
    } else {
        echo "<script>alert('Database error: " . $stmt->error . "');</script>";
    }

    $stmt->close();
    $conn->close();
}
?>
