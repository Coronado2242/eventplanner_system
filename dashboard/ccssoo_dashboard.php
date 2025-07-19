<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

$conn = new mysqli("localhost", "root", "", "eventplanner");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
$logoSrcLSPU = "../img/lspulogo.jpg"; 
$logoSrc = "../img/lspulogo.jpg"; 

$sql = "SELECT filepath FROM site_logo ORDER BY date_uploaded DESC LIMIT 1";
$result = $conn->query($sql);

if ($result && $row = $result->fetch_assoc()) {
    if (!empty($row['filepath'])) {
        $logoSrc = "../account/" . htmlspecialchars($row['filepath']); 
    }
}


$unavailableDates = [];
$sql = "SELECT start_date, end_date, status FROM sooproposal WHERE status IN ('Pending', 'Approved')";
$result = $conn->query($sql);
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $unavailableDates[] = [
            'from' => $row['start_date'],
            'to' => $row['end_date'],
            'status' => strtolower($row['status']) // 'pending' or 'approved'
        ];
    }
}


  //SAVE PLAN OF ACTIVITIES
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['activity_name'])) {

    // a.  Collect form data
    $activity_name   = $_POST['activity_name'];
    $date_range      = $_POST['date_range'];          // "06/21/2025 to 06/23/2025"
    [$d1,$d2]        = array_pad(explode(' to ',$date_range),2,$date_range);
    $start_date      = date('Y-m-d', strtotime($d1));
    $end_date        = $d2 ? date('Y-m-d', strtotime($d2)) : $start_date;
    $objective       = $_POST['objective'];
    $budget          = $_POST['budget'];
    $description     = $_POST['description'];
    $venue           = $_POST['venue'];
    $person_involved = $_POST['person_involved'];

    $department      = $_SESSION['department'] ?? 'CCS';
    $username        = $_SESSION['username']   ?? 'unknown';

    // b.  Insert to DB
    $stmt = $conn->prepare(
        "INSERT INTO sooproposal
        (department, activity_name, start_date, end_date, objective, budget,
         description, venue, person_involved, username)
         VALUES (?,?,?,?,?,?,?,?,?,?)"
    );
    $stmt->bind_param(
        "sssssdssss",
        $department, $activity_name, $start_date, $end_date,
        $objective,  $budget,        $description, $venue,
        $person_involved, $username
    );

    if ($stmt->execute()) {

        $last_id = $stmt->insert_id;

/* ─────────────────────────────────────────────────────────
   2)  GENERATE THE POA PDF  (TCPDF design) 
───────────────────────────────────────────────────────── */
require_once __DIR__ . '/../tcpdf/tcpdf.php';

$target_date = $start_date === $end_date ? $start_date : "$start_date to $end_date";
$budget_disp = 'Php ' . number_format((float)$budget, 2);

$pdf = new TCPDF('P', 'mm', 'A4', true, 'UTF-8', false);
$pdf->SetMargins(15, 10, 15);
$pdf->SetAutoPageBreak(true, 25); // regular break behavior
$pdf->AddPage();

$logoWidth = 25;
$pdf->Image($logoSrcLSPU, 15, 10, $logoWidth);
$pdf->SetXY(15 + $logoWidth + 5, 10);

/* HEADER */
$pdf->SetFont('helvetica', '', 10);
$pdf->Cell(0, 5, '', 0, 1, 'C');
$pdf->Cell(0, 5, 'Republic of the Philippines', 0, 1, 'C');
$pdf->SetFont('helvetica', 'B', 12);
$pdf->Cell(0, 5, 'Laguna State Polytechnic University', 0, 1, 'C');
$pdf->SetFont('helvetica', '', 10);
$pdf->Cell(0, 5, 'Province of Laguna', 0, 1, 'C');
$pdf->Ln(5);

$pdf->SetFont('helvetica', 'B', 12);
$pdf->Cell(0, 5, 'PLAN OF ACTIVITIES', 0, 1, 'C');
$pdf->SetFont('helvetica', 'BU', 11);
$pdf->Cell(0, 6, 'COLLEGE OF COMPUTER STUDIES – STUDENT BODY ORGANIZATION', 0, 1, 'C');
$pdf->Ln(2);
$pdf->SetFont('helvetica', '', 11);
$pdf->Cell(0, 5, 'Name of Organization', 0, 1, 'C');
$pdf->SetFont('helvetica', 'B', 11);
$pdf->Cell(0, 5, '2ND SEMESTER A.Y. 2025-2026', 0, 1, 'C');
$pdf->Ln(6);

/* TABLE */
$tbl = '
<style>th{font-weight:bold;background-color:#f2f2f2;}</style>
<table border="1" cellpadding="4" cellspacing="0">
  <tr>
    <th width="16%">OBJECTIVE</th>
    <th width="16%">ACTIVITIES</th>
    <th width="22%">BRIEF DESCRIPTION</th>
    <th width="20%">PERSONS INVOLVED</th>
    <th width="13%">TARGET DATE</th>
    <th width="13%">BUDGET</th>
  </tr>
  <tr>
    <td height="55">' . htmlspecialchars($objective) . '</td>
    <td>' . htmlspecialchars($activity_name) . '</td>
    <td>' . htmlspecialchars($description) . '</td>
    <td>' . htmlspecialchars($person_involved) . '</td>
    <td>' . $target_date . '</td>
    <td>' . $budget_disp . '</td>
  </tr>
</table>';

$pdf->SetFont('helvetica', '', 9.5);
$pdf->writeHTML($tbl, true, false, false, false, '');

// DISABLE page breaks to force signatures + footer to stay on same page
$pdf->SetAutoPageBreak(false, 20);

/* SIGNATURES */
$pdf->Ln(10);
$pdf->SetFont('helvetica', '', 10);

// Helper function: two-column signature
function sigRow($pdf, $left = '', $right = '') {
    $pdf->Cell(95, 8, $left, 0, 0, 'C');
    $pdf->Cell(95, 8, $right, 0, 1, 'C');
    $pdf->Cell(95, 6, '_________________________', 0, 0, 'C');
    $pdf->Cell(95, 6, '_________________________', 0, 1, 'C');
}

// Helper function: centered signature
function centerSigRow($pdf, $label = '', $name = '') {
  $pdf->Ln(3); // gap above the label
  $pdf->Cell(190, 8, $label, 0, 1, 'C');
  $pdf->Ln(5); // gap below the label, above the line
  $pdf->Cell(190, 6, '_________________________', 0, 1, 'C');
  $pdf->Cell(190, 4, $name, 0, 1, 'C');
}

// Prepared by
$pdf->Cell(95, 8, 'Prepared by:', 0, 1, 'L');
sigRow($pdf, '');
$pdf->SetFont('helvetica', 'I', 9);
$pdf->Cell(95, 4, 'President, CCS SBO', 0, 0, 'C');
$pdf->Cell(95, 4, 'Secretary, CCS SBO', 0, 1, 'C');
$pdf->Ln(4);

// Noted by
$pdf->SetFont('helvetica', '', 10);
$pdf->Cell(95, 8, 'Prepared by:', 0, 1, 'L');
sigRow($pdf, '');
$pdf->SetFont('helvetica', 'I', 9);
$pdf->Cell(95, 4, 'Junior Adviser, CCS SBO', 0, 0, 'C');
$pdf->Cell(95, 4, 'Senior Adviser, CCS SBO', 0, 1, 'C');
$pdf->Ln(4);

// Dean
$pdf->SetFont('helvetica', '', 10);
$pdf->Cell(190, 6, '_________________________', 0, 1, 'C');
$pdf->Cell(190, 4, 'Dean, College of Computer Studies', 0, 1, 'C');
$pdf->Ln(4);

// Recommending Approval
centerSigRow($pdf, 'Recommending Approval:', 'Head, Student Organization and Activities Unit');

// Approved/Disapproved
centerSigRow($pdf, 'Approved/Disapproved:', 'Director/Chairperson, Office of Student Affairs and Services');

/* FOOTER — shown only on page 1 */
$pdf->SetFont('helvetica', '', 8);
$pageWidth = $pdf->getPageWidth();
$margin = 10;

$pdf->SetY(-15);
$pdf->SetX($margin);
$pdf->Cell(0, 5, 'LSPU-OSAS-SF-004', 0, 0, 'L');

$pdf->SetX($pageWidth / 2 - 10);
$pdf->Cell(20, 5, 'Rev. 1', 0, 0, 'C');

$pdf->SetX(-$margin - 20);
$pdf->Cell(20, 5, 'July', 0, 1, 'R');

// Bottom center: year
$pdf->SetY(-10);
$pdf->SetX($pageWidth / 2 - 10);
$pdf->Cell(20, 5, '2025', 0, 0, 'C');

// Optional: re-enable auto page break if needed
$pdf->SetAutoPageBreak(true, 25);

/* SAVE PDF + DB UPDATE */
$uploadDir = realpath(__DIR__ . '/../proposal/uploads');
if (!is_dir($uploadDir)) mkdir($uploadDir, 0775, true);

$poaFile = 'plan_of_activities_' . time() . '.pdf';
$poaPath = $uploadDir . '/' . $poaFile;
$pdf->Output($poaPath, 'F');

if (file_exists($poaPath)) {
    $conn->query("UPDATE sooproposal SET POA_file='$poaFile' WHERE id='$last_id'");
}

echo "<script>
    alert('Activity saved! Proceeding to budget form…');
    document.addEventListener('DOMContentLoaded', function() {
        switchTab('eventBudgetContent');
        document.getElementById('budgetProposalId').value = '$last_id';
    });
</script>";


    } else {
        echo "<script>alert('Error saving activity.');</script>";
    }
}

// Step 2: Run the Query
$sql = "SELECT * FROM activities";
$result = $conn->query($sql);

if (!$result) {
    die("❌ Query error: " . $conn->error);
}


?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>CCS SBO SOO Dashboard</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/themes/material_orange.css">
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
  <link rel="stylesheet" href="../style/sbotreasure1.css">
  <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate" />
  <style>
    body { margin: 0; font-family: Arial, sans-serif; }
    .content { display: none; }
    .content.active { display: block; }
    .sidebar ul ul.submenu {
      list-style-type: none;
      padding-left: 20px;
      display: none;
    }
    .sidebar ul ul.submenu li {
      cursor: pointer;
      padding: 5px 0;
      font-size: 0.95em;
    }
    .sidebar ul ul.submenu li:hover {
      text-decoration: underline;
    }
    .table {
  border-radius: 8px;
  overflow: hidden;
  font-size: 0.95rem;
}

.table thead th {
  background-color: #d1e7dd;
  color: #0f5132;
}

.badge.bg-success {
  font-size: 0.8rem;
  padding: 5px 8px;
}

.badge.bg-secondary {
  font-size: 0.8rem;
  padding: 5px 8px;
  opacity: 0.75;
}

/* Base styles for marking cells */
.pending-date::after,
.approved-date::after {
    content: "";
    width: 8px;
    height: 8px;
    border-radius: 50%;
    position: absolute;
    bottom: 4px;
    right: 4px;
}

/* Orange dot for pending */
.pending-date {
    position: relative;
}
.pending-date::after {
    background-color: orange;
}

/* Green dot for approved */
.approved-date {
    position: relative;
}
.approved-date::after {
    background-color: green;
}

/* Orange bilog para sa Pending */
.flatpickr-day.pending-day {
    position: relative;
    border-radius: 50%;
    border: 2px solid orange !important;
    color: orange !important;
}

/* Green bilog para sa Approved */
.flatpickr-day.approved-day {
    position: relative;
    border-radius: 50%;
    border: 2px solid green !important;
    color: green !important;
}


#proposalModal {
    animation: fadeIn 0.3s ease-in-out;
}
@keyframes fadeIn {
    from {opacity: 0; transform: translate(-50%, -40%);}
    to {opacity: 1; transform: translate(-50%, -50%);}
}

  </style>
