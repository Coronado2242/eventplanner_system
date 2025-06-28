<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "eventplanner";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Approval & Disapproval Logic
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['proposal_id'], $_POST['action'])) {
    $id = (int)$_POST['proposal_id'];
    $action = $_POST['action'];

    if ($action === 'approve') {
        $status = 'Pending';
        $new_level = 'CCS Faculty';  // next level after Treasurer (or Auditor)
    } elseif ($action === 'disapprove') {
        $status = 'Disapproved by Treasurer';
        $new_level = 'CCS President'; // stays in Treasurer since disapproved
    } else {
        die("Invalid action");
    }

    $stmt = $conn->prepare("UPDATE proposals SET status=?, level=? WHERE id=?");
    if (!$stmt) {
        die("Prepare failed: " . $conn->error);
    }
    $stmt->bind_param("ssi", $status, $new_level, $id);
    if ($stmt->execute()) {
        // Redirect para ma-refresh ang list
        header("Location: ccssbopresident_dashboard.php");
        exit;
    }
}

// Fetch proposals for CCS President
$current_level = 'CCS President';
$sql = "SELECT * FROM proposals WHERE level = ? AND status = 'Pending' AND submit = 'submitted'";
$stmt = $conn->prepare($sql);

if (!$stmt) {
    die("Query error: " . $conn->error);
}

$stmt->bind_param("s", $current_level);
$stmt->execute();
$result = $stmt->get_result();  // <- ito ang hinahanap mo para hindi undefined ang $result


$show_modal = false;

if (isset($_SESSION['upload_success']) && $_SESSION['upload_success'] === true) {
    $show_modal = true;
    unset($_SESSION['upload_success']); // clear session to avoid repeat
}
// File Upload Logic Below
$uploadDir = "uploads/";
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0777, true);
}

// File fields to process
$fields = [
    'letter_attachment',
    'constitution',
    'reports',
    'adviser_form',
    'certification',
    'financial',
    'activity_plan'
];

$fileData = []; // To store filenames for DB

foreach ($fields as $field) {
    if (isset($_FILES[$field]) && $_FILES[$field]['error'] === UPLOAD_ERR_OK) {
        $filename = time() . '_' . basename($_FILES[$field]['name']);
        $target = $uploadDir . $filename;

        if (move_uploaded_file($_FILES[$field]['tmp_name'], $target)) {
            $fileData[$field] = $filename;
        } else {
            $fileData[$field] = ''; // ⚠️ FIX: avoid null error
        }
    } else {
        $fileData[$field] = ''; // ⚠️ FIX: avoid null error
    }
}

// Insert into database
$sql = "INSERT INTO requirements(
    letter_attachment, constitution, reports, adviser_form, 
    certification, financial, activity_plan, created_at
) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";

$stmt = $conn->prepare($sql);
$stmt->bind_param(
    "ssssssss",
    $fileData['letter_attachment'],
    $fileData['constitution'],
    $fileData['reports'],
    $fileData['adviser_form'],
    $fileData['certification'],
    $fileData['financial'],
    $fileData['activity_plan'],
    $created_at
);

if ($stmt->execute()) {
    // echo "<h3>✅ Proposal submitted successfully!</h3>";
} else {
    echo "<h3 style='color:red;'>❌ Failed to submit proposal: " . $conn->error . "</h3>";
}

$conn->close();



?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <title>CCS SBO President Portal</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate" />
    <style>
html, body {
  margin: 0;
  padding: 0;
  height: 100%;
  width: 100%;
  overflow-x: hidden;
  position: relative;
  font-family: Arial, sans-serif;
}

body {
  background: url('../img/homebg2.jpg') no-repeat center center fixed;
  background-size: cover;
  position: relative;
}

body::before {
  content: "";
  position: fixed; 
  top: 0;
  left: 0;
  width: 100vw;
  height: 100vh;
  background: rgba(255, 255, 255, 0.4); 
  z-index: -1;
  pointer-events: none; 
}

