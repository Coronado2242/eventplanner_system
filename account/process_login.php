<?php
session_start();

// Connect to eventplanner database
$conn = new mysqli("localhost", "root", "", "eventplanner");

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get user input
$user = trim($_POST['username']);
$pass = trim($_POST['password']);

// 1. Check admin_account
$stmt = $conn->prepare("SELECT adminuser, adminpass, role FROM admin_account WHERE adminuser = ?");
$stmt->bind_param("s", $user);
$stmt->execute();
$result = $stmt->get_result();

if ($row = $result->fetch_assoc()) {
    if ($pass === $row['adminpass']) {
        $_SESSION['admin_logged_in'] = true;
        $_SESSION['admin_username'] = $row['adminuser'];
        $_SESSION['role'] = $row['role'];
        header('Location: ../account/admin_dashboard.php');
        exit();
    }
}
$stmt->close();

// 2. Check department tables dynamically
$query = "SHOW TABLES LIKE '%_department'";
$res = $conn->query($query);

while ($row = $res->fetch_array()) {
    $table = $row[0];

    $stmt = $conn->prepare("SELECT id, username, password, role FROM `$table` WHERE username = ?");
    $stmt->bind_param("s", $user);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($userRow = $result->fetch_assoc()) {
        if ($pass === $userRow['password']) {
            $_SESSION['user_logged_in'] = true;
            $_SESSION['username'] = $userRow['username'];
            $_SESSION['role'] = $userRow['role'];
            $_SESSION['department_table'] = $table;

            $role = strtolower($userRow['role']);

            // Redirect to homepage if role ends with 'soo'
            if (substr($role, -3) === 'soo') {
                header("Location: /eventplanner_system/index.php");
                exit();
            }

            // Build dashboard file path
            $dashboardFile = "/eventplanner_system/dashboard/{$role}_dashboard.php";
            $fullPath = $_SERVER['DOCUMENT_ROOT'] . $dashboardFile;

            // Redirect to dashboard if file exists
            if (file_exists($fullPath)) {
                header("Location: $dashboardFile");
                exit();
            } else {
                // Fallback: redirect to homepage
                header("Location: /eventplanner_system/index.php");
                exit();
            }
        }
    }
    $stmt->close();
}

// If login fails
header('Location: login.php?error=Incorrect+username+or+password');
exit();
