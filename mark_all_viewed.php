<?php
session_start();
$conn = new mysqli("localhost", "root", "", "eventplanner");

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if (isset($_GET['id'])) {
    $id = intval($_GET['id']);
    $stmt = $conn->prepare("UPDATE proposals SET viewed = 1 WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
}

header("Location: " . $_SERVER['HTTP_REFERER']);
exit();
?>
