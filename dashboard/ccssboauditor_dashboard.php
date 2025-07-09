<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();

$conn = new mysqli("localhost", "root", "", "eventplanner");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$logoSrc = "../img/lspulogo.jpg"; // fallback

$sql = "SELECT filepath FROM site_logo ORDER BY date_uploaded DESC LIMIT 1";
$result = $conn->query($sql);

if ($result && $row = $result->fetch_assoc()) {
    if (!empty($row['filepath'])) {
        $logoSrc = "../account/" . htmlspecialchars($row['filepath']); 
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['proposal_id'], $_POST['action'])) {
    $id = (int)$_POST['proposal_id'];
    $action = $_POST['action'];

    if ($action === 'approve_proposal') {
        // Normal proposal approval
        $status = 'Pending';
        $new_level = 'CCS Dean';
        $viewed = 0;

        $stmt = $conn->prepare("UPDATE sooproposal SET status=?, level=?, viewed=? WHERE id=?");
        $stmt->bind_param("ssii", $status, $new_level, $viewed, $id);
        $stmt->execute();

        header("Location: ccssboauditor_dashboard.php?approved=1");
        exit;

    } elseif ($action === 'disapprove_proposal') {
        // Normal proposal disapproval
        $reasons = $_POST['reasons'] ?? [];
        $remarks = [];

        if (in_array("Incomplete Documents", $reasons)) {
            $remarks[] = "Incomplete Documents – " . ($_POST['details_missing'] ?? '');
        }
        if (in_array("Incorrect Information", $reasons)) {
            $remarks[] = "Incorrect Information – " . ($_POST['details_incorrect'] ?? '');
        }
        if (in_array("Other", $reasons)) {
            $remarks[] = "Other – " . ($_POST['details_other'] ?? '');
        }

        foreach ($reasons as $reason) {
            if (!in_array($reason, ["Incomplete Documents", "Incorrect Information", "Other"])) {
                $remarks[] = $reason;
            }
        }

        $final_remarks = implode("; ", $remarks);
        $disapproved_by = $_SESSION['username'] ?? 'Unknown';

        $stmt = $conn->prepare("UPDATE sooproposal SET status='Disapproved', remarks=?, disapproved_by=?, level='' WHERE id=?");
        $stmt->bind_param("ssi", $final_remarks, $disapproved_by, $id);
        $stmt->execute();

        header("Location: ccssboauditor_dashboard.php");
        exit;

    } 
}


// === Handle Financial Approval (Dean) ===
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['proposal_id'], $_POST['action']) && 
    in_array($_POST['action'], ['approve_financial', 'disapprove_financial'])) {

    $id = (int)$_POST['proposal_id'];
    $action = $_POST['action'];

    if ($action === 'approve_financial') {
        $financialstatus = 'Submitted';
        $new_level = 'CCS Financial Treasurer';

        $stmt = $conn->prepare("UPDATE sooproposal SET financialstatus = ?, level = ? WHERE id = ?");
        $stmt->bind_param("ssi", $financialstatus, $new_level, $id);
        $stmt->execute();

        header("Location: ccssboauditor_dashboard.php?financial_approved=1&tab=financial");
        exit;

    } elseif ($action === 'disapprove_financial') {
        $financialstatus = 'Disapproved by Auditor';
        $stmt = $conn->prepare("UPDATE sooproposal SET financialstatus = ?, submit = NULL WHERE id = ?");
        $stmt->bind_param("si", $financialstatus, $id);
        $stmt->execute();

        header("Location: ccssboauditor_dashboard.php?financial_disapproved=1&tab=financial");
        exit;
    }
}


// FETCH proposals for approval by CCS Auditor
$current_level = 'CCS Auditor';
$search_department = '%CCS%';

$stmt = $conn->prepare("SELECT * FROM sooproposal WHERE level=? AND status='Pending' AND submit='submitted' AND department LIKE ?");
$stmt->bind_param("ss", $current_level, $search_department);
$stmt->execute();
$result = $stmt->get_result();
?>


<!-- Flash Messages -->
<?php if(isset($_SESSION['success'])): ?>
<div class="alert alert-success"><?= $_SESSION['success']; unset($_SESSION['success']); ?></div>
<?php endif; ?>