</head>
<body>

<header class="topbar">
  <div class="logo">
    <img src="<?php echo $logoSrc; ?>" alt="Logo" style="height:49px; border-radius:50%; box-shadow:0 4px 8px rgba(0,0,0,0.3);">
    Event<span style="color:blue;">Sync</span>&nbsp;CCS SBO SOO PORTAL</div>
  <nav id="mainNav">
    <a href="../index.php">Home</a>
    <a href="../aboutus.php">About Us</a>
    <a href="../calendar1.php">Calendar</a>
    <div class="admin-info">
      <span><?= htmlspecialchars($_SESSION['fullname'] ?? '') ?> (<?= htmlspecialchars($_SESSION['role'] ?? '') ?>)</span>
      <div class="user-dropdown" id="userDropdown">
        <i class="fa-solid fa-user" onclick="toggleDropdown()"></i>
        <div class="dropdown-menu" id="dropdownMenu" style="display:none;">
          <a href="ccssoo_dashboard.php">Dashboard</a>
          <a href="../account/logout.php">Logout</a>
        </div>
      </div>
    </div>
  </nav>
</header>

<aside class="sidebar">
  <ul>
    <li onclick="switchTab('dashboardContent')"><i class="fa fa-home"></i> Dashboard</li>

    <li onclick="toggleSubMenu('createEventSubMenu')">
      <i class="fa fa-folder-plus"></i> Create Event
      <i class="fa fa-caret-down"></i>
    </li>
    <ul id="createEventSubMenu" class="submenu">
      <li onclick="switchTab('createEventContent')">Create Event Form</li>
      <li onclick="switchTab('eventSummaryContent')">Summary Requirements</li>
      <li onclick="switchTab('eventPendingContent')">Request Pending</li>
      <li onclick="switchTab('eventCompletedContent')">Completed</li>
    </ul>

    <li onclick="toggleSubMenu('financialReportSubMenu')">
      <i class="fa fa-chart-bar"></i> Financial Report
      <i class="fa fa-caret-down"></i>
    </li>
    <ul id="financialReportSubMenu" class="submenu">
      <li onclick="switchTab('financialReportContent')">View Report</li>
      <li onclick="switchTab('eventFinancialPendingContent')">Pending Report</li>
      <li onclick="switchTab('completedFinancialContent')">Completed</li>
    </ul>
  </ul>
