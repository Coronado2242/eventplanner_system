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
    $_SESSION['form_data'] = [
    'department'  => $_POST['department'],
    'event_type'  => $_POST['event_type'],
    'date_range'  => $_POST['date_range'],
    'venue'       => $_POST['venue'],
    'start_time'  => $_POST['start_time'],
    'end_time'    => $_POST['end_time']
];


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

if (isset($_POST['proposal_id'])) {
    $proposal_id = $_POST['proposal_id'];

    // Upload and store files (optional: for updated attachments)
    $uploads = [];
    foreach (['letter_attachment', 'constitution', 'reports', 'adviser_form', 'certification', 'financial'] as $field) {
        if (!empty($_FILES[$field]['name'])) {
            $filename = time() . '_' . basename($_FILES[$field]['name']);
            $target = "uploads/" . $filename;
            move_uploaded_file($_FILES[$field]['tmp_name'], $target);
            $uploads[$field] = $target;
        } else {
            $uploads[$field] = null;
        }
    }

    // Update proposal: reset status and budget_approved, keep existing attachments if no new upload
    $stmt = $conn->prepare("UPDATE proposals SET 
        adviser_form = COALESCE(?, adviser_form),
        certification = COALESCE(?, certification),
        financial = COALESCE(?, financial),
        constitution = COALESCE(?, constitution),
        reports = COALESCE(?, reports),
        letter_attachment = COALESCE(?, letter_attachment),
        status = 'pending',
        budget_approved = 0,
        level = 'Vice President'
        WHERE id = ?");

    $stmt->bind_param("sssssssi",
        $uploads['adviser_form'], $uploads['certification'], $uploads['financial'],
        $uploads['constitution'], $uploads['reports'], $uploads['letter_attachment'],
        $proposal_id);

    if ($stmt->execute()) {
        echo "<script>alert('Budget request resubmitted successfully.'); window.location.href = 'proposal.php';</script>";
    } else {
        echo "<script>alert('Database error: " . $stmt->error . "');</script>";
    }

    $stmt->close();
    $conn->close();
} else {
    echo "<script>alert('Proposal ID not specified.'); window.location.href = 'proposal.php';</script>";
}
?>
