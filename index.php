<?php
session_start();
$role = $_SESSION['role'] ?? '';
$conn = new mysqli("localhost", "root", "", "eventplanner");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$logoSrc = "img/lspulogo.jpg"; // fallback

$sql = "SELECT filepath FROM site_logo ORDER BY date_uploaded DESC LIMIT 1";
$result = $conn->query($sql);

if ($result && $row = $result->fetch_assoc()) {
    if (!empty($row['filepath'])) {
        $logoSrc = "account/" . htmlspecialchars($row['filepath']); 
    }
}

$notifCount = 0;
$notifHTML = '';

// === USER DISAPPROVAL NOTIFICATION (needs username) ===
$excludedRoles = ['superadmin', 'Osas', 'CCSDean', 'CCSSBOVice', 'CCSSBOPresident', 'CCSSBOTreasurer', 'CCSSBOAuditor', 'CCSFaculty'];

if (isset($_SESSION['username']) && $role && !in_array($role, $excludedRoles)) {
    $username = $_SESSION['username'];
    $parts = explode('_', $username);
    $department = $parts[0] ?? '';

    $stmt = $conn->prepare("SELECT id, event_type, disapproved_by, remarks, viewed FROM proposals WHERE department = ? AND status = 'Disapproved' AND notified = 0");
    $stmt->bind_param("s", $department);
    $stmt->execute();
    $result = $stmt->get_result();

    while ($row = $result->fetch_assoc()) {
        $id = $row['id'];
        $event_type = $row['event_type'];
        $disapproved_by = $row['disapproved_by'];
        $remarks = $row['remarks'];
        $viewed = $row['viewed'];

        if ($viewed == 0) {
            $notifCount++; // count only unviewed ones
        }

        $highlightStyle = ($viewed == 0) ? "background-color: #ffeeba;" : "";

        $notifHTML .= "
            <div style='padding: 10px; border-bottom: 1px solid #ccc; $highlightStyle'>
                <b>$event_type</b><br>
                Disapproved by: <b>$disapproved_by</b><br>
                <small>Remarks:</small> <i>$remarks</i><br>
                <a href='#' onclick=\"showModal($id); return false;\" style='display:inline-block; margin-top:5px; padding:5px 10px; background-color:#007bff; color:#fff; text-decoration:none; border-radius:4px;'>View</a>
            </div>
        ";
    }
}


// === FUNCTION TO RENDER APPROVAL NOTIFICATIONS ===
function handleApproverNotifications($conn, $roleMatch, $levelFilter, $redirectUrl) {
    global $notifHTML, $notifCount;

    $stmt = $conn->prepare("SELECT id, event_type, viewed FROM proposals WHERE level = ? AND status = 'Pending'");
    $stmt->bind_param("s", $levelFilter);
    $stmt->execute();
    $stmt->store_result();
    $stmt->bind_result($id, $event_type, $viewed);

    $notifCount = 0;
    $notifHTML = '';

    while ($stmt->fetch()) {
        if (isset($_GET['viewed']) && is_numeric($_GET['viewed']) && $_GET['viewed'] == $id) {
            $conn->query("UPDATE proposals SET viewed = 1 WHERE id = $id");
            header("Location: $redirectUrl");
            exit;
        }

        if ($viewed == 0) {
            $notifCount++;
        }

        $highlightStyle = ($viewed == 0) ? "background-color: #ffeeba;" : "";

        $notifHTML .= "
            <div style='padding: 10px; border-bottom: 1px solid #ccc; $highlightStyle'>
                <b>$event_type</b><br>
                Requires your approval.<br>
                <a href='?viewed=$id' style='display:inline-block; margin-top:5px; padding:5px 10px; background-color:#007bff; color:#fff; text-decoration:none; border-radius:4px;'>Go to Approval</a>
            </div>
        ";
    }
}
// === ROLE-SPECIFIC APPROVER NOTIFICATIONS ===
switch ($role) {
    case 'CCSSBOVice':
        $stmt = $conn->prepare("SELECT id, department, event_type, budget_file, viewed FROM proposals WHERE budget_amount IS NULL AND department = 'CCS' AND status != 'Disapproved'");
        $stmt->execute();
        $stmt->store_result();
        $stmt->bind_result($id, $department, $event_type, $budget_file, $viewed);

        $notifCount = 0;
        $notifHTML = '';

        while ($stmt->fetch()) {
            if (isset($_GET['viewed']) && is_numeric($_GET['viewed']) && $_GET['viewed'] == $id) {
                $conn->query("UPDATE proposals SET viewed = 1 WHERE id = $id");
                header("Location: dashboard/ccssbovice_dashboard.php?tab=proposal");
                exit;
            }

            if ($viewed == 0) {
                $notifCount++;
            }

            $highlightStyle = ($viewed == 0) ? "background-color: #ffeeba;" : "";

            $notifHTML .= "
                <div style='padding: 10px; border-bottom: 1px solid #ccc; $highlightStyle'>
                    <b>$event_type</b><br>
                    Requires your approval.<br>
                    <a href='?viewed=$id' style='display:inline-block; margin-top:5px; padding:5px 10px; background-color:#007bff; color:#fff; text-decoration:none; border-radius:4px;'>Go to Approval</a>
                </div>
            ";
        }
        break;

    case 'CCSSBOTreasurer':
        handleApproverNotifications($conn, $role, 'CCS Treasurer', 'dashboard/ccssbotreasurer_dashboard.php?tab=proposal');
        break;

    case 'CCSSBOAuditor':
        handleApproverNotifications($conn, $role, 'CCS Auditor', 'dashboard/ccssboauditor_dashboard.php?tab=proposal');
        break;

    case 'CCSSBOPresident':
        handleApproverNotifications($conn, $role, 'CCS President', 'dashboard/ccssbopresident_dashboard.php?tab=proposal');
        break;

    case 'CCSFaculty':
        handleApproverNotifications($conn, $role, 'CCS Faculty', 'dashboard/ccsfaculty_dashboard.php?tab=proposal');
        break;

    case 'CCSDean':
        handleApproverNotifications($conn, $role, 'CCS Dean', 'dashboard/ccsdean_dashboard.php?tab=proposal');
        break;

    case 'Osas':
        handleApproverNotifications($conn, $role, 'OSAS', 'dashboard/osas.php?tab=proposal');
        break;

    // Add more roles here if needed...
}


