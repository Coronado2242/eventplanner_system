<?php
// delete_department.php
$conn = new mysqli("localhost", "root", "", "eventplanner");

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['department'])) {
    $department = $_POST['department'];

    // Check kung may user pa sa department
    $check = $conn->query("SELECT COUNT(*) as total FROM users WHERE department='$department'");
    $row = $check->fetch_assoc();

    if ($row['total'] > 0) {
        echo "Cannot delete. Department has active users.";
    } else {
        $conn->query("DELETE FROM departments WHERE department_name='$department'");
        echo "Department deleted successfully.";
    }
}
?>
