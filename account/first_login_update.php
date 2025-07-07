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
    <link
        rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css"
    />
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
<body <?php if (!isset($_GET['updated'])) echo 'onload="$(\'#updateModal\').modal(\'show\');"'; ?> >

<!-- First Login Modal -->
<div class="modal fade" id="updateModal" tabindex="-1" role="dialog" data-backdrop="static">
    <div class="modal-dialog" role="document">
        <form method="POST" action="update_first_login.php" class="modal-content" onsubmit="return validatePasswords();">
            <div class="modal-header">
                <h5 class="modal-title">Complete Your Profile</h5>
            </div>
            <div class="modal-body">
                <div class="form-group">
                    <label>Username</label>
                    <input type="text" name="username" class="form-control mb-3" required value="<?= htmlspecialchars($_SESSION['username']) ?>" readonly>
                </div>
                <div class="form-group">
                    <label>Full Name</label>
                    <input type="text" name="fullname" class="form-control" required>
                </div>
                <div class="form-group position-relative">
                    <label>New Password</label>
                    <input type="password" id="password" name="password" class="form-control" required minlength="4">
                    <i class="fa-solid fa-eye-slash password-toggle" onclick="togglePassword('password', this)"></i>
                </div>
                <div class="form-group position-relative">
                    <label>Confirm Password</label>
                    <input type="password" id="confirm_password" class="form-control" required>
                    <i class="fa-solid fa-eye-slash password-toggle" onclick="togglePassword('confirm_password', this)"></i>
                </div>
            </div>
            <div class="modal-footer">
                <button type="submit" class="btn btn-primary">Update & Continue</button>
            </div>
        </form>
    </div>
</div>

<!-- Success Modal -->
<div class="modal fade" id="successModal" tabindex="-1" role="dialog" aria-labelledby="successModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered" role="document">
    <div class="modal-content">
    <div class="modal-header text-white" style="background-color:rgb(30, 100, 206) !important;">

        <h5 class="modal-title" id="successModalLabel">Profile Updated</h5>
        <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        Your profile has been successfully updated.
      </div>
      <div class="modal-footer">
        <a href="../index.php" class="btn btn-success" style="background-color: rgb(30, 100, 206) !important;">Continue</a>
      </div>
    </div>
  </div>
</div>

<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
function togglePassword(id, icon) {
    const input = document.getElementById(id);
    if (input.type === 'password') {
        input.type = 'text';
        icon.classList.remove('fa-eye-slash');
        icon.classList.add('fa-eye');
    } else {
        input.type = 'password';
        icon.classList.remove('fa-eye');
        icon.classList.add('fa-eye-slash');
    }
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

// show success modal if updated=1
<?php
if (isset($_GET['updated']) && $_GET['updated'] == 1) {
    echo "
    document.addEventListener('DOMContentLoaded', function() {
        $('#successModal').modal('show');
    });
    ";
}
?>
</script>
</body>
</html>
