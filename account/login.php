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

$conn = new mysqli("localhost", "root", "", "eventplanner");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$logoSrc = "../img/lspulogo.jpg"; // fallback

$sql = "SELECT filepath FROM site_logo ORDER BY date_uploaded DESC LIMIT 1";
$result = $conn->query($sql);

if ($result && $row = $result->fetch_assoc()) {
    if (!empty($row['filepath'])) {
        $logoSrc = "" . htmlspecialchars($row['filepath']); 
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Event Sync - Login</title>
  <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">

</head>
<style>
  /* Global Reset & Box-Sizing */
  * {
    box-sizing: border-box;
    margin: 0;
    padding: 0;
  }

  body {
    font-family: 'Roboto', sans-serif;
    display: flex;
    height: 100vh;
    flex-direction: row;
  }

  .left-section {
    background: linear-gradient(to right, #0b0b3b, #3a3a52);
    color: white;
    flex: 1;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    text-align: center;
    padding: 2rem;
  }

  .left-section img {
    width: 200px;
    height: 200px;
    object-fit: cover;
    border-radius: 50%;
    box-shadow: 0 4px 8px rgba(0,0,0,0.3);
    margin-bottom: 2rem;
  }

  .left-section h1 {
    font-weight: 900;
    margin: 0;
    font-size: 2rem;
  }

  .right-section {
    background: #ffffff;
    flex: 1;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 2rem;
    position: relative;
  }

  .close-button {
    position: absolute;
    top: 20px;
    right: 30px;
    font-size: 2rem;
    color: #000;
    text-decoration: none;
  }

  .form-box {
    width: 100%;
    max-width: 400px;
    display: flex;
    flex-direction: column;
    gap: 1rem;
  }

  .form-box h2 {
    text-align: center;
    font-weight: 700;
    margin-bottom: 1rem;
    font-size: 2rem;
  }

  .form-box input[type="text"],
  .form-box input[type="email"],
  .form-box input[type="password"],
  .form-box select,
  .form-box button {
    width: 100%;
    padding: 0.75rem;
    font-size: 1rem;
    border-radius: 5px;
    font-family: inherit;
  }

  .form-box input,
  .form-box select {
    border: 1px solid #ccc;
  }

  .form-box button {
    background: #074a78;
    color: white;
    border: none;
    font-weight: 700;
    cursor: pointer;
    margin-top: 0.5rem;
  }

  .password-wrapper {
    position: relative;
    width: 100%;
  }

  .password-wrapper input {
    padding-right: 2.5rem;
  }

  .toggle-password {
    position: absolute;
    right: 10px;
    top: 50%;
    transform: translateY(-50%);
    cursor: pointer;
    font-size: 18px;
    color: #666;
  }

  .form-box .links {
    display: flex;
    justify-content: space-between;
    font-size: 0.9rem;
  }

  .form-box .links a {
    text-decoration: none;
    color: #0645ad;
  }

  .error {
    color: red;
    text-align: center;
    margin-bottom: 0.5rem;
  }

  /* Responsive Styles */
  @media (max-width: 768px) {
    body {
      flex-direction: column;
      height: auto;
    }

    .left-section,
    .right-section {
      flex: none;
      width: 100%;
      padding: 1.5rem;
    }

    .left-section img {
      width: 150px;
      height: 150px;
    }

    .left-section h1 {
      font-size: 1.5rem;
    }

    .form-box {
      max-width: 100%;
    }

    .close-button {
      top: 10px;
      right: 15px;
      font-size: 1.5rem;
    }
  }
</style>

<body>

  <div class="left-section">
  <img src="<?php echo $logoSrc; ?>" alt="Logo" style="border-radius:50%; box-shadow:0 4px 8px rgba(0,0,0,0.3);">
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
