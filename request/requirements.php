<?php
$host = "localhost";
$user = "root";
$pass = "";
$db   = "eventplanner";

$conn = mysqli_connect($host, $user, $pass, $db);

if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

$sql = "SELECT * FROM proposals WHERE status = 'Pending'";
$result = mysqli_query($conn, $sql);

while ($row = mysqli_fetch_assoc($result)) {
    echo '<div style="border: 1px solid #ccc; border-radius: 10px; padding: 20px; max-width: 900px; position: relative; margin-bottom: 20px;">';
    echo '<h2 style="margin-top: 0;">' . htmlspecialchars($row['event_type']) . '</h2>';

    echo '<div style="display: flex; flex-wrap: wrap; gap: 40px;">';
    echo '<div><strong>Date</strong><br>' . date("M d Y", strtotime($row['start_date'])) . ' - ' . date("M d Y", strtotime($row['end_date'])) . '</div>';
    echo '<div><strong>Time</strong><br><span style="color: gray;">' . htmlspecialchars($row['time']) . '</span></div>';
    echo '<div><strong>Venue</strong><br><span style="color: gray;">' . htmlspecialchars($row['venue']) . '</span></div>';
    echo '<div><strong>Department</strong><br>' . htmlspecialchars($row['department']) . '</div>';
    echo '</div>';

    // Attachments Section
    echo '<h3 style="margin-top: 20px;">Requirements</h3>';
    echo '<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 10px;">';

    $requirements = [
        "Letter Attachment" => "letter_attachment",
        "Adviser Commitment form" => "adviser_form",
        "Constitution ang by-laws of the Org." => "constitution",
        "Certification from Responsive Dean/Associate Dean" => "certification",
        "Accomplishment reports" => "reports",
        "Financial Report" => "financial",
        "Plan of Activities" => "plan",
        "Budget Plan" => "budget"
    ];

    foreach ($requirements as $label => $field) {
        echo '<div style="background: #f1f1f1; padding: 10px; border-radius: 10px;">';
        echo '<small style="color: red;">Requirement*</small><br>';
        echo '<strong>' . $label . '</strong><br>';
        if (!empty($row[$field])) {
            echo '<a href="../proposal/' . htmlspecialchars($row[$field]) . '" target="_blank" style="display: inline-block; margin-top: 5px; background-color: #004080; color: white; padding: 5px 10px; border-radius: 5px; text-decoration: none;">View Attachment</a>';
        } else {
            echo '<span style="color: gray; display: inline-block; margin-top: 5px;">No Attachment</span>';
        }
        echo '</div>';
    }

    echo '</div>';
    echo '</div>';
    echo '</div>';
}
?>
