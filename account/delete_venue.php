<?php
if ($_SERVER["REQUEST_METHOD"] === "GET" && isset($_GET['id'])) {
    $conn = new mysqli("localhost", "root", "", "eventplanner");
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }
    $id = intval($_GET['id']);
    $stmt = $conn->prepare("DELETE FROM venue_db WHERE id = ?");
    $stmt->bind_param("i", $id);
    if ($stmt->execute()) {
        echo "Venue deleted successfully.";
    } else {
        echo "Error deleting venue.";
    }
    $stmt->close();
    $conn->close();
} else {
    echo "Invalid request.";
}
?>
