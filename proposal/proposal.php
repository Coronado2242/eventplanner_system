<?php
session_start();

// === Database connection ===
$host = "localhost";
$user = "root";
$pass = "";
$db = "eventplanner";
$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}

// === Helper function for HTML escaping ===
function e($str) {
    return htmlspecialchars($str, ENT_QUOTES, 'UTF-8');
}

// === Check if user's previous proposal was disapproved ===
$disapproved = false;
$disapprovedMessage = "";
if (isset($_SESSION['proposal_id'])) {
    $stmt = $conn->prepare("SELECT status FROM proposals WHERE id = ?");
    $stmt->bind_param("i", $_SESSION['proposal_id']);
    $stmt->execute();
    $stmt->bind_result($status);
    if ($stmt->fetch()) {
        if ($status === 'Disapproved') {
            $disapproved = true;
            $disapprovedMessage = "Your previous proposal was disapproved. You may submit a new request.";
            // Clear session to allow new proposal submission
            unset($_SESSION['proposal_id'], $_SESSION['form_data'], $_SESSION['uploaded']);
        }
    }
    $stmt->close();
}

// === Notification count for disapproved proposals not yet notified ===
$notificationCount = 0;
$disapprovedProposals = [];
if (isset($_SESSION['username'])) {
    $username = $_SESSION['username'];
    $department = explode('_', $username)[0];

    // Count unnotified disapproved proposals
    $stmt = $conn->prepare("SELECT COUNT(*) FROM proposals WHERE department = ? AND status = 'Disapproved' AND notified = 0");
    $stmt->bind_param("s", $department);
    $stmt->execute();
    $stmt->bind_result($notificationCount);
    $stmt->fetch();
    $stmt->close();

    // Fetch list of disapproved proposals for notifications
    $stmt = $conn->prepare("SELECT id, event_type, remarks, disapproved_by FROM proposals WHERE department = ? AND status = 'Disapproved' ORDER BY id DESC");
    $stmt->bind_param("s", $department);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $disapprovedProposals[] = $row;
    }
    $stmt->close();
}

// === Clear form data if no current proposal id ===
if (!isset($_SESSION['proposal_id']) && isset($_SESSION['form_data'])) {
    unset($_SESSION['form_data']);
}

// === Check if budget is approved for current proposal ===
$budgetApproved = false;
$budgetAmount = null;
$budgetFile = null;
if (isset($_SESSION['proposal_id'])) {
    $stmt = $conn->prepare("SELECT budget_approved, budget_amount, budget_file FROM proposals WHERE id = ?");
    $stmt->bind_param("i", $_SESSION['proposal_id']);
    $stmt->execute();
    $stmt->bind_result($approved, $amount, $file);
    if ($stmt->fetch()) {
        $budgetApproved = (bool)$approved;
        $budgetAmount = $amount;
        $budgetFile = $file;
    }
    $stmt->close();
}

// === Get all proposal date ranges for disabling calendar dates ===
$disabledDateRanges = [];
$sql = "SELECT start_date, end_date FROM proposals";
$result = $conn->query($sql);
if ($result) {
    while ($row = $result->fetch_assoc()) {
        if (!empty($row['start_date']) && !empty($row['end_date'])) {
            $disabledDateRanges[] = [
                'from' => $row['start_date'],
                'to' => $row['end_date']
            ];
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <title>Event Proposal</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css" rel="stylesheet" />
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <style>
        iframe#calendarFrame {
            width: 100%;
            height: 600px;
            border: none;
        }
        .calendar-container {
            padding: 20px;
            background-color: #f9f9f9;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }
        .calendar-wrapper {
            width: 100%;
            overflow: hidden;
        }
        #notificationIcon {
            position: fixed;
            top: 10px;
            right: 10px;
            cursor: pointer;
            z-index: 999;
        }
        #notificationCount {
            position: absolute;
            top: 0;
            right: 0;
            background: orange;
            color: white;
            border-radius: 50%;
            padding: 2px 7px;
            font-size: 14px;
            font-weight: bold;
            background-color: red;
        }
        #notificationContainer {
            display: none;
            position: fixed;
            top: 50px;
            right: 10px;
            width: 300px;
            background: white;
            border-radius: 6px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.2);
            z-index: 999;
        }
    </style>
</head>
<body class="p-5">

<!-- Notification Icon -->
<div id="notificationIcon" title="View notifications">
    <svg xmlns="http://www.w3.org/2000/svg" width="42" height="42" fill="orange" class="bi bi-bell" viewBox="0 0 16 16">
        <path d="M8 16a2 2 0 0 0 1.985-1.75H6.015A2 2 0 0 0 8 16z"/>
        <path d="M8 1a4.978 4.978 0 0 0-4.9 4.507c-.03.168-.06.376-.06.572v3.565l-1.21 2.42A.5.5 0 0 0 2 13h12a.5.5 0 0 0 .468-.688l-1.21-2.42V6.08c0-.196-.03-.404-.06-.572A4.978 4.978 0 0 0 8 1z"/>
    </svg>
    <span id="notificationCount"><?= $notificationCount ?></span>
</div>

