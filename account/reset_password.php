<?php
session_start();

if (!isset($_SESSION['admin_logged_in'])) {
    http_response_code(403);
    echo "Unauthorized.";
    exit;
}

header('Content-Type: text/plain');

// Check required fields
if (!isset($_POST['username']) || !isset($_POST['table'])) {
    http_response_code(400);
    echo "Missing parameters.";
    exit;
}

$username = trim($_POST['username']);
$table = strtolower(trim($_POST['table']));
$defaultPassword = "user12345";

// ✅ ALLOWED tables
$allowedTables = ['solo_accounts', 'ccs_department', 'cte_department', 'cas_department'];

if (!in_array($table, $allowedTables, true)) {
    http_response_code(400);
    echo "Invalid table: '$table'\nAllowed: " . implode(', ', $allowedTables);
    exit;
}

// Connect to database
$conn = new mysqli("localhost", "root", "", "eventplanner");
if ($conn->connect_error) {
    http_response_code(500);
    echo "Database connection failed: " . $conn->connect_error;
    exit;
}

// Update password by username
$query = "UPDATE `$table` SET password = ? WHERE username = ?";
$stmt = $conn->prepare($query);

if (!$stmt) {
    http_response_code(500);
    echo "Prepare failed: " . $conn->error;
    exit;
}

$stmt->bind_param("ss", $defaultPassword, $username);

if ($stmt->execute()) {
    echo "Password for $username has been reset to default (user12345).";
} else {
    http_response_code(500);
    echo "Failed to reset password.";
}

$stmt->close();
$conn->close();
?>
