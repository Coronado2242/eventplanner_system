<?php

session_start();
$conn = new mysqli("localhost", "root", "", "eventplanner");
$result = $conn->query("SELECT id, department, event_type, budget_approved, budget_amount FROM proposals WHERE budget_approved = 0");


?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>CCS SBO Vice President Dashboard</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
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

</style>
<body>

<header class="topbar">
    <div class="logo"><img src="../img/lspulogo.jpg">CCS SBO VICE PRESIDENT PORTAL</div>
    <div class="hamburger" onclick="toggleMobileNav()">☰</div>
    <nav id="mainNav">
        <a href="../index.php">Home</a>
        <a href="../aboutus.php">About Us</a>
        <a href="../calendar1.php">Calendar</a>
        <div class="admin-info">
            <i class="icon-calendar"></i>
            <i class="icon-bell"></i>
            <span>
                <?php
                if (isset($_SESSION['fullname']) && isset($_SESSION['role'])) {
                    echo htmlspecialchars($_SESSION['fullname']) . " (" . htmlspecialchars($_SESSION['role']) . ")";
                }
                ?>
            </span>
            <!-- User Dropdown -->
            <div class="user-dropdown" id="userDropdown">
                <i class="fa-solid fa-user dropdown-toggle" onclick="toggleDropdown()"></i>
                <div class="dropdown-menu" id="dropdownMenu">
                    <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'CCSVice'): ?>
                        <a href="ccsvice_dashboard.php">CCS SBO Vice Dashboard</a>
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
$sql = "SELECT id, department, event_type, budget_file FROM proposals WHERE budget_amount IS NULL AND department = 'CCS'";
$result = $conn->query($sql);

if ($result === false) {
    die("SQL Error: " . $conn->error);
}
?>
<!-- Approval Management Content -->
<div id="approvalContent" style="display:none;">
    <main class="content" >
        <h1 style="margin-bottom: 0;">Request Approval</h1>
<table class="approval-table">
    <thead>
        <tr>
            <th>ID</th>
            <th>Department</th>
            <th>Requirements</th>
             <th>Budget File</th>
            <th>Event Type</th>
            <th>Budget (₱)</th>
            <th>Action</th>
        </tr>
    </thead>
    <tbody>
        <?php while($row = $result->fetch_assoc()): ?>
            <tr data-id="<?= $row['id'] ?>">
                <td><?= $row['id'] ?></td>
                <td><?= htmlspecialchars($row['department']) ?></td>
                <td><button onclick="showRequirementsTab()" style="background-color: #004080; color: white; padding: 5px 10px; border-radius: 5px; border: none; cursor: pointer;">View</button></td>

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
                    </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>

                    </main>
                </div>

<!-- Requirements Content -->
<div id="requirementContent" style="display:none;">
    <main class="content">
        <h1 style="margin-bottom: 0;">Requirements</h1>

        <?php
        $host = "localhost";
        $user = "root";
        $pass = "";
        $db   = "eventplanner";

        $conn = mysqli_connect($host, $user, $pass, $db);

        if (!$conn) {
            die("Connection failed: " . mysqli_connect_error());
        }

        $sql = "SELECT * FROM proposals WHERE budget_amount IS NULL AND department = 'CCS'";
        $result = mysqli_query($conn, $sql);

        while ($row = mysqli_fetch_assoc($result)) {
            echo '<div style="border: 1px solid #ccc; border-radius: 10px; padding: 20px; max-width: 900px; position: relative; margin-bottom: 20px;">';
            echo '<h2 style="margin-top: 0;">' . htmlspecialchars($row['event_type']) . '</h2>';

            echo '<div style="display: flex; flex-wrap: wrap; gap: 40px;">';
            echo '<div><strong>Date</strong><br>' . date("M d Y", strtotime($row['start_date'])) . ' - ' . date("M d Y", strtotime($row['end_date'])) . '</div>';
            echo '<div><strong>Time</strong><br><span style="color: gray;">' . htmlspecialchars($row['time']) . '</span></div>';
            echo '<div><strong>Venue</strong><br><span style="color: gray;">' . htmlspecialchars($row['venue']) . '</span></div>';
            echo '<div><strong>Department</strong><br>' . htmlspecialchars($row['department']) . '</div>';
            echo '</div>';

            // Attachments Section
            echo '<h3 style="margin-top: 20px;">Requirements</h3>';
            echo '<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 10px;">';

            $requirements = [
                "Letter Attachment" => "letter_attachment",
                "Adviser Commitment form" => "adviser_form",
                "Constitution and by-laws of the Org." => "constitution",
                "Certification from Responsive Dean/Associate Dean" => "certification",
                "Accomplishment reports" => "reports",
                "Financial Report" => "financial",
                "Plan of Activities" => "plan",
                "Budget Plan" => "budget"
            ];

            foreach ($requirements as $label => $field) {
                echo '<div style="background: #f1f1f1; padding: 10px; border-radius: 10px;">';
                echo '<small style="color: red;">Requirement*</small><br>';
                echo '<strong>' . $label . '</strong><br>';
                if (!empty($row[$field])) {
                    echo '<a href="../proposal/' . htmlspecialchars($row[$field]) . '" target="_blank" style="display: inline-block; margin-top: 5px; background-color: #004080; color: white; padding: 5px 10px; border-radius: 5px; text-decoration: none;">View Attachment</a>';
                } else {
                    echo '<span style="color: gray; display: inline-block; margin-top: 5px;">No Attachment</span>';
                }
                echo '</div>';
            }

            echo '</div>';
            echo '</div>';
        }
        ?>
    </main>
