<?php

session_start();
$conn = new mysqli("localhost", "root", "", "eventplanner");
$result = $conn->query("SELECT id, department, event_type, budget_approved, budget_amount FROM proposals WHERE budget_approved = 0");

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['action']) && $_POST['action'] === "disapprove") {
    $proposal_id = $_POST['proposal_id'];
    $level = $_POST['level'];
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

    $stmt = $conn->prepare("UPDATE proposals SET status = 'Disapproved', remarks = ?, level = '' WHERE id = ?");
    $stmt->bind_param("si", $final_remarks, $proposal_id);

    if ($stmt->execute()) {
        $_SESSION['success'] = "Proposal disapproved successfully.";
    } else {
        $_SESSION['error'] = "Failed to disapprove the proposal: " . $stmt->error;
    }

    header("Location: ccssbovice_dashboard.php");
    exit();
}

// Fetch proposals currently for Auditor approval (You had $current_level = 'CCS Auditor', changed it accordingly)
$current_level = 'CCS Vice';
$search_department = '%CCS%';

$sql = "SELECT * FROM proposals WHERE level = ? AND status = 'Pending' AND submit = 'submitted' AND department LIKE ?";
$stmt = $conn->prepare($sql);

if (!$stmt) {
    die("Prepare failed: " . $conn->error);
}

$stmt->bind_param("ss", $current_level, $search_department);

$stmt->execute();
$result = $stmt->get_result();


?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>CCS SBO Vice President Dashboard</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" crossorigin="anonymous">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <link rel="stylesheet" href="../style/ccssbovice.css">
  <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js" crossorigin="anonymous"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.min.js" crossorigin="anonymous"></script>

  <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate" />
</head>

<body>

<header class="topbar">
    <div class="logo"><img src="../img/lspulogo.jpg">CCS SBO VICE PRESIDENT PORTAL</div>
    <div class="hamburger" onclick="toggleMobileNav()">☰</div>
    <nav id="mainNav">
        <a href="../index.php">Home</a>
        <a href="../aboutus.php">About Us</a>
        <a href="../calendar1.php">Calendar</a>
        <div class="admin-info">
            <span>
                <?php
                if (isset($_SESSION['fullname']) && isset($_SESSION['role'])) {
                    echo htmlspecialchars($_SESSION['fullname']) . " (" . htmlspecialchars($_SESSION['role']) . ")";
                }
                ?>
            </span>
            <!-- User Dropdown -->
            <div class="user-dropdown" id="userDropdown">
                <i class="fa-solid fa-user" onclick="toggleDropdown()"></i>
                <div class="dropdown-menu" id="dropdownMenu">
                    <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'CCSSBOVice'): ?>
                        <a href="ccssbovice_dashboard.php">CCS SBO Vice Dashboard</a>
                    <?php endif; ?>
                    <a href="../account/logout.php">Logout</a>
                </div>
            </div>
        </div>
    </nav>
</header>

<aside class="sidebar">
    <ul>
        <li id="dashboardTab" class="active"><i class="fa fa-home"></i> <span class="menu-text">Dashboard</span></li>
        <li id="approvalTab"><i class="fa fa-check-circle"></i> <span class="menu-text">Approval</span></li>
        <li id="requirementTab"><i class="fa fa-building"></i> <span class="menu-text">Requirements</span></li>
        <li id="budget_planTab"><i class="fa fa-file-alt"></i> Budget Plan</li>
    </ul>
</aside>

<!-- Dashboard Content -->
<div id="dashboardContent">
<main class="content">
    <h1>CCS SBO Vice President Dashboard</h1>
    <p>Welcome back! Here's what's happening today.</p>

    <iframe id="calendarFrame" style="width:100%; height:600px; border:none;"></iframe>

</main>
</div>

<?php
$conn = new mysqli('localhost', 'root', '', 'eventplanner');

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch proposals that are pending approval (example: add WHERE clause if needed)
$sql = "SELECT id, department, event_type, budget_file FROM proposals WHERE budget_amount IS NULL AND department = 'CCS' AND status != 'Disapproved'";
$result = $conn->query($sql);

