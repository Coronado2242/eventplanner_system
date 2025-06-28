<?php
session_start();
$conn = new mysqli("localhost", "root", "", "eventplanner");

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['proposal_id'])) {
    $id = intval($_POST['proposal_id']);

    if (isset($_POST['submit_proposal'])) {
        $stmt = $conn->prepare("UPDATE sooproposal SET status = 'Pending', submit = 'submitted', level = 'Venues' WHERE id = ?");
        $stmt->bind_param("i", $id);
        if ($stmt->execute()) {
            $_SESSION['message'] = "Proposal submitted successfully.";
        } else {
            $_SESSION['message'] = "Error submitting proposal.";
        }
        $stmt->close();
    } elseif (isset($_POST['cancel_proposal'])) {
        $stmt = $conn->prepare("UPDATE sooproposal SET status = 'Cancelled' WHERE id = ?");
        $stmt->bind_param("i", $id);
        if ($stmt->execute()) {
            $_SESSION['message'] = "Proposal cancelled.";
        } else {
            $_SESSION['message'] = "Error cancelling proposal.";
        }
        $stmt->close();
    }
}

header("Location: ccssoo_dashboard.php");
exit();
?>
