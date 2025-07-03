<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "eventplanner";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Catch upload_id from GET
if (isset($_SESSION['upload_id'])) {
    $upload_id = $_SESSION['upload_id'];
    $stmt = $conn->prepare("SELECT * FROM sooproposal WHERE id = ?");
    $stmt->bind_param("i", $upload_id);
    $stmt->execute();
    $result = $stmt->get_result();
} else {
    $result = false;
}


// APPROVE or DISAPPROVE LOGIC
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['proposal_id'], $_POST['action'])) {
    $id = (int)$_POST['proposal_id'];
    $action = $_POST['action'];

    if ($action === 'approve') {
        $status = 'Pending';
        $new_level = 'CCS Auditor';
        $_SESSION['upload_id'] = $id;
    } elseif ($action === 'disapprove') {
        $status = 'Disapproved by Treasurer';
        $new_level = 'CCS President';
    } else {
        die("Invalid action");
    }

    $stmt = $conn->prepare("UPDATE sooproposal SET status=?, level=? WHERE id=?");
    if (!$stmt) {
        die("Prepare failed: " . $conn->error);
    }
    $stmt->bind_param("ssi", $status, $new_level, $id);
    if ($stmt->execute()) {
        header("Location: ccssbopresident_dashboard.php?upload_id=$id");
        exit;
    }
}



// LOAD PENDING PROPOSALS
$level = 'CCS President';
$search_department = '%CCS%';

$stmt = $conn->prepare("SELECT * FROM sooproposal WHERE level=? AND status='Pending' AND submit='submitted' AND department LIKE ?");
$stmt->bind_param("ss", $level, $search_department);
$stmt->execute();
$result = $stmt->get_result();


// FILE UPLOAD HANDLER
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['letter_attachment'])) {
    $uploadDir = "uploads/";
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }

    $id = $_POST['proposal_id'] ?? null;

    if (!$id) {
        echo "Missing proposal ID.";
        exit;
    } else {
        $stmt = $conn->prepare("SELECT * FROM sooproposal WHERE id = ?");
        if (!$stmt) {
            die("Query Error: " . $conn->error);
        }
        $stmt->bind_param("i", $id); 
        $stmt->execute();
        $result = $stmt->get_result();
    }

    $fields = [
        'letter_attachment', 'constitution', 'reports', 'adviser_form',
        'certification', 'financial'
    ];

    $fileData = [];
    foreach ($fields as $field) {
        if (isset($_FILES[$field]) && $_FILES[$field]['error'] === UPLOAD_ERR_OK) {
            $filename = time() . '_' . uniqid() . '_' . basename($_FILES[$field]['name']);
            $target = $uploadDir . $filename;
            if (move_uploaded_file($_FILES[$field]['tmp_name'], $target)) {
                $fileData[$field] = $filename;
            } else {
                $fileData[$field] = '';
            }
        } else {
            $fileData[$field] = '';
        }
    }

    $sql = "UPDATE sooproposal SET 
        letter_attachment = ?, 
        constitution = ?, 
        reports = ?, 
        adviser_form = ?, 
        certification = ?, 
        financial = ?,
        level = 'CCS Auditor'
    WHERE id = ?";

    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        die("SQL Prepare Error: " . $conn->error);
    }

    $stmt->bind_param(
        'ssssssi',
        $fileData['letter_attachment'],
        $fileData['constitution'],
        $fileData['reports'],
        $fileData['adviser_form'],
        $fileData['certification'],
        $fileData['financial'],
        $id
    );

    if ($stmt->execute()) {
        $_SESSION['upload_success'] = true;
        header("Location: ccssbopresident_dashboard.php?approved=1&upload_id=$id");
        exit;
    } else {
        echo "Update failed: " . $stmt->error;
    }
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
  background-color: #f8f9fa;
  border: 2px dashed #ced4da;
  border-radius: 12px;
  padding: 2rem;
  margin-top: 1rem;
}

.file-input {
  border-radius: 6px;
  padding: 10px;
  font-size: 14px;
  background-color: #ffffff;
  border: 1px solid #ced4da;
  transition: border-color 0.3s, box-shadow 0.3s;
}