if ($result === false) {
    die("SQL Error: " . $conn->error);
}
?>
<!-- Approval Management Content -->
<div id="approvalContent" style="display:none;">
    <main class="content">
        <h1 style="margin-bottom: 0;">Request Approval</h1>
        <table class="approval-table">
            <thead>
                <tr>
                    <th>Department</th>
                    <th>Requirements</th>
                    <th>Budget File</th>
                    <th>Event Type</th>
                    <th>Budget (₱)</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($result->num_rows > 0): ?>
                    <?php while($row = $result->fetch_assoc()): ?>
                        <tr data-id="<?= $row['id'] ?>">
                            <td><?= htmlspecialchars($row['department']) ?></td>
                            <td>
                                <button onclick="showRequirementsTab()" style="background-color: #004080; color: white; padding: 5px 10px; border-radius: 5px; border: none; cursor: pointer;">
                                    View
                                </button>
                            </td>
                            <td>
                                <?php 
                                    $fileName = htmlspecialchars($row['budget_file']);
                                    if (!empty($row['budget_file'])): 
                                        echo "<a href='../proposal/uploads/$fileName' target='_blank'>$fileName</a>";
                                        $isDisabled = '';
                                    else: 
                                        echo "No file";
                                        $isDisabled = 'disabled';
                                    endif;
                                ?>
                            </td>
                            <td><?= htmlspecialchars($row['event_type']) ?></td>
                            <td>
                                <input type="number" name="budget" class="budget-input" placeholder="Enter amount">
                            </td>
                            <td>
                                <button class="approve-btn" onclick="approveBudget(<?= $row['id'] ?>, this)" <?= $isDisabled ?>>Approve</button>
                                <button class="btn btn-danger disapprove-btn" data-id="<?= $row['id'] ?>" data-bs-toggle="modal" data-bs-target="#disapproveModal" <?= $isDisabled ?>>
                                    Disapprove
                                </button>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="6" class="text-center">No proposals found for SBO Vice President.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </main>
</div>


<!-- Requirements Section -->
<div id="requirementContent" class="content" style="display:none;">
  <h1>Requirements</h1>
  <?php
 $sql = "SELECT * FROM proposals WHERE budget_amount IS NULL AND department = 'CCS' AND status != 'Disapproved'";
 $result = mysqli_query($conn, $sql);

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        echo '<div class="card p-4 mb-4 shadow-sm">';
        echo '<h3 class="mb-3">' . htmlspecialchars($row['event_type']) . '</h3>';
        echo '<p><strong>Department:</strong> ' . htmlspecialchars($row['department']) . '</p>';
        echo '<p><strong>Date:</strong> ' . date("F d, Y", strtotime($row['start_date'])) . ' - ' . date("F d, Y", strtotime($row['end_date'])) . '</p>';
        echo '<p><strong>Time:</strong> ' . htmlspecialchars($row['time']) . '</p>';
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
            "Plan of Activities" => "activity_plan",
            "Budget Plan" => "budget_file"
        ];

        $requirementDirectories = [
            "letter_attachment" => "../proposal/",
            "adviser_form" => "../proposal/",
            "constitution" => "../proposal/",
            "certification" => "../proposal/",
            "reports" => "../proposal/",
            "financial" => "../proposal/",
            "activity_plan" => "../proposal/",
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
    echo '<div class="alert alert-info text-center">No requirements found for SBO Vice President.</div>';
}
?>
</div>

<!-- Budget Plan -->
<div id="budgetForm" class="content" style="display:none;">
    <h1>Budget Plan</h1>

    <?php
    $sql = "SELECT * FROM proposals WHERE budget_amount IS NULL AND department = 'CCS' AND level = 'VP'";
    $result = mysqli_query($conn, $sql);

    if (mysqli_num_rows($result) == 0) {
        echo '<div class="alert alert-info text-center">No pending budget plan for SBO Vice President.</div>';
    } else {
        echo '<table>';
        echo '<thead><tr>
                <th>Event Type</th>
                <th>Date</th>
                <th>Time</th>
                <th>Venue</th>
                <th>Department</th>
                <th>Actions</th>
              </tr></thead><tbody>';

        while ($row = mysqli_fetch_assoc($result)) {
            echo '<tr>';
            echo '<td>' . htmlspecialchars($row['event_type']) . '</td>';
            echo '<td>' . date("M d, Y", strtotime($row['start_date'])) . ' - ' . date("M d, Y", strtotime($row['end_date'])) . '</td>';
            echo '<td>' . htmlspecialchars($row['time']) . '</td>';
            echo '<td>' . htmlspecialchars($row['venue']) . '</td>';
            echo '<td>' . htmlspecialchars($row['department']) . '</td>';
            echo '<td>';

            echo '<form method="POST" action="" style="display:inline;">';
            echo '<input type="hidden" name="proposal_id" value="' . htmlspecialchars($row['id']) . '">';
            echo '<input type="hidden" name="level" value="CCSVice">';
            echo '<button type="button" id="uploadBudgetBtn" name="action" value="approve" class="action-btn upload-btn" onclick="openBudgetPlanModal(' . $row['id'] . ')" data-proposal-id="' . $row['id'] . '">Set Budget Plan</button>';
            echo '</form>';

            echo '</td>';
            echo '</tr>';
        }

        echo '</tbody></table>';
    }
    ?>
