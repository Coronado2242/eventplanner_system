<?php
$host = "localhost";
$user = "root";
$pass = "";
$db = "eventplanner"; 

// Create database connection
$conn = new mysqli($host, $user, $pass, $db);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// SQL to create 'proposals' table if it doesn't exist
$createTableSQL = "CREATE TABLE IF NOT EXISTS proposals (
    id INT AUTO_INCREMENT PRIMARY KEY,
    department VARCHAR(100),
    event_type VARCHAR(100),
    start_date DATE,
    end_date DATE,
    venue VARCHAR(255),
    time VARCHAR(50),
    adviser_form VARCHAR(255),
    certification VARCHAR(255),
    financial VARCHAR(255),
    constitution VARCHAR(255),
    reports VARCHAR(255),
    letter_attachment VARCHAR(255),
    status VARCHAR(50), 
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";

// Execute and check if table creation is successful
if ($conn->query($createTableSQL) === TRUE) {
    echo "Table 'proposals' is ready or already exists.";
} else {
    die(" Table creation failed: " . $conn->error);
}

?>
