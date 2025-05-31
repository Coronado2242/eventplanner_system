<?php
session_start();

$host = "localhost";
$user = "root";
$pass = "";
$db   = "eventplanner";

$conn = mysqli_connect($host, $user, $pass, $db);

if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}
?><!DOCTYPE html><html lang="en">
<head>
    <meta charset="UTF-8">
    <title>VP Portal</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
    <link rel="stylesheet" href="vp_dashboard.css">
</head>
<body>
<header class="topbar">
    <div class="logo"><img src="../img/lspulogo.jpg">VP PORTAL</div>
    <div class="hamburger" onclick="toggleMobileNav()">&#9776;</div>
    <nav id="mainNav">
        <a href="../index.php">Home</a>
        <a href="../aboutus.php">About Us</a>
        <a href="../calendar1.php">Calendar</a>
        <div class="admin-info">
            <span><?php echo htmlspecialchars($_SESSION['role']); ?></span>
            <div class="user-dropdown" id="userDropdown">
                <i class="fa-solid fa-user dropdown-toggle" onclick="toggleDropdown()"></i>
                <div class="dropdown-menu" id="dropdownMenu">
                    <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'vp'): ?>
                        <a href="vp_dashboard.php">VP Dashboard</a>
                    <?php endif; ?>
                    <a href="../account/logout.php">Logout</a>
                </div>
            </div>
        </div>
    </nav>
</header><aside class="sidebar">
    <ul>
        <li id="dashboardTab" class="active"><i class="fa fa-home"></i> <span class="menu-text">Dashboard</span></li>
        <li id="approvalTab"><i class="fa fa-check-circle"></i> <span class="menu-text">Approvals</span></li>
        <li id="requirementTab"><i class="fa fa-file"></i> <span class="menu-text">Requirements</span></li>
    </ul>
</aside><div id="dashboardContent">
    <main class="content">
        <h1>VP Dashboard</h1>
        <p>Welcome, Vice President! Here's the status of event proposals.</p>
        <iframe id="calendarFrame" src="../calendar1.php" style="width:100%; height:600px; border:none;"></iframe>
    </main>
</div><div id="approvalContent" style="display:none;">
    <main class="content">
        <h1>Pending Approvals</h1>
        <?php
        $sql = "SELECT * FROM proposals WHERE current_level = 'vp' AND status = 'Pending'";
        $result = mysqli_query($conn, $sql);while ($row = mysqli_fetch_assoc($result)) {
        echo '<div class="proposal-card">';
        echo '<h2>' . htmlspecialchars($row['event_type']) . '</h2>';
        echo '<p><strong>Department:</strong> ' . htmlspecialchars($row['department']) . '</p>';
        echo '<p><strong>Date:</strong> ' . $row['start_date'] . ' to ' . $row['end_date'] . '</p>';
        echo '<p><strong>Venue:</strong> ' . htmlspecialchars($row['venue']) . '</p>';
        echo '<form method="POST" action="process_flow_proposals.php">';
        echo '<input type="hidden" name="proposal_id" value="' . $row['id'] . '">';
        echo '<button name="action" value="approve" class="btn-approve">Approve</button>';

