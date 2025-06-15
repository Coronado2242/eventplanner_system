<?php
session_start();
$conn = new mysqli("localhost", "root", "", "eventplanner");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Extract department from username
$notifCount = 0;
$notifHTML = '';
if (isset($_SESSION['username'])) {
    $username = $_SESSION['username'];
    $parts = explode('_', $username);
    $department = $parts[0] ?? '';

    $stmt = $conn->prepare("SELECT id, disapproved_by, remarks FROM proposals WHERE department = ? AND status = 'Disapproved' AND notified = 0");
    $stmt->bind_param("s", $department);
    $stmt->execute();
    $stmt->store_result();
    $notifCount = $stmt->num_rows;

    if ($notifCount > 0) {
        $stmt->bind_result($proposal_id, $disapproved_by, $remarks);
        while ($stmt->fetch()) {
            $notifHTML .= "
                <div style='padding: 10px; border-bottom: 1px solid #ccc;'>
                    <b>Proposal #$proposal_id</b><br>
                    Disapproved by: <b>$disapproved_by</b><br>
                    <small>Remarks:</small> <i>$remarks</i>
                </div>
            ";
        }
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

        .modal { display: none; position: fixed; z-index: 999; left: 0; top: 8%; width: 100%; height: 100%; overflow: auto; background-color: rgba(0,0,0,0.6); }
        .modal-content { background-color: #fff; margin: 2% auto; padding: 30px; border-radius: 10px; width: 95%; height: 75%; box-shadow: 0 5px 15px rgba(0,0,0,0.3); }
        .close-button { float: right; font-size: 28px; cursor: pointer; }

        .notif-icon { position: relative; cursor: pointer; margin-right: 15px; }
        .notif-badge { position: absolute; top: -8px; right: -10px; background: red; color: white; border-radius: 50%; font-size: 12px; padding: 2px 6px; }
        #notifModal { display: none; position: fixed; z-index: 1000; top: 60px; right: 20px; background: white; border-radius: 10px; box-shadow: 0 5px 15px rgba(0,0,0,0.3); width: 300px; max-height: 400px; overflow-y: auto; }
    </style>
</head>
<body>
    <header class="navbar">
        <div class="logo"><img src="img/lspulogo.jpg">Event<span style="color:blue;">Sync</span></div>
        <nav>
            <ul>
                <li><a href="#" class="active">Home</a></li>
                <li><a href="aboutus.php">About Us</a></li>
                <li><a href="calendar1.php">Calendar</a></li>
                <?php if (isset($_SESSION['admin_logged_in']) || isset($_SESSION['role'])): ?>
                    <li>
                        <div class="admin-info">
                            <span class="notif-icon" onclick="toggleNotif()">
                                <i class="fas fa-bell"></i>
                                <?php if ($notifCount > 0): ?>
                                    <span class="notif-badge"><?php echo $notifCount; ?></span>
                                <?php endif; ?>
                            </span>
                            <?php if (isset($_SESSION['fullname'])): ?>
                                <span><?php echo htmlspecialchars($_SESSION['fullname']); ?></span>
                            <?php endif; ?>
                            <div class="user-dropdown" id="userDropdown">
                                <i class="fa-solid fa-user dropdown-toggle" onclick="toggleDropdown()"></i>
                                <div class="dropdown-menu" id="dropdownMenu">
                                    <?php
                                        $role = $_SESSION['role'] ?? '';
                                        $dashboards = [
                                            'superadmin' => 'admin_dashboard',
                                            'Osas' => 'osas',
                                            'CCSDean' => 'ccsdean_dashboard',
                                            'CCSSBOVice' => 'ccssbovice_dashboard',
                                            'CCSSBOPresident' => 'ccssbopresident_dashboard',
                                            'CCSSBOTreasurer' => 'ccssbotreasurer_dashboard',
                                            'CCSSBOAuditor' => 'ccssboauditor_dashboard',
                                            'CCSFaculty' => 'ccsfaculty_dashboard',
                                        ];
                                        if (isset($dashboards[$role])) {
                                            echo '<a href="dashboard/' . $dashboards[$role] . '.php">' . $role . ' Dashboard</a>';
                                        }
                                    ?>
                                    <a href="account/logout.php">Logout</a>
                                </div>
                            </div>
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
            <?php
            $role = $_SESSION['role'] ?? '';
            if (str_ends_with(strtolower($role), 'soo')):
            ?>
                <p>LET'S START A PLAN</p>
                <div class="buttons">
                    <a href="#" class="btn propose">PROPOSE PLAN</a>
                    <a href="#" class="btn read">Read more</a>
                </div>
            <?php endif; ?>
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
        <?php echo $notifHTML ?: "<div style='padding:10px;'>No disapproved proposals.</div>"; ?>
    </div>

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
</body>
</html>
