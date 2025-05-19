// Fetch and sanitize input
$department = $_POST['department'];
$event_type = $_POST['event_type'];
$date_range = $_POST['date_range'];
$venue = $_POST['venue'];
$time = $_POST['time'];

function upload($file) {
    $uploads_dir = "../uploads/";
    $target = $uploads_dir . basename($file['name']);
    move_uploaded_file($file['tmp_name'], $target);
    return $target;
}

// Upload all files
$adviser_form = upload($_FILES['adviser_form']);
$certification = upload($_FILES['certification']);
$financial_report = upload($_FILES['financial_report']);
$bylaws = upload($_FILES['bylaws']);
$accomplishment = upload($_FILES['accomplishment']);

// Save to DB
$stmt = $conn->prepare("INSERT INTO event_proposals (department, event_type, date_range, venue, time, adviser_form, certification, financial_report, bylaws, accomplishment, status)
VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'pending')");
$stmt->bind_param("ssssssssss", $department, $event_type, $date_range, $venue, $time, $adviser_form, $certification, $financial_report, $bylaws, $accomplishment);
$stmt->execute();

header("Location: proposal_form.php?success=1");
?>