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

    $stmt = $conn->prepare("SELECT id, event_type, disapproved_by, remarks FROM proposals WHERE department = ? AND status = 'Disapproved' AND notified = 0");
    $stmt->bind_param("s", $department);
    $stmt->execute();
    $stmt->store_result();
    $notifCount = $stmt->num_rows;

    if ($notifCount > 0) {
        $stmt->bind_result($id, $event_type, $disapproved_by, $remarks);
        while ($stmt->fetch()) {
            $notifHTML .= "
                <div style='padding: 10px; border-bottom: 1px solid #ccc;'>
                    <b>$event_type</b><br>
                    Disapproved by: <b>$disapproved_by</b><br>
                    <small>Remarks:</small> <i>$remarks</i>
                </div>
            ";
        }
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
?>



<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Contact Us - Event Admin Portal</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate"> 
</head>
<style>
body, html {
    margin: 0;
    padding: 0;
    font-family: Arial, sans-serif;
    height: 100%;
    background: url('img/homebg2.jpg') no-repeat center center fixed;
    background-size: cover;
    position: relative;
}

.calendar-container {
    flex: 1;
    display: flex;
    flex-direction: column;
    height: 100vh;
    position: relative;
    z-index: 1;
}

.calendar-wrapper {
    flex: 1;
    width: 100%;
    height: 100%;
    backdrop-filter: blur(1px);
    background-color: rgba(255, 255, 255, 0.2);
    position: relative;
    z-index: 10;
    display: flex;
    -webkit-backdrop-filter: blur(10px);

}

#calendarFrame {
    flex: 1;
    width: 100%;
    height: 100%;
    border: none;
    z-index: 11;
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

.navbar {
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

.navbar .logo {
    display: flex;
    align-items: center;
    font-size: 26px;
    font-weight: 700;
    color: #222;
}

.navbar .logo span {
    color: #007bff;
}

.navbar ul {
    list-style: none;
    display: flex;
    margin: 0;
    padding: 0;
    gap: 25px;
}

.navbar ul li a {
    text-decoration: none;
    color: #000;
    font-weight: 500;
    font-size: 16px;
    padding: 8px 12px;
    border-radius: 5px;
    transition: background 0.3s, color 0.3s;
}

.navbar ul li a:hover,
.navbar ul li a.active {
    background-color: #007bff;
    color: white;
}

.user-dropdown i {
    font-size: 20px;
    color: #333;
    transition: color 0.3s;
}

.user-dropdown i:hover {color: #007bff;}
.dropdown-menu { display: none; position: absolute; background-color: white; box-shadow: 0 4px 8px rgba(0,0,0,0.1); right: 0; margin-top: 10px; border-radius: 5px; z-index: 100;}
.dropdown-menu a {display: block; padding: 10px; text-decoration: none; color: #333;}
.dropdown-menu a:hover {background-color: #f0f0f0;}
.user-dropdown {position: relative; display: inline-block; margin-left: 20px; cursor: pointer;}
.fa-user {font-size: 18px;}
.navbar {flex-shrink: 0;}
.notif-icon { font-size: 20px; color: #333; transition: color 0.3s; cursor: pointer; margin-left: 15px; }
.notif-icon:hover {color: #007bff;}
.notif-badge { position: absolute;  background: red; color: white; border-radius: 50%; font-size: 12px; padding: 2px 6px; }
#notifModal { display: none; position: fixed; z-index: 1000; top: 60px; right: 20px; background: white; border-radius: 10px; box-shadow: 0 5px 15px rgba(0,0,0,0.3); width: 300px; max-height: 400px; overflow-y: auto; }
</style>
<body>
<header class="navbar">
    <div class="logo">
    <img src="<?php echo $logoSrc; ?>" alt="Logo" style="height:49px; border-radius:50%; box-shadow:0 4px 8px rgba(0,0,0,0.3);">
    Event<span style="color:blue;">Sync</span></div>
        <nav>
            <ul>
                <li><a href="index.php">Home</a></li>
                <li><a href="aboutus.php">About Us</a></li>
                <li><a href="calendar1.php" class="active">Calendar</a></li>
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
    <main class="calendar-container">
        <div class="calendar-wrapper">
            <iframe id="calendarFrame"></iframe>
        </div>
    </main>
    <!-- Notification Modal -->
    <div id="notifModal">
        <?php echo $notifHTML ?: "<div style='padding:10px;'>No notification.</div>"; ?>
    </div>

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
        document.getElementById("calendarFrame").src = "proposal/calendar.php";
    });

    // Notification modal toggle
function toggleNotif() {
    const notifModal = document.getElementById("notifModal");
    notifModal.style.display = notifModal.style.display === "block" ? "none" : "block";
}

function markAllNotificationsViewed() {
    fetch('mark_all_viewed.php')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Reset notif count in UI
                document.getElementById('notifCount').textContent = '0';
            }
        });
}

// Example: Call this function when dropdown opens
document.getElementById('notifBell').addEventListener('click', function () {
    markAllNotificationsViewed();
});
</script>

</body>
</html>