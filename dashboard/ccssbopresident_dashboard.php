
<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

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

// Catch upload_id from GET
$upload_id = $_SESSION['upload_id'] ?? null;
if ($upload_id) {
    $stmt = $conn->prepare("SELECT * FROM sooproposal WHERE id = ?");
    $stmt->bind_param("i", $upload_id);
    $stmt->execute();
    $result = $stmt->get_result();
} else {
    $result = false;
}

// Handle adding activity
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_activity'])) {
    $department = $_POST['department'];
    $activity_name = $_POST['activity_name'];
    $objective = $_POST['objective'];
    $brief_description = $_POST['brief_description'];
    $persons_involved = $_POST['persons_involved'];

    $stmt = $conn->prepare("INSERT INTO activities (department, activity_name, objective, brief_description, person_involved) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("sssss", $department, $activity_name, $objective, $brief_description, $persons_involved);

    if ($stmt->execute()) {
        header("Location: ccssbopresident_dashboard.php?activity_added=1");
        exit;
    } else {
        echo "Error adding activity: " . $stmt->error;
    }
}

// Approval Logic (same as previous code)...
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['proposal_id'], $_POST['action'])) {
    $id = (int)$_POST['proposal_id'];
    $action = $_POST['action'];

    if ($action === 'approve') {
        $status = 'Pending';
        $new_level = 'CCS Auditor';
        $_SESSION['upload_id'] = $id;

        $stmt = $conn->prepare("UPDATE sooproposal SET status = ?, level = ? WHERE id = ?");
        if ($stmt) {
            $stmt->bind_param("ssi", $status, $new_level, $id);
            if ($stmt->execute()) {
                header("Location: ccssbopresident_dashboard.php?upload_id=$id&approved=1");
                exit;
            } else {
                echo "Execute failed: " . $stmt->error;
            }
        } else {
            echo "Prepare failed: " . $conn->error;
        }
    } elseif ($action === 'disapprove') {
        $status = 'Disapproved by President';
        $new_level = 'Disapproved';

        $stmt = $conn->prepare("UPDATE sooproposal SET status = ?, level = ? WHERE id = ?");
        if ($stmt) {
            $stmt->bind_param("ssi", $status, $new_level, $id);
            if ($stmt->execute()) {
                header("Location: ccssbopresident_dashboard.php?upload_id=$id&disapproved=1");
                exit;
            } else {
                echo "Execute failed: " . $stmt->error;
            }
        } else {
            echo "Prepare failed: " . $conn->error;
        }
    }
}

// Financial Approval Logic
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['proposal_id'], $_POST['action']) && 
    in_array($_POST['action'], ['approve_financial', 'disapprove_financial'])) {

    $id = (int)$_POST['proposal_id'];
    $action = $_POST['action'];

    if ($action === 'approve_financial') {
        $financialstatus = 'Submitted';
        $new_level = 'CCS Financial Adviser';

        $stmt = $conn->prepare("UPDATE sooproposal SET financialstatus = ?, level = ? WHERE id = ?");
        $stmt->bind_param("ssi", $financialstatus, $new_level, $id);
        $stmt->execute();

        header("Location: ccssbopresident_dashboard.php?financial_approved=1");
        exit;

    } elseif ($action === 'disapprove_financial') {
        $financialstatus = 'Disapproved by President';
        $stmt = $conn->prepare("UPDATE sooproposal SET financialstatus = ?, submit = NULL WHERE id = ?");
        $stmt->bind_param("si", $financialstatus, $id);
        $stmt->execute();

        header("Location: ccssbopresident_dashboard.php?financial_disapproved=1");
        exit;
    }
}

// Load Pending Proposals
$level = 'CCS President';
$search_department = '%CCS%';

$stmt = $conn->prepare("SELECT * FROM sooproposal WHERE level=? AND status='Pending' AND submit='submitted' AND department LIKE ?");
$stmt->bind_param("ss", $level, $search_department);
$stmt->execute();
$result = $stmt->get_result();