</aside>


<main>
  <div id="dashboardContent" class="content active">
    <h1>Welcome to the CCS SBO SOO Dashboard</h1>
    <iframe src="../proposal/calendar.php" style="width:100%; height:1000px; border:none;"></iframe>
  </div>

  <div id="createEventContent" class="content">
    <h2 class="text-center fw-bold mb-4">PLAN OF ACTIVITIES</h2>
        <form class="container" style="max-width: 900px;" method="POST" action="">
        <div class="row mb-3">
            <div class="col-md-6">
            <label class="form-label">Name of Activities:</label>
            <select name="activity_name" id="activity_name" class="form-control" required>
    <option value="">-- Select an Activity --</option>
    <?php
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $name = htmlspecialchars($row['activity_name']);
            echo "<option value=\"$name\">$name</option>";
        }
    } else {
        echo "<option disabled>No activities found</option>";
    }
    ?>
            </select>
            </div>
            <div class="col-md-6">
            <label class="form-label">Target Date Range:</label>
            <input type="text" class="form-control" name="date_range" id="date_range" required>
            </div>
        </div>

        <div class="row mb-3">
            <div class="col-md-6">
            <label class="form-label">Objective:</label>
            <input type="text" class="form-control" name="objective" id="objective" required>
            </div>
            <div class="col-md-6">
            <label class="form-label">Budget:</label>
            <input type="number" class="form-control" name="budget" id="budget" required>
            </div>
        </div>

        <div class="row mb-3">
            <div class="col-md-6">
            <label class="form-label">Brief Description:</label>
            <input type="text" class="form-control" name="description" id="description">
            </div>
            <div class="col-md-6">
              <label class="form-label">Venue:</label>
              <select name="venue" class="form-control" required>
                <option value="">-- Select a Venue --</option>
                <?php
                $venueQuery = "SELECT id, venue FROM venue_db";
                $venueResult = $conn->query($venueQuery);

                if ($venueResult && $venueResult->num_rows > 0) {
                  while ($venueRow = $venueResult->fetch_assoc()) {
                    $venueName = htmlspecialchars($venueRow['venue']);
                    echo "<option value=\"$venueName\">$venueName</option>";
                  }
                } else {
                  echo "<option disabled>No venues found</option>";
                }
                ?>
              </select>
            </div>

        </div>

        <div class="row mb-4">
            <div class="col-md-12">
            <label class="form-label">Person Involved:</label>
            <input type="text" class="form-control" name="person_involved" id="person_involved" required>
            </div>
        </div>

        <div class="d-flex justify-content-end">
            <button type="submit" class="btn btn-primary px-4" name="submit_event">NEXT</button>
        </div>
        </form>
  </div>

  <div id="eventBudgetContent" class="content">
    <h2 class="mb-4">Submit Budget Plan</h2>
    <form action="" id="myForm" method="POST" onsubmit="return confirmSubmit();">
      <input type="hidden" name="proposal_id" id="budgetProposalId" value="1"><!-- Replace with dynamic proposal ID -->

      <div class="table-responsive">
        <table class="table table-bordered table-striped align-middle text-center">
          <thead class="table-dark">
            <tr>
              <th>Event Name</th>
              <th>Particulars</th>
              <th>Quantity</th>
              <th>Amount</th>
              <th>Total</th>
            </tr>
          </thead>
            <tbody id="budgetTableBody">
            <tr>
                <td><input type="text" name="event_name[]" class="form-control" /></td>
                <td><input type="text" name="particulars[]" class="form-control" /></td>
                <td><input type="number" name="qty[]" class="form-control qty-input" step="1" /></td>
                <td><input type="text" name="amount[]" class="form-control amount-input" /></td>
                <td><input type="number" name="total[]" class="form-control total-input" readonly /></td>
            </tr>
            </tbody>
            <tr>
            <td colspan="4" class="text-end fw-bold">Grand Total:</td>
            <td><input type="text" id="grandTotal" class="form-control fw-bold" readonly /></td>
            </tr>
            <div class="text-start my-2">
            <button type="button" class="btn btn-success btn-sm" onclick="addRow()">
                <i class="fa fa-plus"></i> Add Row
            </button>
            </div>

          </tbody>
        </table>
      </div>

      <div class="text-end mt-3">
        <button type="submit" class="btn btn-primary px-5" name="submit_budget" id="budgetForm">Generate PDF and Submit</button>
      </div>
    </form>
  </div>


  <div id="eventSummaryContent" class="content">
  <h2 class="mb-3 fw-bold">Summary of Requirements</h2>
  <div class="table-responsive">
    <table class="table table-bordered table-hover table-striped text-center align-middle shadow-sm rounded">
      <thead class="table-success text-dark">
        <tr>
          <th>Activity Name</th>
          <th>Target Dates</th>
          <th>POA File</th>
          <th>Budget Plan</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody>
