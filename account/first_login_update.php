<?php
session_start();
if (!isset($_SESSION['user_logged_in']) || !isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>First Login Update</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <style>
        .password-toggle {
            cursor: pointer;
            position: absolute;
            right: 15px;
            top: 38px;
        }
        .position-relative {
            position: relative;
        }
    </style>
</head>
<body onload="$('#updateModal').modal('show');">

<div class="modal fade" id="updateModal" tabindex="-1" role="dialog" data-backdrop="static">
    <div class="modal-dialog" role="document">
        <form method="POST" action="update_first_login.php" class="modal-content" onsubmit="return validatePasswords();">
            <div class="modal-header">
                <h5 class="modal-title">Complete Your Profile</h5>
            </div>
            <div class="modal-body">
                <div class="form-group">
                    <label>Full Name</label>
                    <input type="text" name="fullname" class="form-control" required>
                </div>
                <div class="form-group">
                    <label>Email</label>
                    <input type="text" name="email" class="form-control" required>
                </div>
                <div class="form-group position-relative">
                    <label>New Password</label>
                    <input type="password" id="password" name="password" class="form-control" required minlength="4">
                    <span class="password-toggle" onclick="togglePassword('password')">&#128065;</span>
                </div>
                <div class="form-group position-relative">
                    <label>Confirm Password</label>
                    <input type="password" id="confirm_password" class="form-control" required>
                    <span class="password-toggle" onclick="togglePassword('confirm_password')">&#128065;</span>
                </div>
            </div>
            <div class="modal-footer">
                <button type="submit" class="btn btn-primary">Update & Continue</button>
            </div>
        </form>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
function togglePassword(id) {
    const input = document.getElementById(id);
    input.type = input.type === 'password' ? 'text' : 'password';
}

function validatePasswords() {
    const pass = document.getElementById('password').value;
    const confirm = document.getElementById('confirm_password').value;
    if (pass !== confirm) {
        alert('Passwords do not match.');
        return false;
    }
    return true;
}
</script>
</body>
</html>
