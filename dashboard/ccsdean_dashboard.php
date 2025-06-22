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

// === Separate levels for Proposal and Venue ===
$proposal_level = 'CCS Dean';
$venue_level = 'Venues'; 

// === Handle Approve/Disapprove for Proposals (excluding Gym venue) ===
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['proposal_id'], $_POST['action']) && isset($_POST['type']) && $_POST['type'] === 'proposal') {
    $id = (int)$_POST['proposal_id'];
    $action = $_POST['action'];

    if ($action === 'approve') {
        $status = 'Pending';
<<<<<<< Updated upstream
        $new_level = 'OSAS';
        $viewed = 0;

        $stmt = $conn->prepare("UPDATE sooproposal SET status=?, level=?, viewed=? WHERE id=?");
        $stmt->bind_param("ssii", $status, $new_level, $viewed, $id);
        $stmt->execute();
        header("Location: ccsdean_dashboard.php?approved=1&tab=proposal");
        exit;
<<<<<<< HEAD
        } elseif ($action === 'disapprove') {
=======
        $new_level = 'CCS Dean';  
        $stmt = $conn->prepare("UPDATE proposals SET status=?, level=? WHERE id=?");
        if(!$stmt){
            die("Prepare failed: " . $conn->error);
        }
        $stmt->bind_param("ssi", $status, $new_level, $id);
        if(!$stmt->execute()){
            die("Execute failed: " . $stmt->error);
        }
        header("Location: ccsdean_dashboard.php");
        exit;
    } elseif ($action === 'disapprove') {
>>>>>>> Stashed changes
=======
    } elseif ($action === 'disapprove') {
>>>>>>> b09586ad51f4f85b3a498be3c3bd2280ddf3e561
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
<<<<<<< Updated upstream
=======

        // Debug: Check session username
>>>>>>> Stashed changes
        $disapproved_by = $_SESSION['username'] ?? 'Unknown';

<<<<<<< HEAD
        $stmt = $conn->prepare("UPDATE proposals SET status='Disapproved', remarks=?, disapproved_by=?, level='' WHERE id=?");
<<<<<<< Updated upstream
        if (!$stmt) die("Prepare failed: " . $conn->error);
        $stmt->bind_param("ssi", $final_remarks, $disapproved_by, $id);
        if (!$stmt->execute()) die("Execute failed: " . $stmt->error);
=======
        if(!$stmt){
            die("Prepare failed: " . $conn->error);
        }
        $stmt->bind_param("ssi", $final_remarks, $disapproved_by, $id);
        if(!$stmt->execute()){
            die("Execute failed: " . $stmt->error);
        }

>>>>>>> Stashed changes
        header("Location: ccsdean_dashboard.php");
=======
        $stmt = $conn->prepare("UPDATE sooproposal SET status='Disapproved', remarks=?, disapproved_by=?, level='' WHERE id=?");
        $stmt->bind_param("ssi", $final_remarks, $disapproved_by, $id);
        $stmt->execute();
        header("Location: ccsdean_dashboard.php?tab=proposal");
>>>>>>> b09586ad51f4f85b3a498be3c3bd2280ddf3e561
        exit;
    }
}

<<<<<<< HEAD
<<<<<<< Updated upstream
$current_level = 'CCS Dean';
$search_department = '%CCS%';
=======
// Fetch proposals
$current_level = 'CCS Dean';
$search_department = '%CCS%';

>>>>>>> Stashed changes
$stmt = $conn->prepare("SELECT * FROM proposals WHERE level=? AND status='Pending' AND submit='submitted' AND department LIKE ?");
$stmt->bind_param("ss", $current_level, $search_department);
=======
// === Handle Approve/Disapprove for Venue (Gym only) ===
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

// === Fetch Proposals for Approval (excluding Gym venue) ===
$search_department = '%CCS%';
$stmt = $conn->prepare("SELECT * FROM sooproposal WHERE level=? AND status='Pending' AND submit='submitted' AND department LIKE ?");
$stmt->bind_param("ss", $proposal_level, $search_department);
>>>>>>> b09586ad51f4f85b3a498be3c3bd2280ddf3e561
$stmt->execute();
$proposal_result = $stmt->get_result();

