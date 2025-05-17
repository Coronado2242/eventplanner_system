<?php
session_start();
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Contact Us - Event Admin Portal</title>
    <link rel="stylesheet" href="style/adminstyle.css"> <!-- Link to your CSS -->
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
                <li><a href="contactus.php" class="active">Contact Us</a></li>
                <li><a href="aboutus.php">About Us</a></li>
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

    <section class="contact-section">
        <h1 class="section-title">CONTACT US</h1>
        <div class="contact-cards">
            <div class="contact-card">
                <img src="images/phone-icon.png" alt="Phone Icon" style="width: 100px;">
                <h2>Call us directly at</h2>
                <p style="font-size: 24px; font-weight: bold; color: #003399;">+0912345678</p>
            </div>
            <div class="contact-card">
                <img src="images/chat-icon.png" alt="Chat Icon" style="width: 100px;">
                <h2>Chat with our team</h2>
                <a href="#" class="chat-button">CHAT WITH TEAM</a>
            </div>
        </div>

        <div class="help-section">
            <h2>How can we help?</h2>
            <p>Our service team is available 7 days a week:<br>
                Monday - Sunday | 8:00 AM to 5:00 PM</p>
            <p>
                <a href="tel:0987654321">0987654321</a> / 
                <a href="tel:09123456789">09123456789</a> | 
                <a href="mailto:ask@eventplaner.ph">ask@eventplaner.ph</a>
            </p>
        </div>
    </section>
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
