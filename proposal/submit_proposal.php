<?php
$conn = new mysqli("localhost", "root", "", "calendar");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

                // Fetch events for FullCalendar
if (isset($_GET['action']) && $_GET['action'] === 'fetch') {
    $result = $conn->query("SELECT * FROM proposals");

    $events = [];
    while ($row = $result->fetch_assoc()) {
        $color = '#6c757d';
        if ($row['status'] === 'Pending') $color = '#FFA500';
        if ($row['status'] === 'Approved') $color = '#28a745';
        if ($row['status'] === 'Disapproved') $color = '#dc3545';

        $events[] = [
            'title' => $row['event_type'] . " (" . $row['department'] . ")",
            'start' => $row['start_date'],
            'end' => date('Y-m-d', strtotime($row['end_date'] . ' +1 day')),
            'color' => $color,
            'status' => $row['status']
        ];
    }

    header('Content-Type: application/json');
    echo json_encode($events);
    exit();
}

// Safely get POST values
$department  = $_POST['department'] ?? '';
$event_type  = $_POST['event_type'] ?? '';
$date_range  = $_POST['date_range'] ?? '';
$venue       = $_POST['venue'] ?? '';
$time        = $_POST['time'] ?? '';

// Parse date range into start and end
$dates = explode(' to ', str_replace(' - ', ' to ', $date_range));
$start_date = isset($dates[0]) ? date('Y-m-d', strtotime($dates[0])) : null;
$end_date   = isset($dates[1]) ? date('Y-m-d', strtotime($dates[1])) : null;

// Handle file uploads
function uploadFile($name) {
    if (isset($_FILES[$name]) && $_FILES[$name]['error'] === UPLOAD_ERR_OK) {
        $targetDir = "uploads/";
        if (!is_dir($targetDir)) mkdir($targetDir);
        $filename = time() . '_' . basename($_FILES[$name]["name"]);
        $targetFilePath = $targetDir . $filename;
        if (move_uploaded_file($_FILES[$name]["tmp_name"], $targetFilePath)) {
            return $filename;
        }
    }
    return null;
}

$letter_attachment = uploadFile("letter_attachment");
$constitution      = uploadFile("constitution");
$reports           = uploadFile("reports");
$adviser_form      = uploadFile("adviser_form");
$certification     = uploadFile("certification");
$financial         = uploadFile("financial");

// Insert into database
$sql = "INSERT INTO proposals 
    (department, event_type, start_date, end_date, venue, time,
     adviser_form, certification, financial, constitution, reports, letter_attachment, status)
    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'Pending')";

$stmt = $conn->prepare($sql);
$stmt->bind_param("ssssssssssss", 
    $department, $event_type, $start_date, $end_date, $venue, $time,
    $adviser_form, $certification, $financial, $constitution, $reports, $letter_attachment
);
$stmt->execute();
$stmt->close();
$conn->close();

header("Location: calendar.php");
exit();
?>