</div>

    <!--Upload Budget-->

    <?php
// Show form if proposal_id is passed
if (isset($_GET['budget']) && $_GET['budget'] == 1 && isset($_GET['proposal_id'])) {
    $proposal_id = intval($_GET['proposal_id']);
}
    ?>
 
 <!-- Budget Plan Modal -->
<div id="budgetPlanModal" class="modal">
  <div class="modal-content1">
    <span class="close-btn" onclick="closeBudgetPlanModal()">&times;</span>
<!-- Budget Plan Form (initially hidden) -->
<div class="container mt-5 content" id="budgetPlanForm" style="display:none;">
  <h2 class="mb-4">Submit Budget Plan</h2>

  <form action="" id="myForm" method="POST">
<input type="hidden" name="proposal_id" id="budgetProposalId" value="">

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
        <tbody>
          <?php for ($i = 0; $i < 20; $i++): ?>
          <tr>
            <td><input type="text" name="event_name[]" class="form-control" /></td>
            <td><input type="text" name="particulars[]" class="form-control" /></td>
            <td><input type="number" name="qty[]" class="form-control qty-input" step="1" /></td>
            <td><input type="text" name="amount[]" class="form-control amount-input" /></td>
            <td><input type="number" name="total[]" class="form-control total-input" step="0.01" readonly /></td>
          </tr>
          <?php endfor; ?>
          <tr>
            <td colspan="4" class="text-end fw-bold">Grand Total:</td>
            <td><input type="text" id="grandTotal" class="form-control fw-bold" readonly /></td>
          </tr>
        </tbody>
      </table>
    </div>

    <div class="text-end mt-3">
      <button type="submit" class="btn btn-primary px-5" name="submit_budget" id="budgetForm">Generate PDF and Submit</button>
    </div>
  </form>
</div>
</div>
</div>
<?php
// Enable error reporting for debugging
require('fpdf/fpdf.php'); // Make sure path to fpdf.php is correct

$host = "localhost";
$user = "root";
$pass = "";
$db   = "eventplanner";

$conn = mysqli_connect($host, $user, $pass, $db);
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

// Assuming $proposal_id is passed or set here; if not, set a dummy id for testing

// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_budget'])) {

    $proposal_id = intval($_POST['proposal_id']);
    $event_names = $_POST['event_name'];
    $particulars = $_POST['particulars'];
    $qtys = $_POST['qty'];
    $amounts = $_POST['amount'];
    $totals = $_POST['total'];

    $grandTotal = 0;

    // Calculate grand total
    foreach ($totals as $t) {
        $grandTotal += floatval($t);
    }


    // Insert data into database
    foreach ($event_names as $i => $event_name) {
        if (empty(trim($event_name)) && empty(trim($particulars[$i]))) continue;

        $event = mysqli_real_escape_string($conn, $event_name);
        $particular = mysqli_real_escape_string($conn, $particulars[$i]);
        $qty = intval($qtys[$i]);
        $amount = floatval($amounts[$i]);
        $total = floatval($totals[$i]);

        $sql = "INSERT INTO budget_plans 
                (proposal_id, event_name, particulars, qty, amount, total, grand_total)
                VALUES ('$proposal_id', '$event', '$particular', '$qty', '$amount', '$total', '$grandTotal')";
        mysqli_query($conn, $sql);
    }

$folder = realpath(__DIR__ . '/../proposal/uploads');

if (!$folder) {
    die("Uploads folder NOT found at: " . __DIR__ . '/../proposals/uploads');
}

$filename = $folder . '/budget_plan_' . time() . '.pdf';

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
    for ($i = 0; $i < count($event_names); $i++) {
        if (empty(trim($event_names[$i])) && empty(trim($particulars[$i]))) continue;

        $pdf->Cell(40,10,$event_names[$i],1);
        $pdf->Cell(50,10,$particulars[$i],1);
        $pdf->Cell(20,10,$qtys[$i],1,0,'C');
        $pdf->Cell(30,10,number_format(floatval($amounts[$i]),2),1,0,'R');
        $pdf->Cell(30,10,number_format(floatval($totals[$i]),2),1,1,'R');
    }

    $pdf->SetFont('Arial','B',12);
    $pdf->Cell(140,10,'Grand Total',1,0,'R');
    $pdf->Cell(30,10,number_format($grandTotal, 2),1,1,'R');

    // Save PDF file
    $pdf->Output('F', $filename);

    if (file_exists($filename)) {
        $budgetFileName = basename($filename);

        // Update the proposals table with the PDF filename
        $updateSql = "UPDATE proposals SET budget_file = '$budgetFileName' WHERE id = '$proposal_id'";
        if (mysqli_query($conn, $updateSql)) {
            echo "
                  <a href='../proposal/uploads/$budgetFileName' target='_blank'>View PDF</a></p>";
        } else {
            echo "<p style='color:red;'>PDF created, but failed to update database: " . mysqli_error($conn) . "</p>";
        }
    } else {
        echo "<p style='color:red;'>Failed to create PDF file.</p>";
    }
}

