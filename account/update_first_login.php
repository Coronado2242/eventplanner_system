<?php
session_start();
$conn = new mysqli("localhost", "root", "", "eventplanner");

if (!isset($_SESSION['user_logged_in'], $_SESSION['user_id'], $_SESSION['department_table'])) {
    header("Location: login.php");
    exit();
}

$userId = $_SESSION['user_id'];
$table = $_SESSION['department_table'];
$newUsername = trim($_POST['username']);
$fullname = trim($_POST['fullname']);
$email = trim($_POST['email']);
$newPassword = trim($_POST['password']);

$stmt = $conn->prepare("UPDATE `$table` SET username = ?, fullname = ?, password = ?, email = ?, firstlogin = 'no' WHERE id = ?");
$stmt->bind_param("ssssi", $newUsername, $fullname, $newPassword, $email, $userId);

if ($stmt->execute()) {
    $_SESSION['username'] = $newUsername;

    // Redirect to dashboard
    $role = strtolower($_SESSION['role']);
    if (substr($role, -3) === 'soo') {
        header("Location: /eventplanner_system/index.php");
    } else {
        $dashboard = "/eventplanner_system/dashboard/{$role}_dashboard.php";
        header("Location: $dashboard");
    }
    exit();
} else {
    echo "Update failed: " . $stmt->error;
}