.topbar {
    display: flex;
    justify-content: space-between;
    align-items: center;
    background-color: rgba(255, 255, 255, 0.50); 
    padding: 15px 50px;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.45); 
    position: sticky;
    top: 0;
    z-index: 1000;
    backdrop-filter: blur(10px);
    border-bottom: 1px solid rgba(0, 0, 0, 0.05);
}

.topbar .logo {
    font-weight: bold;
    font-size: 24px;
    display: flex;
    align-items: center;
    gap: 5px;
}

.topbar nav a {
    text-decoration: none;
    color: #000;
    font-weight: 500;
    font-size: 16px;
    padding: 8px 12px;
    border-radius: 5px;
    transition: background 0.3s, color 0.3s;
}
.logo {
    display: flex;
    align-items: center; 
}
.logo img {
    margin-right: 10px; 
    height: 49px; 
    border-radius: 50%; 
    box-shadow: 0 4px 8px rgba(0,0,0,0.3);
}
.admin-info {
    display: inline-block;
    margin-left: 20px;
}

.sidebar {
    width: 220px;
    background: #004080;
    position: fixed;
    top: 80px;
    bottom: 0; 
    color: white;
}

.sidebar ul {
    list-style: none;
    padding: 0;
}

.sidebar ul li {
    padding: 15px 20px;
    cursor: pointer;
}

.sidebar ul li.active, .sidebar ul li:hover {
    background: #0066cc;
}

.content {
    margin-left: 240px;
    padding: 20px;
    margin-top: 60px;
}

.cards {
    display: flex;
    gap: 20px;
    margin-top: 20px;
}

.card {
    background: #f4f4f4;
    padding: 20px;
    flex: 1;
    border-radius: 8px;
    box-shadow: 0 2px 5px rgba(0,0,0,0.1);
}

.positive {
    color: green;
}

.negative {
    color: red;
}

.charts {
    display: flex;
    margin-top: 30px;
    gap: 40px;
    flex-wrap: wrap;
}

.calendar {
    background: #fff;
    padding: 20px;
    border-radius: 8px;
    box-shadow: 0 2px 5px rgba(0,0,0,0.1);
}

.legend span {
    display: inline-block;
    width: 10px;
    height: 10px;
    margin-right: 5px;
    border-radius: 50%;
}

.green { background: green; }
.red { background: red; }
.orange { background: orange; }

.logout-btn {
    margin-left: 15px;
    padding: 5px 10px;
    background: maroon;
    color: white;
    text-decoration: none;
    border-radius: 5px;
    font-weight: bold;
    font-size: 14px;
}

.logout-btn:hover {
    background: darkred;
}

.dropdown-menu {
    display: none;
    position: absolute;
    background-color: white;
    box-shadow: 0 4px 8px rgba(0,0,0,0.1);
    right: 0;
    margin-top: 10px;
    border-radius: 5px;
    z-index: 100;
}
.dropdown-menu a {
    display: block;
    padding: 10px;
    text-decoration: none;
    color: #333;
}
.dropdown-menu a:hover {
    background-color: #f0f0f0;
}
.user-dropdown {
    position: relative;
    display: inline-block;
    margin-left: 20px;
    cursor: pointer;
}
.fa-user {
    font-size: 18px;
}
/* sidebar */

.sidebar.collapsed {
    width: 60px;
}

.sidebar ul {
    list-style-type: none;
    padding: 0;
    margin: 0;
}

.sidebar ul li {
    display: flex;
    align-items: center;
    padding: 15px;
    color: white;
    cursor: pointer;
    transition: background 0.3s;
}

.sidebar ul li i {
    min-width: 20px;
    margin-right: 10px;
    font-size: 18px;
}

.sidebar ul li:hover {
    background-color: #0055a5;
}

.sidebar.collapsed .menu-text {
    display: none;
}