<?php if(isset($_SESSION['error'])): ?>
<div class="alert alert-danger"><?= $_SESSION['error']; unset($_SESSION['error']); ?></div>
<?php endif; ?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>CCS SBO Auditor Dashboard</title>

  <!-- Bootstrap CSS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" crossorigin="anonymous">

  <!-- Your other CSS -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <link rel="stylesheet" href="../style/sbotresure1.css">

  <!-- Popper.js and Bootstrap JS -->
  <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js" crossorigin="anonymous"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.min.js" crossorigin="anonymous"></script>

  <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate" />
</head>


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
    padding: 10px;
    margin-right: 20px;
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
<body>
<header class="topbar">
  <div class="logo">
    <img src="<?php echo $logoSrc; ?>" alt="Logo" style="height:49px; border-radius:50%; box-shadow:0 4px 8px rgba(0,0,0,0.3);">
    Event<span style="color:blue;">Sync</span>&nbsp;CCS SBO AUDITOR PORTAL</div>
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
          <?php if ($_SESSION['role'] === 'CCSSBOAuditor'): ?>
            <a href="ccssboauditor_dashboard.php">CCS SBO Auditor Dashboard</a>
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
    <li id="financialTab"><i class="fa fa-check-circle"></i> Financial Report</li>
  </ul>
</aside>

<!-- Dashboard Section -->
<div id="dashboardContent" class="content">
  <h1>Welcome to the CCS Auditor Dashboard</h1>
  <p>This is your overview page.</p>
  <iframe id="calendarFrame" style="width:100%; height:1000px; border:none;"></iframe>
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
                <form method="POST" action="" style="display:inline;">
    <input type="hidden" name="proposal_id" value="<?= $row['id'] ?>">
        <button type="button"
  class="btn btn-success approve-btn"
  data-id="<?= $row['id'] ?>"
  data-bs-toggle="modal"
  data-bs-target="#approveModal">
  Approve
</button>
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

<!-- Requirements Section -->
<div id="requirementContent" class="content" style="display:none;">
  <h1>Requirements</h1>
  <?php
  $stmt = $conn->prepare("SELECT * FROM sooproposal WHERE level=? AND status='Pending' AND submit='submitted' AND department LIKE ?");
  $stmt->bind_param("ss", $current_level, $search_department);
  $stmt->execute();
  $result = $stmt->get_result();

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
            "Letter Attachment" => "letter_attachment",
            "Adviser Commitment form" => "adviser_form",
            "Constitution ang by-laws of the Org." => "constitution",
            "Certification from Responsive Dean/Associate Dean" => "certification",
            "Accomplishment reports" => "reports",
            "Financial Report" => "financial",
            "Plan of Activities" => "POA_file",
            "Budget Plan" => "budget_file"
        ];

        $requirementDirectories = [
            "letter_attachment" => "../dashboard/uploads/",
            "adviser_form" => "../dashboard/uploads/",
            "constitution" => "../dashboard/uploads/",
            "certification" => "../dashboard/uploads/",
            "reports" => "../dashboard/uploads/",
            "financial" => "../dashboard/uploads/",
            "POA_file" => "../proposal/uploads/",
            "budget_file" => "../proposal/uploads/"
        ];

        foreach ($requirements as $label => $field) {
            echo '<div class="col-md-4">';
            echo '<div class="border rounded p-3 bg-light h-100">';
            echo '<small class="text-danger fw-bold">Requirement*</small><br>';
            echo '<strong>' . $label . '</strong><br>';

            if (!empty($row[$field])) {
                $directory = $requirementDirectories[$field] ?? '../proposal/';
                echo '<a href="' . $directory . htmlspecialchars($row[$field]) . '" target="_blank" class="btn btn-primary btn-sm mt-2">View Attachment</a>';
            } else {
                echo '<span class="text-muted mt-2 d-block">No Attachment</span>';
            }

            echo '</div></div>';
        }

        echo '</div></div>';
    }
} else {
    echo '<div class="alert alert-info text-center">No requirements found for SBO Auditor.</div>';
}
?>
</div>

<!-- Approve Confirmation Modal -->
<div class="modal fade" id="approveModal" tabindex="-1" aria-labelledby="approveModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <form method="POST" action="ccssboauditor_dashboard.php">
      <div class="modal-content">
        <div class="modal-header bg-success text-white">
          <h5 class="modal-title" id="approveModalLabel">Confirm Approval</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>

        <div class="modal-body">
          <input type="hidden" name="proposal_id" id="approve_proposal_id">
          <input type="hidden" name="action" value="approve_proposal">
          Are you sure you want to approve this proposal?
        </div>

        <div class="modal-footer">
          <button type="submit" class="btn btn-success w-100">Yes, Approve</button>
        </div>
      </div>
    </form>
  </div>
