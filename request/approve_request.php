<?php
$host = "localhost";
$user = "root";
$pass = "";
$db   = "eventplanner";

$conn = mysqli_connect($host, $user, $pass, $db);

// Check connection
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}



if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['id'])) {
    $id = intval($_POST['id']);
    
    if (isset($_POST['approve'])) {
        $status = 'approved';
    } elseif (isset($_POST['disapprove'])) {
        $status = 'disapproved';
    } else {
        exit("Invalid action.");
    }

    $sql = "UPDATE proposals SET status = ? WHERE id = ?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, 'si', $status, $id);
    
    if (mysqli_stmt_execute($stmt)) {
        header("Location: ccsdean_dashboard.php"); // Refresh to see changes
        exit();
    } else {    
        echo "Error updating request.";
    }
}
?>