.toggle-btn {
    cursor: pointer;
    padding: 10px;
    font-size: 20px;
    background-color: #003366;
    color: white;
    text-align: center;
}
@media (max-width: 768px) {
    .topbar {
        flex-direction: column;
        align-items: flex-start;
        padding: 10px;
    }

    .topbar nav {
        display: flex;
        flex-direction: column;
        width: 100%;
    }

    .topbar nav a, .admin-info {
        margin: 5px 0;
    }

        .sidebar {
        width: 100%;
        height: auto;
        position: relative;
        top: 0;
        display: flex;
        flex-direction: row;
        justify-content: space-around;
        z-index: 10;
    }

    .sidebar ul {
        flex-direction: row;
        display: flex;
        width: 100%;
        padding: 0;
        margin: 0;
    }

    .sidebar ul li {
        flex: 1;
        justify-content: center;
        padding: 10px;
    }

    .sidebar .toggle-btn {
        display: none;
    }

    .content {
        margin: 0;
        padding: 10px;
    }

    .cards {
        flex-direction: column;
    }

    .charts {
        flex-direction: column;
        gap: 20px;
    }

    iframe {
        height: 400px !important;
    }

    .user-dropdown {
        margin-left: 0;
    }

    .dropdown-menu {
        right: auto;
        left: 0;
    }
}

.hamburger {
    display: none;
    font-size: 26px;
    cursor: pointer;
    padding: 5px 10px;
    background: none;
    border: none;
}

@media (max-width: 768px) {
    .topbar {
        flex-direction: column;
        align-items: flex-start;
    }

    .hamburger {
        display: block;
        margin-left: auto;
    }

    nav#mainNav {
        display: none;
        width: 100%;
        flex-direction: column;
    }

    nav#mainNav.show {
        display: flex;
    }

    nav#mainNav a {
        padding: 10px;
        border-top: 1px solid #ddd;
    }
}

.approval-table {
    width: 100%;
    border-collapse: separate;
    border-spacing: 0 10px;
    margin-top: 20px;
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
}

.approval-table thead {
    background-color: #004080;
    color: white;
}

.approval-table th, .approval-table td {
    padding: 12px 15px;
    text-align: left;
}

.approval-table tbody tr {
    background-color: #f9f9f9;
    border-radius: 8px;
    box-shadow: 0 2px 6px rgba(0, 0, 0, 0.05);
    transition: transform 0.2s ease;
}

.approval-table tbody tr:hover {
    transform: scale(1.01);
    background-color: #f0f8ff;
}

.budget-input {
    width: 100%;
    padding: 6px 10px;
    border: 1px solid #ccc;
    border-radius: 6px;
    font-size: 14px;
    background-color: #fff;
    box-sizing: border-box;
}

.approve-btn {
    background-color: #28a745;
    color: white;
    border: none;
    padding: 8px 14px;
    border-radius: 6px;
    font-weight: bold;
    cursor: pointer;
    transition: background-color 0.3s ease;
}

.approve-btn:hover {
    background-color: #218838;
}

table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 20px;
    font-family: Arial, sans-serif;
}

thead tr {
    background-color: #007BFF; /* example: blue header */
    color: white;
}

th, td {
    border: 1px solid #ddd;
    padding: 8px;
    text-align: left;
}

tr:nth-child(even) {
    background-color: #f2f2f2;
}

.action-btn {
    padding: 6px 12px;
    border: none;
    border-radius: 4px;
    cursor: pointer;
    font-weight: bold;
}

.approve-btn {
    background-color: #28a745;
    color: white;
}

.disapprove-btn {
    background-color: #dc3545;
    color: white;
    margin-left: 5px;
}

.modal {
  display: none;
  position: fixed;
  z-index: 9999;
  left: 0;
  top: 0;
  width: 100%;
  height: 100%;
  overflow-y: auto;
  background-color: rgba(0,0,0,0.6);
}

.modal.active {
  display: block;
}