</div>

<!-- Disapprove Remarks Modal -->
<div class="modal fade" id="disapproveModal" tabindex="-1" aria-labelledby="disapproveModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-lg">
   <form method="POST" action="ccssboauditor_dashboard.php">

      <div class="modal-content">
        <!-- Header -->
        <div class="modal-header bg-danger text-white">
          <h5 class="modal-title" id="disapproveModalLabel">Disapproved</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>

        <!-- Body -->
        <div class="modal-body">
          <input type="hidden" name="proposal_id" id="modal_proposal_id">
          <input type="hidden" name="level" value="CCSSBOAuditor">
          <input type="hidden" name="action" value="disapprove">

          <p><strong>📝 Remarks / Comments:</strong></p>
          <p>
            Dear CCS SOO,<br>
            Thank you for submitting your event proposal. After reviewing the details, we regret to inform you that your proposal has been disapproved due to the following reasons:
          </p>

          <div class="form-check">
            <input class="form-check-input" type="checkbox" name="reasons[]" value="Schedule Conflict" id="reason1">
            <label class="form-check-label" for="reason1">Schedule Conflict – Requested date is already booked.</label>
          </div>

          <div class="form-check">
            <input class="form-check-input" type="checkbox" name="reasons[]" value="Incomplete Documents" id="reason2">
            <label class="form-check-label" for="reason2">Incomplete Documents – Missing:</label>
            <input type="text" class="form-control mt-1" name="details_missing" placeholder="Specify missing documents">
          </div>

          <div class="form-check">
            <input class="form-check-input" type="checkbox" name="reasons[]" value="Incorrect Information" id="reason3">
            <label class="form-check-label" for="reason3">Incorrect Information – Issue(s) found in:</label>
            <input type="text" class="form-control mt-1" name="details_incorrect" placeholder="Specify incorrect information">
          </div>

          <div class="form-check">
            <input class="form-check-input" type="checkbox" name="reasons[]" value="Does not meet guidelines" id="reason4">
            <label class="form-check-label" for="reason4">Proposal does not meet event guidelines.</label>
          </div>

          <div class="form-check">
            <input class="form-check-input" type="checkbox" name="reasons[]" value="Unclear Budget" id="reason5">
            <label class="form-check-label" for="reason5">Budget proposal is not clear or realistic.</label>
          </div>

          <div class="form-check">
            <input class="form-check-input" type="checkbox" name="reasons[]" value="Other" id="reason6">
            <label class="form-check-label" for="reason6">Other:</label>
            <input type="text" class="form-control mt-1" name="details_other" placeholder="Specify other reason">
          </div>

          <p class="mt-3">
            Please address the noted issues and resubmit your proposal for reconsideration.
          </p>
        </div>

        <!-- Footer -->
        <div class="modal-footer">
          <button type="submit" class="btn btn-danger w-100">Submit</button>
        </div>
      </div>
    </form>
  </div>
</div>


