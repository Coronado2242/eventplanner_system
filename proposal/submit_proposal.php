<?php
session_start();

$host = "localhost";
$user = "root";
$pass = "";
$db = "eventplanner";
$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}

if (!isset($_SESSION['proposal_id'])) {
    echo "<script>alert('No proposal ID found.'); window.location.href='proposal.php';</script>";
    exit;
}

$proposal_id = $_SESSION['proposal_id'];

$stmt = $conn->prepare("UPDATE proposals SET submit = 'submitted', level = 'CCS Treasurer', viewed = '0' WHERE id = ?");
if (!$stmt) {
    die("Prepare failed: " . $conn->error);
}
$stmt->bind_param("i", $proposal_id);

if ($stmt->execute()) {
    unset($_SESSION['proposal_id']);
    unset($_SESSION['form_data']);
    unset($_SESSION['uploaded']);

    echo "<script>
        alert('Proposal submitted successfully.');
        window.location.href = 'proposal.php';
    </script>";
} else {
    echo "<script>
        alert('Error submitting proposal.');
        window.location.href = 'proposal.php';
    </script>";
}

$stmt->close();
$conn->close();
?>
