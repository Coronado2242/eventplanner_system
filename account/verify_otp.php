<?php
session_start();

// DB Connection
$conn = new mysqli("localhost", "root", "", "eventplanner");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$email = $_POST['email'] ?? '';
$enteredOtp = $_POST['otp'] ?? '';

if (empty($email) || empty($enteredOtp)) {
    header("Location: signup.php?otp_error=1");
    exit();
}

// Fetch OTP from DB
$stmt = $conn->prepare("SELECT verification_code FROM client_account WHERE clientemail = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();

if ($row = $result->fetch_assoc()) {
    $dbOtp = trim($row['verification_code']);
    if ($enteredOtp === $dbOtp) {
        // Success
        unset($_SESSION['email_for_verification']); // Optional: clear session
        echo "<script>alert('OTP verified successfully!'); window.location.href='login.php';</script>";
    } else {
        // Failed - redirect back
        $_SESSION['email_for_verification'] = $email;
        header("Location: signup.php?otp_error=1");
    }
} else {
    // Email not found
    header("Location: signup.php?otp_error=1");
}
exit();
