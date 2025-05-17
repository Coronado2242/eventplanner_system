<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Event Sync - Sign Up</title>
  <style>
    /* Basic page structure and styles */
    body {
      margin: 0;
      font-family: Arial, sans-serif;
      display: flex;
      height: 100vh;
    }
    .left-panel {
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
    .left-panel img {
        width: 200px;
        margin-bottom: 2rem;
    }
    .left-panel h1 {
      font-weight: bold;
    }
    .left-panel h1 span {
      color: #1e4dd8;
    }
    .signup-form-container {
        background: #ffffff;
        flex: 1;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 2rem;
        position: relative;
    }
    .form-box {
      width: 100%;
      max-width: 400px;
    }
    .form-box h2 {
      text-align: center;
      margin-bottom: 20px;
      font-weight: bold;
    }
    form input, form select {
      width: 100%;
      padding: 12px 15px;
      margin: 10px 0;
      border: 1px solid #ccc;
      border-radius: 10px;
      font-size: 16px;
    }
    .terms {
      margin-top: 10px;
      font-size: 14px;
    }
    .terms input {
      margin-right: 5px;
    }
    .terms a {
      color: #1e4dd8;
      text-decoration: none;
    }
    .signup-button {
      width: 100%;
      background-color: #004080;
      color: white;
      padding: 12px;
      font-size: 18px;
      border: none;
      border-radius: 12px;
      margin-top: 20px;
      cursor: pointer;
      transition: background-color 0.3s;
    }
    .signup-button:hover {
      background-color: #003366;
    }
    .close-button {
      position: absolute;
      top: 20px;
      right: 30px;
      font-size: 30px;
      text-decoration: none;
      color: black;
    }

    /* OTP Modal Styles */
    .otp-modal {
      display: none; /* Hidden by default */
      position: fixed;
      z-index: 1;
      left: 0;
      top: 0;
      width: 100%;
      height: 100%;
      background-color: rgba(0, 0, 0, 0.4); /* Black with transparency */
    }

    .otp-modal-content {
      background-color: #fff;
      margin: 15% auto;
      padding: 20px;
      border: 1px solid #888;
      width: 80%;
      max-width: 400px;
    }

    .otp-modal .close {
      color: #aaa;
      font-size: 28px;
      font-weight: bold;
      position: absolute;
      top: 10px;
      right: 20px;
    }

    .otp-modal .close:hover,
    .otp-modal .close:focus {
      color: black;
      text-decoration: none;
      cursor: pointer;
    }

    .otp-modal input {
      width: 100%;
      padding: 12px;
      margin: 10px 0;
      border: 1px solid #ccc;
      border-radius: 10px;
      font-size: 16px;
    }
    .otp-modal button {
      width: 100%;
      padding: 12px;
      background-color: #004080;
      color: white;
      border: none;
      border-radius: 12px;
      cursor: pointer;
    }
  </style>
</head>
<body>

<div class="left-panel">
  <img src="../images/logo.png" alt="University Logo">
  <h1>EVENT <span>SYNC</span></h1>
</div>

<div class="signup-form-container">
  <a href="login.php" class="close-button">&times;</a>
  <div class="form-box">
    <h2>Add User</h2>
    <?php if (isset($_GET['error'])): ?>
      <p class="error" style="color:red;"><?php echo htmlspecialchars($_GET['error']); ?></p>
    <?php endif; ?>
    <form method="POST" action="process_signup.php">
      <input type="email" name="email" placeholder="Email" required>
      <input type="email" name="confirm_email" placeholder="Confirm Email" required>

      <input type="password" name="password" placeholder="Password" required>
      <input type="password" name="confirm_password" placeholder="Confirm Password" required>

      <select name="organization" required>
          <option value="">User Type</option>
          <option value="sbopresident">SBO President</option>
          <option value="sbovicepresident">SBO Vice President</option>
          <option value="sbotresurer">SBO Tresurer</option>
      </select>

      <select name="department" required>
        <option value="">*Please Select Department*</option>
        <option value="CCS">CCS</option>
        <option value="CA">CA</option>
        <option value="CTE">CTE</option>
        <option value="CBAA">CBAA</option>
        <option value="CCJE">CCJE</option>
        <option value="COE">COE</option>
        <option value="CFND">CFND</option>
        <option value="CAS">CAS</option>
        <option value="CHMT">CHMT</option>
      </select>

      <div class="terms">
        <input type="checkbox" required> By creating an account, you agree to our <a href="#">Terms</a>.<br>
      </div>

      <button type="submit" class="signup-button">ADD USER</button>
    </form>
  </div>
</div>

<!-- OTP Verification Modal -->
<div id="otpModal" class="otp-modal">
  <div class="otp-modal-content">
    <span class="close" onclick="document.getElementById('otpModal').style.display='none'">&times;</span>
    <h3>Enter OTP</h3>
    <form action="verify_otp.php" method="POST">
      <input type="text" name="otp" placeholder="Enter OTP" required>
      <input type="hidden" name="email" value="<?php echo $_SESSION['email_for_verification'] ?? ''; ?>">
      <button type="submit">Verify OTP</button>
    </form>
    <p id="errorMessage" style="color:red; display:none;">Please enter a valid OTP</p>
  </div>
</div>

<!-- Modal & Error Trigger Script -->
<?php if (isset($_GET['otp_error'])): ?>
  <script>
    window.addEventListener("load", function () {
      document.getElementById('otpModal').style.display = 'block';
      document.getElementById('errorMessage').style.display = 'block';
    });
  </script>
<?php elseif (isset($_SESSION['email_for_verification'])): ?>
  <script>
    window.addEventListener("load", function () {
      document.getElementById('otpModal').style.display = 'block';
    });
  </script>
<?php endif; ?>

<!-- Close modal when clicking outside -->
<script>
  window.onclick = function(event) {
    var modal = document.getElementById('otpModal');
    if (event.target == modal) {
      modal.style.display = "none";
    }
  };
</script>

</body>
</html>
