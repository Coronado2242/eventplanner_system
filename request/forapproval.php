
<?php
$host = "localhost";
$user = "root";
$pass = "";
$db   = "eventplanner";

$conn = mysqli_connect($host, $user, $pass, $db);

// Check connection
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}



$sql = "SELECT * FROM proposals WHERE status = 'Pending'";
$result = mysqli_query($conn, $sql);

while ($row = mysqli_fetch_assoc($result)) {
    echo '<div style="border: 1px solid #ccc; border-radius: 10px; padding: 20px; max-width: 800px; position: relative; margin-bottom: 20px;">';
    echo '<h2 style="margin-top: 0;">' . htmlspecialchars($row['event_type']) . '</h2>';
    echo '<span style="position: absolute; top: 20px; right: 20px; color: #FFA07A; font-weight: bold;">' . htmlspecialchars($row['status']) . '</span>';
    
    echo '<div style="display: flex; flex-wrap: wrap; gap: 40px;">';
    echo '<div><strong>Date</strong><br>' . date("M d Y", strtotime($row['start_date'])) . ' - ' . date("M d Y", strtotime($row['end_date'])) . '</div>';
    echo '<div><strong>Time</strong><br><span style="color: gray;">' . htmlspecialchars($row['time']) . '</span></div>';
    echo '<div><strong>Venue</strong><br><span style="color: gray;">' . htmlspecialchars($row['venue']) . '</span></div>';
    echo '<div><strong>Department</strong><br>' . htmlspecialchars($row['department']) . '</div>';
    echo '<div><strong>Requirements</strong><br><a href="../dashboard/ccsdean_dashboard.php?tab=requirements" target="requirementsTab" style="background-color: #004080; color: white; padding: 5px 10px; border-radius: 5px; text-decoration: none;">View Attachment</a></div>';
    echo '</div>';

    echo '<div style="margin-top: 20px;">';
    echo '<form method="POST" action="approve_request.php" style="display:inline;">';
    echo '<input type="hidden" name="id" value="' . $row['id'] . '">';
    echo '<button type="submit" name="approve" style="background-color: green; color: white; border: none; padding: 8px 16px; border-radius: 20px; margin-right: 10px;">Approve</button>';
    echo '</form>';

    echo '<form method="POST" action="approve_request.php" style="display:inline;">';
    echo '<input type="hidden" name="id" value="' . $row['id'] . '">';
    echo '<button type="submit" name="disapprove" style="background-color: red; color: white; border: none; padding: 8px 16px; border-radius: 20px;">Disapprove</button>';
    echo '</form>';
    echo '</div>';
    echo '</div>';
}
?>
