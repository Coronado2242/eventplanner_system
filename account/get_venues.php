<?php
$conn = new mysqli("localhost", "root", "", "eventplanner");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$query = "SELECT id, organizer, capacity, venue FROM venue_db";
$result = $conn->query($query);

$venues = [];
while ($row = $result->fetch_assoc()) {
    $venues[] = $row;
}

header('Content-Type: application/json');
echo json_encode($venues);
?>
