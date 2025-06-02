<?php
session_start();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>EventSync</title>
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

.hero {
    height: 90vh;
    position: relative;
}

.overlay {
    position: absolute;
    top: 10%;
    left: 10%;
    color: white;
}

.overlay h1 {
    font-size: 40px;
    font-weight: bold;
    line-height: 1.2;
    text-shadow: 2px 2px 6px rgba(0,0,0,0.5);
}

.welcome-line {
    font-size: 50px;
    letter-spacing: 3px;
}

.brand-line {
    font-size: 130px;
    font-weight: 800;
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
    background-color: blue;
    color: white;
}

.section-title {
    text-align: center;
    margin-top: 30px;
    font-size: 32px;
    font-weight: bold;
    color: black;
    text-shadow: 2px 2px 3px rgba(0,0,0,0.3);
}

/* Contact Cards */
.contact-section {
    padding: 40px;
    text-align: center;
}

.contact-cards {
    display: flex;
    justify-content: center;
    gap: 50px;
    margin: 30px 0;
}

.contact-card {
    background: white;
    padding: 20px;
    border-radius: 10px;
    width: 300px;
    box-shadow: 0 4px 8px rgba(0,0,0,0.1);
}

.contact-card img {
    margin-bottom: 10px;
}

.chat-button {
    display: inline-block;
    margin-top: 10px;
    padding: 10px 20px;
    background-color: #003399;
    color: white;
    text-decoration: none;
    font-weight: bold;
    border-radius: 8px;
}

/* Help Section */
.help-section {
    margin-top: 50px;
}

.help-section h2 {
    font-size: 24px;
    margin-bottom: 10px;
}

.help-section p {
    margin-top: 5px;
    font-size: 16px;
}

.help-section a {
    color: #003399;
    text-decoration: none;
    font-weight: bold;
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

.modal {
  display: none;
  position: fixed;
  z-index: 999;
  left: 0; top: 8%;
  width: 100%; height: 100%;
  overflow: auto;
  background-color: rgba(0, 0, 0, 0.6);
}

.modal-content {
  background-color: #fff;
  margin: 2% auto;
  padding: 30px;
  border-radius: 10px;
  width: 95%;
  height: 75%;
  box-shadow: 0 5px 15px rgba(0,0,0,0.3);
}


.close-button {
  color: #aaa;
  float: right;
  font-size: 28px;
  font-weight: bold;
  cursor: pointer;
}
.close-button:hover {
  color: #000;
}

</style>
<body>
    <header class="navbar">
        <div class="logo"><img src="img/lspulogo.jpg">Event<span style="color:blue;">Sync</span></div>
        <nav>
    <ul>
        <li><a href="#" class="active">Home</a></li>
        <li><a href="aboutus.php">About Us</a></li>
        <li><a href="calendar1.php">Calendar</a></li>
        
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
                <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'CCSVice'): ?>
                    <a href="dashboard/ccsvice_dashboard.php">CCS SBO Vice Dashboard</a>
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
            <li><a href="account/login.php">Sign In</a></li>
        <?php endif; ?>
    </ul>
</nav>

    </header>

    <section class="hero">
        <div class="overlay">
            <h1>
                <span class="welcome-line">WELCOME TO</span><br>
                <span class="brand-line"><span style="color:black;">Event</span><span style="color:blue;">Sync</span></span>
            </h1>
 <?php
$role = isset($_SESSION['role']) ? strtolower($_SESSION['role']) : '';
if (substr($role, -3) === 'soo'):
?>
    <p>LET'S START A PLAN</p>
    <div class="buttons">
        <a href="#" class="btn propose">PROPOSE PLAN</a>
        <a href="#" class="btn read">Read more</a>
    </div>
<?php endif; ?>

        </div>
    </section>

        <!-- Modal -->
        <div id="proposeModal" class="modal">
      <div class="modal-content">
        <span class="close-button" onclick="closeModal()">&times;</span><b>PROPOSE A PLAN</b>
        <iframe src="proposal/proposal.php" frameborder="0" style="width:100%; height:100%;"></iframe>
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
<script>
document.querySelector('.btn.propose').addEventListener('click', function(e) {
    e.preventDefault(); // Prevent link behavior
    document.getElementById('proposeModal').style.display = 'block';
});

function closeModal() {
    document.getElementById('proposeModal').style.display = 'none';
}

// Optional: close modal when clicking outside content
window.onclick = function(event) {
    const modal = document.getElementById('proposeModal');
    if (event.target === modal) {
        modal.style.display = "none";
    }
}
</script>

</body>
</html>
