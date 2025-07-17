<?php
session_start();
if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: login.php');
    exit();
}

$conn = new mysqli("localhost", "root", "", "eventplanner");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$logoSrc = "../img/lspulogo.jpg"; // fallback

$sql = "SELECT filepath FROM site_logo ORDER BY date_uploaded DESC LIMIT 1";
$result = $conn->query($sql);

if ($result && $row = $result->fetch_assoc()) {
    if (!empty($row['filepath'])) {
        $logoSrc = "" . htmlspecialchars($row['filepath']); 
    }
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
      padding: 10px;
      margin-right: 20px;
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

    #logoModal {
  display: none;
  position: fixed;
  top: 50%; left: 50%;
  transform: translate(-50%, -50%);
  background: #fff;
  padding: 20px;
  border-radius: 10px;
  box-shadow: 0 5px 15px rgba(0,0,0,0.3);
  min-width: 300px;
  z-index: 1000;
}
#logoModal input[type="file"],
#logoModal input[type="text"] {
  width: 100%;
  margin-bottom: 10px;
  padding: 8px;
  box-sizing: border-box;
}
#logoModal button {
  margin-left: 5px;
  padding: 5px 10px;
  border: none;
  border-radius: 4px;
  cursor: pointer;
}
#logoModal button[type="submit"] {
  background: #17a2b8;
  color: white;
}
#logoModal button[type="button"] {
  background: #dc3545;
  color: white;
}
#currentLogo {
  text-align: center;
}

#currentLogo img {
  max-width: 90%;
  height: auto;
  border-radius: 10px;
  box-shadow: 0 8px 20px rgba(0,0,0,0.4);
  margin-bottom: 10px;
  transition: transform 0.3s ease;
}
#currentLogo img:hover {
  transform: scale(1.02);
}
#currentLogo p {
  font-size: 16px;
  color: #333;
}
.modal {
  display: none;
  position: fixed;
  z-index: 9999;
  left: 0; top: 0;
  width: 100vw;
  height: 100vh;
  background-color: rgba(0,0,0,0.5);
}

.modal-content {
  background-color: white;
  margin: 15% auto;
  padding: 20px 30px;
  border-radius: 12px;
  width: 90%;
  max-width: 400px;
  text-align: center;
  box-shadow: 0 10px 25px rgba(0,0,0,0.4);
  animation: fadeIn 0.3s ease-in-out;
}

.modal-content.success {
  background-color: #e6ffed;
  color: #155724;
  border-left: 6px solid #28a745;
}

.modal-buttons {
  margin-top: 15px;
}

.modal-buttons button {
  padding: 8px 20px;
  margin: 0 10px;
  border: none;
  border-radius: 6px;
  cursor: pointer;
  font-weight: bold;
  font-size: 14px;
}

#confirmYes {
  background-color: #dc3545;
  color: white;
}

#confirmNo {
  background-color: #6c757d;
  color: white;
}

@keyframes fadeIn {
  from { opacity: 0; transform: scale(0.95); }
  to { opacity: 1; transform: scale(1); }
}


  </style>
</head>
<body>

  <header class="topbar">
  <div class="logo">
    <img src="<?php echo $logoSrc; ?>" alt="Logo" style="height:49px; border-radius:50%; box-shadow:0 4px 8px rgba(0,0,0,0.3);">
    Event<span style="color:blue;">Sync</span>&nbsp;ADMIN PORTAL</div>
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
      <li id="logoTab">Logo Management</li>
    </ul>
  </aside>

  <div id="dashboardContent">
    <main class="content">
      <h1>Dashboard</h1>
      <p>Welcome back! Here's what's happening today.</p>
      <iframe id="calendarFrame" style="width:100%; height:1000px; border:none;"></iframe>
    </main>
  </div>

  <div id="userManagementContent" style="display:none">
    <main class="content">
      <h1>User Management</h1>
      <a href="signup.php" class="add-user-btn">+ Add Department</a>
      <a href="solo_signup.php" class="add-user-btn" style="background-color: #007bff;">+ Add Solo Account</a><br><br>
      <table id="userTable">
      <thead>
          <tr>
            <th>Full Name</th>
            <th>Username</th>
            <th>Password</th>
            <th>Role</th>
            <th>Date/Time Created</th>
            <th>Action</th>
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
            <th>Organizer</th><th>Capacity</th><th>Venue</th><th>Actions</th>
          </tr>
        </thead>
        <tbody></tbody>
      </table>
    </main>
  </div>