</div>


<!-- Budget Plan -->
<div id="budgetForm" class="content" style="display:none;">
    <h1>Budget Plan</h1>

    <?php
    $sql = "SELECT * FROM proposals WHERE budget_amount IS NULL AND department = 'CCS' AND level = 'VP'";
    $result = mysqli_query($conn, $sql);

    if (mysqli_num_rows($result) == 0) {
    echo "<p>No pending proposals at this time.</p>";
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

        echo '<form method="POST" action=" " style="display:inline;">';
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
  <div class="modal-content">
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
  <div class="modal-dialog">
    <form method="POST" action="../proposal/flow.php">
      <div class="modal-content">
        <div class="modal-header bg-danger text-white">
  <h5 class="modal-title" id="disapproveModalLabel">Disapprove Proposal</h5>
  <button type="button" class="btn-close" id="closeModalBtn" aria-label="Close"></button>
</div>

        <div class="modal-body">
          <input type="hidden" name="proposal_id" id="modal_proposal_id" value="">
          <input type="hidden" name="level" value="CCSVice">
          <input type="hidden" name="action" value="disapprove">
          <div class="mb-3">
            <label for="modal_remarks" class="form-label">Please provide your reason for disapproval:</label>
            <textarea class="form-control" name="remarks" id="modal_remarks" required></textarea>
          </div>
        </div>
        <div class="modal-footer">
          <button type="submit" class="btn btn-danger">Submit Disapproval</button>
        </div>
      </div>
    </form>
  </div>
</div>



<!-- Tab Switching & User Fetching Script -->
<script>

document.addEventListener('DOMContentLoaded', function () {
  const form = document.getElementById('myForm');
  const qtyInputs = document.querySelectorAll('.qty-input');
  const amountInputs = document.querySelectorAll('.amount-input');
  const totalInputs = document.querySelectorAll('.total-input');
  const grandTotalInput = document.getElementById('grandTotal');

  function sanitize(input) {
    // Allow only digits and dot, remove others
    input.value = input.value.replace(/[^0-9.]/g, '');
  }

  function computeRowTotal(rowIndex) {
    const qty = parseFloat(qtyInputs[rowIndex].value) || 0;
    const amount = parseFloat(amountInputs[rowIndex].value) || 0;
    const total = qty * amount;
    totalInputs[rowIndex].value = total.toFixed(2);
  }

  function computeGrandTotal() {
    let grandTotal = 0;
    totalInputs.forEach(input => {
      grandTotal += parseFloat(input.value) || 0;
    });
    grandTotalInput.value = grandTotal.toFixed(2);
  }

  qtyInputs.forEach((qtyInput, index) => {
    qtyInput.addEventListener('input', () => {
      sanitize(qtyInput);
      computeRowTotal(index);
      computeGrandTotal();
    });
  });

  amountInputs.forEach((amountInput, index) => {
    amountInput.addEventListener('input', () => {
      sanitize(amountInput);
      computeRowTotal(index);
      computeGrandTotal();
    });
  });

  // Optional: on page load, compute totals for any pre-filled values
  for (let i = 0; i < qtyInputs.length; i++) {
  if (qtyInputs[i].value && amountInputs[i].value) {
    computeRowTotal(i);
  }
}
computeGrandTotal();
});

// -------------------------------------------------

document.querySelectorAll('.upload-btn').forEach(button => {
  button.addEventListener('click', function() {
    // Kunin ang proposal_id mula sa hidden input ng form na pinanggalingan ng button
    const form = this.closest('form');
    const proposalId = form.querySelector('input[name="proposal_id"]').value;

    // Ipakita ang budget form
    const budgetForm = document.getElementById('budgetForm');
    budgetForm.style.display = 'block';

    // Ilagay ang proposal_id sa budget form hidden input
    const budgetFormInput = budgetForm.querySelector('input[name="proposal_id"]');
    if (budgetFormInput) {
      budgetFormInput.value = proposalId;
    }
  });
});

// =========================================================================================
    const modal = document.getElementById('disapproveModal');
  const proposalIdInput = document.getElementById('modal_proposal_id');
  const openButtons = document.querySelectorAll('.open-modal-btn');
  const closeModalBtn = document.getElementById('closeModalBtn');

  openButtons.forEach(button => {
    button.addEventListener('click', () => {
      const proposalId = button.getAttribute('data-proposal-id');
      proposalIdInput.value = proposalId;
      modal.classList.add('active');
    });
  });

  closeModalBtn.addEventListener('click', () => {
    modal.classList.remove('active');
  });

  modal.addEventListener('click', (event) => {
    if (event.target === modal) {
      modal.classList.remove('active');
    }
  });

document.getElementById("dashboardTab").addEventListener("click", function () {
    document.getElementById("dashboardContent").style.display = "block";
    document.getElementById("approvalContent").style.display = "none";
    document.getElementById("requirementContent").style.display = "none";
    document.getElementById("budgetForm").style.display = "none";
    this.classList.add("active");
    document.getElementById("requirementTab").classList.remove("active");
    document.getElementById("approvalTab").classList.remove("active");
    document.getElementById("budget_planTab").classList.remove("active");
});

document.getElementById("approvalTab").addEventListener("click", function () {
    document.getElementById("dashboardContent").style.display = "none";
    document.getElementById("requirementContent").style.display = "none";
    document.getElementById("approvalContent").style.display = "block";
    document.getElementById("budgetForm").style.display = "none";
    this.classList.add("active");
    document.getElementById("requirementTab").classList.remove("active");
    document.getElementById("dashboardTab").classList.remove("active");
    document.getElementById("budget_planTab").classList.remove("active");
});

document.getElementById("requirementTab").addEventListener("click", function () {
    document.getElementById("dashboardContent").style.display = "none";
    document.getElementById("approvalContent").style.display = "none";
    document.getElementById("requirementContent").style.display = "block";
    document.getElementById("budgetForm").style.display = "none";
    this.classList.add("active");
    document.getElementById("dashboardTab").classList.remove("active");
    document.getElementById("approvalTab").classList.remove("active");
    document.getElementById("budget_planTab").classList.remove("active");
});

document.getElementById("budget_planTab").addEventListener("click", function () {
    document.getElementById("dashboardContent").style.display = "none";
    document.getElementById("approvalContent").style.display = "none";
    document.getElementById("requirementContent").style.display = "none";
    document.getElementById("budgetForm").style.display = "block";
    this.classList.add("active");
    document.getElementById("dashboardTab").classList.remove("active");
    document.getElementById("approvalTab").classList.remove("active");
    document.getElementById("requirementTab").classList.remove("active");
});
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
<!-- Dropdown Script -->
<script>
function toggleDropdown() {
    const menu = document.getElementById("dropdownMenu");
    menu.style.display = (menu.style.display === "block") ? "none" : "block";
}

document.addEventListener("click", function(event) {
    const dropdown = document.getElementById("userDropdown");
    const menu = document.getElementById("dropdownMenu");
    if (!dropdown.contains(event.target)) {
        menu.style.display = "none";
    }
});

document.addEventListener("DOMContentLoaded", function () {
        document.getElementById("calendarFrame").src = "../proposal/calendar.php";
    });

    document.querySelector(".toggle-btn").addEventListener("click", function () {
    const sidebar = document.querySelector(".sidebar");
    sidebar.classList.toggle("collapsed");
});
</script>
<script>

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

    // ===========================================================

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

function showRequirementsTab() {
    document.getElementById("dashboardContent").style.display = "none";
    document.getElementById("approvalContent").style.display = "none";
    document.getElementById("requirementContent").style.display = "block";

    document.getElementById("dashboardTab").classList.remove("active");
    document.getElementById("approvalTab").classList.remove("active");
    document.getElementById("requirementTab").classList.add("active");
}

function toggleMobileNav() {
    const nav = document.getElementById("mainNav");
    nav.classList.toggle("show");
}


 function hideAllSections() {
        document.getElementById("dashboardContent").style.display = "none";
        document.getElementById("approvalContent").style.display = "none";
        document.getElementById("requirementContent").style.display = "none";
        document.getElementById("budgetForm").style.display = "none";

        // Alisin ang 'active' class sa lahat ng sidebar items
        document.querySelectorAll(".sidebar ul li").forEach(function(item) {
            item.classList.remove("active");
        });
    }
  // Close modal kapag pinindot yung X
  closeBtn.addEventListener('click', function() {
    modal.style.display = 'none';
  });

  // Close modal kapag nag-click sa labas ng modal-content
  window.addEventListener('click', function(event) {
    if (event.target == modal) {
      modal.style.display = 'none';
    }
  });
</script>
<script>
function openBudgetPlanModal(proposalId) {
  document.getElementById("budgetProposalId").value = proposalId;
  document.getElementById("budgetPlanModal").classList.add("active");
}

function closeBudgetPlanModal() {
  document.getElementById("budgetPlanModal").classList.remove("active");
}
</script>

</body>
</html>