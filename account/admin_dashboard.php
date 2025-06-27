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
  <style>
html {
  height: 100%;
  background: url('../img/homebg2.jpg') no-repeat center center fixed;
  background-size: cover;
}

body {
  margin: 0;
  padding: 0;
  min-height: 100vh;
  overflow-x: hidden;
  position: relative;
  font-family: Arial, sans-serif;
}


    body::before {
      content: "";
      position: fixed;
      top: 0; left: 0;
      width: 100vw;
      height: 100vh;
      background: rgba(255,255,255,0.4);
      z-index: -1;
      pointer-events: none;
    }
    .topbar {
      display: flex;
      justify-content: space-between;
      align-items: center;
      padding: 15px 50px;
      background-color: rgba(255,255,255,0.5);
      box-shadow: 0 4px 12px rgba(0,0,0,0.45);
      position: sticky; top: 0; z-index: 1000;
      backdrop-filter: blur(10px);
      border-bottom: 1px solid rgba(0,0,0,0.05);
    }
    .logo { display: flex; align-items: center; font-weight: bold; font-size: 24px; }
    .logo img { margin-right: 10px; height: 49px; border-radius: 50%; box-shadow: 0 4px 8px rgba(0,0,0,0.3); }
    nav a { margin-left: 15px; text-decoration: none; color: #000; font-weight: 500; font-size: 16px; padding: 8px 12px; border-radius: 5px; transition: background .3s; }
    nav a:hover { background: rgba(0,0,0,0.1); }
    .admin-info { display: inline-block; margin-left: 20px; position: relative; }
    .fa-user { font-size: 18px; cursor: pointer; }
    .dropdown-menu {
      display: none;
      position: absolute;
      right: 0; margin-top: 10px;
      background: white;
      box-shadow: 0 4px 8px rgba(0,0,0,0.1);
      border-radius: 5px;
      z-index: 100;
    }
    .dropdown-menu a { padding: 10px; display: block; color: #333; text-decoration: none; }
    .dropdown-menu a:hover { background: #f0f0f0; }

    .sidebar {
      width: 220px;
      background: #004080;
      position: fixed;
      top: 67px;
      bottom: 0;
      color: white;
      padding-top: 10px;
    }
    .sidebar ul { list-style: none; padding: 0; margin: 0; }
    .sidebar ul li {
      padding: 15px 20px;
      cursor: pointer;
      transition: background 0.3s;
    }
    .sidebar ul li.active, .sidebar ul li:hover {
      background: #0066cc;
    }
    .content {
      margin-left: 240px;
      padding: 20px;
      margin-top: 60px;
    }

    table {
      width: 100%;
      border-collapse: collapse;
      font-size: 15px;
      margin-top: 20px;
    }
    thead th {
      background: #003366;
      color: white;
      padding: 12px 15px;
      text-align: center;
      vertical-align: middle;
    }
    th, td {
      padding: 12px 15px;
      border: 1px solid #ddd;
      text-align: center;
      vertical-align: middle;
      background: #fff;
      color: #333;
    }
    tbody tr:hover {
      background: #f2f6fa;
    }

    .add-user-btn {
      background-color: #28a745;
      color: white;
      padding: 8px 16px;
      border: none;
      border-radius: 20px;
      cursor: pointer;
      text-decoration: none;
      float: right;
    }
    .modal-overlay, #editModal {
      display: none;
      position: fixed;
      z-index: 999;
    }
    .modal-overlay {
      top: 0; left: 0;
      width: 100%; height: 100%;
      background: rgba(0,0,0,0.5);
    }
    #editModal {
      top: 50%; left: 50%;
      transform: translate(-50%, -50%);
      background: #fff;
      padding: 20px;
      border-radius: 10px;
      box-shadow: 0 5px 15px rgba(0,0,0,0.3);
      min-width: 300px;
    }
    #editModal input { width: 100%; margin-bottom: 10px; padding: 8px; box-sizing: border-box; }
    #editModal button {
      margin-left: 5px;
      padding: 5px 10px;
      border: none;
      border-radius: 4px;
      cursor: pointer;
    }
    #editModal button:nth-child(1) { background: #28a745; color: white; }
    #editModal button:nth-child(2) { background: #dc3545; color: white; }

    .editBtn { background: green; color: white; padding: 5px 10px; border: none; border-radius: 4px; cursor: pointer; }
    .editBtn:hover { background: #006600; }
    .deleteBtn { background: #dc3545; color: white; padding: 5px 10px; border: none; border-radius: 4px; cursor: pointer; }
    .deleteBtn:hover { background: #b02a37; }
  </style>
</head>
<body>

  <header class="topbar">
    <div class="logo"><img src="../img/lspulogo.jpg" alt="Logo">EVENT ADMIN PORTAL</div>
    <nav>
      <a href="../index.php">Home</a>
      <a href="../aboutus.php">About Us</a>
      <a href="../calendar1.php">Calendar</a>
      <div class="admin-info" id="userDropdown">
	              <i class="icon-calendar"></i>
            <i class="icon-bell"></i>
            <span><?php echo htmlspecialchars($_SESSION['role']); ?></span>
        <i class="fa-solid fa-user" onclick="toggleDropdown()"></i>
        <div class="dropdown-menu" id="dropdownMenu">
          <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'superadmin'): ?>
            <a href="admin_dashboard.php">Admin Dashboard</a>
          <?php endif; ?>
          <a href="logout.php">Logout</a>
        </div>
      </div>
    </nav>
  </header>

  <aside class="sidebar">
    <ul>
      <li id="dashboardTab" class="active">Dashboard</li>
      <li id="userManagementTab">User Management</li>
      <li>Event Monitoring</li>
      <li>Budget Analytics</li>
      <li id="venueTab">Venue</li>
      <li id="activitiesTab">Activities</li>   
    </ul>
  </aside>

  <div id="dashboardContent">
    <main class="content">
      <h1>Dashboard</h1>
      <p>Welcome back! Here's what's happening today.</p>
      <iframe id="calendarFrame" style="width:100%; height:600px; border:none;"></iframe>
    </main>
  </div>

  <div id="userManagementContent" style="display:none">
    <main class="content">
      <h1>User Management</h1>
      <a href="signup.php" class="add-user-btn">+ Add Department</a>
      <a href="solo_signup.php" class="add-user-btn" style="background-color: #007bff;">+ Add Solo Account</a>
      <table id="userTable">
        <thead>
          <tr>
            <th>Full Name</th><th>Username</th><th>Email</th><th>Password</th><th>Role</th><th>Date/Time Created</th>
          </tr>
        </thead>
        <tbody></tbody>
      </table>
    </main>
  </div>

  <div id="venueContent" style="display:none">
    <main class="content">
      <h1>Venue Management</h1>
      <a href="venue.php" class="add-user-btn">+ Add Venue</a><br><br>
      <table id="venueTable">
        <thead>
          <tr>
            <th>Organizer</th><th>Email</th><th>Venue</th><th>Actions</th>
          </tr>
        </thead>
        <tbody></tbody>
      </table>
    </main>
  </div>

<div id="activitiesContent" style="display:none">
  <main class="content">
    <h1>POA Activities</h1>
    <a href="activities.php" class="add-user-btn" style="background-color: #007bff;">+ Add Activities</a>
    <table>
      <thead>
        <tr>
          <th>Department</th>
          <th>Activity Name</th>
          <th>Objective</th>
          <th>Brief Description</th>
          <th>Persons Involved</th>
        </tr>
      </thead>
      <tbody>
        <?php
        $conn = new mysqli("localhost", "root", "", "eventplanner");
        $sql = "SELECT * FROM activities ORDER BY created_at DESC";
        $res = $conn->query($sql);
        if ($res->num_rows > 0):
          while ($row = $res->fetch_assoc()):
        ?>
        <tr>
          <td><?= htmlspecialchars($row['department']) ?></td>
          <td><?= htmlspecialchars($row['activity_name']) ?></td>
          <td><?= htmlspecialchars($row['objective']) ?></td>
          <td><?= htmlspecialchars($row['brief_description']) ?></td>
          <td><?= htmlspecialchars($row['person_involved']) ?></td>
        </tr>
        <?php endwhile; else: ?>
        <tr><td colspan="6">No activities found.</td></tr>
        <?php endif; ?>
      </tbody>
    </table>
  </main>
</div>




  <div class="modal-overlay" id="modalOverlay" onclick="closeEditModal()"></div>
  <div id="editModal">
    <h3>Edit Venue</h3>
    <input type="hidden" id="editVenueId">
    <label>Organizer:</label>
    <input type="text" id="editOrganizer">
    <label>Email:</label>
    <input type="email" id="editEmail">
    <label>Venue:</label>
    <input type="text" id="editVenue">
    <div style="text-align: right;">
      <button onclick="saveVenueEdit()">Save</button>
      <button onclick="closeEditModal()">Cancel</button>
    </div>
  </div>
<script>
  document.addEventListener('DOMContentLoaded', () => {
    document.getElementById("calendarFrame").src = "../proposal/calendar.php";

    function toggleDropdown() {
      const menu = document.getElementById("dropdownMenu");
      menu.style.display = (menu.style.display === "block") ? "none" : "block";
    }
    window.toggleDropdown = toggleDropdown;

    document.addEventListener("click", (e) => {
      if (!document.getElementById("userDropdown").contains(e.target)) {
        document.getElementById("dropdownMenu").style.display = "none";
      }
    });

    // Tab helpers
    function deactivateAllTabs() {
      ["dashboardTab", "userManagementTab", "venueTab", "activitiesTab"].forEach(id => {
        const el = document.getElementById(id);
        if (el) el.classList.remove('active');
      });
      ["dashboardContent", "userManagementContent", "venueContent", "activitiesContent"].forEach(id => {
        const el = document.getElementById(id);
        if (el) el.style.display = "none";
      });
    }

    document.getElementById("dashboardTab").addEventListener("click", () => {
      deactivateAllTabs();
      document.getElementById("dashboardTab").classList.add('active');
      document.getElementById("dashboardContent").style.display = 'block';
    });

    document.getElementById("userManagementTab").addEventListener("click", () => {
      deactivateAllTabs();
      document.getElementById("userManagementTab").classList.add('active');
      document.getElementById("userManagementContent").style.display = 'block';
      fetch("get_users.php")
        .then(r => r.json())
        .then(users => {
          const tbody = document.querySelector("#userTable tbody");
          tbody.innerHTML = "";
          const grouped = {};
          users.forEach(u => {
            (grouped[u.department] ??= []).push(u);
          });
          Object.keys(grouped).forEach(dept => {
            const header = document.createElement("tr");
            header.innerHTML = `<th colspan="6" style="background:#004080;color:white;">${dept.toUpperCase()}</th>`;
            tbody.append(header);
            grouped[dept].forEach(u => {
              const row = document.createElement("tr");
              row.innerHTML = `
                <td>${u.fullname}</td><td>${u.username}</td><td>${u.email}</td><td>${u.password}</td><td>${u.role}</td><td>${u.created_at}</td>
              `;
              tbody.append(row);
            });
          });

          // Fetch and append solo accounts (from solo_accounts table)
          fetch("get_solo_users.php")
            .then(r => r.json())
            .then(soloUsers => {
              const header = document.createElement("tr");
              header.innerHTML = `<th colspan="6" style="background:#004080;color:white;">SOLO ACCOUNTS</th>`;
              tbody.append(header);
              soloUsers.forEach(u => {
                const row = document.createElement("tr");
                row.innerHTML = `
                  <td>${u.fullname || '-'}</td><td>${u.username}</td><td>${u.email || '-'}</td><td>${u.password}</td><td>${u.role}</td><td>${u.created_at}</td>
                `;
                tbody.append(row);
              });
            });
        })
        .catch(e => console.error(e));
    });

    document.getElementById("venueTab").addEventListener("click", () => {
      deactivateAllTabs();
      document.getElementById("venueTab").classList.add('active');
      document.getElementById("venueContent").style.display = 'block';
      loadVenues();
    });

    // ✅ AYOS NA ITO: Gumagana na ang Activities tab
    const activitiesTab = document.getElementById("activitiesTab");
    if (activitiesTab) {
      activitiesTab.addEventListener("click", () => {
        deactivateAllTabs();
        document.getElementById("activitiesTab").classList.add('active');
        document.getElementById("activitiesContent").style.display = 'block';
      });
    }

    // Venue Modal Logic
    window.editVenue = (id, org, email, venue) => {
      document.getElementById("editVenueId").value = id;
      document.getElementById("editOrganizer").value = org;
      document.getElementById("editEmail").value = email;
      document.getElementById("editVenue").value = venue;
      document.getElementById("modalOverlay").style.display = 'block';
      document.getElementById("editModal").style.display = 'block';
    }

    window.closeEditModal = () => {
      document.getElementById("modalOverlay").style.display = 'none';
      document.getElementById("editModal").style.display = 'none';
    }

    window.saveVenueEdit = () => {
      const data = {
        id: document.getElementById("editVenueId").value,
        organizer: document.getElementById("editOrganizer").value,
        email: document.getElementById("editEmail").value,
        venue: document.getElementById("editVenue").value,
      };
      fetch('edit_venue.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(data)
      })
        .then(r => r.text())
        .then(msg => { alert(msg); closeEditModal(); loadVenues(); })
        .catch(e => console.error(e));
    }

    window.loadVenues = () => {
      fetch("get_venues.php")
        .then(r => r.json())
        .then(venues => {
          const tbody = document.querySelector("#venueTable tbody");
          tbody.innerHTML = "";
          venues.forEach(v => {
            const tr = document.createElement("tr");
            tr.innerHTML = `
              <td>${v.organizer}</td>
              <td>${v.email}</td>
              <td>${v.venue}</td>
              <td>
                <button class="editBtn" onclick="editVenue('${v.id}','${v.organizer}','${v.email}','${v.venue}')">Edit</button>
                <button class="deleteBtn" onclick="deleteVenue('${v.id}')">Delete</button>
              </td>
            `;
            tbody.append(tr);
          });
        }).catch(e => console.error(e));
    }

    window.deleteVenue = (id) => {
      if (confirm("Delete this venue?")) {
        fetch(`delete_user.php?id=${id}`)
          .then(r => r.text())
          .then(msg => { alert(msg); loadVenues(); })
          .catch(e => console.error(e));
      }
    };

    // Initial load
    loadVenues();
  });
</script>
</body>
</html>