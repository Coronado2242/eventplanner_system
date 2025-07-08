<?php
session_start();
$conn = new mysqli("localhost", "root", "", "eventplanner");

if (!isset($_SESSION['admin_username'])) {
    header("Location: login.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $newPass = trim($_POST["new_password"]);
    $user = $_SESSION['admin_username'];

    $stmt = $conn->prepare("UPDATE admin_account SET adminpass = ?, firstlogin = 'no' WHERE adminuser = ?");
    $stmt->bind_param("ss", $newPass, $user);
    if ($stmt->execute()) {
        echo "<script>
                alert('Password updated successfully!');
                window.location.href = '../dashboard/osas.php';
              </script>";
        exit();
    } else {
        echo "<script>alert('Failed to update password.');</script>";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>First Login - Update Password</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <!-- Modal -->
    <div class="modal show d-block" tabindex="-1">
      <div class="modal-dialog">
        <div class="modal-content">
          <form method="POST">
              <div class="modal-header">
                <h5 class="modal-title">Edit User</h5>
              </div>
              <div class="modal-body">
                  <div class="mb-3">
                      <label for="new_password" class="form-label">New Password</label>
                      <input type="password" class="form-control" id="new_password" name="new_password" required>
                  </div>
              </div>
              <div class="modal-footer">
                  <button type="submit" class="btn btn-primary">Update</button>
              </div>
          </form>
        </div>
      </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
