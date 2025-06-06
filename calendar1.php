<?php
session_start();
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

.user-dropdown i:hover {
    color: #007bff;
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

.navbar {
    flex-shrink: 0;
}

</style>
<body>
    <header class="navbar">
        <div class="logo"><img src="img/lspulogo.jpg">Event<span style="color:blue;">Sync</span></div>
        <nav>
            <ul>
                <li><a href="index.php">Home</a></li>
                <li><a href="aboutus.php">About Us</a></li>
                <li><a href="#" class="active">Calendar</a></li>
                <!-- Only show user dropdown if admin or client is logged in -->
                <?php if (isset($_SESSION['admin_logged_in']) || isset($_SESSION['role'])): ?>
                    <li>
            <div class="admin-info">
                <i class="icon-calendar"></i>
                <i class="icon-bell"></i>

                <!-- Display role if set -->
                <?php if (isset($_SESSION['fullname'])): ?>
                    <span><?php echo htmlspecialchars($_SESSION['fullname']); ?></span>
                <?php endif; ?>
                <?php if (isset($_SESSION['role'])): ?>
                    <span><?php echo htmlspecialchars($_SESSION['role']); ?></span>
                <?php endif; ?>

                <!-- User Dropdown -->
                <div class="user-dropdown" id="userDropdown">
                    <i class="fa-solid fa-user dropdown-toggle" onclick="toggleDropdown()"></i>
                    <div class="dropdown-menu" id="dropdownMenu">
                        <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'superadmin'): ?>
                            <a href="account/admin_dashboard.php">Admin Dashboard</a>
                        <?php endif; ?>
                        <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'CCSDean'): ?>
                            <a href="dashboard/ccsdean_dashboard.php">CCS Dean Dashboard</a>
                        <?php endif; ?>
                        <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'CCSFaculty'): ?>
                            <a href="dashboard/ccsfaculty_dashboard.php">CCS Faculty Dashboard</a>
                        <?php endif; ?>
                        <a href="account/logout.php">Logout</a>
                    </div>
                </div>
            </div>
        </li>

                <?php else: ?>
                    <!-- Show sign in only if no one is logged in -->
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
</script>

</body>
</html>