// === Fetch Venue Requests for Gym ===
$venue_stmt = $conn->prepare("SELECT * FROM sooproposal WHERE level=? AND status='Pending' AND submit='submitted' AND department LIKE ? AND LOWER(venue) = 'gym'");
$venue_stmt->bind_param("ss", $venue_level, $search_department);
$venue_stmt->execute();
$venue_result = $venue_stmt->get_result();
?>

<<<<<<< HEAD
<<<<<<< Updated upstream
=======
<!-- Flash Messages -->
<?php if(isset($_SESSION['success'])): ?>
<div class="alert alert-success"><?= $_SESSION['success']; unset($_SESSION['success']); ?></div>
<?php endif; ?>

<?php if(isset($_SESSION['error'])): ?>
<div class="alert alert-danger"><?= $_SESSION['error']; unset($_SESSION['error']); ?></div>
<?php endif; ?>

>>>>>>> Stashed changes
=======

>>>>>>> b09586ad51f4f85b3a498be3c3bd2280ddf3e561
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
<<<<<<< Updated upstream
  <title>CCS Dean Dashboard</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" crossorigin="anonymous">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <link rel="stylesheet" href="../style/sbotreasure.css">
  <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js" crossorigin="anonymous"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.min.js" crossorigin="anonymous"></script>
  <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate" />
</head>

=======
  <title>CCS SBO Dean Dashboard</title>

  <!-- Bootstrap CSS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" crossorigin="anonymous">

  <!-- Your other CSS -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <link rel="stylesheet" href="../style/css_all.css">

  <!-- Popper.js and Bootstrap JS -->
  <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js" crossorigin="anonymous"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.min.js" crossorigin="anonymous"></script>

  <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate" />
</head>

</style>
>>>>>>> Stashed changes
<body>
<header class="topbar">
  <div class="logo"><img src="../img/lspulogo.jpg" alt="Logo">CCS DEAN PORTAL</div>
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
    <li id="venuerequirementTab"> Requirements</li>
    </ul>
  </ul>
</aside>

<!-- Dashboard Section -->
<div id="dashboardContent" class="content">
  <h1>Welcome to the CCS Dean Dashboard</h1>
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
    <?php if ($proposal_result && $proposal_result->num_rows > 0): ?>
      <?php while ($row = $proposal_result->fetch_assoc()): ?>
        <tr>
<<<<<<< Updated upstream
          <td><?= htmlspecialchars($row['department']) ?></td>
          <td><?= htmlspecialchars($row['activity_name']) ?></td>
          <td><?= htmlspecialchars($row['start_date']) ?></td>
          <td><?= htmlspecialchars($row['end_date']) ?></td>
          <td><?= htmlspecialchars($row['venue']) ?></td>
          <td><?= htmlspecialchars($row['status']) ?></td>
          <td>
            <button type="button" class="btn btn-success btn-sm approve-btn" 
            data-id="<?= $row['id'] ?>" 
            data-bs-toggle="modal" 
            data-bs-target="#approveModal">Approve
            </button>
            <button type="button" class="btn btn-danger btn-sm disapprove-btn" data-id="<?= $row['id'] ?>" data-bs-toggle="modal" data-bs-target="#disapproveModal">Disapprove</button>
          </td>
=======
            <td><?= htmlspecialchars($row['id']) ?></td>
            <td><?= htmlspecialchars($row['department']) ?></td>
            <td><?= htmlspecialchars($row['event_type']) ?></td>
            <td><?= htmlspecialchars($row['start_date']) ?></td>
            <td><?= htmlspecialchars($row['end_date']) ?></td>
            <td><?= htmlspecialchars($row['venue']) ?></td>
            <td><?= htmlspecialchars($row['status']) ?></td>
            <td>
                <div style="display: flex; flex-direction: column; gap: 10px;">

                <form method="post" action="" style="margin:0;">
                    <!-- Important: assign proposal_id value here -->
                    <input type="hidden" name="proposal_id" value="<?= htmlspecialchars($row['id']) ?>" />
                    <input type="hidden" name="level" value="CCS Dean">


                    <button type="submit" name="action" value="approve" class="approve-btn">Approve</button>
                </form>
                <form method="POST" action="ccsdean_dashboard.php">
                            <button type="button" class="btn btn-danger disapprove-btn" data-id="<?= $row['id'] ?>" data-bs-toggle="modal" data-bs-target="#disapproveModal">
                        Disapprove
                        </button>
                    </form>
            </div>
            </td>
