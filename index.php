<?php
session_start();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>EventSync</title>
    <link rel="stylesheet" href="style/adminstyle.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate"> 
</head>
<style>
.modal {
  display: none;
  position: fixed;
  z-index: 999;
  left: 0; top: 0;
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
    
        <div class="logo"><img src="img/lspulogo.jpg" style="height: 50px; border-radius: 50%; box-shadow: 0 4px 8px rgba(0,0,0,0.3);">Event<span style="color:blue;">Sync</span></div>
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

    <section class="hero">
        <div class="overlay">
            <h1>WELCOME TO <span style="color:black;">Event</span><span style="color:blue;">Sync</span></h1>
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
