<?php
session_start();
$conn = new mysqli("localhost", "root", "", "eventplanner");

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get and sanitize input
$user = isset($_POST['username']) ? trim($_POST['username']) : '';
$pass = isset($_POST['password']) ? trim($_POST['password']) : '';

if (empty($user) || empty($pass)) {
    die("Username or password is empty.");
}

// Check in admin_account
$stmt = $conn->prepare("SELECT adminid AS id, adminuser AS username, adminpass AS password, role, firstlogin FROM admin_account WHERE adminuser = ?");
$stmt->bind_param("s", $user);
$stmt->execute();
$result = $stmt->get_result();

if ($row = $result->fetch_assoc()) {
    if ($pass === $row['password']) {
        $_SESSION['user_logged_in'] = true;
        $_SESSION['admin_logged_in'] = true; // ✅ Add this line
        $_SESSION['username'] = $row['username'];
        $_SESSION['role'] = $row['role'];
        $_SESSION['user_id'] = $row['id'];
        $_SESSION['account_type'] = 'admin';

        if ($row['firstlogin'] === 'yes') {
            header("Location: osas_login_update.php");
            exit();
        }

        header("Location: ../index.php");
        exit();
    } else {
        header("Location: login.php?error=Incorrect+password+for+{$user}");
        exit();
    }
}
$stmt->close();

// Check department tables
$query = "SHOW TABLES LIKE '%_department'";
$res = $conn->query($query);

while ($row = $res->fetch_array()) {
    $table = $row[0];

    $stmt = $conn->prepare("SELECT id, username, password, role, firstlogin, fullname FROM `$table` WHERE username = ?");
    $stmt->bind_param("s", $user);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($userRow = $result->fetch_assoc()) {
        if ($pass === $userRow['password']) {
            $_SESSION['user_logged_in'] = true;
            $_SESSION['username'] = $userRow['username'];
            $_SESSION['role'] = $userRow['role'];
            $_SESSION['department_table'] = $table;
            $_SESSION['user_id'] = $userRow['id'];
            $_SESSION['fullname'] = $userRow['fullname'];
            $_SESSION['account_type'] = 'department';

            if ($userRow['firstlogin'] === 'yes') {
                header("Location: first_login_update.php");
                exit();
            }

            header("Location: ../index.php");
            exit();
        }
    }
    $stmt->close();
}

// If login fails
header("Location: login.php?error=Incorrect+username+or+password");
exit();
