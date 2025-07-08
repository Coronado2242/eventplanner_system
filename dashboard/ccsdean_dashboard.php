<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "eventplanner";

$conn = new mysqli($servername, $username, $password, $dbname);
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

// === Separate levels for Proposal and Venue ===
$proposal_level = 'CCS Dean';
$venue_level = 'Venues'; 

// === Handle Approve/Disapprove for Proposals (excluding voag venue) ===
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['proposal_id'], $_POST['action']) && isset($_POST['type']) && $_POST['type'] === 'proposal') {
    $id = (int)$_POST['proposal_id'];
    $action = $_POST['action'];

    if ($action === 'approve') {
        $status = 'Pending';
        $new_level = 'OSAS';
        $viewed = 0;

        $stmt = $conn->prepare("UPDATE sooproposal SET status=?, level=?, viewed=? WHERE id=?");
        $stmt->bind_param("ssii", $status, $new_level, $viewed, $id);
        $stmt->execute();
        header("Location: ccsdean_dashboard.php?approved=1&tab=proposal");
        exit;
    } elseif ($action === 'disapprove') {
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
        header("Location: ccsdean_dashboard.php?tab=proposal");
        exit;
    }
}

// === Handle Approve/Disapprove for Venue (voag only) ===
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['proposal_id'], $_POST['action']) && isset($_POST['type']) && $_POST['type'] === 'venue') {
    $id = (int)$_POST['proposal_id'];
    $action = $_POST['action'];

    if ($action === 'approve') {
        $status = 'Pending';
        $new_level = 'CCS Treasurer';
        $viewed = 0;

        $stmt = $conn->prepare("UPDATE sooproposal SET status=?, level=?, viewed=? WHERE id=?");
        $stmt->bind_param("ssii", $status, $new_level, $viewed, $id);
        $stmt->execute();
        header("Location: ccsdean_dashboard.php?approved=1&tab=venue");
        exit;
    } elseif ($action === 'disapprove') {
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
        header("Location: ccsdean_dashboard.php?tab=venue");
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
        $new_level = 'Financial OSAS';

        $stmt = $conn->prepare("UPDATE sooproposal SET financialstatus = ?, level = ? WHERE id = ?");
        $stmt->bind_param("ssi", $financialstatus, $new_level, $id);
        $stmt->execute();

        header("Location: ccsdean_dashboard.php?financial_approved=1&tab=financial");
        exit;

    } elseif ($action === 'disapprove_financial') {
        $financialstatus = 'Disapproved by Dean';
        $stmt = $conn->prepare("UPDATE sooproposal SET financialstatus = ?, submit = NULL WHERE id = ?");
        $stmt->bind_param("si", $financialstatus, $id);
        $stmt->execute();

        header("Location: ccsdean_dashboard.php?financial_disapproved=1&tab=financial");
        exit;
    }
}

// === Fetch Proposals for Approval (excluding voag venue) ===
$search_department = '%CCS%';
$stmt = $conn->prepare("SELECT * FROM sooproposal WHERE level=? AND status='Pending' AND submit='submitted' AND department LIKE ?");
$stmt->bind_param("ss", $proposal_level, $search_department);
$stmt->execute();
$proposal_result = $stmt->get_result();

// === Fetch Venue Requests for voag ===
$venue_stmt = $conn->prepare("SELECT * FROM sooproposal WHERE level=? AND status='Pending' AND submit='submitted' AND department LIKE ? AND LOWER(venue) = 'voag'");
$venue_stmt->bind_param("ss", $venue_level, $search_department);
$venue_stmt->execute();
$venue_result = $venue_stmt->get_result();
?>


<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>CCS Dean Dashboard</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" crossorigin="anonymous">
  <link rel="stylesheet" href="../style/sbotreasure1.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js" crossorigin="anonymous"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.min.js" crossorigin="anonymous"></script>
  <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate" />
</head>

