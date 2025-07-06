<?php session_start(); 
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
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Event Sync - Add Department</title>
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

    form select {
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

    /* Responsive Styles */
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
  <img src="<?php echo $logoSrc; ?>" alt="Logo" style="border-radius:50%; box-shadow:0 4px 8px rgba(0,0,0,0.3);">
  <h1>EVENT <span>SYNC</span></h1>
</div>
<div class="signup-form-container">
  <a href="login.php" class="close-button">&times;</a>
  <div class="form-box">
    <h2>Add Department</h2>
    <?php if (isset($_GET['error'])): ?>
      <p class="error" style="color:red;"><?php echo htmlspecialchars($_GET['error']); ?></p>
    <?php elseif (isset($_GET['success'])): ?>
      <p class="success" style="color:green;"><?php echo htmlspecialchars($_GET['success']); ?></p>
    <?php endif; ?>
    <form method="POST" action="process_signup.php">
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
        <input type="checkbox" required> By adding a department, you agree to our <a href="#">Terms</a>.
      </div>

      <button type="submit" class="signup-button">ADD DEPARTMENT</button>
    </form>
  </div>
</div>

</body>
</html>
