<?php
session_start();

// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "venue_db";

$conn = new mysqli($servername, $username, $password, $dbname);

// Check the connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get and sanitize user inputs
    $organizer = $conn->real_escape_string($_POST['organization']);
    $email = $conn->real_escape_string($_POST['email']);
    $venue = $conn->real_escape_string($_POST['venue']);

    // Prepare and execute the insert query
    $insert_query = "INSERT INTO venue_db (organizer, email, venue) VALUES (?, ?, ?)";
    $stmt = $conn->prepare($insert_query);
    $stmt->bind_param("sss", $organizer, $email, $venue);

    if ($stmt->execute()) {
        $stmt->close();
        $conn->close();
        header("Location: venue.php?success=1");
        exit();
    } else {
        $stmt->close();
        $conn->close();
        header("Location: venue.php?error=" . urlencode("Error adding venue"));
        exit();
    }
}
?>
