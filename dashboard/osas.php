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

// Approval & Disapproval Logic
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['proposal_id'], $_POST['action'])) {
    $id = (int)$_POST['proposal_id'];
    $action = $_POST['action'];

    if ($action === 'approve') {
        $status = 'Approved';
        $new_level = 'Completed';
        $viewed = 0;

        $stmt = $conn->prepare("UPDATE sooproposal SET status=?, level=?, viewed=? WHERE id=?");
        if (!$stmt) die("Prepare failed: " . $conn->error);
        
        $stmt->bind_param("ssii", $status, $new_level, $viewed, $id);
        if (!$stmt->execute()) die("Execute failed: " . $stmt->error);

        // CHECK if this is financial approval based on extra POST variable or context
        if (isset($_POST['financial_approval']) && $_POST['financial_approval'] == '1') {
            $_SESSION['financial_done'] = true;
            header("Location: osas.php?tab=financial");
        } else {
            // Normal proposal approval
            header("Location: osas.php?approved=1");
        }
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
        if (empty($disapproved_by) || $disapproved_by === 'Unknown') {
            die("Error: Disapproved by user is not set in session.");
        }

        $stmt = $conn->prepare("UPDATE sooproposal SET status='Disapproved', remarks=?, disapproved_by=?, level='' WHERE id=?");
        if (!$stmt) die("Prepare failed: " . $conn->error);
        $stmt->bind_param("ssi", $final_remarks, $disapproved_by, $id);
        if (!$stmt->execute()) die("Execute failed: " . $stmt->error);

        header("Location: ccsfaculty_dashboard.php");
        exit;
    }
}
// === Handle Financial Approval (Dean) ===
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['proposal_id'], $_POST['action']) && 
    in_array($_POST['action'], ['approve_financial', 'disapprove_financial'])) {

    $id = (int)$_POST['proposal_id'];
    $action = $_POST['action'];

    if ($action === 'approve_financial') {
        $financialstatus = 'Completed';
        $new_level = 'Financial Completed';

        $stmt = $conn->prepare("UPDATE sooproposal SET financialstatus = ?, level = ? WHERE id = ?");
        $stmt->bind_param("ssi", $financialstatus, $new_level, $id);
        $stmt->execute();

        header("Location: osas.php?financial_approved=1&tab=financial");
        exit;

    } elseif ($action === 'disapprove_financial') {
        $financialstatus = 'Disapproved by Dean';
        $stmt = $conn->prepare("UPDATE sooproposal SET financialstatus = ?, submit = NULL WHERE id = ?");
        $stmt->bind_param("si", $financialstatus, $id);
        $stmt->execute();

        header("Location: osas.php?financial_disapproved=1&tab=financial");
        exit;
    }
}
// Fetch proposals
$current_level = 'OSAS';
$search_department = '%CCS%';

$stmt = $conn->prepare("SELECT * FROM sooproposal WHERE level=? AND status='Pending' AND submit='submitted' AND department LIKE ?");
$stmt->bind_param("ss", $current_level, $search_department);
$stmt->execute();
$result = $stmt->get_result();
?>

<!-- Flash Messages -->
<?php if (isset($_SESSION['success'])): ?>
    <div class="alert alert-success"><?= $_SESSION['success']; unset($_SESSION['success']); ?></div>
<?php endif; ?>

<?php if (isset($_SESSION['error'])): ?>
    <div class="alert alert-danger"><?= $_SESSION['error']; unset($_SESSION['error']); ?></div>
<?php endif; ?>

<!-- Modal Trigger Script for Financial -->
<?php if (isset($_SESSION['financial_done'])): ?>
<script>
    window.addEventListener('DOMContentLoaded', () => {
        const financialModal = new bootstrap.Modal(document.getElementById('financialModal'));
        financialModal.show();
    });
</script>
<?php unset($_SESSION['financial_done']); ?>
<?php endif; ?>


<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>OSAS Dashboard</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" crossorigin="anonymous">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <link rel="stylesheet" href="../style/sbotreasure1.css">
  <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js" crossorigin="anonymous"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.min.js" crossorigin="anonymous"></script>
  <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate" />
</head>

<body>
<header class="topbar">
  <div class="logo"><img src="../img/lspulogo.jpg" alt="Logo">OSAS PORTAL</div>
  <div class="hamburger" onclick="toggleMobileNav()">☰</div>
  <nav id="mainNav">
    <a href="../index.php">Home</a>
    <a href="../aboutus.php">About Us</a>
    <a href="../calendar1.php">Calendar</a>
    <div class="admin-info">
        <span><?php echo htmlspecialchars($_SESSION['role']); ?></span>
      <div class="user-dropdown" id="userDropdown">
        <i class="fa-solid fa-user dropdown-toggle" onclick="toggleDropdown()"></i>
        <div class="dropdown-menu" id="dropdownMenu" style="display:none;">
          <?php if ($_SESSION['role'] === 'Osas'): ?>
            <a href="osas.php">OSAS Dashboard</a>
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
  <h1>Welcome to the OSAS Dashboard</h1>
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
      <tr><td colspan="8" class="text-center">No proposals found for OSAS.</td></tr>
    <?php endif; ?>
    </tbody>
</table>

</div>

<!-- Requirements Section -->
<div id="requirementContent" class="content" style="display:none;">
  <h1>Requirements</h1>
  <?php
  $stmt = $conn->prepare("SELECT * FROM sooproposal WHERE level=? AND status='Pending' AND submit='submitted'");
  $stmt->bind_param("s", $current_level);
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
    echo '<div class="alert alert-info text-center">No requirements found for OSAS.</div>';
}
?>
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
      $query = "SELECT * FROM sooproposal WHERE submit = 'Submitted' AND level = 'Financial OSAS'";
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
<form method="POST" action="osas.php">
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

<!-- Approve Confirmation Modal -->
<div class="modal fade" id="approveModal" tabindex="-1" aria-labelledby="approveModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <form method="POST" action="osas.php">
      <div class="modal-content">
        <div class="modal-header bg-success text-white">
          <h5 class="modal-title" id="approveModalLabel">Confirm Approval</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>

        <div class="modal-body">
          <input type="hidden" name="proposal_id" id="approve_proposal_id">
          <input type="hidden" name="action" value="approve">
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
   <form method="POST" action="osas.php">

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




        <!-- Body -->
        <div class="modal-body">
          <input type="hidden" name="proposal_id" id="modal_proposal_id">
          <input type="hidden" name="level" value="OSAS">
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

<!-- Financial Done Modal -->
<div class="modal fade" id="financialDoneModal" tabindex="-1" aria-labelledby="financialDoneModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content shadow">
      <div class="modal-header bg-success text-white">
        <h5 class="modal-title" id="financialDoneModalLabel">Financial Report Approved</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body text-center">
        <p class="fs-5">The financial report has been successfully approved and marked as <strong>Completed</strong>.</p>
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
 const urlParams = new URLSearchParams(window.location.search);
  const showModal = urlParams.get('modal') === 'financial_done';

  if (showModal) {
    const modal = new bootstrap.Modal(document.getElementById('financialDoneModal'));
    modal.show();

    // Remove the ?modal=financial_done from the URL after showing
    window.history.replaceState({}, document.title, window.location.pathname + window.location.search.replace(/(&|\?)modal=financial_done/, ''));
  }
 
</script>
</body>
</html>