<?php
$username = $_SESSION['username'] ?? '';
$query = "SELECT * FROM sooproposal WHERE username = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();

while ($row = $result->fetch_assoc()):
    $poa = $row['POA_file'] ?? '';
    $budget = $row['budget_file'] ?? '';
    $status = trim($row['status'] ?? '');

    // Only show if both files exist AND status is empty (not submitted yet)
    if (empty($poa) || empty($budget) || !empty($status)) continue;

    $start = date("M d, Y", strtotime($row['start_date']));
    $end = date("M d, Y", strtotime($row['end_date']));
?>
      <tr>
        <td><?= htmlspecialchars($row['activity_name']) ?></td>
        <td><?= "$start to $end" ?></td>
        <td>
          <?php if ($poa): ?>
            <a href="../proposal/uploads/<?= $poa ?>" target="_blank" class="badge bg-success text-decoration-none">
              <i class="fa fa-file-pdf"></i> View
            </a>
          <?php else: ?>
            <span class="badge bg-secondary">Not Generated</span>
          <?php endif; ?>
        </td>
        <td>
          <?php if ($budget): ?>
            <a href="../proposal/uploads/<?= $budget ?>" target="_blank" class="badge bg-success text-decoration-none">
              <i class="fa fa-file-pdf"></i> View
            </a>
          <?php else: ?>
            <span class="badge bg-secondary">Not Generated</span>
          <?php endif; ?>
        </td>
        <td>
          <button type="button" class="btn btn-success btn-sm" onclick="openModal('submit', <?= $row['id'] ?>)">Submit</button>
          <button type="button" class="btn btn-danger btn-sm" onclick="openModal('cancel', <?= $row['id'] ?>)">Cancel</button>
        </td>

      </tr>
      <?php endwhile; ?>

      </tbody>
    </table>
  </div>