>>>>>>> Stashed changes
        </tr>
      <?php endwhile; ?>
    <?php else: ?>
      <tr><td colspan="8" class="text-center">No proposals found for Dean.</td></tr>
    <?php endif; ?>
    </tbody>
  </table>
</div>

<!-- Requirements Section -->
<div id="requirementContent" class="content" style="display:none;">
  <h1>Requirements</h1>
  <?php
$stmt = $conn->prepare("SELECT * FROM proposals WHERE level=? AND status='Pending' AND submit='submitted' AND department LIKE ?");
$stmt->bind_param("ss", $current_level, $search_department);
$stmt->execute();
$result = $stmt->get_result();

<<<<<<< Updated upstream
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
=======
<!-- Requirements Content -->
<<div id="requirementContent" style="display:none;">
    <main class="content">
        <h1 style="margin-bottom: 0;">Requirements</h1>
>>>>>>> Stashed changes

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

<<<<<<< Updated upstream
            if (!empty($row[$field])) {
                $directory = $requirementDirectories[$field] ?? '../proposal/';
                echo '<a href="' . $directory . htmlspecialchars($row[$field]) . '" target="_blank" class="btn btn-primary btn-sm mt-2">View Attachment</a>';
            } else {
                echo '<span class="text-muted mt-2 d-block">No Attachment</span>';
=======
$sql = "SELECT * FROM proposals WHERE status = 'Pending' AND department = 'CCS'";

$result = mysqli_query($conn, $sql);

// Dito mo ilalagay yung check kung may records
if (mysqli_num_rows($result) == 0) {
} else {

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
>>>>>>> Stashed changes
            }

            echo '</div></div>';
        }
<<<<<<< Updated upstream

        echo '</div></div>';
    }
} else {
    echo '<div class="alert alert-info text-center">No requirements found for Dean.</div>';
}
?>
<<<<<<< HEAD

=======
    }
        ?>
    </main>
>>>>>>> Stashed changes
=======
>>>>>>> b09586ad51f4f85b3a498be3c3bd2280ddf3e561
</div>

<!-- Venue Approval -->
<div id="venueContent" class="content" style="display:none;">
  <h1>Pending Venue Requests</h1>
  <table class="table table-bordered">
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
    venue: "venueContent"
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
  if (tab && ['dashboard', 'proposal', 'requirement', 'venue'].includes(tab)) {
    switchTab(tab);
<<<<<<< HEAD

<<<<<<< Updated upstream
    // Optional: Remove the query string from the URL
    window.history.replaceState({}, document.title, window.location.pathname);
=======
    window.history.replaceState({}, document.title, window.location.pathname); // Clean URL
>>>>>>> b09586ad51f4f85b3a498be3c3bd2280ddf3e561
  }

  if (urlParams.get('approved') === '1') {
    alert("✅ Proposal approved successfully!");
    window.history.replaceState({}, document.title, window.location.pathname); // Clean URL
  }
}

document.addEventListener("DOMContentLoaded", function () {
  document.getElementById("calendarFrame").src = "../proposal/calendar.php";

  // Set tab click listeners
  const tabIds = ["dashboard", "proposal", "requirement", "venue"];
  tabIds.forEach(tab => {
    const tabElement = document.getElementById(tab + "Tab");
    if (tabElement) {
      tabElement.addEventListener("click", () => switchTab(tab));
    }
  });

  // Setup modals and check URL
  setupModalButtons();
  checkURLParams();
});
=======
    requirementTab.addEventListener('click', () => {
        clearActive();
        requirementTab.classList.add('active');
        dashboardContent.style.display = 'none';
        proposalContent.style.display = 'none';
        requirementCon
        tent.style.display = 'block';
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


>>>>>>> Stashed changes
</script>

</body>
</html>