<!-- Notification Dropdown -->
<div id="notificationContainer" class="card shadow p-3">
    <h5>Notifications</h5>
    <ul class="list-group list-group-flush">
        <?php if (!empty($disapprovedProposals)): ?>
            <?php foreach ($disapprovedProposals as $proposal): ?>
                <li class="list-group-item">
                    <a href="proposal_details.php?id=<?= urlencode($proposal['id']) ?>">
                        <?= e($proposal['event_type']) ?>
                    </a>
                    <br>
                    <small>Remarks: <?= e($proposal['remarks']) ?></small><br>
                    <small>Disapproved by: <?= e($proposal['disapproved_by']) ?></small>
                </li>
            <?php endforeach; ?>
        <?php else: ?>
            <li class="list-group-item">Walang disapproved na proposals sa iyong department.</li>
        <?php endif; ?>
    </ul>
    <button id="closeNotifBtn" class="btn btn-sm btn-secondary mt-2 w-100">Close</button>
</div>

<div class="container-fluid">
    <div class="row">
        <!-- Left Side: Proposal Form -->
        <div class="col-md-6">
            <?php
            $form_locked = isset($_SESSION['proposal_id']) && !$budgetApproved;
            $username = $_SESSION['username'] ?? '';
            $department = explode('_', $username)[0];
            ?>
            <form action="<?= $budgetApproved ? 'submit_proposal.php' : 'request_budget.php' ?>" method="POST" enctype="multipart/form-data">
                <!-- Department -->
                <div class="mb-3">
                    <input type="text" class="form-control" value="<?= e($department) ?>" readonly />
                    <input type="hidden" name="department" value="<?= e($department) ?>" />
                </div>

                <!-- Event Type -->
                <div class="mb-3">
                    <input type="text" name="event_type" class="form-control" placeholder="Type of Event"
                        value="<?= e($_SESSION['form_data']['event_type'] ?? '') ?>"
                        <?= $form_locked ? 'readonly' : 'required' ?> />
                </div>

                <!-- Date Range -->
                <div class="mb-3">
                    <input type="text" id="dateRange" name="date_range" placeholder="Select Date Range"
                        class="form-control"
                        value="<?= e($_SESSION['form_data']['date_range'] ?? '') ?>"
                        <?= $form_locked ? 'readonly' : 'required' ?> />
                </div>

                <!-- Venue -->
                <div class="mb-3">
                    <input type="text" name="venue" class="form-control" placeholder="Venue"
                        value="<?= e($_SESSION['form_data']['venue'] ?? '') ?>"
                        <?= $form_locked ? 'readonly' : 'required' ?> />
                </div>

                <!-- File Attachments -->
                <?php for ($i = 1; $i <= 6; $i++): ?>
                    <div class="mb-3">
                        <label for="file<?= $i ?>">Attachment <?= $i ?></label>
                        <input type="file" id="file<?= $i ?>" name="file<?= $i ?>" class="form-control" <?= $form_locked ? 'disabled' : '' ?> />
                    </div>
                <?php endfor; ?>

                <!-- Submit Button -->
                <button type="submit" class="btn btn-primary" <?= $form_locked ? 'disabled' : '' ?>>
                    <?= $budgetApproved ? 'Submit Proposal' : 'Request Budget' ?>
                </button>

                <?php if ($budgetApproved): ?>
                    <p class="mt-3">Budget Approved Amount: ₱<?= number_format($budgetAmount, 2) ?></p>
                    <?php if ($budgetFile): ?>
                        <p><a href="uploads/<?= e($budgetFile) ?>" target="_blank" class="btn btn-sm btn-outline-success">View Budget File</a></p>
                    <?php endif; ?>
                <?php endif; ?>

                <?php if ($disapproved): ?>
                    <div class="alert alert-warning mt-3"><?= e($disapprovedMessage) ?></div>
                <?php endif; ?>
            </form>
        </div>

        <!-- Right Side: Calendar iframe -->
        <div class="col-md-6 calendar-container">
            <div class="calendar-wrapper">
                <iframe src="../proposal/calendar.php" id="calendarFrame" title="Calendar"></iframe>
            </div>
        </div>
    </div>
</div>

<script>
    // Toggle notification container visibility
    document.getElementById('notificationIcon').addEventListener('click', () => {
        const container = document.getElementById('notificationContainer');
        container.style.display = container.style.display === 'block' ? 'none' : 'block';
    });

    document.getElementById('closeNotifBtn').addEventListener('click', () => {
        document.getElementById('notificationContainer').style.display = 'none';
    });

    // Parse disabled date ranges from PHP to JS
    const disabledDateRanges = <?= json_encode($disabledDateRanges); ?>;

    function isDateInRange(date, range) {
        const d = date.getTime();
        return (d >= new Date(range.from).getTime() && d <= new Date(range.to).getTime());
    }

    function isDateInDisabledRanges(date) {
        return disabledDateRanges.some(range => isDateInRange(date, range));
    }

    // Initialize flatpickr with disabled date ranges
    flatpickr("#dateRange", {
        mode: "range",
        minDate: "today",
        disable: disabledDateRanges.map(r => ({
            from: r.from,
            to: r.to
        })),
        onChange: function(selectedDates, dateStr, instance) {
            if (selectedDates.length === 2) {
                let start = selectedDates[0];
                let end = selectedDates[1];
                let conflict = false;

                // Iterate all dates in the selected range to check for conflicts
                for (let d = new Date(start); d <= end; d.setDate(d.getDate() + 1)) {
                    if (isDateInDisabledRanges(new Date(d))) {
                        conflict = true;
                        break;
                    }
                }

                if (conflict) {
                    alert("The selected date range overlaps with existing proposals. Please choose a different range.");
                    instance.clear();
                }
            }
        }
    });
</script>

</body>
</html>