</div>

<!-- Modal -->
<!-- MODAL -->
<div id="proposalModal" style="display:none; position:fixed; top:30%; left:47%; transform:translate(-50%, -50%);
    background: #fff8e1; border: 2px solid #ffa000; padding: 20px 30px; border-radius: 12px; box-shadow: 0 8px 20px rgba(0,0,0,0.3); 
    z-index: 1000; text-align: center; max-width: 400px; font-family: 'Segoe UI', sans-serif;">
    
    <div style="font-size: 40px; color: #ffa000; margin-bottom: 10px;">
        ⚠️
    </div>
    
    <p style="font-size: 18px; color: #5d4037;"><strong>Already have a proposal on this date.</strong></p>
    
    <button onclick="document.getElementById('proposalModal').style.display='none'" 
        style="margin-top: 15px; padding: 10px 20px; border: none; background: #ffa000; color: white; 
        font-weight: bold; border-radius: 8px; cursor: pointer; box-shadow: 0 4px 10px rgba(0,0,0,0.2);">
        Close
    </button>
</div>


<!-- Confirmation Modal -->
<div class="modal fade" id="confirmationModal" tabindex="-1" aria-labelledby="confirmationModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="confirmationModalLabel">Confirm Action</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body" id="modalMessage">
        Are you sure you want to proceed?
      </div>
      <div class="modal-footer">
        <form id="modalForm" method="POST" action="handle_action.php">
          <input type="hidden" name="proposal_id" id="modalProposalId" value="">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" id="modalSubmitButton" class="btn"></button>
        </form>
      </div>
    </div>
  </div>
</div>



<div id="eventPendingContent" class="content">
  <h2 class="mb-3 fw-bold">Pending Proposal</h2>
  <div class="table-responsive">
    <table class="table table-bordered table-hover table-striped text-center align-middle shadow-sm rounded">
      <thead class="table-success text-dark">
        <tr>
          <th>Activity Name</th>
          <th>Status</th>
          <th>POA File</th>
          <th>Budget Plan</th>
        </tr>
      </thead>
      <tbody>
      <?php

      $username = $_SESSION['username'] ?? '';
      $query = "SELECT * FROM sooproposal WHERE username = ? AND status = 'Pending'";
      $stmt = $conn->prepare($query);
      $stmt->bind_param("s", $username);
      $stmt->execute();
      $result = $stmt->get_result();

      while ($row = $result->fetch_assoc()):
          $poa = $row['POA_file'];
          $budget = $row['budget_file'];
      ?>
      <tr>
        <td><?= htmlspecialchars($row['activity_name']) ?></td>
        <td><span class="badge bg-warning text-dark">Pending</span></td>
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
      </tr>
      <?php endwhile; ?>
      </tbody>
    </table>
  </div>
</div>

<div id="eventCompletedContent" class="content">
  <h2 class="mb-3 fw-bold">Completed Proposal</h2>
  <div class="table-responsive">
    <table class="table table-bordered table-hover table-striped text-center align-middle shadow-sm rounded">
      <thead class="table-success text-dark">
        <tr>
          <th>Activity Name</th>
          <th>Status</th>
          <th>POA File</th>
          <th>Budget Plan</th>
        </tr>
      </thead>
      <tbody>
      <?php

      $username = $_SESSION['username'] ?? '';
      $query = "SELECT * FROM sooproposal WHERE username = ? AND level = 'Completed'";
      $stmt = $conn->prepare($query);
      $stmt->bind_param("s", $username);
      $stmt->execute();
      $result = $stmt->get_result();

      while ($row = $result->fetch_assoc()):
          $poa = $row['POA_file'];
          $budget = $row['budget_file'];
      ?>
      <tr>
        <td><?= htmlspecialchars($row['activity_name']) ?></td>
        <td><span class="badge bg-success text-light">Completed</span></td>
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
      </tr>
      <?php endwhile; ?>
      </tbody>
    </table>
  </div>
</div>

