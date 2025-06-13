<?php
// edit_venue.php

header('Content-Type: application/json');

// Include your DB connection
$host = 'localhost';
$db = 'eventplanner';
$user = 'root';
$pass = '';
$conn = new PDO("mysql:host=$host;dbname=$db", $user, $pass);

$data = json_decode(file_get_contents("php://input"), true);

// Validate input
if (!isset($data['id'], $data['organizer'], $data['email'], $data['venue'])) {
    echo json_encode("Invalid input.");
    exit;
}

$id = $data['id'];
$organizer = $data['organizer'];
$email = $data['email'];
$venue = $data['venue'];

// Update query
$sql = "UPDATE venue_db SET organizer = ?, email = ?, venue = ? WHERE id = ?";
$stmt = $conn->prepare($sql);
$success = $stmt->execute([$organizer, $email, $venue, $id]);

if ($success) {
    echo json_encode("Venue updated successfully.");
} else {
    echo json_encode("Failed to update venue.");
}
?>