<!-- Disapprove Remarks Modal -->
<div class="modal fade" id="disapproveModal" tabindex="-1" aria-labelledby="disapproveModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-lg">
   <form method="POST" action="ccssboauditor_dashboard.php">

      <div class="modal-content">
        <!-- Header -->
        <div class="modal-header bg-danger text-white">
          <h5 class="modal-title" id="disapproveModalLabel">Disapproved</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>

        <!-- Body -->
        <div class="modal-body">
          <input type="hidden" name="proposal_id" id="modal_proposal_id">
          <input type="hidden" name="level" value="CCSVice">
          <input type="hidden" name="action" value="disapprove">

          <p><strong>📝 Remarks / Comments:</strong></p>
          <p>
            Dear [Name],<br>
            Thank you for submitting your event proposal. After reviewing the details, we regret to inform you that your proposal has been disapproved due to the following reasons:
          </p>

          <div class="form-check">
            <input class="form-check-input" type="checkbox" name="reasons[]" value="Schedule Conflict" id="reason1">
            <label class="form-check-label" for="reason1">Schedule Conflict – Requested date is already booked.</label>
          </div>

          <div class="form-check">
            <input class="form-check-input" type="checkbox" name="reasons[]" value="Incomplete Documents" id="reason2">
            <label class="form-check-label" for="reason2">Incomplete Documents – Missing:</label>
            <input type="text" class="form-control mt-1" name="details_missing" placeholder="Specify missing documents">
          </div>

          <div class="form-check">
            <input class="form-check-input" type="checkbox" name="reasons[]" value="Incorrect Information" id="reason3">
            <label class="form-check-label" for="reason3">Incorrect Information – Issue(s) found in:</label>
            <input type="text" class="form-control mt-1" name="details_incorrect" placeholder="Specify incorrect information">
          </div>

          <div class="form-check">
            <input class="form-check-input" type="checkbox" name="reasons[]" value="Does not meet guidelines" id="reason4">
            <label class="form-check-label" for="reason4">Proposal does not meet event guidelines.</label>
          </div>

          <div class="form-check">
            <input class="form-check-input" type="checkbox" name="reasons[]" value="Unclear Budget" id="reason5">
            <label class="form-check-label" for="reason5">Budget proposal is not clear or realistic.</label>
          </div>

          <div class="form-check">
            <input class="form-check-input" type="checkbox" name="reasons[]" value="Other" id="reason6">
            <label class="form-check-label" for="reason6">Other:</label>
            <input type="text" class="form-control mt-1" name="details_other" placeholder="Specify other reason">
          </div>

          <p class="mt-3">
            Please address the noted issues and resubmit your proposal for reconsideration.
          </p>
        </div>

        <!-- Footer -->
        <div class="modal-footer">
          <button type="submit" class="btn btn-danger w-100">Submit</button>
        </div>
      </div>
    </form>
  </div>
</div>


<div id="financialReportContent" class="content" style="display:none;">
  <h2>Financial Report (For Approval)</h2>
  <div class="table-responsive">
    <table class="table table-bordered table-hover table-striped text-center align-middle shadow-sm rounded">
      <thead class="table-success text-dark">
        <tr>
          <th>Activity Name</th>
          <th>Plan Of Activities</th>
          <th>Budget Plan</th>
          <th>Budget Amount</th>
          <th>Receipt</th>
          <th>Action</th>
        </tr>
      </thead>
      <tbody>
      <?php
      $query = "SELECT * FROM sooproposal WHERE submit = 'Submitted' AND level = 'Financial Auditor'";
      $result = $conn->query($query);

      while ($row = $result->fetch_assoc()):
          $poa = $row['POA_file'];
          $budget = $row['budget_file'];
          $receipt = $row['receipt_file'];
          $budget_amount = $row['budget'] ?? '0.00';
      ?>
      <tr>
        <td><?= htmlspecialchars($row['activity_name']) ?></td>
        <td>
          <?php if ($poa): ?>
            <a href="../proposal/uploads/<?= htmlspecialchars($poa) ?>" target="_blank" class="badge bg-success text-decoration-none">
              <i class="fa fa-file-pdf"></i> View
            </a>
          <?php else: ?>
            <span class="badge bg-secondary">Not Generated</span>
          <?php endif; ?>
        </td>
        <td>
          <?php if ($budget): ?>
            <a href="../proposal/uploads/<?= htmlspecialchars($budget) ?>" target="_blank" class="badge bg-success text-decoration-none">
              <i class="fa fa-file-pdf"></i> View
            </a>
          <?php else: ?>
            <span class="badge bg-secondary">Not Generated</span>
          <?php endif; ?>
        </td>
        <td>
          <span class="badge bg-info text-dark">
            ₱<?= number_format($budget_amount, 2) ?>
          </span>
        </td>
        <td>
          <?php if ($receipt): ?>
            <a href="../proposal/uploads/<?= htmlspecialchars($receipt) ?>" target="_blank" class="badge bg-info text-decoration-none">
              <i class="fa fa-file-pdf"></i> View
            </a>
          <?php else: ?>
            <span class="badge bg-secondary">Not Uploaded</span>
          <?php endif; ?>
        </td>
        <td>
<form method="POST" action="ccssboauditor_dashboard.php" onsubmit="return confirmSubmit(this);">
  <input type="hidden" name="proposal_id" value="<?= $row['id'] ?>">
  <input type="hidden" name="action" value="">
  <div class="d-grid gap-1">
  <button type="button" class="btn btn-success btn-sm" data-bs-toggle="modal" data-bs-target="#financialApproveModal<?= $row['id'] ?>">Approve</button>
  <button type="button" class="btn btn-danger btn-sm" data-bs-toggle="modal" data-bs-target="#financialDisapproveModal<?= $row['id'] ?>">Disapprove</button>
