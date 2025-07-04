<?php session_start(); 
$conn = new mysqli("localhost", "root", "", "eventplanner");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$logoSrc = "img/lspulogo.jpg"; // fallback

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
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    }

    body {
      margin: 0;
      height: 100vh;
      display: flex;
      background: #f4f7fa;
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
      width: 180px;
      height: 180px;
      border-radius: 50%;
      object-fit: cover;
      box-shadow: 0 4px 10px rgba(0,0,0,0.3);
      margin-bottom: 1.5rem;
    }

    .left-panel h1 span {
      color: #1e90ff;
    }

    .signup-form-container {
      flex: 1;
      display: flex;
      align-items: center;
      justify-content: center;
      padding: 2rem;
      background: #fff;
      position: relative;
    }

    .form-box {
      width: 100%;
      max-width: 500px;
      background: #ffffff;
      padding: 2rem;
      border-radius: 12px;
      box-shadow: 0 8px 24px rgba(0, 0, 0, 0.1);
    }

    .form-box h2 {
      text-align: center;
      margin-bottom: 1.5rem;
      font-weight: bold;
      color: #003366;
    }

    form {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 1rem;
    }

    form input[type="text"],
    form textarea {
      grid-column: span 1;
      padding: 12px 14px;
      border: 1px solid #ccc;
      border-radius: 8px;
      font-size: 15px;
      transition: border 0.3s ease;
    }

    form textarea {
      grid-column: span 2;
      resize: none;
      height: 100px;
    }

    form input:focus,
    form textarea:focus {
      outline: none;
      border-color: #1e90ff;
    }

    .terms {
      grid-column: span 2;
      font-size: 14px;
    }

    .terms input {
      margin-right: 6px;
    }

    .signup-button {
      grid-column: span 2;
      width: 100%;
      background-color: #004080;
      color: white;
      padding: 14px;
      font-size: 17px;
      border: none;
      border-radius: 10px;
      cursor: pointer;
      transition: background-color 0.3s;
    }

    .signup-button:hover {
      background-color: #003060;
    }

    .close-button {
      position: absolute;
      top: 20px;
      right: 30px;
      font-size: 30px;
      text-decoration: none;
      color: black;
    }

    @media (max-width: 768px) {
      body {
        flex-direction: column;
      }

      form {
        grid-template-columns: 1fr;
      }

      form textarea {
        grid-column: span 1;
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
    <h2>Add Department Activity</h2>

    <?php if (isset($_GET['error'])): ?>
      <p class="error" style="color:red;"><?php echo htmlspecialchars($_GET['error']); ?></p>
    <?php elseif (isset($_GET['success'])): ?>
      <p class="success" style="color:green;"><?php echo htmlspecialchars($_GET['success']); ?></p>
    <?php endif; ?>

    <form method="POST" action="process_add_activity.php">
      <input type="text" name="department" placeholder="Department" required>
      <input type="text" name="activity_name" placeholder="Activity Name" required>
      <input type="text" name="objective" placeholder="Objective" required>
      <textarea name="brief_description" placeholder="Brief Description" rows="3" required></textarea>
      <input type="text" name="person_involved" placeholder="Person Involved" required>

      <div class="terms">
        <input type="checkbox" required> I confirm that the above activity details are accurate.
      </div>

      <button type="submit" class="signup-button">ADD ACTIVITY</button>
    </form>
  </div>
</div>

</body>
</html>