<?php
session_start();

$host = "localhost";
$user = "root";
$pass = "";
$db   = "eventplanner";

$conn = mysqli_connect($host, $user, $pass, $db);
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
include 'db_connection.php'; // Ensure this uses $conn

// Approval flow (order of reviewers)
$approval_flow = [
    'VP',
    'CCS Treasurer',
    'CCS Auditor',
    'President',
    'Faculty Adviser',
    'Dean',
    'OSAS'
];

// Get POST data
$proposal_id = isset($_POST['proposal_id']) ? intval($_POST['proposal_id']) : 0;
$action = isset($_POST['action']) ? $_POST['action'] : '';
$current_level = isset($_POST['current_level']) ? $_POST['current_level'] : '';
$budget = isset($_POST['budget']) ? $_POST['budget'] : null;

if ($proposal_id <= 0 || !in_array($action, ['approve', 'disapprove']) || empty($current_level)) {
    echo "invalid_input";
    exit;
}

// Find current level index
$current_index = array_search($current_level, $approval_flow);
$next_level = isset($approval_flow[$current_index + 1]) ? $approval_flow[$current_index + 1] : null;

// Determine new status and current_level
if ($next_level) {
    // Move to next level regardless of action
    $new_level = $next_level;
    $status = 'Pending'; // Still under process
} else {
    // Last level reached (OSAS)
    $new_level = 'Completed';
    $status = ($action === 'approve') ? 'Final Approved' : 'Final Disapproved';
}

// Update proposals table
$sql = "UPDATE proposals SET status = ?, budget = ?, current_level = ? WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("sssi", $status, $budget, $new_level, $proposal_id);

if ($stmt->execute()) {
    echo "success";
} else {
    echo "error";
}

$stmt->close();
$conn->close();
?>