</div>

</form>

        </td>
      </tr>
      <?php endwhile; ?>
      </tbody>
    </table>
  </div>
</div>

<div class="modal fade" id="financialApproveModal<?= $row['id'] ?>" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <form method="POST" action="ccssboauditor_dashboard.php">
      <input type="hidden" name="proposal_id" value="<?= $row['id'] ?>">
      <input type="hidden" name="action" value="approve_financial">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Confirm Financial Approval</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          Are you sure you want to <strong>approve</strong> this proposal financially?
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-success">Yes, Approve</button>
        </div>
      </div>
    </form>
  </div>
</div>

<div class="modal fade" id="financialDisapproveModal<?= $row['id'] ?>" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <form method="POST" action="ccssboauditor_dashboard.php">
      <input type="hidden" name="proposal_id" value="<?= $row['id'] ?>">
      <input type="hidden" name="action" value="disapprove_financial">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Confirm Financial Disapproval</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          Are you sure you want to <strong>disapprove</strong> this proposal financially?
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-danger">Yes, Disapprove</button>
        </div>
      </div>
    </form>
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

document.addEventListener("DOMContentLoaded", function () {
  document.getElementById("calendarFrame").src = "../proposal/calendar.php";

  document.getElementById("dashboardTab").addEventListener("click", () => switchTab("dashboard"));
  document.getElementById("proposalTab").addEventListener("click", () => switchTab("proposal"));
  document.getElementById("requirementTab").addEventListener("click", () => switchTab("requirement"));
   document.getElementById("financialTab").addEventListener("click", () => switchTab("financial"));

  document.querySelectorAll('.disapprove-btn').forEach(button => {
    button.addEventListener('click', function () {
      const proposalId = this.getAttribute('data-id');
      document.getElementById('modal_proposal_id').value = proposalId;
    });
  });
});

function switchTab(tab) {
  const sections = {
    dashboard: "dashboardContent",
    proposal: "proposalContent",
    requirement: "requirementContent",
       financial: "financialReportContent"
  };

  for (const key in sections) {
    document.getElementById(sections[key]).style.display = (key === tab) ? 'block' : 'none';
    document.getElementById(key + 'Tab').classList.toggle('active', key === tab);
  }
}

// Set proposal ID into modal for approval
document.querySelectorAll('.approve-btn').forEach(button => {
  button.addEventListener('click', function () {
    const proposalId = this.getAttribute('data-id');
    document.getElementById('approve_proposal_id').value = proposalId;
  });
});


  // Check for ?approved=1 in URL
  document.addEventListener("DOMContentLoaded", function () {
    const urlParams = new URLSearchParams(window.location.search);
    if (urlParams.get('approved') === '1') {
      alert("✅ Proposal approved successfully!");
      // Remove the query string from URL
      window.history.replaceState({}, document.title, window.location.pathname);
    }
  });

  document.addEventListener("DOMContentLoaded", function () {
  const urlParams = new URLSearchParams(window.location.search);
  const tab = urlParams.get('tab');

  if (tab && ['dashboard', 'proposal', 'requirement'].includes(tab)) {
    switchTab(tab);

    // Optional: Remove the query string from the URL
    window.history.replaceState({}, document.title, window.location.pathname);
  }

  if (urlParams.get('approved') === '1') {
    alert("✅ Proposal approved successfully!");
  }
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


    document.querySelectorAll('.disapprove-btn').forEach(btn => {
  btn.addEventListener('click', () => {
    const proposalId = btn.getAttribute('data-id');
    document.getElementById('modalProposalId').value = proposalId;
  });
});

document.addEventListener('DOMContentLoaded', () => {
  document.querySelectorAll('.disapprove-btn').forEach(btn => {
    btn.addEventListener('click', () => {
      const proposalId = btn.getAttribute('data-id');
      const modalInput = document.getElementById('modal_proposal_id');
      if (modalInput) {
        modalInput.value = proposalId;
      } else {
        console.error('Element with id modal_proposal_id not found!');
      }
    });
  });
});
    
  function setActionAndSubmit(button, action) {
    const form = button.closest('form');
    form.querySelector('input[name="action"]').value = action;

    let message = (action === 'approve_financial')
      ? 'Are you sure you want to approve this financial report?'
      : 'Are you sure you want to disapprove this financial report?';

    if (confirm(message)) {
      form.submit();
    }
  }


</script>
</body>
</html>
