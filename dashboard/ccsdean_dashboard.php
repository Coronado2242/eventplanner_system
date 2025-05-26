<?php
session_start();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Dashboard</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
</head>
<style>
    body {
    margin: 0;
    font-family: Arial, sans-serif;
}

.topbar {
    background: #ccc;
    padding: 10px 20px;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.topbar .logo {
    font-weight: bold;
    font-size: 24px;
}

.topbar nav a {
    margin: 0 15px;
    text-decoration: none;
    color: black;
    font-weight: bold;
}

.admin-info {
    display: inline-block;
    margin-left: 20px;
}

.sidebar {
    width: 220px;
    background: #004080;
    position: fixed;
    top: 47px;
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

</style>
<body>

<header class="topbar">
    <div class="logo">EVENT ADMIN PORTAL</div>
    <nav>
        <a href="../index.php">Home</a>
        <a href="../aboutus.php">About Us</a>
        <a href="../calendar1.php">Calendar</a>
        <div class="admin-info">
            <i class="icon-calendar"></i>
            <i class="icon-bell"></i>
            <span><?php echo htmlspecialchars($_SESSION['role']); ?></span>

            <!-- User Dropdown -->
            <div class="user-dropdown" id="userDropdown">
                <i class="fa-solid fa-user dropdown-toggle" onclick="toggleDropdown()"></i>
                <div class="dropdown-menu" id="dropdownMenu">
                    <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'superadmin'): ?>
                        <a href="admin_dashboard.php">Admin Dashboard</a>
                    <?php endif; ?>
                    <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'CCSDean'): ?>
                        <a href="ccsdean_dashboard.php">CCS Dean Dashboard</a>
                    <?php endif; ?>
                    <a href="../account/logout.php">Logout</a>
                </div>
            </div>
        </div>
    </nav>
</header>

<aside class="sidebar">
    <div class="toggle-btn">&#9776;</div>
    <ul>
        <li id="dashboardTab" class="active"><i class="fa fa-home"></i> <span class="menu-text">Dashboard</span></li>
        <li id="approvalTab"><i class="fa fa-check-circle"></i> <span class="menu-text">Approval</span></li>
        <li id="requirementTab"><i class="fa fa-building"></i> <span class="menu-text">Requirements</span></li>
    </ul>
</aside>

<!-- Dashboard Content -->
<div id="dashboardContent">
<main class="content">
    <h1>CCS Dean Dashboard</h1>
    <p>Welcome back! Here's what's happening today.</p>

    <iframe id="calendarFrame" style="width:100%; height:600px; border:none;"></iframe>

</main>
</div>

<!-- User Management Content -->
<div id="approvalContent" style="display:none;">
    <main class="content" >
        <h1 style="margin-bottom: 0;">Request Approval</h1>
    <iframe id="approvalFrame" style="width:100%; height:600px; border:none;"></iframe>
    </main>
</div>


<!-- Venue Content -->
<div id="requirementContent" style="display:none;">
    <main class="content" >
        <h1 style="margin-bottom: 0;">Requirements</h1>


    </main>
</div>


<!-- Tab Switching & User Fetching Script -->
<script>
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

document.addEventListener("DOMContentLoaded", function () {
        document.getElementById("approvalFrame").src = "../request/forapproval.php";
    });

    document.querySelector(".toggle-btn").addEventListener("click", function () {
    const sidebar = document.querySelector(".sidebar");
    sidebar.classList.toggle("collapsed");
});
</script>


</body>
</html>
