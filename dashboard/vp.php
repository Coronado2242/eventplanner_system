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
    <meta charset="UTF-8" />
    <title>VP Portal</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />
    <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate" />
    <style>
        /* Basic reset and styling */
        html, body {
            margin: 0; padding: 0; height: 100%; width: 100%; font-family: Arial, sans-serif;
            background: url('../img/homebg2.jpg') no-repeat center center fixed;
            background-size: cover;
        }
        body::before {
            content: "";
            position: fixed; top: 0; left: 0; width: 100vw; height: 100vh;
            background: rgba(255,255,255,0.4);
            z-index: -1; pointer-events: none;
        }
        .topbar {
            display: flex; justify-content: space-between; align-items: center;
            background-color: rgba(255,255,255,0.5);
            padding: 15px 50px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.45);
            position: sticky; top: 0; z-index: 1000; backdrop-filter: blur(10px);
            border-bottom: 1px solid rgba(0,0,0,0.05);
        }
        .topbar .logo {
            font-weight: bold; font-size: 24px; display: flex; align-items: center; gap: 5px;
        }
        .topbar nav a {
            text-decoration: none; color: #000; font-weight: 500; font-size: 16px;
            padding: 8px 12px; border-radius: 5px; transition: background 0.3s, color 0.3s;
        }
        .logo img {
            margin-right: 10px; height: 49px; border-radius: 50%; box-shadow: 0 4px 8px rgba(0,0,0,0.3);
        }
        .admin-info {
            display: inline-block; margin-left: 20px;
        }
        .sidebar {
            width: 220px; background: #004080; position: fixed; top: 80px; bottom: 0; color: white;
        }
        .sidebar ul {
            list-style: none; padding: 0;
        }
        .sidebar ul li {
            padding: 15px 20px; cursor: pointer; display: flex; align-items: center; gap: 10px;
        }
        .sidebar ul li.active, .sidebar ul li:hover {
            background: #0066cc;
        }
        .content {
            margin-left: 240px; padding: 20px; margin-top: 60px;
        }
        .logout-btn {
            margin-left: 15px; padding: 5px 10px; background: maroon; color: white; text-decoration: none;
            border-radius: 5px; font-weight: bold; font-size: 14px;
        }
        .logout-btn:hover {
            background: darkred;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            background: white;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
            border-radius: 10px;
            overflow: hidden;
        }
        th, td {
            padding: 12px 15px;
            border: 1px solid #ddd;
            text-align: left;
        }
        th {
            background-color: #004080;
            color: white;
        }
        tr:nth-child(even) {
            background-color: #f3f3f3;
        }
        .action-btn {
            padding: 6px 12px;
            border: none;
            border-radius: 20px;
            cursor: pointer;
            color: white;
            font-weight: bold;
            margin-right: 5px;
        }
        .approve-btn {
            background-color: green;
        }
        .disapprove-btn {
            background-color: red;
        }
    </style>
</head>
<body>

<header class="topbar">
    <div class="logo"><img src="../img/lspulogo.jpg" alt="Logo">VP ORTAL</div>
    <nav>
        <a href="../index.php">Home</a>
        <a href="../aboutus.php">About Us</a>
        <a href="../calendar1.php">Calendar</a>
        <div class="admin-info">
            <span><?php echo htmlspecialchars($_SESSION['role'] ?? 'VP'); ?></span>
            <div class="user-dropdown" id="userDropdown">
                <i class="fa-solid fa-user dropdown-toggle" onclick="toggleDropdown()"></i>
                <div class="dropdown-menu" id="dropdownMenu" style="display:none;">
                    <a href="../account/logout.php">Logout</a>
                </div>
            </div>
        </div>
    </nav>
</header>

<aside class="sidebar">
    <ul>
        <li id="dashboardTab" class="active"><i class="fa fa-home"></i> Dashboard</li>
        <li id="proposalTab"><i class="fa fa-file-alt"></i> Proposals</li>
        <li id="requirementTab"><i class="fa fa-check-circle"></i> Requirements</li>
        <li id="budgetTab"><i class="fa fa-money-bill-wave"></i> Set Budget</li>

    </ul>
</aside>

<!-- Dashboard Content -->
<div id="dashboardContent" class="content">
    <h1>Welcome to the VP Dashboard</h1>
    <p>This is your overview page.</p>
</div>

