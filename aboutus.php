<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>About Us - Event Admin Portal</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate"> 
</head>
<style>
body {
    margin: 0;
    padding: 0;
    font-family: Arial, sans-serif;
    height: 100%;
    background: url('img/homebg1.jpg') no-repeat center center fixed;
    background-size: cover;
}

.navbar {
    display: flex;
    justify-content: space-between;
    align-items: center;
    background: linear-gradient(#ddd, #999);
    padding: 10px 50px;
}

.navbar .logo {
    font-size: 24px;
    font-weight: bold;
}

.navbar ul {
    list-style: none;
    display: flex;
    margin: 0;
    padding: 0;
}

.navbar ul li {
    margin-left: 20px;
}

.navbar ul li a {
    text-decoration: none;
    color: black;
    font-weight: bold;
}

.navbar ul li a.active {
    color: maroon;
}

.hero {
    background: url('your-background-image.png') no-repeat center center/cover;
    height: 90vh;
    position: relative;
}

.overlay {
    position: absolute;
    top: 30%;
    left: 10%;
    color: white;
}

.overlay h1 {
    font-size: 40px;
    font-weight: bold;
}

.overlay p {
    margin-top: 10px;
    font-size: 20px;
    font-weight: bold;
}

.buttons {
    margin-top: 20px;
}

.btn {
    padding: 10px 20px;
    text-decoration: none;
    border-radius: 5px;
    margin-right: 10px;
    font-weight: bold;
}

.btn.propose {
    background-color: #555;
    color: white;
}

.btn.read {
    background-color: maroon;
    color: white;
}

.container {
    background-color: rgba(238, 238, 238, 0.8); /* #eee with 80% opacity */
    margin: 50px auto;
    padding: 30px;
    width: 80%;
    border-radius: 10px;
    text-align: center;
}

h1 {
    margin-bottom: 20px;
    text-shadow: 1px 1px 2px #000;
}

.team {
    display: flex;
    justify-content: center;
    gap: 30px;
    margin-top: 30px;
}

.member {
    text-align: center;
}

.member img {
    width: 150px;
    height: 150px;
    border-radius: 20px;
    object-fit: cover;
}

.contact a {
    color: black;
    text-decoration: underline;
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

</style>

<body>

<header class="navbar">
        <div class="logo">Event<span style="color:blue;">Sync</span></div>
        <nav>
            <ul>
                <li><a href="index.php">Home</a></li>
                <li><a href="aboutus.php" class="active">About Us</a></li>
                <li><a href="calendar1.php">Calendar</a></li>
                <!-- Only show user dropdown if admin or client is logged in -->
                <?php if (isset($_SESSION['admin_logged_in']) || isset($_SESSION['role'])): ?>
                    <li>
            <div class="admin-info">
                <i class="icon-calendar"></i>
                <i class="icon-bell"></i>

                <!-- Display role if set -->
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
            <img src="img/alyssa.jpg" alt="Alyssa Rubie Caguin">
            <p>CAGUIN, ALYSSA RUBIE M.</p>
        </div>
        <div class="member">
            <img src="img/ranzel.jpg" alt="Ranzel B. Facundo">
            <p>FACUNDO, RANZEL B.</p>
        </div>
        <div class="member">
            <img src="img/james.jpeg" alt="James Leorix Magnaye">
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
