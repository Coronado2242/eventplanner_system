<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>About Us - Event Admin Portal</title>
    <link rel="stylesheet" href="style/aboutus.css">
    <link rel="stylesheet" href="style/adminstyle.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate"> 
</head>
<body>

<header class="navbar">
        <div class="logo">Event<span style="color:blue;">Sync</span></div>
        <nav>
            <ul>
                <li><a href="index.php">Home</a></li>
                <li><a href="contactus.php">Contact Us</a></li>
                <li><a href="aboutus.php" class="active">About Us</a></li>
                <li><a href="calendar1.php">Calendar</a></li>
                <!-- Only show user dropdown if admin or client is logged in -->
                <?php if (isset($_SESSION['admin_logged_in']) || isset($_SESSION['client_logged_in'])): ?>
                    <li>
            <div class="admin-info">
                <i class="icon-calendar"></i>
                <i class="icon-bell"></i>

                <!-- Display role if set -->
                <?php if (isset($_SESSION['role'])): ?>
                    <span><?php echo htmlspecialchars($_SESSION['role']); ?></span>
                <?php endif; ?>

                <!-- Display client email if set -->
                <?php if (isset($_SESSION['client_email'])): ?>
                    <span><?php echo htmlspecialchars($_SESSION['client_email']); ?></span>
                <?php endif; ?>

                <!-- User Dropdown -->
                <div class="user-dropdown" id="userDropdown">
                    <i class="fa-solid fa-user dropdown-toggle" onclick="toggleDropdown()"></i>
                    <div class="dropdown-menu" id="dropdownMenu">
                        <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'superadmin'): ?>
                            <a href="account/admin_dashboard.php">Admin Dashboard</a>
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

<div class="container">
    <h1>ABOUT US</h1>
    <p>We are a team of three passionate student administrators from the Service Management program, united by a shared goal: to make event planning easier, smarter, and more accessible. As part of our capstone project, we developed a user-friendly web-based event planner designed to help clients organize and manage their events with ease.</p>
    <p>Our platform offers tools that simplify the planning process from scheduling and guest management to tracking tasks and sending updates. Whether it's a small gathering or a large corporate event, our system is built to support every step of the journey.</p>

    <h2>How can we help?</h2>
    <div class="contact">
        <p>Our service team is available 7 days a week:<br>
        Monday - Sunday | 8:00 AM to 5:00 PM</p>
        <p>
            <a href="tel:0987654321">0987654321</a> / 
            <a href="tel:09123456789">09123456789</a><br>
            <a href="mailto:ask@eventplaner.ph">ask@eventplaner.ph</a>
        </p>
    </div>

    <div class="team">
        <div class="member">
            <img src="alyssa.jpg" alt="Alyssa Rubie Caguin">
            <p>CAGUIN, ALYSSA RUBIE M.</p>
        </div>
        <div class="member">
            <img src="ranzel.jpg" alt="Ranzel B. Facundo">
            <p>FACUNDO, RANZEL B.</p>
        </div>
        <div class="member">
            <img src="james.jpg" alt="James Leorix Magnaye">
            <p>MAGNAYE, JAMES LEORIX M.</p>
        </div>
    </div>
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
</script>
</body>
</html>