$dashboardUrl = "#"; // default fallback

switch ($_SESSION['role'] ?? '') {
    case 'superadmin':
        $dashboardUrl = 'account/admin_dashboard.php';
        break;
    case 'Osas':
        $dashboardUrl = 'dashboard/osas.php';
        break;
    case 'CCSDean':
        $dashboardUrl = 'dashboard/ccsdean_dashboard.php';
        break;
    case 'CCSSBOVice':
        $dashboardUrl = 'dashboard/ccssbovice_dashboard.php';
        break;
    case 'CCSSBOPresident':
        $dashboardUrl = 'dashboard/ccssbopresident_dashboard.php';
        break;
    case 'CCSSBOTreasurer':
        $dashboardUrl = 'dashboard/ccssbotreasurer_dashboard.php';
        break;
    case 'CCSSBOAuditor':
        $dashboardUrl = 'dashboard/ccssboauditor_dashboard.php';
        break;
    case 'CCSFaculty':
        $dashboardUrl = 'dashboard/ccsfaculty_dashboard.php';
        break;
    default:
        if (isset($_SESSION['account_type']) && $_SESSION['account_type'] === 'department') {
            $dashboardUrl = 'dashboard/' . strtolower($_SESSION['role']) . '_dashboard.php';
        }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>EventSync</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate"> 
    <style>
        body, html { margin: 0; padding: 0; font-family: Arial, sans-serif; background: url('img/homebg2.jpg') no-repeat center center fixed; background-size: cover; }
        .navbar { display: flex; justify-content: space-between; align-items: center; background: rgba(255,255,255,0.5); padding: 15px 50px; position: sticky; top: 0; z-index: 1000; backdrop-filter: blur(10px); box-shadow: 0 4px 12px rgba(0,0,0,0.45); }
        .logo { display: flex; align-items: center; font-size: 26px; font-weight: 700; color: #222; }
        .logo img { margin-right: 10px; height: 49px; border-radius: 50%; box-shadow: 0 4px 8px rgba(0,0,0,0.3); }
        .navbar ul { list-style: none; display: flex; gap: 25px; margin: 0; padding: 0; }
        .navbar ul li a { text-decoration: none; color: #000; font-weight: 500; font-size: 16px; padding: 8px 12px; border-radius: 5px; transition: 0.3s; }
        .navbar ul li a:hover, .navbar ul li a.active { background: #007bff; color: #fff; }
        .hero { height: 90vh; position: relative; }
        .overlay { position: absolute; top: 10%; left: 10%; color: white; }
        .overlay h1 { font-size: 40px; font-weight: bold; line-height: 1.2; text-shadow: 2px 2px 6px rgba(0,0,0,0.5); }
        .welcome-line { font-size: 50px; letter-spacing: 3px; }
        .brand-line { font-size: 130px; font-weight: 800; }
        .buttons { margin-top: 20px; }
        .btn { padding: 10px 20px; text-decoration: none; border-radius: 5px; margin-right: 10px; font-weight: bold; }
        .btn.propose { background: #555; color: white; }
        .btn.read { background: blue; color: white; }

        .user-dropdown { position: relative; display: inline-block; margin-left: 20px; cursor: pointer; }
        .dropdown-menu { display: none; position: absolute; background: white; box-shadow: 0 4px 8px rgba(0,0,0,0.1); right: 0; margin-top: 10px; border-radius: 5px; z-index: 100; }
        .dropdown-menu a { display: block; padding: 10px; text-decoration: none; color: #333; }
        .dropdown-menu a:hover { background: #f0f0f0; }
        .user-dropdown i {font-size: 20px; color: #333; transition: color 0.3s;}
        .user-dropdown i:hover {color: #007bff;}
        .modal { display: none; position: fixed; z-index: 999; left: 0; top: 8%; width: 100%; height: 100%; overflow: auto; background-color: rgba(0,0,0,0.6); }
        .modal-content { background-color: #fff; margin: 2% auto; padding: 30px; border-radius: 10px; width: 95%; height: 75%; box-shadow: 0 5px 15px rgba(0,0,0,0.3); }
        .close-button { float: right; font-size: 28px; cursor: pointer; }

.notif-icon { font-size: 20px; color: #333; transition: color 0.3s; cursor: pointer; margin-left: 15px; }
.notif-icon:hover {color: #007bff;}
.notif-badge { position: absolute;  background: red; color: white; border-radius: 50%; font-size: 12px; padding: 2px 6px; }
#notifModal { display: none; position: fixed; z-index: 1000; top: 60px; right: 20px; background: white; border-radius: 10px; box-shadow: 0 5px 15px rgba(0,0,0,0.3); width: 300px; max-height: 400px; overflow-y: auto; }
    </style>
</head>
<body>
    <header class="navbar">
    <div class="logo">
    <img src="<?php echo $logoSrc; ?>" alt="Logo" style="height:49px; border-radius:50%; box-shadow:0 4px 8px rgba(0,0,0,0.3);">
    Event<span style="color:blue;">Sync</span></div>
        <nav>
            <ul>
                <li><a href="#" class="active">Home</a></li>
                <li><a href="aboutus.php">About Us</a></li>
                <li><a href="calendar1.php">Calendar</a></li>
                <?php if (isset($_SESSION['admin_logged_in']) || isset($_SESSION['role'])): ?>
                    <li>
                        <div class="admin-info">

                        <?php if (isset($_SESSION['fullname'])): ?>
                            <span><?php echo htmlspecialchars($_SESSION['fullname']); ?></span>
                        <?php endif; ?>
                        <?php if (isset($_SESSION['role'])): ?>
                            <span><?php echo htmlspecialchars($_SESSION['role']); ?></span>
                        <?php endif; ?>
                            <div class="user-dropdown" id="userDropdown">
                                <i class="fa-solid fa-user dropdown-toggle" onclick="toggleDropdown()"></i>
                                <div class="dropdown-menu" id="dropdownMenu">
                                <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'superadmin'): ?>
                                    <a href="account/admin_dashboard.php">Admin Dashboard</a>
                                <?php endif; ?>
                                <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'Osas'): ?>
                                    <a href="dashboard/osas.php">Osas Dashboard</a>
                                <?php endif; ?>
                                <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'CCSDean'): ?>
                                    <a href="dashboard/ccsdean_dashboard.php">CCS Dean Dashboard</a>
                                <?php endif; ?>
                                <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'CCSSBOVice'): ?>
                                    <a href="dashboard/ccssbovice_dashboard.php">CCS SBO Vice Dashboard</a>
                                <?php endif; ?>
                                <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'CCSSBOPresident'): ?>
                                    <a href="dashboard/ccssbopresident_dashboard.php">CCS SBO President Dashboard</a>
                                <?php endif; ?>
                                <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'CCSSBOTreasurer'): ?>
                                    <a href="dashboard/ccssbotreasurer_dashboard.php">CCS SBO Treasurer Dashboard</a>
                                <?php endif; ?>
                                <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'CCSSBOAuditor'): ?>
                                    <a href="dashboard/ccssboauditor_dashboard.php">CCS SBO Auditor Dashboard</a>
                                <?php endif; ?>
                                <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'CCSFaculty'): ?>
                                    <a href="dashboard/ccsfaculty_dashboard.php">CCS Faculty Dashboard</a>
                                <?php endif; ?>
                                    <a href="account/logout.php">Logout</a>
                                </div>
                            </div>
                            <span class="notif-icon" onclick="toggleNotif()">
                                <i class="fas fa-bell"></i>
                                <?php if ($notifCount > 0): ?>
                                    <span class="notif-badge"><?php echo $notifCount; ?></span>
                                <?php endif; ?>
                            </span>
                        </div>
                    </li>
                <?php else: ?>
                    <li><a href="account/login.php">Sign In</a></li>
                <?php endif; ?>
            </ul>
        </nav>
    </header>

    <section class="hero">
        <div class="overlay">
            <h1><span class="welcome-line">WELCOME TO</span><br><span class="brand-line"><span style="color:black;">Event</span><span style="color:blue;">Sync</span></span></h1>
                <p>LET'S START A PLAN</p>
                <div class="buttons">
                    <a href="<?php echo $dashboardUrl; ?>" class="btn read">Go to Dashboard</a>
                </div>
        </div>
    </section>

    <!-- Proposal Modal -->
    <div id="proposeModal" class="modal">
        <div class="modal-content">
            <span class="close-button" onclick="closeModal()">&times;</span>
            <b>PROPOSE A PLAN</b>
            <iframe src="proposal/proposal.php" frameborder="0" style="width:100%; height:90%;"></iframe>
        </div>
    </div>

    <!-- Notification Modal -->
    <div id="notifModal">
        <?php echo $notifHTML ?: "<div style='padding:10px;'>No notification.</div>"; ?>
    </div>
<!-- Modal -->
<div id="detailsModal" style="display: none; position: fixed; top: 50%; left: 50%; transform: translate(-50%, -50%); background: #ffffff; padding: 30px; border-radius: 10px; width: 420px; max-width: 90%; z-index: 9999; box-shadow: 0 10px 25px rgba(0, 0, 0, 0.2); font-family: 'Segoe UI', sans-serif;">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
        <h3 style="margin: 0; font-size: 20px;">📄 Proposal Details</h3>
        <button onclick="closeModal()" style="background: none; border: none; font-size: 20px; cursor: pointer; color: #999;">&times;</button>
    </div>
    <div id="modalContent" style="font-size: 15px; color: #333;">
        Loading...
    </div>
    <div style="text-align: right; margin-top: 20px;">
        <button onclick="closeModal()" style="padding: 8px 16px; background-color: #007bff; color: white; border: none; border-radius: 5px; cursor: pointer; font-size: 14px;">Close</button>
    </div>
</div>

<!-- Overlay -->
<div id="modalOverlay" onclick="closeModal()" style="display: none; position: fixed; top: 0; left: 0; width: 100vw; height: 100vh; background: rgba(0, 0, 0, 0.4); z-index: 9998;"></div>


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

document.querySelector('.btn.propose')?.addEventListener('click', function(e) {
    e.preventDefault();
    document.getElementById('proposeModal').style.display = 'block';
});
function closeModal() {
    document.getElementById('proposeModal').style.display = 'none';
}
window.onclick = function(event) {
    const modal = document.getElementById('proposeModal');
    if (event.target === modal) {
        modal.style.display = "none";
    }
}

// Notification modal toggle
function toggleNotif() {
    const notifModal = document.getElementById("notifModal");
    notifModal.style.display = notifModal.style.display === "block" ? "none" : "block";
}
</script>
<script>
function showModal(id) {
    document.getElementById('detailsModal').style.display = 'block';
    document.getElementById('modalOverlay').style.display = 'block';

    const modalContent = document.getElementById('modalContent');
    modalContent.innerHTML = "Loading...";

    fetch(`get_proposal_details.php?id=${id}`)
        .then(res => res.text())
        .then(data => {
            modalContent.innerHTML = data;
        })
        .catch(() => {
            modalContent.innerHTML = "Failed to load details.";
        });
}

function closeModal() {
    document.getElementById('detailsModal').style.display = 'none';
    document.getElementById('modalOverlay').style.display = 'none';
}
</script>

</body>
</html>
