<?php
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
$proposal_id = isset($_POST['proposal_id']) ? intval($_POST['proposal_id']) : 0;
$action = isset($_POST['action']) ? $_POST['action'] : '';
$current_level = isset($_POST['level']) ? $_POST['level'] : '';
$budget = isset($_POST['budget']) ? $_POST['budget'] : null;

if ($proposal_id <= 0 || !in_array($action, ['approve', 'disapprove']) || empty($current_level)) {
    echo "invalid_input";
    exit;
}

$current_index = array_search($current_level, $approval_flow);
$next_level = isset($approval_flow[$current_index + 1]) ? $approval_flow[$current_index + 1] : null;

// Get approver username or fallback to level name
$approved_by = isset($_SESSION['username']) ? $_SESSION['username'] : $current_level;
$approved_at = date('Y-m-d H:i:s');

// Determine new status and level based on action
if ($action === 'approve') {
    if ($next_level) {
        $new_level = $next_level;
        $status = 'Pending'; // still in approval flow
    } else {
        $new_level = 'Completed';
        $status = 'Final Approved';
    }
} else { // disapprove
    $new_level = $current_level; // stay at current level
    $status = 'Disapproved';
}

// Prepare update query
$sql = "UPDATE proposals SET status = ?, budget = ?, level = ?, approved_by = ?, approved_at = ? WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("sssssi", $status, $budget, $new_level, $approved_by, $approved_at, $proposal_id);

if ($stmt->execute()) {
    // Redirect to dashboard depending on new level
    switch ($new_level) {
        case 'VP':
            header("Location: ../dashboard/vp_dashboard.php");
            break;
        case 'CCS Treasurer':
            header("Location: ../dashboard/ccstresurer_dashboard.php");
            break;
        case 'CCS Auditor':
            header("Location: ../dashboard/ccsauditor_dashboard.php");
            break;
        case 'President':
            header("Location: ../dashboard/president_dashboard.php");
            break;
        case 'Faculty Adviser':
            header("Location: ../dashboard/faculty_dashboard.php");
            break;
        case 'Dean':
            header("Location: ../dashboard/dean_dashboard.php");
            break;
        case 'OSAS':
            header("Location: ../dashboard/osas_dashboard.php");
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