<div id="financialReportContent" class="content">
  <h2>Financial Report</h2>
  <div class="table-responsive">
    <table class="table table-bordered table-hover table-striped text-center align-middle shadow-sm rounded">
      <thead class="table-success text-dark">
        <tr>
          <th>Activity Name</th>
          <th>Plan Of Activities</th>
          <th>Budget Plan</th>
          <th>Budget Amount</th>
          <th>Receipt</th>
        </tr>
      </thead>
      <tbody>
      <?php
      $username = $_SESSION['username'] ?? '';
      $query = "SELECT * FROM sooproposal WHERE username = ? AND level = 'Completed' AND (financialstatus IS NULL OR financialstatus != 'Submitted')";
      $stmt = $conn->prepare($query);
      $stmt->bind_param("s", $username);
      $stmt->execute();
      $result = $stmt->get_result();

      $today = date("Y-m-d");

      while ($row = $result->fetch_assoc()):
          $poa = $row['POA_file'];
          $budget = $row['budget_file'];
          $end_date = $row['end_date'];
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
        <!-- Single form with file + submit -->
        <form action="upload_receipt.php" method="post" enctype="multipart/form-data">
        <input type="hidden" name="proposal_id" value="<?= $row['id'] ?>">
        <input type="hidden" name="return_page" value="ccssoo_dashboard.php">
        <div class="input-group input-group-sm mb-2">
          <input type="file" name="receipt_file" class="form-control form-control-sm" required>
        </div>
        <button type="submit" class="btn btn-success btn-sm w-100">Submit</button>
      </form>

      </td>
      </tr>
      <?php endwhile; ?>
      </tbody>
    </table>
  </div>
</div>


<div id="eventFinancialPendingContent" class="content">
  <h2 class="mb-3 fw-bold">Pending Report</h2>
  <div class="table-responsive">
    <table class="table table-bordered table-hover table-striped text-center align-middle shadow-sm rounded">
      <thead class="table-success text-dark">
        <tr>
          <th>Activity Name</th>
          <th>Status</th>
          <th>POA File</th>
          <th>Budget Plan</th>
        </tr>
      </thead>
      <tbody>
<?php
$username = $_SESSION['username'] ?? '';
$query = "SELECT * FROM sooproposal WHERE username = ? AND financialstatus = 'Submitted'";
$stmt = $conn->prepare($query);
$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();

while ($row = $result->fetch_assoc()):
    $poa = $row['POA_file'];
    $budget = $row['budget_file'];
?>
<tr>
  <td><?= htmlspecialchars($row['activity_name']) ?></td>
    <td>
    <?php
    $approvedBy = $row['level'];
    $words = explode(' ', $approvedBy);
    $lastWord = end($words);
    ?>
    <span class="badge bg-primary">
      Approving by <?= htmlspecialchars($lastWord) ?>
    </span>
  </td>

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
</tr>
<?php endwhile; ?>
</tbody>

    </table>
  </div>
</div>
</main>

<?php
// Fetch all completed financial reports (approved by OSAS)
$sql = "SELECT * FROM sooproposal WHERE level='Financial Completed' AND status='Approved' ORDER BY end_date DESC";
$result = $conn->query($sql);
?>

<div id="completedFinancialContent" class="content">
  <h2>Completed Financial Reports</h2>

  <?php if ($result->num_rows > 0): ?>
    <div class="table-responsive">
      <table class="table table-bordered table-striped table-hover text-center align-middle shadow-sm rounded">
        <thead class="table-success">
          <tr>
            <th>Event Name</th>
            <th>Department</th>
            <th>Start Date</th>
            <th>End Date</th>
            <th>Status</th>
          </tr>
        </thead>
        <tbody>
          <?php while ($row = $result->fetch_assoc()): ?>
            <tr>
              <td><?= htmlspecialchars($row['activity_name']) ?></td>
              <td><?= htmlspecialchars($row['department']) ?></td>
              <td><?= htmlspecialchars($row['start_date']) ?></td>
              <td><?= htmlspecialchars($row['end_date']) ?></td>
              <td><span class="badge bg-success">Completed</span></td>

            </tr>
          <?php endwhile; ?>
        </tbody>
      </table>
    </div>
  <?php else: ?>
    <p class="text-muted">No completed financial reports yet.</p>
  <?php endif; ?>
</div>


<script>
function addRow() {
  const tbody = document.getElementById("budgetTableBody");
  const row = document.createElement("tr");
  row.innerHTML = `
    <td><input type="text" name="event_name[]" class="form-control" /></td>
    <td><input type="text" name="particulars[]" class="form-control" /></td>
    <td><input type="number" name="qty[]" class="form-control qty-input" step="1" /></td>
    <td><input type="text" name="amount[]" class="form-control amount-input" /></td>
    <td><input type="number" name="total[]" class="form-control total-input" readonly /></td>
  `;
  tbody.appendChild(row);

  // Reattach listeners for new inputs
  row.querySelector(".qty-input").addEventListener("input", calculateTotal);
  row.querySelector(".amount-input").addEventListener("input", calculateTotal);
}

// Expose calculateTotal globally so it can be reused
function calculateTotal() {
  let grand = 0;
  document.querySelectorAll("#budgetTableBody tr").forEach(row => {
    const qty = parseFloat(row.querySelector(".qty-input")?.value || 0);
    const amt = parseFloat(row.querySelector(".amount-input")?.value || 0);
    const total = qty * amt;
    const totalInput = row.querySelector(".total-input");
    if (totalInput) {
      totalInput.value = total.toFixed(2);
      grand += total;
    }
  });
  document.getElementById("grandTotal").value = grand.toFixed(2);
}

  flatpickr("input[name='date_range']", {
    mode: "range",
    dateFormat: "m/d/Y",
    showMonths: 1,
    disableMobile: true,
    theme: "material_orange" // If using the orange theme
  });