?>

<!-- Disapprove Remarks Modal -->
<div class="modal fade" id="disapproveModal" tabindex="-1" aria-labelledby="disapproveModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-lg">
    <form method="POST" action="ccssbovice_dashboard.php">
      <div class="modal-content">
        <!-- Header -->
        <div class="modal-header bg-danger text-white">
          <h5 class="modal-title" id="disapproveModalLabel">Disapproved</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>

        <!-- Body -->
        <div class="modal-body">
          <input type="hidden" name="proposal_id" id="modal_proposal_id">
          <input type="hidden" name="level" value="CCSSBOVice">
          <input type="hidden" name="action" value="disapprove">

          <p><strong>📝 Remarks / Comments:</strong></p>
          <p>
            Dear ,<br>
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

<!-- Tab Switching & User Fetching Script -->
<script>
document.addEventListener("DOMContentLoaded", function () {
  // Auto-compute totals
  const qtyInputs = document.querySelectorAll('.qty-input');
  const amountInputs = document.querySelectorAll('.amount-input');
  const totalInputs = document.querySelectorAll('.total-input');
  const grandTotalInput = document.getElementById('grandTotal');

  function sanitize(input) {
    input.value = input.value.replace(/[^0-9.]/g, '');
  }

  function computeRowTotal(index) {
    const qty = parseFloat(qtyInputs[index].value) || 0;
    const amount = parseFloat(amountInputs[index].value) || 0;
    totalInputs[index].value = (qty * amount).toFixed(2);
  }

  function computeGrandTotal() {
    let total = 0;
    totalInputs.forEach(input => {
      total += parseFloat(input.value) || 0;
    });
    grandTotalInput.value = total.toFixed(2);
  }

  qtyInputs.forEach((input, i) => {
    input.addEventListener('input', () => {
      sanitize(input);
      computeRowTotal(i);
      computeGrandTotal();
    });
  });

  amountInputs.forEach((input, i) => {
    input.addEventListener('input', () => {
      sanitize(input);
      computeRowTotal(i);
      computeGrandTotal();
    });
  });

  // On load, compute totals if prefilled
  qtyInputs.forEach((_, i) => computeRowTotal(i));
  computeGrandTotal();

  // Inject calendar iframe
  const calendarFrame = document.getElementById("calendarFrame");
  if (calendarFrame) {
    calendarFrame.src = "../proposal/calendar.php";
  }

  // Sidebar toggle
  const toggleBtn = document.querySelector(".toggle-btn");
  if (toggleBtn) {
    toggleBtn.addEventListener("click", () => {
      document.querySelector(".sidebar").classList.toggle("collapsed");
    });
  }

  // Disapprove modal - set proposal_id
  document.querySelectorAll(".disapprove-btn").forEach(btn => {
    btn.addEventListener("click", function () {
      const id = this.getAttribute("data-id");
      const modalInput = document.getElementById("modal_proposal_id");
      if (modalInput) modalInput.value = id;
    });
  });

  // Close modal on click outside
  const modal = document.getElementById('budgetPlanModal');
  const closeBtn = document.querySelector('.btn-close');
  if (closeBtn) {
    closeBtn.addEventListener("click", () => {
      if (modal) modal.classList.remove("active");
    });
  }

  window.addEventListener("click", function (e) {
    if (e.target === modal) {
      modal.classList.remove("active");
    }
  });
});
</script>

<script>
function showRequirementsTab() {
    document.getElementById("dashboardContent").style.display = "none";
    document.getElementById("approvalContent").style.display = "none";
    document.getElementById("requirementContent").style.display = "block";

    document.getElementById("dashboardTab").classList.remove("active");
    document.getElementById("approvalTab").classList.remove("active");
    document.getElementById("requirementTab").classList.add("active");
}
// Tab switching
function hideAllSections() {
  document.getElementById("dashboardContent").style.display = "none";
  document.getElementById("approvalContent").style.display = "none";
  document.getElementById("requirementContent").style.display = "none";
  document.getElementById("budgetForm").style.display = "none";
  document.getElementById("budgetPlanForm").style.display = "none";

  document.querySelectorAll(".sidebar ul li").forEach(li => {
    li.classList.remove("active");
  });
}

