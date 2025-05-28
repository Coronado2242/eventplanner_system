<?php
$proposal_id = intval($_POST['proposal_id']);
$budget = floatval($_POST['budget']);

$conn = new mysqli("localhost", "root", "", "eventplanner");
$conn->query("UPDATE proposals SET budget_amount = $budget, budget_approved = 1 WHERE id = $proposal_id");
$conn->close();

echo "Budget updated successfully.";
?>
