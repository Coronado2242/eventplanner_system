<?php
session_start();
$conn = new mysqli("localhost", "root", "", "eventplanner");

$venueOptions = [];

// Get all tables that have a 'venue' column (excluding certain tables)
$query = "SELECT TABLE_NAME 
          FROM INFORMATION_SCHEMA.COLUMNS 
          WHERE TABLE_SCHEMA = 'eventplanner' 
          AND COLUMN_NAME = 'venue'";

$result = $conn->query($query);
$tablesWithVenue = [];

if ($result) {
    while ($row = $result->fetch_assoc()) {
        $table = $row['TABLE_NAME'];
        if ($table !== 'sooproposal' && $table !== 'proposals') {
            $tablesWithVenue[] = $table;
        }
    }
}

foreach ($tablesWithVenue as $table) {
    // Check which columns exist
    $roleCheck = $conn->query("SHOW COLUMNS FROM `$table` LIKE 'role'");
    $hasRole = ($roleCheck && $roleCheck->num_rows > 0);

    $fullnameCheck = $conn->query("SHOW COLUMNS FROM `$table` LIKE 'fullname'");
    $hasFullname = ($fullnameCheck && $fullnameCheck->num_rows > 0);

    $emailCheck = $conn->query("SHOW COLUMNS FROM `$table` LIKE 'email'");
    $hasEmail = ($emailCheck && $emailCheck->num_rows > 0);

    // Build SELECT
    $selectFields = "DISTINCT venue";
    if ($hasRole) $selectFields .= ", role";
    if ($hasFullname) $selectFields .= ", fullname";
    if ($hasEmail) $selectFields .= ", email";

    $whereClause = "venue IS NOT NULL AND venue != ''";
    $firstLoginCheck = $conn->query("SHOW COLUMNS FROM `$table` LIKE 'firstlogin'");
    $hasFirstLogin = ($firstLoginCheck && $firstLoginCheck->num_rows > 0);
    if ($hasFirstLogin) {
        $whereClause .= " AND firstlogin = 'no'";
    }

    $sql = "SELECT $selectFields FROM `$table` WHERE $whereClause";
    $res = $conn->query($sql);
    if ($res && $res->num_rows > 0) {
        while ($r = $res->fetch_assoc()) {
            $venueKey = $r['venue'];
            $venueOptions[$venueKey] = [
                'venue' => $venueKey,
                'role' => $hasRole ? ($r['role'] ?? '') : '',
                'fullname' => $hasFullname ? ($r['fullname'] ?? '') : '',
                'email' => $hasEmail ? ($r['email'] ?? '') : '',
                'table' => $table
            ];
        }
    }
}

ksort($venueOptions);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0"> 
  <title>Event Sync - Add Venue</title>
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

    <form method="POST" action="process_venue.php">
      <input type="text" id="organizer" name="organization" placeholder="Organizer" readonly required>
      <input type="email" id="emailField" name="email" placeholder="Email" readonly required>

      <select name="venue" id="venueSelect" required>
        <option value="">* Select Venue *</option>
        <?php foreach ($venueOptions as $venueData): ?>
          <option 
            value="<?= htmlspecialchars($venueData['venue']) ?>" 
            data-role="<?= htmlspecialchars($venueData['role']) ?>" 
            data-fullname="<?= htmlspecialchars($venueData['fullname']) ?>"
            data-email="<?= htmlspecialchars($venueData['email']) ?>"
            data-table="<?= htmlspecialchars($venueData['table']) ?>"
          >
            <?= htmlspecialchars($venueData['venue']) ?>
          </option>
        <?php endforeach; ?>
      </select>

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

<script>
document.getElementById('venueSelect').addEventListener('change', function () {
  const selectedOption = this.options[this.selectedIndex];
  const fullname = selectedOption.getAttribute('data-fullname');
  const email = selectedOption.getAttribute('data-email');
  document.getElementById('organizer').value = fullname || '';
  document.getElementById('emailField').value = email || '';
});
</script>

</body>
</html>
