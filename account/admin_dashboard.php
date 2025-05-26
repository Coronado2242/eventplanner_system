<?php
session_start();
if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: login.php');
    exit();
}
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
html, body {
  margin: 0;
  padding: 0;
  height: 100%;
  width: 100%;
  overflow-x: hidden;
  position: relative;
  font-family: Arial, sans-serif;
}

body {
  background: url('../img/homebg2.jpg') no-repeat center center fixed;
  background-size: cover;
  position: relative;
}

body::before {
  content: "";
  position: fixed; 
  top: 0;
  left: 0;
  width: 100vw;
  height: 100vh;
  background: rgba(255, 255, 255, 0.4); 
  z-index: -1;
  pointer-events: none; 
}

.topbar {
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

.topbar .logo {
    font-weight: bold;
    font-size: 24px;
}

.topbar nav a {
    text-decoration: none;
    color: #000;
    font-weight: 500;
    font-size: 16px;
    padding: 8px 12px;
    border-radius: 5px;
    transition: background 0.3s, color 0.3s;
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
    padding-top: 10px;
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
</style>
<body>

<header class="topbar">
<div class="logo"><img src="../img/lspulogo.jpg">EVENT ADMIN PORTAL</div>
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
                    <a href="logout.php">Logout</a>
                </div>
            </div>
        </div>
    </nav>
</header>



<aside class="sidebar">
    <div class="toggle-btn">&#9776;</div>
    <ul>
        <li id="dashboardTab" class="active">Dashboard</li>
        <li id="userManagementTab">User Management</li>
        <li>Event Monitoring</li>
        <li>Budget Analytics</li>
        <li id="venueTab">Venue</li>
    </ul>
</aside>

<!-- Dashboard Content -->
<div id="dashboardContent">
<main class="content">
    <h1>Dashboard</h1>
    <p>Welcome back! Here's what's happening today.</p>

    <div class="cards">
        <div class="card">
            <h3>Events</h3>
            <p>3 <span class="positive">+1%</span></p>
            <small>1 new today</small>
        </div>
        <div class="card">
            <h3>Budget</h3>
            <p>₱ 20,000.00 <span class="positive">+2%</span></p>
            <small>1 this month</small>
        </div>
        <div class="card">
            <h3>Active Users</h3>
            <p>6 <span class="positive">+1%</span></p>
            <small>2 online now</small>
        </div>
        <div class="card">
            <h3>Support Ticket</h3>
            <p>1 <span class="negative">-2%</span></p>
            <small>1 unresolved</small>
        </div>
    </div>

    <div class="charts">
        <canvas id="eventsChart" width="400" height="200"></canvas>

        <div class="calendar">
            <h3>APRIL 2025</h3>
            <img src="calendar_image.png" alt="Calendar" width="300">
            <div class="legend">
                <span class="green"></span> Available Schedule
                <span class="red"></span> Not Available
                <span class="orange"></span> Pending
            </div>
        </div>
    </div>
</main>
</div>

<!-- User Management Content -->
<div id="userManagementContent" style="display:none;">
    <main class="content" >
        <h1 style="margin-bottom: 0;">User Management</h1>
        <p style="margin-top: 5px; color: #666;">Manage user department and accounts</p>
        <div>
            <a href="signup.php" class="add-user-btn" style="background-color: #28a745; color: white; padding: 8px 16px; border: none; border-radius: 20px; float: right; cursor: pointer;" >+ Add Department</a>
        </div>
        <div style="margin: 20px 0; clear: both;">
      <input type="text" placeholder="Search User..." style="width: 50%; padding: 8px; border-radius: 20px; border: 1px solid #ccc;">
    </div>
        <table  style="width: 100%; border-collapse: collapse;" width="100%" id="userTable">
            <thead>
                <tr style="background-color: #003366; color: white; padding: 10px;">
                    <th>Username</th>
                    <th>Password</th>  
                    <th>Department</th>
                    <th>User Type</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody style="text-align: center; padding: 10px; border-bottom: 1px solid #ddd;">
                <!-- Filled dynamically -->
            </tbody>
        </table>
    </main>
</div>


<!-- Venue Content -->
<div id="venueContent" style="display:none;">
    <main class="content" >
        <h1 style="margin-bottom: 0;">Venue Management</h1>
        <div>
            <a href="venue.php" class="add-user-btn" style="background-color: #28a745; color: white; padding: 8px 16px; border: none; border-radius: 20px; float: right; cursor: pointer;" >+ Add Venue</a>
        </div>
        <div style="margin: 20px 0; clear: both;">
      <input type="text" placeholder="Search User..." style="width: 50%; padding: 8px; border-radius: 20px; border: 1px solid #ccc;">
    </div>
        <table style="width: 100%; border-collapse: collapse;" width="100%" id="venueTable">
            <thead>
                <tr style="background-color: #003366; color: white; padding: 10px;">
                    <th>Organizer</th>
                    <th>Email</th>
                    <th>Status</th>
                    <th>Venue</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody style="text-align: center; padding: 10px; border-bottom: 1px solid #ddd;">
                <!-- Filled dynamically -->
            </tbody>
        </table>
    </main>
</div>

<!-- Chart Script -->
<script>
const ctx = document.getElementById('eventsChart').getContext('2d');
const eventsChart = new Chart(ctx, {
    type: 'bar',
    data: {
        labels: ['Oct', 'Nov', 'Dec', 'Jan', 'Feb', 'Mar', 'Apr'],
        datasets: [{
            label: 'Number of Events',
            data: [4, 3, 9, 1, 2, 4, 2],
            backgroundColor: 'blue'
        }]
    },
    options: {
        responsive: true,
        scales: {
            y: {
                beginAtZero: true
            }
        }
    }
});
</script>

<!-- Tab Switching & User Fetching Script -->
<script>
document.getElementById("dashboardTab").addEventListener("click", function () {
    document.getElementById("dashboardContent").style.display = "block";
    document.getElementById("userManagementContent").style.display = "none";
    document.getElementById("venueContent").style.display = "none";
    this.classList.add("active");
    document.getElementById("venueTab").classList.remove("active");
    document.getElementById("userManagementTab").classList.remove("active");
});

document.getElementById("userManagementTab").addEventListener("click", function () {
    document.getElementById("dashboardContent").style.display = "none";
    document.getElementById("venueContent").style.display = "none";
    document.getElementById("userManagementContent").style.display = "block";
    this.classList.add("active");
    document.getElementById("venueTab").classList.remove("active");
    document.getElementById("dashboardTab").classList.remove("active");

    // Fetch users
    fetch("get_users.php")
    .then(response => response.json())
        .then(users => {
        const table = document.getElementById("userTable");
        const departments = {};

        // Group users by department
        users.forEach(user => {
            if (!departments[user.department]) {
                departments[user.department] = [];
            }
            departments[user.department].push(user);
        });

        // Clear current table content
        table.innerHTML = "";

        for (const department in departments) {
            const group = departments[department];

            // Create a section heading
            const heading = document.createElement("tr");
            heading.innerHTML = `<th colspan="6" style="background:#004080; color: white; text-align: center; padding: 10px;">${department.toUpperCase()}</th>`;
            table.appendChild(heading);

            // Add column headers for each department section
            const header = document.createElement("tr");
            header.innerHTML = `
                <th>Username</th>
                <th>Password</th>  
                <th>Department</th>
                <th>User Type</th>
                <th>Status</th>
            `;
            table.appendChild(header);

            // Populate user rows
            group.forEach(user => {
                const row = document.createElement("tr");
                row.innerHTML = `
                    <td>${user.email}</td>
                    <td>${user.password}</td>
                    <td>${user.department}</td>
                    <td>${user.role}</td>
                    <td>${user.status}</td>
                `;
                table.appendChild(row);
            });
        }
    })

        .catch(err => console.error("Error loading users:", err));
});

</script>
<script>
document.getElementById("venueTab").addEventListener("click", function () {
    document.getElementById("dashboardContent").style.display = "none";
    document.getElementById("userManagementContent").style.display = "none";
    document.getElementById("venueContent").style.display = "block";
    this.classList.add("active");
    document.getElementById("dashboardTab").classList.remove("active");
    document.getElementById("userManagementTab").classList.remove("active");

    // Fetch users
    fetch("get_venues.php")
        .then(response => response.json())
        .then(venues => {
            const tbody = document.querySelector("#venueTable tbody");
            tbody.innerHTML = "";
            venues.forEach(user => {
                const row = `<tr>
                    <td>${user.organizer}</td>
                    <td>${user.email}</td>
                    <td>${user.status}</td>
                    <td>${user.venue}</td>
                    <td><button onclick="deleteUser('${user.id}')">Delete</button></td>
                </tr>`;
                tbody.innerHTML += row;
            });
        })
        .catch(err => console.error("Error loading users:", err));
});

function deleteUser(id) {
    if (confirm("Delete this venue?")) {
        fetch(`delete_user.php?id=${id}`)
            .then(res => res.text())
            .then(msg => {
                alert(msg);
                document.getElementById("venueTab").click(); // refresh
            });
    }
}
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
</script>
</body>
</html>
