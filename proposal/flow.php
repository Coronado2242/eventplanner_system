<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();

$host = "localhost";
$user = "root";
$pass = "";
$db   = "eventplanner";

$conn = mysqli_connect($host, $user, $pass, $db);
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

// Approval flow sequence
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
$proposal_id    = isset($_POST['proposal_id']) ? intval($_POST['proposal_id']) : 0;
$action         = isset($_POST['action']) ? $_POST['action'] : '';
$current_level  = isset($_POST['level']) ? $_POST['level'] : '';
$budget         = isset($_POST['budget']) ? $_POST['budget'] : null;
$remarks        = isset($_POST['remarks']) ? trim($_POST['remarks']) : null;

var_dump($remarks);
// var_dump($_POST);
// exit;

// Basic validation
if ($proposal_id <= 0 || !in_array($action, ['approve', 'disapprove']) || empty($current_level)) {
    echo "invalid_input";
    exit;
}

// If disapprove, remarks is required
if ($action === 'disapprove' && empty($remarks)) {
    echo "remarks_required";
    exit;
}

$current_index = array_search($current_level, $approval_flow);
$next_level    = isset($approval_flow[$current_index + 1]) ? $approval_flow[$current_index + 1] : null;

$approved_by   = isset($_SESSION['username']) ? $_SESSION['username'] : $current_level;
$approved_at   = date('Y-m-d H:i:s');

// Determine new status and level based on action
if ($action === 'approve') {
    if ($next_level) {
        $new_level = $next_level;
        $status = 'Pending'; // default for in-progress approvals
        $remarks = null; // clear remarks on approval
    } else {
        // LAST APPROVER NA ITO
        $new_level = 'Completed';
        $status = 'Approved'; // ← FINAL STATUS kapag approve na sa lahat
        $remarks = null;
    }
} else {
    // Disapproved
    $new_level = $current_level; // stay on current level
    $status = 'Disapproved';
    // keep remarks
}

// Update the proposal in the database
$sql = "UPDATE proposals SET status = ?, budget = ?, level = ?, approved_by = ?, approved_at = ?, remarks = ? WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ssssssi", $status, $budget, $new_level, $approved_by, $approved_at, $remarks, $proposal_id);


if ($stmt->execute()) {
    // Redirect user based on next level
    switch ($new_level) {
        case 'CCSVice':
            header("Location: ../dashboard/ccsvice_dashboard.php");
            break;
        case 'CCS Treasurer':
            header("Location: ../dashboard/ccstreasurer_dashboard.php");
            break;
        case 'CCS Auditor':
            header("Location: ../dashboard/ccsauditor_dashboard.php");
            break;
        case 'President':
            header("Location: ../dashboard/ccspresident_dashboard.php");
            break;
        case 'Faculty Adviser':
            header("Location: ../dashboard/ctefaculty_dashboard.php");
            break;
        case 'Dean':
            header("Location: ../dashboard/ctedean_dashboard.php");
            break;
        case 'OSAS':
            header("Location: ../dashboard/osas.php");
            break;
        case 'Completed':
            header("Location: ../dashboard/final_summary.php");
            break;
        default:
            header("Location: ../dashboard/dashboard.php");
            break;
    }
    exit();
} else {
    echo "error_updating";
}

$stmt->close();
$conn->close();
?>