</script>

<script>
function toggleDropdown() {
  const menu = document.getElementById('dropdownMenu');
  menu.style.display = menu.style.display === 'block' ? 'none' : 'block';
}

function toggleSubMenu(id) {
  const submenu = document.getElementById(id);
  submenu.style.display = submenu.style.display === 'block' ? 'none' : 'block';
}

function switchTab(tabId) {
  document.querySelectorAll('.content').forEach(c => c.classList.remove('active'));
  document.getElementById(tabId).classList.add('active');
}

function goToBudgetPlan() {
  const requiredFields = ['activity_name', 'target_date', 'objective', 'budget', 'person_involved'];
  for (const id of requiredFields) {
    const input = document.getElementById(id);
    if (!input.value.trim()) {
      alert("Please fill out all required fields.");
      return;
    }
  }

  if (confirm("Are you sure you want to proceed to the Budget Plan?")) {
    switchTab('eventBudgetContent');
  }
}

function confirmSubmit() {
  return confirm("Are you sure you want to generate and submit the budget plan?");
}

document.addEventListener("DOMContentLoaded", function () {
  const qtyInputs = document.querySelectorAll(".qty-input");
  const amountInputs = document.querySelectorAll(".amount-input");

  function calculateTotal() {
    let grand = 0;
    document.querySelectorAll("tbody tr").forEach(row => {
      const qty = parseFloat(row.querySelector(".qty-input")?.value || 0);
      const amt = parseFloat(row.querySelector(".amount-input")?.value || 0);
      const total = qty * amt;
      const totalInput = row.querySelector(".total-input");
      if (totalInput) {
        totalInput.value = total.toFixed(2);
        grand += total;
      }
    });
    document.getElementById("grandTotal").value = grand.toFixed(2);
  }

  qtyInputs.forEach(input => input.addEventListener("input", calculateTotal));
  amountInputs.forEach(input => input.addEventListener("input", calculateTotal));
});
function switchTab(tabId) {
  document.querySelectorAll('.content').forEach(c => c.classList.remove('active'));
  document.getElementById(tabId).classList.add('active');
}
</script>
<script>
document.getElementById("activity_name").addEventListener("change", function () {
    const activity = this.value;

    if (activity !== "") {
        fetch("get_activity_info.php?activity_name=" + encodeURIComponent(activity))
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    document.getElementById("objective").value = data.objective || "";
                    document.getElementById("description").value = data.description || "";
                    document.getElementById("person_involved").value = data.person_involved || "";
                    document.getElementById("budget").value = data.budgets || "";
                } else {
                    alert("Activity not found.");
                }
            })
            .catch(error => {
                console.error("Error fetching activity data:", error);
            });
    } else {
        // Clear fields if nothing selected
        document.getElementById("objective").value = "";
        document.getElementById("description").value = "";
        document.getElementById("person_involved").value = "";
        document.getElementById("budget").value = "";
    }
});


function openModal(action, proposalId) {
  const modalLabel = document.getElementById('confirmationModalLabel');
  const modalMessage = document.getElementById('modalMessage');
  const modalSubmitButton = document.getElementById('modalSubmitButton');
  const modalProposalId = document.getElementById('modalProposalId');

  // Update modal content
  if (action === 'submit') {
    modalLabel.textContent = 'Confirm Submission';
    modalMessage.textContent = 'Are you sure you want to submit this proposal?';
    modalSubmitButton.textContent = 'Submit';
    modalSubmitButton.className = 'btn btn-success';
    modalSubmitButton.name = 'submit_proposal';
  } else if (action === 'cancel') {
    modalLabel.textContent = 'Confirm Cancellation';
    modalMessage.textContent = 'Are you sure you want to cancel this proposal?';
    modalSubmitButton.textContent = 'Cancel';
    modalSubmitButton.className = 'btn btn-danger';
    modalSubmitButton.name = 'cancel_proposal';
  }

  modalProposalId.value = proposalId;

  // Show modal
  const modal = new bootstrap.Modal(document.getElementById('confirmationModal'));
  modal.show();
}


</script>

<script>
document.addEventListener('DOMContentLoaded', function() {
  // Select all file inputs
  const inputs = document.querySelectorAll('.receipt-input');

  inputs.forEach(input => {
    input.addEventListener('change', function() {
      const proposalId = this.dataset.proposalId;
      const file = this.files[0];
      if (!file) return;

      const statusDiv = document.getElementById('upload-status-' + proposalId);
      statusDiv.textContent = 'Uploading...';

      const formData = new FormData();
      formData.append('proposal_id', proposalId);
      formData.append('receipt_file', file);

      fetch('upload_receipt.php', {
        method: 'POST',
        body: formData
      })
      .then(response => response.text())
      .then(result => {
        statusDiv.textContent = 'Uploaded successfully!';
      })
      .catch(error => {
        console.error(error);
        statusDiv.textContent = 'Upload failed.';
      });
    });
  });
});
</script>