// File Upload Handler
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['letter_attachment'])) {
    $uploadDir = "uploads/";
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }

    $id = $_POST['proposal_id'] ?? null;

    if (!$id) {
        echo "Missing proposal ID.";
        exit;
    } else {
        $stmt = $conn->prepare("SELECT * FROM sooproposal WHERE id = ?");
        if (!$stmt) {
            die("Query Error: " . $conn->error);
        }
        $stmt->bind_param("i", $id); 
        $stmt->execute();
        $result = $stmt->get_result();
    }

    $fields = [
        'letter_attachment', 'constitution', 'reports', 'adviser_form',
        'certification', 'financial'
    ];

    $fileData = [];
    foreach ($fields as $field) {
        if (isset($_FILES[$field]) && $_FILES[$field]['error'] === UPLOAD_ERR_OK) {
            $filename = time() . '_' . uniqid() . '_' . basename($_FILES[$field]['name']);
            $target = $uploadDir . $filename;
            if (move_uploaded_file($_FILES[$field]['tmp_name'], $target)) {
                $fileData[$field] = $filename;
            } else {
                $fileData[$field] = '';
            }
        } else {
            $fileData[$field] = '';
        }
    }

    $sql = "UPDATE sooproposal SET 
        letter_attachment = ?, 
        constitution = ?, 
        reports = ?, 
        adviser_form = ?, 
        certification = ?, 
        financial = ?,
        level = 'CCS Auditor'
    WHERE id = ?";

    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        die("SQL Prepare Error: " . $conn->error);
    }

    $stmt->bind_param(
        'ssssssi',
        $fileData['letter_attachment'],
        $fileData['constitution'],
        $fileData['reports'],
        $fileData['adviser_form'],
        $fileData['certification'],
        $fileData['financial'],
        $id
    );

    if ($stmt->execute()) {
        $_SESSION['upload_success'] = true;
        header("Location: ccssbopresident_dashboard.php?approved=1&upload_id=$id");
        exit;
    } else {
        echo "Update failed: " . $stmt->error;
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <title>CCS SBO President Portal</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate" />
    <style>
        /* Your CSS styles here */
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
          background-size: cover;
          position: relative;
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
          padding: 10px;
          margin-right: 20px;
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

    </style>
</head>

<body>
<header class="topbar">
    <div class="logo">
        <img src="<?php echo $logoSrc; ?>" alt="Logo" style="height:49px; border-radius:50%; box-shadow:0 4px 8px rgba(0,0,0,0.3);">
        Event<span style="color:blue;">Sync</span>&nbsp;CCS SBO PRESIDENT PORTAL
    </div>
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
                    <?php if ($_SESSION['role'] === 'CCSSBOPresident'): ?>
                        <a href="ccssbopresident_dashboard.php">CCS SBO President Dashboard</a>
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
        <li id="activitiesTab"><i class="fa fa-calendar"></i> Activities</li>
    </ul>
</aside>

<!-- Dashboard Section -->
<div id="dashboardContent" class="content">
    <h1>Welcome to the CCS SBO President Dashboard</h1>
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
                <th>Attachment</th>
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
                                <a href="../proposal/uploads/<?= urlencode($row['budget_file']) ?>" target="_blank" class="btn btn-primary btn-sm d-inline-flex align-items-center">
                                    <i class="fa fa-file-alt me-2"></i> View Budget File
                                </a>
                            <?php else: ?>
                                No File
                            <?php endif; ?>
                        </td>
                        <td><?= htmlspecialchars($row['status']) ?></td>
                        <td>
                            <form method="POST" style="display:inline;">
                                <input type="hidden" name="proposal_id" value="<?= $row['id'] ?>">
                                <button type="button" class="action-btn approve-btn" data-id="<?= $row['id'] ?>">Approve</button>
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

<!-- Requirements Section (not changed, keep it as is) -->
<div id="requirementContent" class="content" style="display:none;">
    <h1>Requirements Section</h1>
    <?php
    // Sample session data
    $level = 'CCS President';
    $search_department = '%CCS%';

    $stmt = $conn->prepare("SELECT * FROM sooproposal WHERE level=? AND status='Pending' AND submit='submitted' AND department LIKE ?");
    $stmt->bind_param("ss", $level, $search_department);
    $stmt->execute();
    $result = $stmt->get_result();

    // Check and display results
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
                "Plan of Activities" => "POA_file",
                "Budget Plan" => "budget_file"
            ];

            foreach ($requirements as $label => $field) {
                echo '<div class="col-md-4">';
                echo '<div class="border rounded p-3 bg-light h-100">';
                echo '<small class="text-danger fw-bold">Requirement*</small><br>';
                echo '<strong>' . $label . '</strong><br>';

                if (!empty($row[$field])) {
                    echo '<a href="../proposal/uploads/' . htmlspecialchars($row[$field]) . '" target="_blank" class="btn btn-primary btn-sm mt-2">View Attachment</a>';
                } else {
                    echo '<span class="text-muted mt-2 d-block">No Attachment</span>';
                }

                echo '</div></div>';
            }

            echo '</div></div>'; // end row and card
        }
    } else {
        echo '<div class="alert alert-info text-center">No requirements found for SBO President (department: CCS).</div>';
    }
    ?>
