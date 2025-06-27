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
    $venue = trim($_POST['venue'] ?? '');

    if (empty($venue)) {
        header("Location: solo_signup.php?error=Please select a venue");
        exit;
    }

    // Create the unified solo_accounts table if it doesn't exist
    $createTableSQL = "
        CREATE TABLE IF NOT EXISTS solo_accounts (
            id INT AUTO_INCREMENT PRIMARY KEY,
            username VARCHAR(255) NOT NULL UNIQUE,
            password VARCHAR(255) NOT NULL,
            role VARCHAR(50),
            venue VARCHAR(255),
            email VARCHAR(255) DEFAULT '',
            fullname VARCHAR(255) DEFAULT '',
            firstlogin VARCHAR(255) DEFAULT 'yes',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        );
    ";

    if (!$conn->query($createTableSQL)) {
        header("Location: solo_signup.php?error=Database error creating solo_accounts table");
        exit;
    }

    // Default solo account info
    $normalized = strtolower(str_replace(' ', '', $venue));  // e.g., researchhall
    $username = $normalized . "_incharge";                   // e.g., researchhall_incharge
    $password = "user12345";                                  // can be hashed later
    $role = ucfirst($venue) . "InCharge";                    // e.g., Research HallInCharge
    $email = '';
    $fullname = '';
    $firstlogin = 'yes';

    // Check if account already exists
    $check = $conn->prepare("SELECT id FROM solo_accounts WHERE username = ?");
    $check->bind_param("s", $username);
    $check->execute();
    $check->store_result();

    if ($check->num_rows === 0) {
        $insert = $conn->prepare("INSERT INTO solo_accounts (username, password, role, venue, email, fullname, firstlogin) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $insert->bind_param("sssssss", $username, $password, $role, $venue, $email, $fullname, $firstlogin);
        $insert->execute();
    }

    header("Location: solo_signup.php?success=Venue '$venue' added with solo account '$username'");
    exit;
} else {
    header("Location: solo_signup.php");
    exit;
}