document.getElementById("dashboardTab").addEventListener("click", function () {
  hideAllSections();
  document.getElementById("dashboardContent").style.display = "block";
  this.classList.add("active");
});

document.getElementById("approvalTab").addEventListener("click", function () {
  hideAllSections();
  document.getElementById("approvalContent").style.display = "block";
  this.classList.add("active");
});

document.getElementById("requirementTab").addEventListener("click", function () {
  hideAllSections();
  document.getElementById("requirementContent").style.display = "block";
  this.classList.add("active");
});

document.getElementById("budget_planTab").addEventListener("click", function () {
  hideAllSections();
  document.getElementById("budgetForm").style.display = "block";
  this.classList.add("active");
});
</script>

<script>
//==============================Upload budget js================================================
 document.querySelectorAll('.upload-btn').forEach(btn => {
  btn.addEventListener('click', function () {
    // Kunin yung proposal ID mula sa button na kinlick
    const proposalId = this.getAttribute('data-proposal-id');

    // I-set sa hidden field
    document.getElementById('budgetProposalId').value = proposalId;

    // Ipakita ang budget form
    hideAllSections();
    document.getElementById('budgetPlanForm').style.display = 'block';

    // Activate tab kung meron
    document.getElementById("budget_planTab").classList.add("active");
  });
});

//====================================submit budget js=================================================
document.getElementById("submitBudgetBtn").addEventListener("click", function (e) {
    e.preventDefault(); // stop default form submit

    const formElement = document.getElementById("myForm");
    const formData = new FormData(formElement);

    fetch("budget_plan.php", {
        method: "POST",
        body: formData
    })
    .then(response => response.text())
    .then(result => {
        alert("✅ Success! " + result);
    })
    .catch(error => {
        console.error("❌ Error:", error);
    });
});

</script>

<script>
// Approve budget with validation
function approveBudget(proposalId, button) {
  const row = button.closest("tr");
  const budget = row.querySelector("input[name='budget']").value;

  if (!budget || isNaN(budget) || budget <= 0) {
    alert("Please enter a valid budget.");
    return;
  }

  const formData = new FormData();
  formData.append("proposal_id", proposalId);
  formData.append("budget", budget);

  fetch("../request/update_budget.php", {
    method: "POST",
    body: formData
  })
  .then(res => res.text())
  .then(data => {
    alert("✅ " + data);
    row.remove();
  })
  .catch(err => {
    alert("❌ Error updating budget.");
    console.error(err);
  });
}
</script>

<script>
// Dropdown toggle for user menu
function toggleDropdown() {
  const menu = document.getElementById("dropdownMenu");
  menu.style.display = (menu.style.display === "block") ? "none" : "block";
}

// Open modal and show form, set proposal_id value
function openBudgetPlanModal(proposalId) {
  const modal = document.getElementById('budgetPlanModal');
  const form = document.getElementById('budgetPlanForm');
  const inputProposalId = document.getElementById('budgetProposalId');

  inputProposalId.value = proposalId; // set hidden input proposal_id
  modal.style.display = 'block';
  form.style.display = 'block'; // show form inside modal
}

// Close modal and hide form
function closeBudgetPlanModal() {
  const modal = document.getElementById('budgetPlanModal');
  const form = document.getElementById('budgetPlanForm');

  modal.style.display = 'none';
  form.style.display = 'none';
}

// Optional: close modal if user clicks outside modal-content
window.onclick = function(event) {
  const modal = document.getElementById('budgetPlanModal');
  if (event.target == modal) {
    closeBudgetPlanModal();
  }
}

document.addEventListener("click", function (event) {
  const dropdown = document.getElementById("userDropdown");
  const menu = document.getElementById("dropdownMenu");
  if (!dropdown.contains(event.target)) {
    menu.style.display = "none";
  }
});

function toggleMobileNav() {
  const nav = document.getElementById("mainNav");
  nav.classList.toggle("show");
}

function switchTab(tab) {
  const sections = {
    dashboard: "dashboardContent",
    proposal: "approvalContent",
    requirement: "requirementContent",
    budget: "budgetForm"
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

  if (tab && ['dashboard', 'proposal', 'requirement', 'budget'].includes(tab)) {
    switchTab(tab);

    // Optional: Remove the query string from the URL
    window.history.replaceState({}, document.title, window.location.pathname);
  }

  if (urlParams.get('approved') === '1') {
    alert("✅ Proposal approved successfully!");
  }
});
</script>

</body>
</html>