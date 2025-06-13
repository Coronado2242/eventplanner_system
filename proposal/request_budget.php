<?php
session_start();
$host = "localhost";
$user = "root";
$pass = "";
$db = "eventplanner";
$conn = new mysqli($host, $user, $pass, $db);

// === Check login ===
if (!isset($_SESSION['username'])) {
    exit;
}

$username = $_SESSION['username'];

// === CASE 1: New Proposal Request ===
if (isset($_POST['department'], $_POST['event_type'], $_POST['date_range'], $_POST['venue'], $_POST['time'])) {
    $_SESSION['form_data'] = [
        'department'  => $_POST['department'],
        'event_type'  => $_POST['event_type'],
        'date_range'  => $_POST['date_range'],
        'venue'       => $_POST['venue'],
        'start_time'  => $_POST['start_time'],
        'end_time'    => $_POST['end_time']
    ];

    // Upload files
    $uploads = [];
    foreach (['letter_attachment', 'constitution', 'reports', 'adviser_form', 'certification', 'financial', 'activity_plan'] as $field) {
        if (!empty($_FILES[$field]['name'])) {
            $filename = time() . '_' . basename($_FILES[$field]['name']);
            $target = "uploads/" . $filename;
            move_uploaded_file($_FILES[$field]['tmp_name'], $target);
            $uploads[$field] = $target;
        } else {
            $uploads[$field] = null;
        }
    }
    $_SESSION['uploaded'] = $uploads;

    // Parse dates
    $dates = preg_split("/ to | - /", $_POST['date_range']);
    $start_date = isset($dates[0]) ? date('Y-m-d', strtotime(trim($dates[0]))) : null;
    $end_date = isset($dates[1]) ? date('Y-m-d', strtotime(trim($dates[1]))) : $start_date;

    // Insert into proposals
    $stmt = $conn->prepare("INSERT INTO proposals 
        (username, department, event_type, start_date, end_date, venue, time, 
        adviser_form, certification, financial, constitution, reports, letter_attachment, activity_plan, 
        status, budget_approved) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'pending', 0)");

    $stmt->bind_param("ssssssssssssss",
        $username, $_POST['department'], $_POST['event_type'], $start_date, $end_date, $_POST['venue'], $_POST['time'],
        $uploads['adviser_form'], $uploads['certification'], $uploads['financial'],
        $uploads['constitution'], $uploads['reports'], $uploads['letter_attachment'], $uploads['activity_plan']
    );

    if ($stmt->execute()) {
        $_SESSION['proposal_id'] = $stmt->insert_id;
        $stmt->close();
        $conn->close();
        echo "<script>alert('Successfully requested budget.'); window.location.href = 'proposal.php';</script>";
        exit; // ✅ Important
    } else {
        echo "<script>alert('Database error: " . $stmt->error . "');</script>";
        $stmt->close();
        $conn->close();
        exit; // ✅ Stop further processing
    }
}

// === CASE 2: Update Existing Proposal ===
if (isset($_POST['proposal_id'])) {
    $proposal_id = $_POST['proposal_id'];

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

    $stmt->bind_param("ssssssi",
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
    exit;
}

// === Fallback: No data ===
echo "<script>alert('Proposal ID not specified.'); window.location.href = 'proposal.php';</script>";
exit;
