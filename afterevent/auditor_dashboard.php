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

// Fetch proposals
$current_level = 'CCS Auditor';
$search_department = '%CCS%';

// Get current date for overdue check
$current_date = date('Y-m-d');

$stmt = $conn->prepare("SELECT * FROM sooproposal WHERE level=? AND status='Pending' AND submit='submitted' AND department LIKE ?");
$stmt->bind_param("ss", $current_level, $search_department);
$stmt->execute();
$result = $stmt->get_result();

// Handle approval/rejection
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action'])) {
    $proposal_id = $_POST['proposal_id'];
    $action = $_POST['action'];
    $remarks = $_POST['remarks'] ?? '';
    
    if ($action == 'approve') {
        $status = 'Approved by Auditor';
        $next_step = 'SBOReview';
    } else {
        $status = 'Rejected by Auditor';
        $next_step = 'Auditor';
    }
    
    $update_stmt = $conn->prepare("UPDATE sooproposal SET status=?, current_step=?, auditor_remarks=? WHERE id=?");
    $update_stmt->bind_param("sssi", $status, $next_step, $remarks, $proposal_id);
    
    if ($update_stmt->execute()) {
        $_SESSION['success'] = "Proposal has been " . ($action == 'approve' ? 'approved' : 'rejected');
    } else {
        $_SESSION['error'] = "Error updating proposal: " . $conn->error;
    }
    
    header("Location: ccssboauditor_dashboard.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>CCS SBO Auditor Dashboard</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" crossorigin="anonymous">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <link rel="stylesheet" href="../style/css_all.css">
  <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js" crossorigin="anonymous"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.min.js" crossorigin="anonymous"></script>
  <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate" />
</head>

<body>
<header class="topbar">
  <div class="logo"><img src="../img/lspulogo.jpg" alt="Logo">CCS SBO AUDITOR PORTAL</div>
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
  </ul>
</aside>

<!-- Dashboard Section -->
<div id="dashboardContent" class="content">
  <h1>Welcome to the CCS Auditor Dashboard</h1>
  <p>This is your overview page.</p>
  <iframe id="calendarFrame" style="width:100%; height:600px; border:none;"></iframe>
</div>

<!-- Proposals Section -->
<div id="proposalContent" class="content" style="display:none;">
  <h2>Pending Proposals</h2>
  <p>These proposals need your review and approval.</p>
  
  <div class="table-responsive mt-3">
    <table class="table table-bordered table-striped align-middle text-center">
      <thead class="table-dark">
        <tr>
          <th>Event Name</th>
          <th>Particulars</th>
          <th>Quantity</th>
          <th>Amount</th>
          <th>Total</th>
          <th>Status</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php if ($result->num_rows > 0): ?>
          <?php while ($row = $result->fetch_assoc()): 
            $end_date = new DateTime($row['event_ended']);
            $today = new DateTime();
            $is_overdue = $end_date < $today;
          ?>
            <tr class="<?= $is_overdue ? 'table-warning' : '' ?>">
              <td><?= htmlspecialchars($row['event_name']) ?></td>
              <td><?= htmlspecialchars($row['particulars']) ?></td>
              <td><?= htmlspecialchars($row['quantity']) ?></td>
              <td><?= htmlspecialchars($row['amount']) ?></td>
              <td><?= htmlspecialchars($row['total']) ?></td>
              <td>
                <?= htmlspecialchars($row['status']) ?>
                <?php if ($is_overdue): ?>
                  <span class="badge bg-danger">Overdue</span>
                <?php endif; ?>
              </td>
              <td>
                <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#reviewModal<?= $row['id'] ?>">
                  <i class="fas fa-eye"></i> Review
                </button>
              </td>
            </tr>
            
            <!-- Review Modal -->
            <div class="modal fade" id="reviewModal<?= $row['id'] ?>" tabindex="-1" aria-labelledby="reviewModalLabel" aria-hidden="true">
              <div class="modal-dialog">
                <div class="modal-content">
                  <div class="modal-header">
                    <h5 class="modal-title" id="reviewModalLabel">Review: <?= htmlspecialchars($row['event_name']) ?></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                  </div>
                  <div class="modal-body">
                    <table class="table table-bordered">
                      <tr>
                        <th>Particulars</th>
                        <td><?= htmlspecialchars($row['particulars']) ?></td>
                      </tr>
                      <tr>
                        <th>Quantity</th>
                        <td><?= htmlspecialchars($row['quantity']) ?></td>
                      </tr>
                      <tr>
                        <th>Amount</th>
                        <td><?= htmlspecialchars($row['amount']) ?></td>
                      </tr>
                      <tr>
                        <th>Total</th>
                        <td><?= htmlspecialchars($row['total']) ?></td>
                      </tr>
                      <tr>
                        <th>End Date</th>
                        <td><?= date('M d, Y', strtotime($row['event_ended'])) ?></td>
                      </tr>
                    </table>
                    
                    <?php if ($is_overdue): ?>
                      <div class="alert alert-warning">
                        This event is overdue by <?= $today->diff($end_date)->days ?> days
                      </div>
                    <?php endif; ?>
                    
                    <form method="POST">
                      <input type="hidden" name="proposal_id" value="<?= $row['id'] ?>">
                      <div class="mb-3">
                        <label for="remarks<?= $row['id'] ?>" class="form-label">Remarks</label>
                        <textarea class="form-control" id="remarks<?= $row['id'] ?>" name="remarks" rows="3"></textarea>
                      </div>
                      <div class="d-flex justify-content-between">
                        <button type="submit" name="action" value="reject" class="btn btn-danger">Reject</button>
                        <button type="submit" name="action" value="approve" class="btn btn-success">Approve</button>
                      </div>
                    </form>
                  </div>
                </div>
              </div>
            </div>
          <?php endwhile; ?>
        <?php else: ?>
          <tr>
            <td colspan="7" class="text-center">No pending proposals found</td>
          </tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

<!-- Budget Section (your original code exactly as is) -->
<div id="eventBudgetContent" class="content" style="display:none;">
    <h2 class="mb-4">Submit Budget Plan</h2>
    <form action="" id="myForm" method="POST" onsubmit="return confirmSubmit();">
      <input type="hidden" name="proposal_id" id="budgetProposalId" value="1">

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

<script>
// Tab navigation
function showTab(tabId) {
  document.querySelectorAll('.content').forEach(content => {
    content.style.display = 'none';
  });
  
  document.querySelectorAll('.sidebar li').forEach(tab => {
    tab.classList.remove('active');
  });
  
  document.getElementById(tabId).classList.add('active');
  
  if (tabId === 'dashboardTab') {
    document.getElementById('dashboardContent').style.display = 'block';
  } else if (tabId === 'proposalTab') {
    document.getElementById('proposalContent').style.display = 'block';
  } else if (tabId === 'requirementTab') {
    document.getElementById('eventBudgetContent').style.display = 'block';
  }
}

// Initialize dashboard as default
document.addEventListener('DOMContentLoaded', function() {
  document.getElementById('dashboardContent').style.display = 'block';
  
  // Add click event listeners to sidebar tabs
  document.querySelectorAll('.sidebar li').forEach(tab => {
    tab.addEventListener('click', function() {
      showTab(this.id);
    });
  });
});

// Toggle dropdown menu
function toggleDropdown() {
  const dropdown = document.getElementById('dropdownMenu');
  dropdown.style.display = dropdown.style.display === 'none' ? 'block' : 'none';
}

// Mobile navigation toggle
function toggleMobileNav() {
  const nav = document.getElementById('mainNav');
  nav.style.display = nav.style.display === 'none' ? 'flex' : 'none';
}

// Your existing budget form functions would go here
</script>
</body>
</html>