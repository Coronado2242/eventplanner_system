<?php
session_start();
$conn = new mysqli("localhost", "root", "", "eventplanner");
$result = $conn->query("SELECT id, department, event_type, budget_approved, budget_amount FROM proposals WHERE budget_approved = 0");




?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Dashboard</title>
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
    display: none; /* hidden by default */
    position: fixed;
    z-index: 1000;
    left: 0; top: 0;
    width: 100%; height: 100%;
    overflow: auto;
    background-color: rgba(0,0,0,0.5);
  }
  /* Modal content box */
  .modal-content {
    background-color: #fff;
    margin: 10% auto;
    padding: 20px;
    border-radius: 5px;
    width: 400px;
    position: relative;
  }
  /* Close button */
  .close-btn {
    position: absolute;
    right: 10px; top: 10px;
    font-size: 24px;
    font-weight: bold;
    cursor: pointer;
  }

  .modal {
  display: none;
  /* other styles for overlay */
}
.modal.active {
  display: flex; /* or block, whatever you want */
  /* styles for visible modal */
}


</style>
<body>

<header class="topbar">
    <div class="logo"><img src="../img/lspulogo.jpg">CCS DEAN PORTAL</div>
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
        <li id="proposalTab"><i class="fa fa-file-alt"></i> Proposals</li>
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

<!-- User Management Content -->
<div id="approvalContent" style="display:none;">
    <main class="content" >
        <h1 style="margin-bottom: 0;">Request Approval</h1>
<table class="approval-table">
    <thead>
        <tr>
            <th>ID</th>
            <th>Department</th>
            <th>Requirements</th>
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
                <td><?= htmlspecialchars($row['event_type']) ?></td>
                <td>
                    <input type="number" name="budget" class="budget-input" placeholder="Enter amount">
                </td>
                <td>
                    <button class="approve-btn" onclick="approveBudget(<?= $row['id'] ?>, this)">Approve</button>
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

        $sql = "SELECT * FROM proposals WHERE budget_amount IS NULL";
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

<!-- Proposals Content -->
<div id="proposalContent" class="content" style="display:none;">
    <h1>Pending Proposals for Approval</h1>

    <?php
    $sql = "SELECT * FROM proposals WHERE status = 'Pending' AND level = 'VP'";
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
        
        // Output the Approve form
        echo '<form method="POST" action="../proposal/flow.php" style="display:inline;">';
        echo '<input type="hidden" name="proposal_id" value="' . htmlspecialchars($row['id']) . '">';
        echo '<input type="hidden" name="level" value="CCSVice">';
        echo '<button type="submit" name="action" value="approve" class="action-btn approve-btn">Approve</button>';
        echo '</form>';

        // Output the Disapprove form
        echo '<form method="POST" action="../proposal/flow.php" style="display:inline; margin-left:5px;">';
        echo '<input type="hidden" name="proposal_id" value="' . htmlspecialchars($row['id']) . '">';
        echo '<input type="hidden" name="level" value="CCSVice">';
echo "<button type='button' class='btn btn-danger btn-sm open-modal-btn' data-proposal-id='" . htmlspecialchars($row['id']) . "'>Disapprove</button>";



        echo '</form>';

        echo '</td>';
        echo '</tr>';
    }

    echo '</tbody></table>';
}
    ?>
</div>

<!-- Budget Plan -->
<div id="budgetForm" class="content" style="display:none;">
    <h1>Pending Proposals for Approval</h1>

    <?php
    $sql = "SELECT * FROM proposals WHERE status = 'Pending' AND level = 'VP'";
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
        echo '<button type="submit" name="action" value="approve" class="action-btn upload-btn">Upload Budget</button>';
        echo '</form>';
        
        // Output the Approve form
//         echo '<form method="POST" action="../proposal/flow.php" style="display:inline;">';
//         echo '<input type="hidden" name="proposal_id" value="' . htmlspecialchars($row['id']) . '">';
//         echo '<input type="hidden" name="level" value="CCSVice">';
//         echo '<button type="submit" name="action" value="approve" class="action-btn approve-btn">Approve</button>';
//         echo '</form>';

