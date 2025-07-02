<?php
session_start();
$conn = new mysqli("localhost", "root", "", "eventplanner");

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["proposal_id"])) {
    $proposal_id = intval($_POST["proposal_id"]);

    // For example, set status to Submitted
    $sql = "UPDATE sooproposal SET status = 'Submitted' WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $proposal_id);
    $stmt->execute();

    header("Location: dashboard.php?submit=success");
    exit();
}
?>
