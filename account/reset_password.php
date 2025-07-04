<?php
session_start();

if (!isset($_SESSION['admin_logged_in'])) {
    http_response_code(403);
    echo "Unauthorized.";
    exit;
}

header('Content-Type: text/plain');

// Decode JSON input
$input = json_decode(file_get_contents('php://input'), true);

if (!isset($input['id'], $input['table'])) {
    http_response_code(400);
    echo "Missing parameters.";
    exit;
}

$id = intval($input['id']);
$table = trim($input['table']);
$defaultPassword = "123456";

// ✅ Allow only these tables
$allowedTables = ['solo_accounts', 'ccs_department', 'cte_department'];

if (!in_array($table, $allowedTables, true)) {
    http_response_code(400);
    echo "Invalid table.";
    exit;
}

// Connect to DB
$conn = new mysqli("localhost", "root", "", "eventplanner");
if ($conn->connect_error) {
    http_response_code(500);
    echo "Database connection failed.";
    exit;
}

$query = "UPDATE `$table` SET password = ? WHERE id = ?";
$stmt = $conn->prepare($query);
if (!$stmt) {
    http_response_code(500);
    echo "Prepare failed: " . $conn->error;
    exit;
}

$stmt->bind_param("si", $defaultPassword, $id);

if ($stmt->execute()) {
    echo "Password has been reset to default (123456).";
} else {
    http_response_code(500);
    echo "Failed to reset password.";
}

$stmt->close();
$conn->close();
?>