.modal-content {
  background-color: #fff;
  margin: 5% auto;
  padding: 30px;
  border-radius: 8px;
  max-width: 95%;
  width: 90%;
}

.close-btn {
  color: #aaa;
  float: right;
  font-size: 28px;
  cursor: pointer;
}
.close-btn:hover {
  color: black;
}

 .upload-box {
            background: #fff;
            padding: 2rem;
            border-radius: 15px;
            box-shadow: 0 4px 10px rgba(0,0,0,0.1);
        }
        .upload-title {
            font-weight: 600;
            margin-bottom: 1.5rem;
        }

</style>
</head>

<body>
<header class="topbar">
  <div class="logo"><img src="../img/lspulogo.jpg" alt="Logo">CCS SBO PRESIDENT PORTAL</div>
  <div class="hamburger" onclick="toggleMobileNav()">☰</div>
  <nav id="mainNav">
    <a href="../index.php">Home</a>
    <a href="../aboutus.php">About Us</a>
    <a href="../calendar1.php">Calendar</a>
    <div class="admin-info">
      <span>
        <?= isset($_SESSION['fullname']) && isset($_SESSION['role']) ? htmlspecialchars($_SESSION['fullname']) . " (" . htmlspecialchars($_SESSION['role']) . ")" : '' ?>
      </span>
      <div class="user-dropdown" id="userDropdown">
        <i class="fa-solid fa-user" onclick="toggleDropdown()"></i>
        <div class="dropdown-menu" id="dropdownMenu" style="display:none;">
          <?php if ($_SESSION['role'] === 'CCSSBOPresident'): ?>
            <a href="ccssbopresident_dashboard.php">CCS SBO President Dashboard</a>
          <?php endif; ?>
          <a href="../account/logout.php">Logout</a>
        </div>
      </div>
    </div>
  </nav>
</header>

<aside class="sidebar">
  <ul>
    <li id="dashboardTab" class="active"><i class="fa fa-home"></i> Dashboard</li>
    <li id="proposalTab"><i class="fa fa-file-alt"></i> Proposals</li>
    <li id="requirementTab"><i class="fa fa-check-circle"></i> Requirements</li>
    <li id="ApprovalTab"><i class="fa fa-check-circle"></i> Approval</li>
  </ul>
</aside>

<!-- Dashboard Section -->
<div id="dashboardContent" class="content">
  <h1>Welcome to the CCS SBO President Dashboard</h1>
  <p>This is your overview page.</p>
  <iframe id="calendarFrame" style="width:100%; height:600px; border:none;"></iframe>
</div>

<!-- Proposals Section -->
<div id="proposalContent" class="content" style="display:none;">
  <h1>Pending Proposals for Approval</h1>
  <table>
    <thead>
      <tr>
        <th>Department</th><th>Event Type</th><th>Start Date</th><th>End Date</th><th>Venue</th><th>Status</th><th>Actions</th>
      </tr>
    </thead>
    <tbody>
    <?php if ($result && $result->num_rows > 0): ?>
      <?php while ($row = $result->fetch_assoc()): ?>
        <tr>
            <td><?= htmlspecialchars($row['id']) ?></td>
            <td><?= htmlspecialchars($row['department']) ?></td>
            <td><?= htmlspecialchars($row['event_type']) ?></td>
            <td><?= htmlspecialchars($row['start_date']) ?></td>
            <td><?= htmlspecialchars($row['end_date']) ?></td>
            <td><?= htmlspecialchars($row['venue']) ?></td>
            <td><?= htmlspecialchars($row['status']) ?></td>
            <td>
                <form method="post" action="" style="margin:0;">
                    <!-- Important: assign proposal_id value here -->
                    <input type="hidden" name="proposal_id" value="<?= htmlspecialchars($row['id']) ?>" />
                    <input type="hidden" name="level" value="CCS President">


                    <button type="submit" name="action" value="approve" class="approve-btn">Approve</button>
                    <button type="submit" name="action" value="disapprove" class="disapprove-btn">Disapprove</button>
                </form>
            </td>
        </tr>
      <?php endwhile; ?>
    <?php else: ?>
      <tr><td colspan="8" class="text-center">No proposals found for SBO President.</td></tr>
    <?php endif; ?>
    </tbody>
  </table>