<script>
document.addEventListener("DOMContentLoaded", function () {
    const unavailable = <?php echo json_encode($unavailableDates); ?>;

    const disabledDates = new Set();
    const statusMap = {};

    unavailable.forEach(range => {
        const start = new Date(range.from);
        const end = new Date(range.to);
        const status = range.status;

        for (let d = new Date(start); d <= end; d.setDate(d.getDate() + 1)) {
            const dStr = d.toISOString().slice(0, 10);
            disabledDates.add(dStr);
            statusMap[dStr] = status.toLowerCase();
        }
    });

    function isDateBlocked(date) {
        const d = date.toISOString().slice(0, 10);
        return disabledDates.has(d);
    }

    flatpickr("#date_range", {
        mode: "range",
        dateFormat: "Y-m-d",
        disable: [isDateBlocked],
        onDayCreate: function (dObj, dStr, fp, dayElem) {
            const date = dayElem.dateObj.toISOString().slice(0, 10);

            if (statusMap[date]) {
                if (statusMap[date] === 'pending') {
                    dayElem.classList.add('pending-day');
                } else if (statusMap[date] === 'approved') {
                    dayElem.classList.add('approved-day');
                }

                // Attach click event for disabled dates
                dayElem.addEventListener('click', function (e) {
                    e.preventDefault();
                    showModal();
                });
            }
        }
    });

    // Modal functions
    window.showModal = function () {
        document.getElementById('proposalModal').style.display = 'block';
        document.getElementById('modalOverlay').style.display = 'block';
    }

    window.closeModal = function () {
        document.getElementById('proposalModal').style.display = 'none';
        document.getElementById('modalOverlay').style.display = 'none';
    }
});
</script>





<?php
require_once('fpdf/fpdf.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_budget'])) {
    $proposal_id = intval($_POST['proposal_id']);
    $event_names = $_POST['event_name'];
    $particulars = $_POST['particulars'];
    $qtys = $_POST['qty'];
    $amounts = $_POST['amount'];
    $totals = $_POST['total'];

    $grandTotal = array_sum(array_map('floatval', $totals));

    // === Generate Budget Plan PDF ===
    $pdf = new FPDF();
    $pdf->AddPage();
    $pdf->SetFont('Arial','B',14);
    $pdf->Cell(0,10,'Budget Plan',0,1,'C');
    $pdf->SetFont('Arial','B',12);
    $pdf->Cell(40,10,'Event Name',1);
    $pdf->Cell(50,10,'Particulars',1);
    $pdf->Cell(20,10,'Qty',1,0,'C');
    $pdf->Cell(30,10,'Amount',1,0,'R');
    $pdf->Cell(30,10,'Total',1,1,'R');
    $pdf->SetFont('Arial','',12);

    foreach ($event_names as $i => $event_name) {
        if (empty(trim($event_name)) && empty(trim($particulars[$i]))) continue;

        $pdf->Cell(40,10,$event_name,1);
        $pdf->Cell(50,10,$particulars[$i],1);
        $pdf->Cell(20,10,$qtys[$i],1,0,'C');
        $pdf->Cell(30,10,number_format(floatval($amounts[$i]),2),1,0,'R');
        $pdf->Cell(30,10,number_format(floatval($totals[$i]),2),1,1,'R');
    }

    $pdf->SetFont('Arial','B',12);
    $pdf->Cell(140,10,'Grand Total',1,0,'R');
    $pdf->Cell(30,10,number_format($grandTotal,2),1,1,'R');

    $uploadDir = realpath(__DIR__ . '/../proposal/uploads');
    $budgetFile = 'budget_plan_' . time() . '.pdf';
    $budgetPath = $uploadDir . '/' . $budgetFile;
    $pdf->Output('F', $budgetPath);

    // === Save Budget File & Entries ===
    if (file_exists($budgetPath)) {
        $conn->query("UPDATE sooproposal SET budget_file = '$budgetFile' WHERE id = '$proposal_id'");

        foreach ($event_names as $i => $event_name) {
            if (empty(trim($event_name)) && empty(trim($particulars[$i]))) continue;

            $event = $conn->real_escape_string($event_name);
            $particular = $conn->real_escape_string($particulars[$i]);
            $qty = intval($qtys[$i]);
            $amount = floatval($amounts[$i]);
            $total = floatval($totals[$i]);

            $conn->query("INSERT INTO budget_plans 
              (proposal_id, event_name, particulars, qty, amount, total, grand_total, attachment)
              VALUES ('$proposal_id', '$event', '$particular', '$qty', '$amount', '$total', '$grandTotal', '$budgetFile')");
        }

        echo "<p><a href='../proposal/uploads/$budgetFile' target='_blank'>View Generated Budget PDF</a></p>";
    } else {
        echo "<p style='color:red;'>Failed to generate PDF.</p>";
    }
}

?>

</body>
</html>
