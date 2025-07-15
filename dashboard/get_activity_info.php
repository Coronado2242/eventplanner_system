<?php
header('Content-Type: application/json');

$conn = new mysqli("localhost", "root", "", "eventplanner");
if ($conn->connect_error) {
    echo json_encode(['success' => false, 'message' => 'Database connection failed']);
    exit;
}

$activityName = $_GET['activity_name'] ?? '';
$activityName = $conn->real_escape_string($activityName);


$sql = "SELECT objective, brief_description, person_involved, budgets FROM activities WHERE activity_name = '$activityName' LIMIT 1";
$result = $conn->query($sql);

if ($result && $result->num_rows > 0) {
    $row = $result->fetch_assoc();
    echo json_encode([
        'success' => true,
        'objective' => $row['objective'],
        'description' => $row['brief_description'], 
        'person_involved' => $row['person_involved'],
        'budgets' => $row['budgets'] ?? '' 
    ]);
} else {
    echo json_encode(['success' => false, 'message' => 'Activity not found']);
}
?>
