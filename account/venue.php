<?php session_start(); ?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0"> <!-- ✅ Important for responsiveness -->
  <title>Event Sync - Sign Up</title>
  <style>
    * {
      box-sizing: border-box;
    }

    body {
      margin: 0;
      font-family: Arial, sans-serif;
      display: flex;
      flex-direction: row;
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
      height: 200px;
      border-radius: 50%;
      object-fit: cover;
      box-shadow: 0 4px 8px rgba(0,0,0,0.3);
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

    /* ✅ Responsive adjustments */
    @media (max-width: 768px) {
      body {
        flex-direction: column;
        height: auto;
      }

      .left-panel {
        padding: 1.5rem;
      }

      .left-panel img {
        width: 150px;
        height: 150px;
      }

      .signup-form-container {
        padding: 1.5rem;
      }

      .form-box {
        max-width: 100%;
        padding: 0 1rem;
      }

      .close-button {
        top: 10px;
        right: 20px;
        font-size: 26px;
      }
    }
  </style>
</head>
<body>

<div class="left-panel">
  <img src="../img/lspulogo.jpg" alt="University Logo">
  <h1>EVENT <span>SYNC</span></h1>
</div>

<div class="signup-form-container">
  <a href="login.php" class="close-button">&times;</a>
  <div class="form-box">
    <h2>Add Venue</h2>

    <?php if (isset($_GET['error'])): ?>
      <p class="error" style="color:red;"><?php echo htmlspecialchars($_GET['error']); ?></p>
    <?php endif; ?>

    <?php if (isset($_GET['success'])): ?>
      <script>
        alert("Venue added successfully!");
      </script>
    <?php endif; ?>

    <form method="POST" action="process_venue.php">
      <input type="text" name="organization" placeholder="Organizer" required>
      <input type="email" name="email" placeholder="Email" required>
      <input type="text" name="venue" placeholder="Venue" required>

      <button type="submit" class="signup-button">ADD VENUE</button>
    </form>
  </div>
</div>

<?php if (isset($_GET['success'])): ?>
  <script>
    alert("Venue added successfully!");
    window.location.href = "admin_dashboard.php";
  </script>
<?php endif; ?>

</body>
</html>
