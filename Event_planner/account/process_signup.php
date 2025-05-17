<?php
session_start();
include 'send_verification_code.php'; // Include the function to send the OTP email

// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "eventplanner";

$conn = new mysqli($servername, $username, $password, $dbname);

// Check the connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get and sanitize user inputs
    $email = trim($_POST['email']);
    $confirm_email = trim($_POST['confirm_email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $organization = trim($_POST['organization']);
    $department = trim($_POST['department']);

    // Validate email match
    if ($email !== $confirm_email) {
        header("Location: signup.php?error=" . urlencode("Emails do not match"));
        exit();
    }

    // Validate password match
    if ($password !== $confirm_password) {
        header("Location: signup.php?error=" . urlencode("Passwords do not match"));
        exit();
    }

    // Hash the password
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    // Check if email already exists
    $check_email_query = "SELECT * FROM client_account WHERE clientemail = ?";
    $stmt = $conn->prepare($check_email_query);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        // Email already exists
        $stmt->close();
        $conn->close();
        header("Location: signup.php?error=" . urlencode("Email already registered"));
        exit();
    }
    $stmt->close(); // Close after checking

    // Generate 6-digit OTP
    $verification_code = rand(100000, 999999);

    // Send OTP via email
    $email_sent = sendVerificationCode($email, $verification_code);

    if ($email_sent === true) {
        // Insert into database with OTP
        $insert_query = "INSERT INTO client_account (clientemail, clientpassword, organization, department, verification_code) VALUES (?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($insert_query);
        $stmt->bind_param("sssss", $email, $hashed_password, $organization, $department, $verification_code);

        if ($stmt->execute()) {
            // OTP sent and registration successful
            $_SESSION['email_for_verification'] = $email;  // Store email in session for OTP verification
            $stmt->close();
            $conn->close();
            header("Location: signup.php?verify=true");  // Redirect to show OTP modal
            exit();
        } else {
            // If there's an error during insertion
            $stmt->close();
            $conn->close();
            header("Location: signup.php?error=" . urlencode("Error during registration"));
            exit();
        }
    } else {
        // If OTP email failed to send
        $conn->close();
        header("Location: signup.php?error=" . urlencode($email_sent));  // Send error message back
        exit();
    }
}
?>