</div>

<!-- Activities Section -->
<div id="activitiesContent" style="display:none;">
    <h1>POA Activities</h1>
    <a href="#" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addActivityModal">+ Add Activities</a><br><br>
    <table>
        <thead>
            <tr>
                <th>Department</th>
                <th>Activity Name</th>
                <th>Objective</th>
                <th>Brief Description</th>
                <th>Persons Involved</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $sql = "SELECT * FROM activities ORDER BY created_at DESC";
            $res = $conn->query($sql);
            if ($res->num_rows > 0):
                while ($row = $res->fetch_assoc()):
            ?>
            <tr>
                <td><?= htmlspecialchars($row['department']) ?></td>
                <td><?= htmlspecialchars($row['activity_name']) ?></td>
                <td><?= htmlspecialchars($row['objective']) ?></td>
                <td><?= htmlspecialchars($row['brief_description']) ?></td>
                <td><?= htmlspecialchars($row['person_involved']) ?></td>
            </tr>
            <?php endwhile; else: ?>
            <tr><td colspan="5">No activities found.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<!-- Modal for Adding Activity -->
<div class="modal fade" id="addActivityModal" tabindex="-1" aria-labelledby="addActivityModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <form method="POST" action="ccssbopresident_dashboard.php">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="addActivityModalLabel">Add Activity</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="department" class="form-label">Department</label>
                        <input type="text" class="form-control" name="department" required>
                    </div>
                    <div class="mb-3">
                        <label for="activity_name" class="form-label">Activity Name</label>
                        <input type="text" class="form-control" name="activity_name" required>
                    </div>
                    <div class="mb-3">
                        <label for="objective" class="form-label">Objective</label>
                        <input type="text" class="form-control" name="objective" required>
                    </div>
                    <div class="mb-3">
                        <label for="brief_description" class="form-label">Brief Description</label>
                        <textarea class="form-control" name="brief_description" required></textarea>
                    </div>
                    <div class="mb-3">
                        <label for="persons_involved" class="form-label">Persons Involved</label>
                        <input type="text" class="form-control" name="persons_involved" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" name="add_activity" class="btn btn-primary">Add Activity</button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- JavaScript Section -->
<script>
function toggleDropdown() {
    const menu = document.getElementById('dropdownMenu');
    menu.style.display = (menu.style.display === 'block') ? 'none' : 'block';
}

function toggleMobileNav() {
    const nav = document.getElementById("mainNav");
    nav.classList.toggle("show");
}
    
const dashboardTab = document.getElementById('dashboardTab');
const proposalTab = document.getElementById('proposalTab');
const requirementTab = document.getElementById('requirementTab');
const financialTab = document.getElementById('financialTab');
const activitiesTab = document.getElementById('activitiesTab');

const dashboardContent = document.getElementById('dashboardContent');
const proposalContent = document.getElementById('proposalContent');
const requirementContent = document.getElementById('requirementContent');
const financialContent = document.getElementById('financialReportContent');
const activitiesContent = document.getElementById('activitiesContent');

function clearActive() {
    dashboardTab.classList.remove('active');
    proposalTab.classList.remove('active');
    requirementTab.classList.remove('active');
    financialTab.classList.remove('active');
    activitiesTab.classList.remove('active');

    dashboardContent.style.display = 'none';
    proposalContent.style.display = 'none';
    requirementContent.style.display = 'none';
    financialContent.style.display = 'none';
    activitiesContent.style.display = 'none';
}

dashboardTab.addEventListener('click', () => {
    clearActive();
    dashboardTab.classList.add('active');
    dashboardContent.style.display = 'block';
});

proposalTab.addEventListener('click', () => {
    clearActive();
    proposalTab.classList.add('active');
    proposalContent.style.display = 'block';
});

requirementTab.addEventListener('click', () => {
    clearActive();
    requirementTab.classList.add('active');
    requirementContent.style.display = 'block';
});

financialTab.addEventListener('click', () => {
    clearActive();
    financialTab.classList.add('active');
    financialContent.style.display = 'block';
});

activitiesTab.addEventListener('click', () => {
    clearActive();
    activitiesTab.classList.add('active');
    activitiesContent.style.display = 'block';
});

document.addEventListener("DOMContentLoaded", function () {
    document.getElementById("calendarFrame").src = "../proposal/calendar.php";
});
</script>

<script>
  <?php if (isset($_GET['activity_added']) && $_GET['activity_added'] == 1): ?>
    alert("✅ Activity added successfully!");
  <?php endif; ?>
</script>

</body>
</html>