<body>
<header class="topbar">
  <div class="logo">
    <img src="<?php echo $logoSrc; ?>" alt="Logo" style="height:49px; border-radius:50%; box-shadow:0 4px 8px rgba(0,0,0,0.3);">
    Event<span style="color:blue;">Sync</span>&nbsp;CCS DEAN PORTAL</div>
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
          <?php if ($_SESSION['role'] === 'CCSDean'): ?>
            <a href="ccsdean_dashboard.php">CCS Dean Dashboard</a>
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
    <li onclick="toggleSubMenu('createEventSubMenu')"><i class="fa fa-folder-plus" ></i> Proposal Approval <i class="fa fa-caret-down" ></i></li>
    <ul id="createEventSubMenu" class="submenu" style="display:none;">
    <li id="proposalTab"> Proposals</li>
    <li id="requirementTab"> Requirements</li>
    </ul>
    <li onclick="toggleSubMenu('venueSubMenu')"><i class="fa fa-folder-plus" ></i> Venue Approval <i class="fa fa-caret-down" ></i></li>
    <ul id="venueSubMenu" class="submenu" style="display:none;">
    <li id="venueTab"> Venue</li>
    <li id="vrequirementTab"> Requirements</li>
    </ul>
    <li id="financialTab"><i class="fa fa-check-circle"></i> Financial Report</li>
  </ul>
</aside>

<!-- Dashboard Section -->
<div id="dashboardContent" class="content">
  <h1>Welcome to the CCS Dean Dashboard</h1>
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
    <?php if ($proposal_result && $proposal_result->num_rows > 0): ?>
      <?php while ($row = $proposal_result->fetch_assoc()): ?>
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
  <input type="hidden" name="type" value="proposal">
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
  // ✅ FIX: Use $proposal_level instead of $current_level
  $stmt = $conn->prepare("
    SELECT * FROM sooproposal 
    WHERE level=? AND status='Pending' AND submit='submitted' AND department LIKE ?
  ");
  $stmt->bind_param("ss", $proposal_level, $search_department);
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
              "Constitution and By-laws of the Org." => "constitution",
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
      echo '<div class="alert alert-info text-center">No requirements found for Dean.</div>';
  }
  ?>
</div>


<!-- Venue Approval -->
<div id="venueContent" class="content" style="display:none;">
  <h1>Pending Venue Requests</h1>
  <table>
    <thead><tr><th>Department</th><th>Activity Name</th><th>Date</th><th>Venue</th><th>Status</th><th>Actions</th></tr></thead>
    <tbody>
      <?php if ($venue_result && $venue_result->num_rows > 0): while ($row = $venue_result->fetch_assoc()): ?>
        <tr>
          <td><?= htmlspecialchars($row['department']) ?></td>
          <td><?= htmlspecialchars($row['activity_name']) ?></td>
          <td><?= htmlspecialchars($row['start_date']) ?> - <?= htmlspecialchars($row['end_date']) ?></td>
          <td><?= htmlspecialchars($row['venue']) ?></td>
          <td><?= htmlspecialchars($row['status']) ?></td>
          <td>
            <button class="btn btn-success btn-sm approve-btn" data-id="<?= $row['id'] ?>" data-bs-toggle="modal" data-bs-target="#approveModal">Approve</button>
            <button class="btn btn-danger btn-sm disapprove-btn" data-id="<?= $row['id'] ?>" data-bs-toggle="modal" data-bs-target="#disapproveModal">Disapprove</button>
          </td>
        </tr>
      <?php endwhile; else: ?>
        <tr><td colspan="7" class="text-center">No venue requests found.</td></tr>
      <?php endif; ?>
    </tbody>
  </table>
</div>

<!-- Venue Requirements Section -->
<div id="vrequirementContent" class="content" style="display:none;">
  <h1>Venue Requirements</h1>
  <?php
  // ✅ NEW query for venue requirements
  $venue_req_stmt = $conn->prepare("
    SELECT * FROM sooproposal 
    WHERE level=? AND status='Pending' AND submit='submitted' 
      AND department LIKE ? AND LOWER(venue) = 'voag'
  ");
  $venue_req_stmt->bind_param("ss", $venue_level, $search_department);
  $venue_req_stmt->execute();
  $venue_result = $venue_req_stmt->get_result();

  if ($venue_result->num_rows > 0) {
    while ($row = $venue_result->fetch_assoc()) {
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
          echo '<a href="' . $directory . htmlspecialchars($row[$field]) . '" target="_blank" class="btn btn-primary btn-sm mt-2">View Attachment</a>';
        } else {
          echo '<span class="text-muted mt-2 d-block">No Attachment</span>';
        }

        echo '</div></div>';
      }

      echo '</div></div>';
    }
  } else {
    echo '<div class="alert alert-info text-center">No requirements found for Venue.</div>';
  }
  ?>
</div>


