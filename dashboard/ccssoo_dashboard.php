<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

$conn = new mysqli("localhost", "root", "", "eventplanner");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// SAVE PLAN OF ACTIVITIES
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['activity_name'])) {
    $activity_name = $_POST['activity_name'];
    $date_range = $_POST['date_range']; // e.g., "06/21/2025 to 06/23/2025"
    $dates = explode(" to ", $date_range);
    $start_date = date('Y-m-d', strtotime($dates[0]));
    $end_date = isset($dates[1]) ? date('Y-m-d', strtotime($dates[1])) : $start_date;
    $objective = $_POST['objective'];
    $budget = $_POST['budget'];
    $description = $_POST['description'];
    $venue = $_POST['venue'];
    $person_involved = $_POST['person_involved'];
    $department = $_SESSION['department'] ?? 'CCS'; // fallback
    $username = $_SESSION['username'] ?? 'unknown';

    $stmt = $conn->prepare("INSERT INTO sooproposal (department, activity_name, start_date, end_date, objective, budget, description, venue, person_involved, username) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("sssssdssss", $department, $activity_name, $start_date, $end_date, $objective, $budget, $description, $venue, $person_involved, $username);

    if ($stmt->execute()) {
        $last_id = $stmt->insert_id;
        echo "<script>
            alert('Activity saved! Proceeding to budget form...');
            document.addEventListener('DOMContentLoaded', function() {
                switchTab('eventBudgetContent');
                document.getElementById('budgetProposalId').value = '$last_id';
            });
        </script>";
    } else {
        echo "<script>alert('Error saving activity.');</script>";
    }
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
  <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
  <link rel="stylesheet" href="../style/sbotreasure.css">
  <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate" />
  <style>
    body { margin: 0; font-family: Arial, sans-serif; }
    .content { display: none; padding: 20px; }
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
  </style>
</head>
<body>

<header class="topbar">
  <div class="logo"><img src="../img/lspulogo.jpg" alt="Logo">CCS SBO SOO PORTAL</div>
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
    <li onclick="toggleSubMenu('createEventSubMenu')"><i class="fa fa-folder-plus"></i> Create Event <i class="fa fa-caret-down"></i></li>
    <ul id="createEventSubMenu" class="submenu">
      <li onclick="switchTab('createEventContent')">Create Event Form</li>
      <li onclick="switchTab('financialReportContent')">Financial Report</li>
    </ul>
    
  </ul>
</aside>

<main>
  <div id="dashboardContent" class="content active">
    <h1>Welcome to the CCS SBO SOO Dashboard</h1>
    <iframe src="../proposal/calendar.php" style="width:100%; height:600px; border:none;"></iframe>
  </div>

  <div id="createEventContent" class="content">
    <h2 class="text-center fw-bold mb-4">PLAN OF ACTIVITIES</h2>
        <form class="container" style="max-width: 900px;" method="POST" action="">
        <div class="row mb-3">
            <div class="col-md-6">
            <label class="form-label">Name of Activities:</label>
            <input type="text" class="form-control" name="activity_name" required>
            </div>
            <div class="col-md-6">
            <label class="form-label">Target Date Range:</label>
            <input type="text" class="form-control" name="date_range" id="date_range" required>
            </div>
        </div>

        <div class="row mb-3">
            <div class="col-md-6">
            <label class="form-label">Objective:</label>
            <input type="text" class="form-control" name="objective" required>
            </div>
            <div class="col-md-6">
            <label class="form-label">Budget:</label>
            <input type="number" class="form-control" name="budget" required>
            </div>
        </div>

        <div class="row mb-3">
            <div class="col-md-6">
            <label class="form-label">Brief Description:</label>
            <input type="text" class="form-control" name="description">
            </div>
            <div class="col-md-6">
            <label class="form-label">Venue:</label>
            <input type="text" class="form-control" name="venue">
            </div>
        </div>

        <div class="row mb-4">
            <div class="col-md-12">
            <label class="form-label">Person Involved:</label>
            <input type="text" class="form-control" name="person_involved" required>
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

  <div id="financialReportContent" class="content">
    <h2>Financial Report</h2>
    <p>Financial report functionality here...</p>
  </div>
</main>
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
</script>

<?php
require('fpdf/fpdf.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_budget'])) {
    $proposal_id = intval($_POST['proposal_id']);
    $event_names = $_POST['event_name'];
    $particulars = $_POST['particulars'];
    $qtys = $_POST['qty'];
    $amounts = $_POST['amount'];
    $totals = $_POST['total'];

    $grandTotal = array_sum(array_map('floatval', $totals));

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
    $pdfFile = 'budget_plan_' . time() . '.pdf';
    $pdfPath = $uploadDir . '/' . $pdfFile;
    $pdf->Output('F', $pdfPath);

    if (file_exists($pdfPath)) {
        $conn->query("UPDATE sooproposal  SET budget_file = '$pdfFile' WHERE id = '$proposal_id'");

        foreach ($event_names as $i => $event_name) {
            if (empty(trim($event_name)) && empty(trim($particulars[$i]))) continue;

            $event = $conn->real_escape_string($event_name);
            $particular = $conn->real_escape_string($particulars[$i]);
            $qty = intval($qtys[$i]);
            $amount = floatval($amounts[$i]);
            $total = floatval($totals[$i]);

            $conn->query("INSERT INTO budget_plans 
              (proposal_id, event_name, particulars, qty, amount, total, grand_total, attachment)
              VALUES ('$proposal_id', '$event', '$particular', '$qty', '$amount', '$total', '$grandTotal', '$pdfFile')");
        }

        echo "<p><a href='../proposal/uploads/$pdfFile' target='_blank'>View Generated Budget PDF</a></p>";
    } else {
        echo "<p style='color:red;'>Failed to generate PDF.</p>";
    }
}
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['activity_name'])) {
    require_once('fpdf/fpdf.php');

    // === SAFE INPUT HANDLING ===
    $objective = $_POST['objective'] ?? '';
    $activity_name = $_POST['activity_name'] ?? '';
    $description = $_POST['description'] ?? '';
    $person_involved = $_POST['person_involved'] ?? '';
    $budget = $_POST['budget'] ?? 0;
    $last_id = $_POST['last_id'] ?? null;

    // === HANDLE DATE RANGE ===
    $date_range = $_POST['date_range'] ?? '';
    if (!empty($date_range) && strpos($date_range, 'to') !== false) {
        [$start_date, $end_date] = array_map('trim', explode('to', $date_range));
    } else {
        $start_date = '';
        $end_date = '';
    }

    // === SAFE DATE FORMATTING ===
    if (!empty($start_date) && strtotime($start_date) !== false &&
        !empty($end_date) && strtotime($end_date) !== false) {
        $target_date = date("F j, Y", strtotime($start_date)) . " to " . date("F j, Y", strtotime($end_date));
    } else {
        $target_date = "N/A";
    }

    // === FORMAT BUDGET ===
    $budget_display = 'Php ' . number_format(floatval($budget), 2);

    // === SETUP PDF ===
    $poa_pdf = new FPDF();
    $poa_pdf->AddPage();
    $poa_pdf->SetFont('Arial', 'B', 14);
    $poa_pdf->Cell(0, 10, 'Plan of Activities', 0, 1, 'C');
    $poa_pdf->Ln(5);

    // === TABLE HEADER ===
    $poa_pdf->SetFont('Arial', 'B', 10);
    $poa_pdf->SetFillColor(220, 220, 220);
    $headers = ['OBJECTIVE', 'ACTIVITIES', 'BRIEF DESCRIPTION', 'PERSONS INVOLVED', 'TARGET DATE', 'BUDGET'];
    $widths = [30, 30, 40, 40, 30, 20]; // Must total ≤ 190mm

    foreach ($headers as $i => $header) {
        $poa_pdf->Cell($widths[$i], 10, $header, 1, 0, 'C', true);
    }
    $poa_pdf->Ln();

    // === TABLE ROW DATA ===
    $poa_pdf->SetFont('Arial', '', 9);
    $rowData = [$objective, $activity_name, $description, $person_involved, $target_date, $budget_display];

    // === DYNAMIC ROW HEIGHT BASED ON WRAPPING ===
    $lineHeight = 5;
    $cellLines = [];
    $maxLines = 1;

    foreach ($rowData as $i => $text) {
        // Estimate number of lines
        $textWidth = $widths[$i] - 2; // Adjust for padding
        $words = explode(' ', $text);
        $line = '';
        $lines = 1;
        foreach ($words as $word) {
            if ($poa_pdf->GetStringWidth($line . ' ' . $word) < $textWidth) {
                $line .= ' ' . $word;
            } else {
                $lines++;
                $line = $word;
            }
        }
        $cellLines[$i] = $lines;
        if ($lines > $maxLines) $maxLines = $lines;
    }

    $rowHeight = $lineHeight * $maxLines;

    // === DRAW CELLS EVENLY ALIGNED ===
    $x = $poa_pdf->GetX();
    $y = $poa_pdf->GetY();

    for ($i = 0; $i < count($rowData); $i++) {
        $poa_pdf->SetXY($x, $y);
        $currentX = $x;
        $currentY = $y;

        // Draw rectangle
        $poa_pdf->Rect($currentX, $currentY, $widths[$i], $rowHeight);

        // Write text inside
        $poa_pdf->MultiCell($widths[$i], $lineHeight, $rowData[$i], 0);

        // Move X cursor to the next cell
        $x += $widths[$i];
    }

    // Move Y cursor to the next row
    $poa_pdf->SetY($y + $rowHeight);

    // === SAVE PDF FILE ===
    $uploadDir = realpath(__DIR__ . '/../proposal/uploads');
    $poaFile = 'plan_of_activities_' . time() . '.pdf';
    $poaPath = $uploadDir . '/' . $poaFile;
    $poa_pdf->Output('F', $poaPath);

    // === STORE PDF PATH IN DATABASE ===
    if ($last_id) {
        $conn->query("UPDATE sooproposal SET POA_file = '$poaFile' WHERE id = '$last_id'");
    }

    // === OUTPUT DOWNLOAD LINK ===
    echo "<p><a href='../proposal/uploads/$poaFile' target='_blank'>View Plan of Activities PDF</a></p>";
}

?>

</body>
</html>
