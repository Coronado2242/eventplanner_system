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
    $email = $conn->real_escape_string($_POST['email']);
    $status = $conn->real_escape_string($_POST['status']);
    $venue = $conn->real_escape_string($_POST['venue']);

    // Prepare and execute the insert query
    $insert_query = "INSERT INTO venue_db (organizer, email, status, venue) VALUES (?, ?, ?, ?)";
    $stmt = $conn->prepare($insert_query);
    $stmt->bind_param("ssss", $organizer, $email, $status, $venue);

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
