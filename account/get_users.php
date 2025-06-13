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

    // Include fullname, username, email, password, role, created_at
    $res = $conn->query("SELECT fullname, username, email, password, role, created_at FROM `$table`"); 
    while ($user = $res->fetch_assoc()) {
        $departments[] = [
            'fullname' => $user['fullname'],
            'username' => $user['username'],
            'email' => $user['email'],
            'password' => $user['password'],
            'role' => $user['role'],
            'created_at' => $user['created_at'],
            'department' => $deptName,
            'status' => 'Active'
        ];
    }
}

header('Content-Type: application/json');
echo json_encode($departments);
?>
