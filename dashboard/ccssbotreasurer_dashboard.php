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
        $status = 'Pending';
        $new_level = 'CCS Auditor';
        $viewed = 0;
    
        $stmt = $conn->prepare("UPDATE sooproposal SET status=?, level=?, viewed=? WHERE id=?");
        if (!$stmt) die("Prepare failed: " . $conn->error);
    
        $stmt->bind_param("ssii", $status, $new_level, $viewed, $id);
        if (!$stmt->execute()) die("Execute failed: " . $stmt->error);
    
        // Redirect with success flag
        header("Location: ccssbotreasurer_dashboard.php?approved=1");
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
        header("Location: ccssbotreasurer_dashboard.php");
        exit;
    }
}

$current_level = 'CCS Treasurer';
$search_department = '%CCS%';
$stmt = $conn->prepare("SELECT * FROM sooproposal WHERE level=? AND status='Pending' AND submit='submitted' AND department LIKE ?");
$stmt->bind_param("ss", $current_level, $search_department);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>CCS SBO Treasurer Dashboard</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" crossorigin="anonymous">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <link rel="stylesheet" href="../style/sbotreasure.css">
  <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js" crossorigin="anonymous"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.min.js" crossorigin="anonymous"></script>
  <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate" />
  
</head>
<style>

</style>
<body>
<header class="topbar">
  <div class="logo"><img src="../img/lspulogo.jpg" alt="Logo">CCS TREASURER PORTAL</div>
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
          <?php if ($_SESSION['role'] === 'CCSSBOTreasurer'): ?>
            <a href="ccssbotreasurer_dashboard.php">CCS SBO Treasurer Dashboard</a>
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
    <h1>Welcome to the CCS Treasurer Dashboard</h1>
    <p>This is your overview page.</p>
    <iframe id="calendarFrame" style="width:100%; height:600px; border:none;"></iframe>
</div>

<!-- Proposals Section -->
<div id="proposalContent" class="content" style="display:none;">
  <h1>Pending Proposals for Approval</h1>
  <table>
    <thead>
      <tr>
        <th>Department</th><th>Event Type</th><th>Start Date</th><th>End Date</th><th>Venue</th><th>Status</th><th>Actions</th>
      </tr>
    </thead>
    <tbody>
    <?php if ($result && $result->num_rows > 0): ?>
      <?php while ($row = $result->fetch_assoc()): ?>
        <tr>
            <td><?= htmlspecialchars($row['id']) ?></td>
            <td><?= htmlspecialchars($row['department']) ?></td>
            <td><?= htmlspecialchars($row['event_type']) ?></td>
            <td><?= htmlspecialchars($row['start_date']) ?></td>
            <td><?= htmlspecialchars($row['end_date']) ?></td>
            <td><?= htmlspecialchars($row['venue']) ?></td>
            <td><?= htmlspecialchars($row['status']) ?></td>
            <td>
                <form method="POST" action="" style="display:inline;">
    <input type="hidden" name="proposal_id" value="<?= $row['id'] ?>">
    <button type="submit" name="action" value="approve" class="action-btn approve-btn">Approve</button>
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

</body>
</html>

<?php
$conn->close();
?>
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

 $sql = "SELECT * FROM sooproposal WHERE budget_amount IS NULL AND department = 'CCS' AND status != 'Disapproved'";
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

<!-- Disapprove Remarks Modal -->
<div class="modal fade" id="disapproveModal" tabindex="-1" aria-labelledby="disapproveModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-lg">
   <form method="POST" action="ccssbotreasurer_dashboard.php">

      <div class="modal-content">
        <!-- Header -->
        <div class="modal-header bg-danger text-white">
          <h5 class="modal-title" id="disapproveModalLabel">Disapproved</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>

        <!-- Body -->
        <div class="modal-body">
          <input type="hidden" name="proposal_id" id="modal_proposal_id">
          <input type="hidden" name="level" value="CCSSBOTreasurer">
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
    requirement: "requirementContent"
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

</script>
</body>
</html>
