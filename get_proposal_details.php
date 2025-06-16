<?php
if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $conn = new mysqli("localhost", "root", "", "eventplanner");

    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    $id = (int)$_GET['id'];

    // Update viewed = 1
    $updateStmt = $conn->prepare("UPDATE proposals SET viewed = 1 WHERE id = ?");
    $updateStmt->bind_param("i", $id);
    $updateStmt->execute();
    $updateStmt->close();

    // Fetch proposal details
    $stmt = $conn->prepare("SELECT event_type, department, start_date, end_date, venue, time, remarks FROM proposals WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->bind_result($event_type, $department, $start_date, $end_date, $venue, $time, $remarks);

    if ($stmt->fetch()) {
        echo "<p><strong>Event:</strong> $event_type</p>";
        echo "<p><strong>Department:</strong> $department</p>";
        echo "<p><strong>Start Date:</strong> $start_date</p>";
        echo "<p><strong>End Date:</strong> $end_date</p>";
        echo "<p><strong>Venue:</strong> $venue</p>";
        echo "<p><strong>Time:</strong> $time</p>";
        echo "<p><strong>Remarks:</strong><br><i>$remarks</i></p>";
    } else {
        echo "No details found.";
    }

    $stmt->close();
    $conn->close();
}
?>