<!-- Proposals Content -->
<div id="proposalContent" class="content" style="display:none;">
    <h1>Pending Proposals for Approval</h1>

    <?php
    $sql = "SELECT * FROM proposals WHERE status = 'Pending' AND level = 'VP'";
    $result = mysqli_query($conn, $sql);

    if (mysqli_num_rows($result) == 0) {
    echo "<p>No pending proposals at this time.</p>";
} else {
    echo '<table>';
    echo '<thead><tr>
            <th>Event Type</th>
            <th>Date</th>
            <th>Time</th>
            <th>Venue</th>
            <th>Department</th>
            <th>Actions</th>
          </tr></thead><tbody>';

    while ($row = mysqli_fetch_assoc($result)) {
        echo '<tr>';
        echo '<td>' . htmlspecialchars($row['event_type']) . '</td>';
        echo '<td>' . date("M d, Y", strtotime($row['start_date'])) . ' - ' . date("M d, Y", strtotime($row['end_date'])) . '</td>';
        echo '<td>' . htmlspecialchars($row['time']) . '</td>';
        echo '<td>' . htmlspecialchars($row['venue']) . '</td>';
        echo '<td>' . htmlspecialchars($row['department']) . '</td>';
        echo '<td>';
        
        // Output the Approve form
        echo '<form method="POST" action="../proposal/flow.php" style="display:inline;">';
        echo '<input type="hidden" name="proposal_id" value="' . htmlspecialchars($row['id']) . '">';
        echo '<input type="hidden" name="level" value="VP">';
        echo '<button type="submit" name="action" value="approve" class="action-btn approve-btn">Approve</button>';
        echo '</form>';

        // Output the Disapprove form
        echo '<form method="POST" action="../proposal/flow.php" style="display:inline; margin-left:5px;">';
        echo '<input type="hidden" name="proposal_id" value="' . htmlspecialchars($row['id']) . '">';
        echo '<input type="hidden" name="level" value="VP">';
        echo '<button type="submit" name="action" value="disapprove" class="action-btn disapprove-btn">Disapprove</button>';
        echo '</form>';

        echo '</td>';
        echo '</tr>';
    }

    echo '</tbody></table>';
}
    ?>
</div>

<!-- Set Budget Content -->
<div id="budgetContent" class="content" style="display:none;">

    <h1>Set Budget for a Proposal</h1>
    <form method="POST" action="" style="max-width:500px; background:white; padding:20px; border-radius:10px; box-shadow:0 0 10px rgba(0,0,0,0.1);">
        <div>
       <label for="id">Proposal ID:</label>
        <input type="number" name="id" id="id" required />

        </div>
        <div>
            <label for="budget">Set Budget (₱):</label>
            <input type="number" name="budget" id="budget" required step="0.01" class="form-control" style="width:100%; padding:8px; margin:8px 0;" />
        </div>
        <button type="submit" name="save_budget" class="action-btn approve-btn" style="margin-top:10px;">Save Budget</button>
    </form>

    <?php
    // Process budget saving only if form is submitted
  if (isset($_POST['save_budget'])) {
    $id = intval($_POST['id']);  // tamang variable name para sa id
    $budget = floatval($_POST['budget']);

    // Ayusin ang SQL syntax (gamitin ang ',' imbis na 'AND' sa SET)
    $sql = "UPDATE proposals SET budget_amount = ?, budget_status = 'approve' WHERE id = ?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "di", $budget, $id);  // gamitin ang $id dito

    if (mysqli_stmt_execute($stmt)) {
        echo "✔️ Budget approved successfully for Proposal ID $id.";
    } else {
        echo "❌ Error approving budget: " . mysqli_error($conn);
    }
    mysqli_stmt_close($stmt);
}



    ?>
</div>


<!-- Requirements Content -->
<div id="requirementContent" class="content" style="display:none;">
    <h1>Requirements Section</h1>
    <p>Requirements details will go here.</p>
</div>

<script>
    // Toggle dropdown menu
    function toggleDropdown(){
        const menu = document.getElementById('dropdownMenu');
        menu.style.display = (menu.style.display === 'block') ? 'none' : 'block';
    }

    // Sidebar navigation tabs
    const dashboardTab = document.getElementById('dashboardTab');
    const proposalTab = document.getElementById('proposalTab');
    const requirementTab = document.getElementById('requirementTab');

    const dashboardContent = document.getElementById('dashboardContent');
    const proposalContent = document.getElementById('proposalContent');
    const requirementContent = document.getElementById('requirementContent');

    function clearActive() {
        dashboardTab.classList.remove('active');
        proposalTab.classList.remove('active');
        requirementTab.classList.remove('active');
    }

    dashboardTab.addEventListener('click', () => {
        clearActive();
        dashboardTab.classList.add('active');
        dashboardContent.style.display = 'block';
        proposalContent.style.display = 'none';
        requirementContent.style.display = 'none';
    });

    proposalTab.addEventListener('click', () => {
        clearActive();
        proposalTab.classList.add('active');
        dashboardContent.style.display = 'none';
        proposalContent.style.display = 'block';
        requirementContent.style.display = 'none';
    });

    requirementTab.addEventListener('click', () => {
        clearActive();
        requirementTab.classList.add('active');
        dashboardContent.style.display = 'none';
        proposalContent.style.display = 'none';
        requirementContent.style.display = 'block';
    });


    const budgetTab = document.getElementById('budgetTab');
    const budgetContent = document.getElementById('budgetContent');

    budgetTab.addEventListener('click', () => {
        clearActive();
        budgetTab.classList.add('active');
        dashboardContent.style.display = 'none';
        proposalContent.style.display = 'none';
        requirementContent.style.display = 'none';
        budgetContent.style.display = 'block';
});

function clearActive() {
    dashboardTab.classList.remove('active');
    proposalTab.classList.remove('active');
    requirementTab.classList.remove('active');
    budgetTab.classList.remove('active');
}

</script>

</body>
</html>
