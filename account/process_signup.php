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

// Validate form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $department = $_POST['department'] ?? '';

    if (empty($department)) {
        header("Location: signup.php?error=Please select a department");
        exit;
    }

    // Define the table name and create department-specific table with venue column
    $table = strtolower($department) . "_department";
    $createTableSQL = "
        CREATE TABLE IF NOT EXISTS `$table` (
            id INT AUTO_INCREMENT PRIMARY KEY,
            username VARCHAR(255) NOT NULL UNIQUE,
            password VARCHAR(255) NOT NULL,
            role VARCHAR(50),
            email VARCHAR(255) DEFAULT '',
            fullname VARCHAR(255) DEFAULT '',
            firstlogin VARCHAR(255) DEFAULT 'yes',
            venue VARCHAR(255) DEFAULT '',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        );
    ";
    if (!$conn->query($createTableSQL)) {
        header("Location: signup.php?error=Database error when creating department table");
        exit;
    }

    // Username and role pairs
    $defaultAccounts = [
        ["{$department}_dean", "{$department}Dean"],
        ["{$department}_facultyadviser", "{$department}Faculty"],
        ["{$department}_sbopresident", "{$department}SBOPresident"],
        ["{$department}_sbovice", "{$department}SBOVice"],
        ["{$department}_sbotreasurer", "{$department}SBOTreasurer"],
        ["{$department}_sboauditor", "{$department}SBOAuditor"],
        ["{$department}_sbosoo", "{$department}SOO"],
    ];

    $defaultPassword = "user12345";

    foreach ($defaultAccounts as [$username, $role]) {
        $check = $conn->prepare("SELECT id FROM `$table` WHERE username = ?");
        $check->bind_param("s", $username);
        $check->execute();
        $check->store_result();
    
        if ($check->num_rows === 0) {
            $email = '';
            $fullname = '';
            $firstlogin = 'yes';
            $venue = ($role === "CCSDean") ? "Voag" : "";
    
            $insert = $conn->prepare("INSERT INTO `$table` (username, password, role, email, fullname, firstlogin, venue) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $insert->bind_param("sssssss", $username, $defaultPassword, $role, $email, $fullname, $firstlogin, $venue);
            $insert->execute();
        }
    }

    header("Location: signup.php?success=Department '$department' added with default users and venue");
    exit;
} else {
    header("Location: signup.php");
    exit;
}
