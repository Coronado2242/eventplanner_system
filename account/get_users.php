<?php
// DB Connection
$conn = new mysqli("localhost", "root", "", "eventplanner");

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Make sure these column names match your actual database
$query = "SELECT id, clientemail, department, organization, verification_code FROM client_account";
$result = $conn->query($query);

$users = [];

while ($row = $result->fetch_assoc()) {
    $users[] = $row;
}

header('Content-Type: application/json');
echo json_encode($users);
?>