<!-- Approve Confirmation Modal -->
<div class="modal fade" id="approveModal" tabindex="-1" aria-labelledby="approveModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <form method="POST" action="ccsdean_dashboard.php">
      <div class="modal-content">
        <div class="modal-header bg-success text-white">
          <h5 class="modal-title" id="approveModalLabel">Confirm Approval</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>

        <div class="modal-body">
          <input type="hidden" name="proposal_id" id="approve_proposal_id">
          <input type="hidden" name="action" value="approve">
          <input type="hidden" name="type" id="approve_type">
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
   <form method="POST" action="ccsdean_dashboard.php">

      <div class="modal-content">
        <!-- Header -->
        <div class="modal-header bg-danger text-white">
          <h5 class="modal-title" id="disapproveModalLabel">Disapproved</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>

        <!-- Body -->
        <div class="modal-body">
          <input type="hidden" name="proposal_id" id="modal_proposal_id">
          <input type="hidden" name="level" value="CCSDean">
          <input type="hidden" name="action" value="disapprove">
          <input type="hidden" name="type" id="modal_type">

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
      $query = "SELECT * FROM sooproposal WHERE submit = 'Submitted' AND level = 'CCS Financial Dean'";
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
<form method="POST" action="ccsdean_dashboard.php">
  <input type="hidden" name="proposal_id" value="<?= $row['id'] ?>">
  <div class="d-grid gap-1">
    <button type="submit" name="action" value="approve_financial" class="btn btn-success btn-sm">Approve</button>
    <button type="submit" name="action" value="disapprove_financial" class="btn btn-danger btn-sm">Disapprove</button>
  </div>
</form>
        </td>
      </tr>
      <?php endwhile; ?>
      </tbody>
    </table>
  </div>
</div>

<script>
function toggleDropdown() {
  const menu = document.getElementById('dropdownMenu');
  menu.style.display = menu.style.display === 'block' ? 'none' : 'block';
}

function toggleMobileNav() {
  const nav = document.getElementById("mainNav");
  nav.classList.toggle("show");
}

function toggleSubMenu(id) {
  const submenu = document.getElementById(id);
  submenu.style.display = submenu.style.display === 'block' ? 'none' : 'block';
}

function switchTab(tab) {
  const sections = {
    dashboard: "dashboardContent",
    proposal: "proposalContent",
    requirement: "requirementContent",
    venue: "venueContent",
    vrequirement: "vrequirementContent",
    financial: "financialReportContent"  // ✅ include financial here
  };

  for (const key in sections) {
    const content = document.getElementById(sections[key]);
    const tabElement = document.getElementById(key + "Tab");

    if (content) content.style.display = (key === tab) ? 'block' : 'none';
    if (tabElement) tabElement.classList.toggle('active', key === tab);
  }
}

function setupModalButtons() {
  document.querySelectorAll('.approve-btn').forEach(button => {
    button.addEventListener('click', function () {
      const proposalId = this.getAttribute('data-id');
      const contentSection = this.closest('#proposalContent') ? 'proposal' : 'venue';
      document.getElementById('approve_proposal_id').value = proposalId;
      document.getElementById('approve_type').value = contentSection;
    });
  });

  document.querySelectorAll('.disapprove-btn').forEach(button => {
    button.addEventListener('click', function () {
      const proposalId = this.getAttribute('data-id');
      const contentSection = this.closest('#proposalContent') ? 'proposal' : 'venue';
      document.getElementById('modal_proposal_id').value = proposalId;
      document.getElementById('modal_type').value = contentSection;
    });
  });
}

function checkURLParams() {
  const urlParams = new URLSearchParams(window.location.search);

  const tab = urlParams.get('tab');
  if (tab && ['dashboard', 'proposal', 'requirement', 'venue', 'financial'].includes(tab)) {
    switchTab(tab);
    window.history.replaceState({}, document.title, window.location.pathname);
  }

  if (urlParams.get('approved') === '1') {
    alert("✅ Proposal approved successfully!");
    window.history.replaceState({}, document.title, window.location.pathname);
  }
}

document.addEventListener("DOMContentLoaded", function () {
  document.getElementById("calendarFrame").src = "../proposal/calendar.php";

  // ✅ Add all tabs here including financial
  const tabIds = ["dashboard", "proposal", "requirement", "venue", "vrequirement", "financial"];
  tabIds.forEach(tab => {
    const tabElement = document.getElementById(tab + "Tab");
    if (tabElement) {
      tabElement.addEventListener("click", () => switchTab(tab));
    }
  });

  setupModalButtons();
  checkURLParams();
});

</script>

</body>
</html>