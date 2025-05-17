<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "eventplanner";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$departments = [];
$query = "SHOW TABLES LIKE '%_department'";
$result = $conn->query($query);

while ($row = $result->fetch_array()) {
    $table = $row[0];
    $deptName = ucfirst(str_replace('_department', '', $table));
    $res = $conn->query("SELECT id, username, password, role FROM `$table`");  // add password here
    while ($user = $res->fetch_assoc()) {
        $departments[] = [
            'email' => $user['username'],
            'password' => $user['password'],  // include password here
            'department' => $deptName,
            'role' => $user['role'],
            'status' => 'Active'
        ];
    }
}

header('Content-Type: application/json');
echo json_encode($departments);
?>
