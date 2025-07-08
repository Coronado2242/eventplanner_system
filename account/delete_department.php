<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['department']) || empty($_POST['department'])) {
    echo "Error: Invalid request.";
    exit;
}

$department = $_POST['department'];

// Connect to DB
$conn = new mysqli("localhost", "root", "", "eventplanner");
if ($conn->connect_error) {
    die("Error: Connection failed - " . $conn->connect_error);
}

// Sanitize input
if (!preg_match('/^[a-zA-Z0-9_]+$/', $department)) {
    echo "Error: Invalid department name.";
    exit;
}

// Format: ccs_department, cte_department, etc.
$tableName = strtolower($department) . "_department";

// Check if table exists
$check = $conn->query("SHOW TABLES LIKE '$tableName'");
if ($check->num_rows === 0) {
    echo "Error: Table '$tableName' does not exist.";
    exit;
}

// Drop the table
$sql = "DROP TABLE IF EXISTS `$tableName`";
if ($conn->query($sql) === TRUE) {
    echo "Success: Department '$department' deleted (table: $tableName).";
} else {
    echo "Error deleting table '$tableName': " . $conn->error;
}

$conn->close();
?>
