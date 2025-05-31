<?php

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "eventplanner";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// 2. Handle Approve/Disapprove POST request
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['proposal_id'], $_POST['action'])) {
    $id = (int)$_POST['proposal_id'];
    $action = $_POST['action'];

    if ($action === 'approve') {
        $status = 'Approved by Treasurer';
    } elseif ($action === 'disapprove') {
        $status = 'Disapproved by Treasurer';
    } else {
        $status = null;
    }

    if ($status) {
        $stmt = $conn->prepare("UPDATE proposals SET status=? WHERE id=?");
        $stmt->bind_param("si", $status, $id);
        $stmt->execute();
        $stmt->close();

        header("Location: " . $_SERVER['PHP_SELF']);
        exit();
    }
}

// 3. Get proposals for Treasurer approval
$sql = "SELECT * FROM proposals WHERE level='Treasurer' ORDER BY created_at DESC";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<title>Treasurer Dashboard</title>
<style>
    table { width:100%; border-collapse: collapse; }
    th, td { border:1px solid #ccc; padding:8px; }
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
                <form method="post" style="margin:0;">
                    <input type="hidden" name="proposal_id" value="<?= $row['id'] ?>" />
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