.file-input:focus {
  border-color: #80bdff;
  box-shadow: 0 0 0 0.2rem rgba(0,123,255,.25);
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
        <th>Department</th>
        <th>Event Type</th>
        <th>Start Date</th>
        <th>End Date</th>
        <th>Venue</th>
        <th>Attatchment</th>
        <th>Status</th>
        <th>Actions</th>
      </tr>
    </thead>
    <tbody>
    <?php if ($result && $result->num_rows > 0): ?>
      <?php while ($row = $result->fetch_assoc()): ?>
        <tr>
            <td><?= htmlspecialchars($row['department']) ?></td>
            <td><?= htmlspecialchars($row['activity_name']) ?></td>
            <td><?= htmlspecialchars($row['start_date']) ?></td>
            <td><?= htmlspecialchars($row['end_date']) ?></td>
            <td><?= htmlspecialchars($row['venue']) ?></td>
          <td>
  <?php if (!empty($row['budget_file'])): ?>
    <a href="../proposal/uploads/<?= urlencode($row['budget_file']) ?>" 
   target="_blank" 
   class="btn btn-primary btn-sm d-inline-flex align-items-center">
   <i class="fa fa-file-alt me-2"></i> View Budget File
</a>

  <?php else: ?>
    No File
  <?php endif; ?>
</td>
            <td><?= htmlspecialchars($row['status']) ?></td>

            <td>
<form method="POST" style="display:inline;">
  <input type="hidden" name="proposal_id" value="<?= $row['id'] ?>">
  <button type="button" class="action-btn approve-btn" data-id="<?= $row['id'] ?>">Approve</button>
</form>
<form method="POST" action="ccssbotreasurer_dashboard.php" style="display: inline;">
    <button type="button" class="btn btn-danger disapprove-btn" data-id="<?= $row['id'] ?>" data-bs-toggle="modal" data-bs-target="#disapproveModal">
  Disapprove
</button>

</form>

            </td>
        </tr>
      <?php endwhile; ?>
    <?php else: ?>
      <tr><td colspan="8" class="text-center">No proposals found for SBO Treasurer.</td></tr>
    <?php endif; ?>
    </tbody>
</table>
  
</div>



<!-- Requirements Content -->
<div id="requirementContent" class="content" style="display:none;">
    <h1>Requirements Section</h1>

<?php
// Error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// MySQL Connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "eventplanner";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Sample session data
$level = 'CCS President';
$search_department = '%CCS%';

$stmt = $conn->prepare("SELECT * FROM sooproposal WHERE level=? AND status='Pending' AND submit='submitted' AND department LIKE ?");
$stmt->bind_param("ss", $level, $search_department);
$stmt->execute();
$result = $stmt->get_result();


// Check and display results
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        echo '<div class="card p-4 mb-4 shadow-sm">';
        echo '<h3 class="mb-3">' . htmlspecialchars($row['activity_name']) . '</h3>';
        echo '<p><strong>Department:</strong> ' . htmlspecialchars($row['department']) . '</p>';
        echo '<p><strong>Date:</strong> ' . date("F d, Y", strtotime($row['start_date'])) . ' - ' . date("F d, Y", strtotime($row['end_date'])) . '</p>';
        echo '<p><strong>Venue:</strong> ' . htmlspecialchars($row['venue']) . '</p>';
        echo '<h5 class="mt-4">Requirements</h5>';
        echo '<div class="row g-3">';

        $requirements = [
            "Plan of Activities" => "POA_file",
            "Budget Plan" => "budget_file"
        ];

        $requirementDirectories = [
            "POA_file" => "../proposal/",
            "budget_file" => "../proposal/uploads/"
        ];

        foreach ($requirements as $label => $field) {
            echo '<div class="col-md-4">';
            echo '<div class="border rounded p-3 bg-light h-100">';
            echo '<small class="text-danger fw-bold">Requirement*</small><br>';
            echo '<strong>' . $label . '</strong><br>';

            if (!empty($row[$field])) {
                $directory = $requirementDirectories[$field] ?? '../proposal/';
                $file_path = $directory . htmlspecialchars($row[$field]);
                echo '<a href="' . $file_path . '" target="_blank" class="btn btn-primary btn-sm mt-2">View Attachment</a>';
            } else {
                echo '<span class="text-muted mt-2 d-block">No Attachment</span>';
            }

            echo '</div></div>';
        }

        echo '</div></div>'; // end row and card
    }
} else {
    echo '<div class="alert alert-info text-center">No requirements found for SBO President (department: CCS).</div>';
}

