<?php
session_start();
$conn = new mysqli("localhost", "root", "", "eventplanner");

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['proposal_id'])) {
    $id = intval($_POST['proposal_id']);

    if (isset($_POST['submit_proposal'])) {
        $conn->query("UPDATE sooproposal SET status = 'Submitted' WHERE id = $id");
        $_SESSION['message'] = "Proposal submitted successfully.";
    }

    if (isset($_POST['cancel_proposal'])) {
        $conn->query("UPDATE sooproposal SET status = 'Cancelled' WHERE id = $id");
        $_SESSION['message'] = "Proposal cancelled.";
    }
}

header("Location: ccssoo_dashboard.php");
exit();