<div id="activitiesContent" style="display:none">
  <main class="content">
    <h1>POA Activities</h1>
    <a href="activities.php" class="add-user-btn" style="background-color: #007bff;">+ Add Activities</a><br><br>
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

<div id="logoContent" style="display:none">
  <main class="content">
    <h1>Logo Management</h1>
    <div>
    <button class="add-user-btn" style="background-color:#17a2b8;" onclick="openLogoModal()">Change Logo</button></div><br><br><br><br>
    <div id="currentLogo" style="margin-bottom:20px;">
    </div>
  </div>
  </main>
</div>


<div class="modal-overlay" id="logoModalOverlay" onclick="closeLogoModal()"></div>
<div id="logoModal">
  <h3>Upload New Logo</h3>
  <form id="logoUploadForm" enctype="multipart/form-data">
    <label>Select Logo Image:</label>
    <input type="file" name="logo" accept="image/*" required>
    <br>
    <div style="text-align: right; margin-top: 10px;">
      <button type="submit">Upload</button>
      <button type="button" onclick="closeLogoModal()">Cancel</button>
    </div>
  </form>
</div>


<!--  Confirm Modal -->
<div id="confirmModal" class="modal">
  <div class="modal-content">
    <i class="fas fa-exclamation-triangle modal-icon confirm-icon"></i>
    <p id="confirmText">Are you sure you want to delete this department?</p>
    <div class="modal-buttons">
      <button id="confirmYes">Yes</button>
      <button id="confirmNo">No</button>
    </div>
  </div>
</div>

<!--  Success Modal -->
<div id="successModal" class="modal">
  <div class="modal-content success">
    <i class="fas fa-check-circle modal-icon success-icon"></i>
    <p id="successText">Department deleted successfully.</p>
  </div>
</div>



<script>
  document.addEventListener('DOMContentLoaded', () => {
    document.getElementById("calendarFrame").src = "../proposal/calendar.php";
    loadLogo();

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
  ["dashboardTab", "userManagementTab", "venueTab", "activitiesTab", "logoTab"].forEach(id => {
    const el = document.getElementById(id);
    if (el) el.classList.remove('active');
  });
  ["dashboardContent", "userManagementContent", "venueContent", "activitiesContent", "logoContent"].forEach(id => {
    const el = document.getElementById(id);
    if (el) el.style.display = "none";
  });
}



    document.getElementById("dashboardTab").addEventListener("click", () => {
      deactivateAllTabs();
      document.getElementById("dashboardTab").classList.add('active');
      document.getElementById("dashboardContent").style.display = 'block';
    });
    const logoTab = document.getElementById("logoTab");
  if (logoTab) {
    logoTab.addEventListener("click", () => {
      deactivateAllTabs();
      logoTab.classList.add('active');
      document.getElementById("logoContent").style.display = "block";
    });
  }
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
      header.innerHTML = `<th colspan="7" style="background:#004080;color:white;">${dept.toUpperCase()}</th>`;
      tbody.append(header);
      grouped[dept].forEach(u => {
        const row = document.createElement("tr");
        row.innerHTML = `
          <td>${u.fullname}</td>
          <td>${u.username}</td>
          <td>${"*".repeat(8)}</td>
          <td>${u.role}</td>
          <td>${u.created_at}</td>
          <td>
          <button class="deleteBtn" onclick="resetPassword('${u.username}', '${u.username.split('_')[0].toLowerCase()}_department')">Reset Password</button>


          </td>
        `;
        tbody.append(row);
      });

      const deleteRow = document.createElement("tr");
      deleteRow.setAttribute("data-dept", dept);
      const deleteCell = document.createElement("td");
      deleteCell.colSpan = 7;
      deleteCell.style.textAlign = "right";

      const deleteBtn = document.createElement("button");
      deleteBtn.className = "deleteBtn";
      deleteBtn.textContent = `Delete Department`;
      deleteBtn.onclick = () => {
        deleteDepartment(dept);
      };

      deleteCell.appendChild(deleteBtn);
      deleteRow.appendChild(deleteCell);
      tbody.append(deleteRow);
    });

          // Fetch and append solo accounts (from solo_accounts table)
         fetch("get_solo_users.php")
      .then(r => r.json())
      .then(soloUsers => {
        const header = document.createElement("tr");
        header.innerHTML = `<th colspan="7" style="background:#004080;color:white;">SOLO ACCOUNTS</th>`;
        tbody.append(header);
        soloUsers.forEach(u => {
          const row = document.createElement("tr");
          row.innerHTML = `
            <td>${u.fullname || '-'}</td>
            <td>${u.username}</td>
            <td>${"*".repeat(8)}</td>
            <td>${u.role}</td>
            <td>${u.created_at}</td>
            <td>
              <button class="editBtn" onclick="changePassword('${u.id}', '${u.username}')">Change Password</button>
              <button class="deleteBtn" onclick="resetPassword('${u.id}', '${u.username}')">Reset to Default</button>

            </td>
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
              <td>${v.capacity}</td>
              <td>${v.venue}</td>
              <td>
                <button class="deleteBtn" onclick="deleteVenue('${v.id}')">Delete</button>
              </td>
            `;
            tbody.append(tr);
          });
        }).catch(e => console.error(e));
    }

    window.deleteVenue = (id) => {
      if (confirm("Delete this venue?")) {
        fetch(`delete_venue.php?id=${id}`)
          .then(r => r.text())
          .then(msg => { 
            alert(msg); 
            loadVenues(); 
          })
          .catch(e => console.error(e));
      }
    };


    // Initial load
    loadVenues();
  });
  window.openLogoModal = () => {
  document.getElementById("logoModalOverlay").style.display = "block";
  document.getElementById("logoModal").style.display = "block";
};

