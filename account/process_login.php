<?php
session_start();
$conn = new mysqli("localhost", "root", "", "eventplanner");

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Step 1: Get and sanitize user input
$user = isset($_POST['username']) ? trim($_POST['username']) : '';
$pass = isset($_POST['password']) ? trim($_POST['password']) : '';

if (empty($user) || empty($pass)) {
    die("Username or password is empty.");
}

// Step 2: Check in admin_account
$stmt = $conn->prepare("SELECT adminid, adminuser, adminpass, role, firstlogin FROM admin_account WHERE adminuser = ?");
$stmt->bind_param("s", $user);
$stmt->execute();
$result = $stmt->get_result();

if ($row = $result->fetch_assoc()) {

    if ($pass === $row['adminpass']) {
        $_SESSION['admin_logged_in'] = true;
        $_SESSION['admin_username'] = $row['adminuser'];
        $_SESSION['role'] = $row['role'];
        $_SESSION['admin_id'] = $row['adminid'];

        // Handle first login redirect
        if (isset($row['firstlogin']) && $row['firstlogin'] === 'yes') {
            header("Location: osas_login_update.php");
            exit();
        }

        // Redirect to role dashboard if it exists
        $role = strtolower($row['role']);

        switch ($role) {
            case 'superadmin':
                header("Location: ../account/admin_dashboard.php");
                break;
            case 'osas':
                header("Location: ../account/osas_dashboard.php");
                break;
            default:
                // fallback dashboard
                header("Location: ../account/admin_dashboard.php");
        }
        exit();

    } else {
        // Password incorrect
        header("Location: login.php?error=Incorrect+password+for+{$user}");
        exit();
    }
}


$stmt->close();

// 2. Check department tables dynamically
$query = "SHOW TABLES LIKE '%_department'";
$res = $conn->query($query);

while ($row = $res->fetch_array()) {
    $table = $row[0];

    $stmt = $conn->prepare("SELECT id, username, password, role, firstlogin FROM `$table` WHERE username = ?");
    $stmt->bind_param("s", $user);
    $stmt->execute();
    $result = $stmt->get_result();

    // ✅ Fetch the user row properly
    if ($userRow = $result->fetch_assoc()) {
        if ($pass === $userRow['password']) {
            $_SESSION['user_logged_in'] = true;
            $_SESSION['username'] = $userRow['username'];
            $_SESSION['role'] = $userRow['role'];
            $_SESSION['department_table'] = $table;
            $_SESSION['user_id'] = $userRow['id'];

            if ($userRow['firstlogin'] === 'yes') {
                header("Location: first_login_update.php");
                exit();
            }

            $role = strtolower($userRow['role']);
            if (substr($role, -3) === 'soo') {
                header("Location: ../index.php");
                exit();
            }

            $dashboardFile = "../dashboard/{$role}_dashboard.php";
            $fullPath = $_SERVER['DOCUMENT_ROOT'] . $dashboardFile;

            if (file_exists($fullPath)) {
                header("Location: $dashboardFile");
                exit();
            } else {
                header("Location: ../dashboard/{$role}_dashboard.php");
                exit();
            }
        }
    }
    $stmt->close();
}

// If login fails
header('Location: login.php?error=Incorrect+username+or+password');
exit();
