<?php
session_start();

$host = "localhost";
$user = "root";
$pass = "";
$db   = "eventplanner";

$conn = mysqli_connect($host, $user, $pass, $db);

if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>VP Portal</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
    <link rel="stylesheet" href="vp_dashboard.css">
    <style>
        /* Minimal styling for table and buttons */
        table {
            width: 100%;
            border-collapse: collapse;
        }
        table, th, td {
            border:1px solid #ccc;
        }
        th, td {
            padding: 8px;
            text-align: left;
        }
        .btn-success {
            background-color: #4CAF50;
            border: none;
            color: white;
            padding: 5px 10px;
            cursor: pointer;
            margin-right: 5px;
        }
        .btn-danger {
            background-color: #f44336;
            border: none;
            color: white;
            padding: 5px 10px;
            cursor: pointer;
        }
        /* Sidebar active tab highlight */
        .sidebar ul li.active {
            background-color: #333;
            color: white;
        }
        .sidebar ul li {
            cursor: pointer;
            padding: 10px 15px;
            list-style: none;
        }
    </style>
</head>
<body>
<header class="topbar">
    <div class="logo"><img src="../img/lspulogo.jpg" alt="Logo" style="height:40px; vertical-align: middle;"> VP PORTAL</div>
    <div class="hamburger" onclick="toggleMobileNav()">&#9776;</div>
    <nav id="mainNav">
        <a href="../index.php">Home</a>
        <a href="../aboutus.php">About Us</a>
        <a href="../calendar1.php">Calendar</a>
        <div class="admin-info">
            <span><?php echo htmlspecialchars($_SESSION['role'] ?? ''); ?></span>
            <div class="user-dropdown" id="userDropdown">
                <i class="fa-solid fa-user dropdown-toggle" onclick="toggleDropdown()"></i>
                <div class="dropdown-menu" id="dropdownMenu" style="display:none;">
                    <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'vp'): ?>
                        <a href="vp_dashboard.php">VP Dashboard</a>
                    <?php endif; ?>
                    <a href="../account/logout.php">Logout</a>
                </div>
            </div>
        </div>
    </nav>
</header>

<aside class="sidebar">
    <ul>
        <li id="dashboardTab" class="active"><i class="fa fa-home"></i> <span class="menu-text">Dashboard</span></li>
        <li id="approvalTab"><i class="fa fa-check-circle"></i> <span class="menu-text">Approvals</span></li>
        <li id="requirementTab"><i class="fa fa-file"></i> <span class="menu-text">Requirements</span></li>
        <li id="proposalTab"><i class="fa fa-folder-open"></i> <span class="menu-text">Proposals</span></li>
    </ul>
</aside>

<!-- Dashboard Content -->
<div id="dashboardContent" style="padding:20px;">
    <main class="content">
        <h1>VP Dashboard</h1>
        <p>Welcome, Vice President! Here's the status of event proposals.</p>
        <iframe id="calendarFrame" src="../calendar1.php" style="width:100%; height:600px; border:none;"></iframe>
    </main>
</div>

<!-- Approvals Content -->
<div id="approvalContent" style="display:none; padding:20px;">
    <main class="content">
        <h1>Pending Approvals</h1>
        <!-- You can place approval related content here -->
    </main>
</div>

<!-- Requirements Content -->
<div id="requirementContent" style="display:none; padding:20px;">
    <main class="content">
        <h1>Requirements</h1>
        <!-- You can place requirements related content here -->
    </main>
</div>

<!-- Proposals Content -->
<div id="proposalContent" style="display:none; padding:20px;">
    <h2>Proposals for Approval</h2>
    <?php
    $sql = "SELECT * FROM proposals WHERE current_level = 'vp' ORDER BY id DESC";
    $result = $conn->query($sql);
    if ($result && $result->num_rows > 0):
    ?>
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>ID</th>
                <th>Department</th>
                <th>Event Type</th>
                <th>Start Date</th>
                <th>End Date</th>
                <th>Status</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
        <?php while($row = $result->fetch_assoc()): ?>
            <tr>
                <td><?= htmlspecialchars($row['id']) ?></td>
                <td><?= htmlspecialchars($row['department']) ?></td>
                <td><?= htmlspecialchars($row['event_type']) ?></td>
                <td><?= htmlspecialchars($row['start_date']) ?></td>
                <td><?= htmlspecialchars($row['end_date']) ?></td>
                <td><?= ucfirst(htmlspecialchars($row['status'])) ?></td>
                <td>
                    <form method="POST" action="process_flow_proposals.php" onsubmit="return confirm('Are you sure you want to proceed?');">
                        <input type="hidden" name="proposal_id" value="<?= htmlspecialchars($row['id']) ?>">
                        <input type="hidden" name="current_level" value="vp">
                        <button type="submit" name="action" value="approve" class="btn btn-success btn-sm">Approve</button>
                        <button type="submit" name="action" value="disapprove" class="btn btn-danger btn-sm">Disapprove</button>
                    </form>
                </td>
            </tr>
        <?php endwhile; ?>
        </tbody>
    </table>
    <?php else: ?>
        <p>No proposals pending your approval at this time.</p>
    <?php endif; ?>
</div>

<script>
    function toggleDropdown() {
        const menu = document.getElementById('dropdownMenu');
        menu.style.display = (menu.style.display === 'block') ? 'none' : 'block';
    }

    function toggleMobileNav() {
        const nav = document.getElementById('mainNav');
        nav.style.display = (nav.style.display === 'block') ? 'none' : 'block';
    }

    // Tab navigation logic
    function hideAllSections() {
        document.getElementById('dashboardContent').style.display = 'none';
        document.getElementById('approvalContent').style.display = 'none';
        document.getElementById('requirementContent').style.display = 'none';
        document.getElementById('proposalContent').style.display = 'none';

        // Remove active class from all sidebar items
        document.querySelectorAll('.sidebar ul li').forEach(item => item.classList.remove('active'));
    }

    document.getElementById('dashboardTab').addEventListener('click', function() {
        hideAllSections();
        this.classList.add('active');
        document.getElementById('dashboardContent').style.display = 'block';
    });

    document.getElementById('approvalTab').addEventListener('click', function() {
        hideAllSections();
        this.classList.add('active');
        document.getElementById('approvalContent').style.display = 'block';
    });

    document.getElementById('requirementTab').addEventListener('click', function() {
        hideAllSections();
        this.classList.add('active');
        document.getElementById('requirementContent').style.display = 'block';
    });

    document.getElementById('proposalTab').addEventListener('click', function() {
        hideAllSections();
        this.classList.add('active');
        document.getElementById('proposalContent').style.display = 'block';
    });

    // Show dashboard by default
    window.onload = () => {
        hideAllSections();
        document.getElementById('dashboardContent').style.display = 'block';
        document.getElementById('dashboardTab').classList.add('active');
    };
</script>

</body>
</html>
