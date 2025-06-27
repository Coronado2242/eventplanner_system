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

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get and sanitize POST data
    $department = trim($_POST['department'] ?? '');
    $activity_name = trim($_POST['activity_name'] ?? '');
    $objective = trim($_POST['objective'] ?? '');
    $brief_description = trim($_POST['brief_description'] ?? '');
    $person_involved = trim($_POST['person_involved'] ?? '');

    // Validation
    if (empty($department) || empty($activity_name) || empty($objective) || empty($brief_description) || empty($person_involved)) {
        header("Location: activities.php?error=All fields are required");
        exit;
    }

    // Create the table if it doesn't exist
    $createTableSQL = "
        CREATE TABLE IF NOT EXISTS activities (
            id INT AUTO_INCREMENT PRIMARY KEY,
            department VARCHAR(255) NOT NULL,
            activity_name VARCHAR(255) NOT NULL,
            objective TEXT NOT NULL,
            brief_description TEXT NOT NULL,
            person_involved TEXT NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        );
    ";

    if (!$conn->query($createTableSQL)) {
        header("Location: add_activity.php?error=Failed to create table");
        exit;
    }

    // Insert the activity into the table
    $stmt = $conn->prepare("
        INSERT INTO activities (department, activity_name, objective, brief_description, person_involved) 
        VALUES (?, ?, ?, ?, ?)
    ");
    $stmt->bind_param("sssss", $department, $activity_name, $objective, $brief_description, $person_involved);

    if ($stmt->execute()) {
        header("Location: activities.php?success=Activity added successfully");
    } else {
        header("Location: activities.php?error=Failed to add activity");
    }

    exit;
} else {
    header("Location: activity.php");
    exit;
}
