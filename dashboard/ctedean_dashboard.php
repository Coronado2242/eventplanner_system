<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "eventplanner";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['proposal_id'], $_POST['action'])) {
    $id = (int)$_POST['proposal_id'];
    $action = $_POST['action'];

    if ($action === 'approve') {
        $status = 'Pending';
        $new_level = 'Dean';  // next level after Treasurer
    } elseif ($action === 'disapprove') {
        $status = 'Disapproved by Treasurer';
        $new_level = 'Dean'; // stays in Treasurer since disapproved
    } else {
        die("Invalid action");
    }

    $stmt = $conn->prepare("UPDATE proposals SET status=?, level=? WHERE id=?");
    if (!$stmt) {
        die("Prepare failed: " . $conn->error);
    }
    $stmt->bind_param("ssi", $status, $new_level, $id);
    if ($stmt->execute()) {
        echo "Update successful!";
        header("Location: treasurer_dashboard.php"); // Redirect para mai-refresh ang list
        exit;
    } else {
        die("Execute failed: " . $stmt->error);
    }
}

// Fetch proposals currently for Treasurer approval
$current_level = 'Dean';

$sql = "SELECT * FROM proposals WHERE level = ? AND status = 'Pending'";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $current_level);

$stmt->execute();
$result = $stmt->get_result();

?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<title>Treasurer Dashboard</title>
<style>
    table { width:100%; border-collapse: collapse; }
    th, td { border:1px solid #ccc; padding:8px; text-align:left; }
    th { background:#eee; }
    button { padding:6px 10px; margin-right:4px; cursor:pointer; }
    .approve-btn { background:green; color:#fff; border:none; }
    .disapprove-btn { background:red; color:#fff; border:none; }
</style>
</head>
<body>

<h2>Proposals for Treasurer Approval</h2>

<table>
    <thead>
        <tr>
            <th>ID</th><th>Department</th><th>Event Type</th><th>Start Date</th><th>End Date</th><th>Venue</th><th>Status</th><th>Actions</th>
        </tr>
    </thead>
    <tbody>
    <?php if ($result && $result->num_rows > 0): ?>
        <?php while ($row = $result->fetch_assoc()): ?>
        <tr>
            <td><?= htmlspecialchars($row['id']) ?></td>
            <td><?= htmlspecialchars($row['department']) ?></td>
            <td><?= htmlspecialchars($row['event_type']) ?></td>
            <td><?= htmlspecialchars($row['start_date']) ?></td>
            <td><?= htmlspecialchars($row['end_date']) ?></td>
            <td><?= htmlspecialchars($row['venue']) ?></td>
            <td><?= htmlspecialchars($row['status']) ?></td>
            <td>
                <form method="post" action="../proposal/flow.php" style="margin:0;">
                    <!-- Important: assign proposal_id value here -->
                    <input type="hidden" name="proposal_id" value="<?= htmlspecialchars($row['id']) ?>" />
                    <input type="hidden" name="level" value="Dean">


                    <button type="submit" name="action" value="approve" class="approve-btn">Approve</button>
                    <button type="submit" name="action" value="disapprove" class="disapprove-btn">Disapprove</button>
                </form>
            </td>
        </tr>
        <?php endwhile; ?>
    <?php else: ?>
        <tr><td colspan="8" style="text-align:center;">No proposals found for Treasurer.</td></tr>
    <?php endif; ?>
    </tbody>
</table>

</body>
</html>

<?php
$conn->close();
?>