//         // Output the Disapprove form
//         echo '<form method="POST" action="../proposal/flow.php" style="display:inline; margin-left:5px;">';
//         echo '<input type="hidden" name="proposal_id" value="' . htmlspecialchars($row['id']) . '">';
//         echo '<input type="hidden" name="level" value="CCSVice">';
// echo "<button type='button' class='btn btn-danger btn-sm open-modal-btn' data-proposal-id='" . htmlspecialchars($row['id']) . "'>Disapprove</button>";



        // echo '</form>';

        echo '</td>';
        echo '</tr>';
    }

    echo '</tbody></table>';
}
    ?>
</div>

    <!--Upload Budget-->

<div class="container mt-5">
  <h2 class="mb-4">Submit Budget Plan</h2>

  <form action="submit_budget_pdf.php" method="POST">

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
            <td><input type="number" name="qty[]" class="form-control" step="1" /></td>
            <td><input type="number" name="amount[]" class="form-control" step="0.01" /></td>
            <td><input type="number" name="total[]" class="form-control" step="0.01" /></td>
          </tr>
          <?php endfor; ?>
        </tbody>
      </table>
    </div>

    <div class="text-end mt-3">
      <button type="submit" class="btn btn-primary px-5">Generate PDF and Submit</button>
    </div>
  </form>
</div>










    

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

 const proposalContent = document.getElementById('proposalContent');
     const proposalTab = document.getElementById('proposalTab');


document.getElementById("dashboardTab").addEventListener("click", function () {
    document.getElementById("dashboardContent").style.display = "block";
    document.getElementById("approvalContent").style.display = "none";
    document.getElementById("requirementContent").style.display = "none";
    this.classList.add("active");
    document.getElementById("requirementTab").classList.remove("active");
    document.getElementById("approvalTab").classList.remove("active");
});

document.getElementById("approvalTab").addEventListener("click", function () {
    document.getElementById("dashboardContent").style.display = "none";
    document.getElementById("requirementContent").style.display = "none";
    document.getElementById("approvalContent").style.display = "block";
    this.classList.add("active");
    document.getElementById("requirementTab").classList.remove("active");
    document.getElementById("dashboardTab").classList.remove("active");
});

document.getElementById("requirementTab").addEventListener("click", function () {
    document.getElementById("dashboardContent").style.display = "none";
    document.getElementById("approvalContent").style.display = "none";
    document.getElementById("requirementContent").style.display = "block";
    this.classList.add("active");
    document.getElementById("dashboardTab").classList.remove("active");
    document.getElementById("approvalTab").classList.remove("active");
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
        document.getElementById("calendarFrame").src = "../calendar/calendar.php";
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
        document.getElementById("proposalContent").style.display = "none";

        // Alisin ang 'active' class sa lahat ng sidebar items
        document.querySelectorAll(".sidebar ul li").forEach(function(item) {
            item.classList.remove("active");
        });
    }

    // Event listeners para sa bawat sidebar tab
    document.getElementById("dashboardTab").addEventListener("click", function() {
        hideAllSections();
        document.getElementById("dashboardContent").style.display = "block";
        this.classList.add("active");
    });

    document.getElementById("approvalTab").addEventListener("click", function() {
        hideAllSections();
        document.getElementById("approvalContent").style.display = "block";
        this.classList.add("active");
    });

    document.getElementById("requirementTab").addEventListener("click", function() {
        hideAllSections();
        document.getElementById("requirementContent").style.display = "block";
        this.classList.add("active");
    });

    document.getElementById("proposalTab").addEventListener("click", function() {
        hideAllSections();
        document.getElementById("proposalContent").style.display = "block";
        this.classList.add("active");
    });



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



const budgetTab = document.getElementById("budget_planTab");
    const budgetForm = document.getElementById("budgetForm");

    function toggleBudgetForm() {
      budgetForm.classList.toggle("active");
    }

    budgetTab.addEventListener("click", () => {
      toggleBudgetForm();
    });
    
    
</script>


</body>
</html>
