<?php
session_start();

if (isset($_SESSION['admin_logged_in'])) {
    header('Location: admin_dashboard.php');
    exit();
}

if (isset($_SESSION['admin_logged_in'])) {
    header('Location: osas_dashboard.php');
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Event Sync - Login</title>
  <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
  <link rel="stylesheet" href="../style/account.css">
</head>

<body>

  <div class="left-section">
  <img src="../img/lspulogo.jpg" alt="University Logo"
  style="width: 200px; height: 200px; border-radius: 50%; box-shadow: 0 4px 8px rgba(0,0,0,0.3); object-fit: cover;">
    <h1>EVENT <span style="color:#0d6efd;">SYNC</span></h1>
  </div>

  <div class="right-section">
    <a href="../index.php" class="close-button">&times;</a>
    <div class="form-box">
      <h2>SIGN IN</h2>
      <?php if (isset($_GET['error'])): ?>
        <p class="error"><?php echo htmlspecialchars($_GET['error']); ?></p>
      <?php endif; ?>
      <form method="POST" action="process_login.php">
        <input type="text" name="username" placeholder="Username" required>

        <div class="password-wrapper">
          <input type="password" name="password" id="password" placeholder="Password" required>
          <i class="fa-solid fa-eye toggle-password" id="togglePassword" onclick="togglePassword()"></i>
        </div>
        <button type="submit">SIGN IN</button>
      </form>
    </div>
  </div>

  <script>
    // Toggle Login Password
    function togglePassword() {
      var passwordInput = document.getElementById("password");
      var toggleIcon = document.getElementById("togglePassword");

      if (passwordInput.type === "password") {
        passwordInput.type = "text";
        toggleIcon.classList.remove('fa-eye');
        toggleIcon.classList.add('fa-eye-slash');
      } else {
        passwordInput.type = "password";
        toggleIcon.classList.remove('fa-eye-slash');
        toggleIcon.classList.add('fa-eye');
      }
    }


    // Toggle Sign Up Passwords
    function toggleSignupPassword() {
      var passwordInput = document.getElementById("signup_password");
      if (passwordInput.type === "password") {
        passwordInput.type = "text";
      } else {
        passwordInput.type = "password";
      }
    }

    function toggleSignupConfirmPassword() {
      var passwordInput = document.getElementById("signup_confirm_password");
      if (passwordInput.type === "password") {
        passwordInput.type = "text";
      } else {
        passwordInput.type = "password";
      }
    }

    // Modal Open/Close
    var modal = document.getElementById("signUpModal");
    var btn = document.getElementById("signUpBtn");
    var closeBtn = document.getElementById("closeModal");

    btn.onclick = function() {
      modal.style.display = "block";
    }

    closeBtn.onclick = function() {
      modal.style.display = "none";
    }

    window.onclick = function(event) {
      if (event.target == modal) {
        modal.style.display = "none";
      }
    }
  </script>

</body>
</html>