$conn->close();
?>

    
</div>


<!-- Approval Modal -->
<div class="modal fade" id="approvalModal" tabindex="-1" aria-labelledby="approvalModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-lg">
    <div class="modal-content">
      <form id="approvalUploadForm" action="ccssbopresident_dashboard.php" method="POST" enctype="multipart/form-data">
        <div class="modal-header">
          <h5 class="modal-title">Upload Requirements</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <input type="hidden" name="proposal_id" id="modalProposalId">

          <?php
          $fields = ['letter_attachment','constitution','reports','adviser_form','certification','financial'];
          foreach ($fields as $field): ?>
            <div class="mb-3">
              <label><?= ucfirst(str_replace('_', ' ', $field)) ?>:</label>
              <input type="file" name="<?= $field ?>" class="form-control file-input" required>
            </div>
          <?php endforeach; ?>
        </div>
        <div class="modal-footer">
          <button type="submit" name="upload_files" class="btn btn-success">Submit & Approved</button>
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
        </div>
      </form>
    </div>
  </div>
</div>



<!-- modal -->

<div class="modal fade" id="myModal" tabindex="-1" aria-labelledby="modalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content text-center p-4">
      <img src="../img/chik.png" alt="Approved" class="mx-auto mb-3" style="width: 120px; height: 120px;">
      <h4 class="mb-2 text-success fw-bold">APPROVED</h4>
      <p class="text-muted">Your submission has been approved successfully.</p>
      <button type="submit" class="btn btn-success mt-3" data-bs-dismiss="modal">OK</button>
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

    const dashboardContent = document.getElementById('dashboardContent');
    const proposalContent = document.getElementById('proposalContent');
    const requirementContent = document.getElementById('requirementContent');

  function clearActive() {
    dashboardTab.classList.remove('active');
    proposalTab.classList.remove('active');
    requirementTab.classList.remove('active');

    dashboardContent.style.display = 'none';
    proposalContent.style.display = 'none';
    requirementContent.style.display = 'none';
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

        document.addEventListener("DOMContentLoaded", function () {
        document.getElementById("calendarFrame").src = "../proposal/calendar.php";
    });

    document.addEventListener('DOMContentLoaded', () => {
  const uploadBtn = document.getElementById('uploadBtn');
  const fileInputs = document.querySelectorAll('.file-input');
  const uploadForm = document.getElementById('uploadForm');
  let allowSubmit = false;

  const checkFilesFilled = () => {
    let allFilled = true;
    fileInputs.forEach(input => {
      if (!input.value) {
        allFilled = false;
      }
    });
    uploadBtn.disabled = !allFilled;
  };

  fileInputs.forEach(input => {
    input.addEventListener('change', checkFilesFilled);
  });

  uploadForm.addEventListener('submit', (e) => {
    if (!allowSubmit) {
      e.preventDefault();
      console.log("Intercepted form submission to show modal.");

      const myModal = new bootstrap.Modal(document.getElementById('myModal'));
      myModal.show();

      document.getElementById('myModal').addEventListener('hidden.bs.modal', () => {
        allowSubmit = true;
        uploadForm.submit(); // submit only once
      }, { once: true }); // prevent multiple triggers
    }
  });
});
document.querySelectorAll('.approve-btn').forEach(button => {
  button.addEventListener('click', function () {
    const id = this.getAttribute('data-id');
    document.getElementById('uploadProposalId').value = id;
    document.getElementById('ApprovalTab').click();
  });
});
document.querySelectorAll('.approve-btn').forEach(button => {
  button.addEventListener('click', function () {
    const id = this.getAttribute('data-id');
    document.getElementById('modalProposalId').value = id;
    const approvalModal = new bootstrap.Modal(document.getElementById('approvalModal'));
    approvalModal.show();
  });
});

</script>
<script>
  <?php if (isset($_GET['approved']) && $_GET['approved'] == 1): ?>
    alert("✅ Proposal approved successfully!");
  <?php endif; ?>
</script>
</body>
</html>
