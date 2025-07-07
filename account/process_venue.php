<?php
session_start();

// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "eventplanner";

$conn = new mysqli($servername, $username, $password, $dbname);

// Check the connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get and sanitize user inputs
    $organizer = $conn->real_escape_string($_POST['organization']);
    $capacity = intval($_POST['capacity']); // ensure numeric
    $venue = $conn->real_escape_string($_POST['venue']);

    // Check if the venue already exists
    $check_query = "SELECT id FROM venue_db WHERE venue = ?";
    $check_stmt = $conn->prepare($check_query);
    $check_stmt->bind_param("s", $venue);
    $check_stmt->execute();
    $check_stmt->store_result();

    if ($check_stmt->num_rows > 0) {
        // Venue exists, redirect with error
        $check_stmt->close();
        $conn->close();
        header("Location: venue.php?error=" . urlencode("Venue already exists."));
        exit();
    }
    $check_stmt->close();

    // Insert the new venue
    $insert_query = "INSERT INTO venue_db (organizer, capacity, venue) VALUES (?, ?, ?)";
    $stmt = $conn->prepare($insert_query);
    $stmt->bind_param("sis", $organizer, $capacity, $venue);

    if ($stmt->execute()) {
        $stmt->close();
        $conn->close();
        header("Location: venue.php?success=1");
        exit();
    } else {
        $stmt->close();
        $conn->close();
        header("Location: venue.php?error=" . urlencode("Error adding venue."));
        exit();
    }
}
?>
