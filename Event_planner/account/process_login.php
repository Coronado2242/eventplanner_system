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

// admin_account
$stmt = $conn->prepare("SELECT adminuser, adminpass, role FROM admin_account WHERE adminuser = ?");

$stmt->bind_param("s", $user);
$stmt->execute();
$result = $stmt->get_result();

if ($row = $result->fetch_assoc()) {
    if ($pass === $row['adminpass']) {
        $_SESSION['admin_logged_in'] = true;
        $_SESSION['admin_username'] = $row['adminuser'];
        $_SESSION['role'] = $row['role']; // ✅ now this works
        header('Location: admin_dashboard.php');
        exit();
    }
}

$stmt->close();

// client_account
$stmt = $conn->prepare("SELECT clientemail, clientpassword FROM client_account WHERE clientemail = ?");
$stmt->bind_param("s", $user);
$stmt->execute();
$result = $stmt->get_result();

if ($row = $result->fetch_assoc()) {
    if (password_verify($pass, $row['clientpassword'])) { 
        $_SESSION['client_logged_in'] = true;
        $_SESSION['client_email'] = $row['clientemail'];
        header('Location: ../index.php');
        exit();
    }
}
$stmt->close();

// If login fails
header('Location: login.php?error=Incorrect+username+or+password');
exit();
?>