window.closeLogoModal = () => {
  document.getElementById("logoModalOverlay").style.display = "none";
  document.getElementById("logoModal").style.display = "none";
};

// Handle the form submission
document.getElementById("logoUploadForm").addEventListener("submit", function(e) {
  e.preventDefault();
  const formData = new FormData(this);

  fetch("update_logo.php", {
    method: "POST",
    body: formData
  })
  .then(r => r.text())
  .then(msg => {
    alert(msg);
    closeLogoModal();
    loadLogo();
  })
  .catch(e => console.error(e));
});

window.loadLogo = () => {
  fetch("get_logo.php")
    .then(r => r.json())
    .then(logo => {
      const container = document.getElementById("currentLogo");
      if (logo) {
        container.innerHTML = `
          <img src="${logo.filepath}" alt="Current Logo">
          <p><strong>File:</strong> ${logo.filename}</p>
          <p><strong>Date:</strong> ${logo.date_uploaded}</p>
        `;
      } else {
        container.innerHTML = "<p>No logo available.</p>";
      }
    })
    .catch(e => console.error(e));
};

logoTab.addEventListener("click", () => {
  deactivateAllTabs();
  logoTab.classList.add('active');
  document.getElementById("logoContent").style.display = "block";
  loadLogo();
});

window.changePassword = (id, username) => {
  const newPassword = prompt(`Enter new password for ${username}:`);
  if (newPassword) {
    fetch('change_password.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ id, newPassword })
    })
    .then(r => r.text())
    .then(msg => alert(msg))
    .catch(e => console.error(e));
  }
};

window.resetPassword = (username, table) => {
  if (confirm(`Reset password for ${username} to default?`)) {
    const formData = new FormData();
    formData.append("username", username);
    formData.append("table", table);

    fetch("reset_password.php", {
      method: "POST",
      body: formData
    })
    .then(res => res.text())
    .then(msg => {
      alert(msg);
    })
    .catch(err => {
      console.error(err);
      alert("Something went wrong.");
    });
  }
};






function showSuccessModal(message = "Department deleted successfully.") {
  document.getElementById("successText").innerText = message;
  document.getElementById("successModal").classList.add("show");
}

function closeSuccessModal() {
  document.getElementById("successModal").classList.remove("show");
}


let departmentToDelete = "";

window.deleteDepartment = (department) => {
  departmentToDelete = department;
  document.getElementById("confirmText").textContent =
    `Are you sure you want to delete "${department}" department?`;
  document.getElementById("confirmModal").style.display = "block";
};

// Handle YES
document.getElementById("confirmYes").onclick = () => {
  document.getElementById("confirmModal").style.display = "none";

  const formData = new FormData();
  formData.append("department", departmentToDelete);

  fetch("delete_department.php", {
    method: "POST",
    body: formData
  })
  .then(res => res.text())
  .then(msg => {
    if (msg.startsWith("Success")) {
      document.getElementById("successText").textContent = msg;
      document.getElementById("successModal").style.display = "block";

      setTimeout(() => {
        document.getElementById("successModal").style.display = "none";
        location.reload();
      }, 2000);
    } else {
      alert(msg);
    }
  })
  .catch(err => alert("Error: " + err));
};

// Handle NO
document.getElementById("confirmNo").onclick = () => {
  document.getElementById("confirmModal").style.display = "none";
};










</script>
</body>
</html>