</div>

<!-- Requirements Content -->
<div id="requirementContent" class="content" style="display:none;">
    <h1>Requirements Section</h1>
    <p>Requirements details will go here.</p>
</div>



<?php

$files = isset($files) && is_array($files) ? $files : [];
?>

<div id="approvalContent" class="content" style="display:none;">
   <div class="row">
    <form action="ccssbopresident_dashboard.php" method="POST" enctype="multipart/form-data">
    <?php
    $files = [
        'letter_attachment',
        'constitution',
        'reports',
        'adviser_form',
        'certification',
        'financial',
        'activity_plan'
    ];
    ?>

    <?php foreach ($files as $field): ?>
        <div class="mb-3">
            <label class="form-label"><?= ucfirst(str_replace('_', ' ', $field)) ?> <span class="text-danger">*</span></label>
            <input type="file" name="<?= $field ?>" class="form-control" accept=".pdf,.doc,.docx" required>
        </div>

        
    <?php endforeach; ?>

<button type="submit" name="upload_btn" class="btn btn-primary">Upload Files</button>

</form>

    </div>
</div>

<!-- modal -->

<div class="modal fade" id="myModal" tabindex="-1" aria-labelledby="modalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content border-success">
      <div class="modal-header bg-success text-white">
        <h5 class="modal-title" id="approvedModalLabel">✅ Approved</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
    
      <div class="modal-footer">
        <button type="button" class="btn btn-success" data-bs-dismiss="modal">OK</button>
      </div>
    </div>
  </div>
</div>




<script>
function toggleDropdown() {
  const menu = document.getElementById('dropdownMenu');
  menu.style.display = (menu.style.display === 'block') ? 'none' : 'block';
}

function toggleMobileNav() {
  const nav = document.getElementById("mainNav");
  nav.classList.toggle("show");
}
    const dashboardTab = document.getElementById('dashboardTab');
    const proposalTab = document.getElementById('proposalTab');
    const requirementTab = document.getElementById('requirementTab');
    const approvalTab = document.getElementById('ApprovalTab');

    const dashboardContent = document.getElementById('dashboardContent');
    const proposalContent = document.getElementById('proposalContent');
    const requirementContent = document.getElementById('requirementContent');
        const approvaltContent = document.getElementById('approvalContent');

    function clearActive() {
        dashboardTab.classList.remove('active');
        proposalTab.classList.remove('active');
        requirementTab.classList.remove('active');
        approvalTab.classList.remove('active');
    }

    dashboardTab.addEventListener('click', () => {
        clearActive();
        dashboardTab.classList.add('active');
        dashboardContent.style.display = 'block';
        proposalContent.style.display = 'none';
        requirementContent.style.display = 'none';
    });

    proposalTab.addEventListener('click', () => {
        clearActive();
        proposalTab.classList.add('active');
        dashboardContent.style.display = 'none';
        proposalContent.style.display = 'block';
        requirementContent.style.display = 'none';
    });

    requirementTab.addEventListener('click', () => {
        clearActive();
        requirementTab.classList.add('active');
        dashboardContent.style.display = 'none';
        proposalContent.style.display = 'none';
        requirementContent.style.display = 'block';
    });

      approvalTab.addEventListener('click', () => {
        clearActive();
        requirementTab.classList.add('active');
        dashboardContent.style.display = 'none';
        proposalContent.style.display = 'none';
        approvalContent.style.display = 'block';
    });
        document.addEventListener("DOMContentLoaded", function () {
        document.getElementById("calendarFrame").src = "../proposal/calendar.php";
    });

    
</script>
</body>
</html>
