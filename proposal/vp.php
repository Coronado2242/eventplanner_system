<?php
$host = "localhost";
$user = "root";
$pass = "";
$db   = "eventplanner";

$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Handle Approve/Disapprove action
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['id'])) {
    $id = intval($_POST['id']);
    $budget = $_POST['budget'];
    
    if (isset($_POST['approve'])) {
        $status = 'approved';
    } elseif (isset($_POST['disapprove'])) {
        $status = 'disapproved';
    } else {
        exit("Invalid action.");
    }

    $stmt = $conn->prepare("UPDATE proposals SET status = ?, budget_amount = ? WHERE id = ?");
    $stmt->bind_param("ssi", $status, $budget, $id);
    
    if ($stmt->execute()) {
        header("Location: admin_approval.php");
        exit();
    } else {
        echo "Error updating request.";
    }
}

// Fetch proposals
$results = $conn->query("SELECT id, department, event_type FROM proposals WHERE status IS NULL");
?>

<!DOCTYPE html>
<html>
<head>
    <title>Admin Approval</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .sidebar {
            height: 100vh;
            background: #003366;
            color: white;
            padding: 20px;
        }
        .sidebar a {
            color: white;
            text-decoration: none;
            display: block;
            margin-bottom: 10px;
        }
        .sidebar a:hover {
            background: #002244;
            padding-left: 5px;
        }
        .content {
            padding: 30px;
        }
        table th {
            background: #003366;
            color: white;
        }
        .btn-approve {
            background-color: #28a745;
            color: white;
        }
        .btn-disapprove {
            background-color: #dc3545;
            color: white;
        }
    </style>
</head>
<body>
<div class="d-flex">
    <!-- Sidebar -->
    <div class="sidebar">
        <h4>EVENT ADMIN PORTAL</h4>
        <a href="#">🏠 Dashboard</a>
        <a href="#" style="font-weight: bold;">✔️ Approval</a>
        <a href="#">📋 Requirements</a>
    </div>

    <!-- Content -->
    <div class="content w-100">
        <h2>Request Approval</h2>
        <table class="table table-bordered align-middle">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Department</th>
                    <th>Event Type</th>
                    <th>Budget (₱)</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
            <?php while($row = $results->fetch_assoc()): ?>
                <tr>
                    <form method="POST" action="">
                        <td><?= $row['id'] ?></td>
                        <td><?= $row['department'] ?></td>
                        <td><?= $row['event_type'] ?></td>
                        <td>
                            <input type="number" name="budget" class="form-control" placeholder="Enter amount" required>
                        </td>
                        <td>
                            <input type="hidden" name="id" value="<?= $row['id'] ?>">
                            <button type="submit" name="approve" class="btn btn-sm btn-approve mb-1">Approve</button>
                            <button type="submit" name="disapprove" class="btn btn-sm btn-disapprove">Disapprove</button>
                        </td>
                    </form>
                </tr>
            <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>
</body>
</html>
