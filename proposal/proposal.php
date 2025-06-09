<?php
session_start();

// === Database connection ===
$host = "localhost";
$user = "root";
$pass = "";
$db = "eventplanner";
$conn = new mysqli($host, $user, $pass, $db);

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
    <meta charset="UTF-8">
    <title>Event Proposal</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css" rel="stylesheet" />
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
</head>
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
     <div id="message"></div>
    <h5>Notifications</h5>
    <ul class="list-group list-group-flush">
        <?php if (!empty($disapprovedProposals)): ?>
            <?php foreach ($disapprovedProposals as $proposal): ?>
                <li class="list-group-item">
                   <span style="font-weight: bold;"><?= urlencode($proposal['id']) ?></span>
                        <span style="font-weight: bold;"><?= e($proposal['event_type']) ?></span>
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
        <!-- Left Side: Form -->
        <div class="col-md-6">
            <?php
                $form_locked = isset($_SESSION['proposal_id']) && !$budgetApproved;
                ?>
                <form action="<?= $budgetApproved ? 'submit_proposal.php' : 'request_budget.php' ?>" method="POST" enctype="multipart/form-data">

                    <!-- Department -->
                    <div class="mb-3">
                        <?php 
                            $username = $_SESSION['username'] ?? '';
                            $department = explode('_', $username)[0];
                            ?>
                            <input type="text" class="form-control" value="<?= htmlspecialchars($department) ?>" readonly>
                            <input type="hidden" name="department" value="<?= htmlspecialchars($department) ?>">
                    </div>

                    <!-- Event Type -->
                    <div class="mb-3">
                        <input type="text" name="event_type" class="form-control" placeholder="Type of Event"
                            value="<?= e($_SESSION['form_data']['event_type'] ?? '') ?>"
                            <?= $form_locked ? 'readonly' : 'required' ?>>
                    </div>

                    <!-- Date Range -->
                    <div class="mb-3">
                        <input type="text" name="date_range" class="form-control" placeholder="Date Range"
                            value="<?= e($_SESSION['form_data']['date_range'] ?? '') ?>"
                            <?= $form_locked ? 'readonly' : 'required' ?>>
                    </div>

                    <!-- Venue -->
                    <div class="mb-3">
                        <?php if ($form_locked): ?>
                            <input type="text" class="form-control" value="<?= e($_SESSION['form_data']['venue']) ?>" readonly>
                            <input type="hidden" name="venue" value="<?= e($_SESSION['form_data']['venue']) ?>">
                        <?php else: ?>
                            <select name="venue" class="form-control" required>
                                <option value="">Select Venue</option>
                                <?php
                                $venue_query = $conn->query("SELECT DISTINCT venue FROM venue_db ORDER BY venue ASC");
                                $selectedVenue = $_SESSION['form_data']['venue'] ?? '';
                                while ($row = $venue_query->fetch_assoc()) {
                                    $venue = $row['venue'];
                                    $selected = ($selectedVenue === $venue) ? 'selected' : '';
                                    echo "<option value='" . e($venue) . "' $selected>" . e($venue) . "</option>";
                                }
                                ?>
                            </select>
                        <?php endif; ?>
                    </div>

                    <!-- Time Range -->
                    <div class="mb-3">
                        <label>Event Time</label>
                        <div class="row g-2">
                            <div class="col">
                                <input type="text" id="startTime" name="start_time" class="form-control"
                                    placeholder="Start Time" value="<?= e($_SESSION['form_data']['start_time'] ?? '') ?>"
                                    <?= $form_locked ? 'readonly' : 'required' ?>>
                            </div>
                            <div class="col">
                                <input type="text" id="endTime" name="end_time" class="form-control"
                                    placeholder="End Time" value="<?= e($_SESSION['form_data']['end_time'] ?? '') ?>"
                                    <?= $form_locked ? 'readonly' : 'required' ?>>
                            </div>
                        </div>
                    </div>
                    <input type="hidden" name="time" id="combinedTime">

            <!-- File Uploads -->
            <?php
            foreach (['letter_attachment', 'constitution', 'reports', 'adviser_form', 'certification', 'financial', 'activity_plan'] as $file) {
                echo "<div class='mb-3'><label>" . ucfirst(str_replace('_', ' ', $file)) . "</label>";
                if ($form_locked && isset($_SESSION['uploaded'][$file])) {
                    echo "<p class='form-control-plaintext'>" . e(basename($_SESSION['uploaded'][$file])) . "</p>";
                } else {
                    echo "<input type='file' name='$file' class='form-control' accept='.pdf,.doc,.docx' " . 
                        (isset($_SESSION['uploaded'][$file]) ? '' : 'required') . " />";
                    if (isset($_SESSION['uploaded'][$file])) {
                        echo "<small>Already uploaded: " . e(basename($_SESSION['uploaded'][$file])) . "</small>";
                    }
                }
                echo "</div>";
            }
            ?>
            <?php if ($disapproved): ?>

    <div class="alert alert-danger text-center">
        <?= $disapprovedMessage ?>
    </div>
<?php endif; ?>


                    <!-- Buttons -->
                    <div class="text-center">
                        <?php if ($budgetApproved && $budgetAmount): ?>
                            <?php if (!empty($budgetFile)): ?>
                                <p class="alert alert-info">
                                    Budget File: 
                                    <a href="../proposal/uploads/<?= e($budgetFile) ?>" target="_blank"><?= e($budgetFile) ?></a>
                                </p>
                            <?php endif; ?>
                            <p class="alert alert-success">Approved Budget: ₱<?= e($budgetAmount) ?></p>
                            <button type="submit" class="btn btn-primary">Submit Proposal</button>
                        <?php else: ?>
                            <button type="submit" class="btn btn-warning" <?= $form_locked ? 'disabled' : '' ?>>Request Budget</button>
                        <?php endif; ?>
                    </div>
                </form>
        </div>

        <!-- Right Side: Calendar -->
        <div class="col-md-6">
            <main class="calendar-container">
                <div class="calendar-wrapper">
                    <iframe id="calendarFrame"></iframe>
                </div>
            </main>
        </div>
    </div>
</div>

<?php if (!$form_locked): ?>

<script>
flatpickr("input[name='date_range']", {
    mode: "range",
    dateFormat: "m/d/Y"
});


flatpickr("#startTime", {
    enableTime: true,
    noCalendar: true,
    dateFormat: "h:i K"
});

flatpickr("#endTime", {
    enableTime: true,
    noCalendar: true,
    dateFormat: "h:i K"
});

document.querySelector("form").addEventListener("submit", function () {
    const start = document.getElementById("startTime").value;
    const end = document.getElementById("endTime").value;
    document.getElementById("combinedTime").value = start + " - " + end;
});
</script>
<?php endif; ?>
<script>
    document.addEventListener("DOMContentLoaded", function () {
    document.getElementById("calendarFrame").src = "../proposal/calendar.php";
});
</script>
<?php if ($budgetApproved): ?>
<script>
document.addEventListener("DOMContentLoaded", function () {
    const inputs = document.querySelectorAll("form input, form select, form textarea");
    inputs.forEach(el => el.disabled = true);
});
</script>
<?php endif; ?>


<script>
 const disabledRanges = <?= json_encode($disabledDateRanges); ?>;
   console.log(disabledRanges); // dapat dito lumabas lahat ng ranges mo

function normalizeDate(d) {
    return new Date(d.getFullYear(), d.getMonth(), d.getDate());
}

function isDateInDisabledRanges(date) {
    const normalizedDate = normalizeDate(date);
    return disabledRanges.some(range => {
        const from = normalizeDate(new Date(range.from));
        const to = normalizeDate(new Date(range.to));
        return normalizedDate >= from && normalizedDate <= to;
    });
}

function highlightProposalDates(date, element) {
    if (isDateInDisabledRanges(date)) {
        element.style.borderBottom = "3px solid orange";
        element.style.fontWeight = "600";
        element.title = "Date already has proposal";
    }
}


flatpickr("input[name='date_range']", {
    mode: "range",
    dateFormat: "m/d/Y",
    onDayCreate: function(dObj, dStr, fp, dayElem) {
        highlightProposalDates(dayElem.dateObj, dayElem);
    },
    onChange: function(selectedDates, dateStr, instance) {
        if (selectedDates.length === 2) {
            const [start, end] = selectedDates;
            let conflict = false;

            for (let d = new Date(start); d <= end; ) {
                if (isDateInDisabledRanges(new Date(d))) {
                    conflict = true;
                    break;
                }
                d = new Date(d.getTime() + 86400000); // add 1 day
            }

            if (conflict) {
                alert("Already have proposal on selected date range. Please choose another date.");
                instance.clear();
            }
        }
    }
});


   const notifCount = <?= $notificationCount ?>;
const notifIcon = document.getElementById('notificationIcon');
const notifCountBadge = document.getElementById('notificationCount');
const notifContainer = document.getElementById('notificationContainer');
const notifList = document.getElementById('notificationList');
const closeNotifBtn = document.getElementById('closeNotifBtn');
 const disabledDateRanges = <?= json_encode($disabledDateRanges); ?>;

if (notifCount > 0) {
    notifIcon.style.display = 'block';
    notifCountBadge.textContent = notifCount;
} else {
    notifIcon.style.display = 'none';
}

// Toggle notification container visibility when clicking the bell icon
notifIcon.addEventListener('click', function () {
    if (notifContainer.style.display === 'none' || notifContainer.style.display === '') {
        // Show container
        notifContainer.style.display = 'block';

      
        notifList.innerHTML = '';
        for (let i = 1; i <= notifCount; i++) {
            const li = document.createElement('li');
            li.className = 'list-group-item';
            li.textContent = `Notification ${i}`;
            notifList.appendChild(li);
        }
    } else {
        // Hide container
        notifContainer.style.display = 'none';
    }
});

// Close button hides the notification container
closeNotifBtn.addEventListener('click', function () {
    notifContainer.style.display = 'none';
});


// Function to load notification list
function loadNotifications() {
    notifList.innerHTML = ''; // clear list

    <?php
    // Fetch disapproved proposals for the user's department to display in notifications
    if (isset($department)) {
        $stmt = $conn->prepare("SELECT id, event_type FROM proposals WHERE department = ? AND status = 'Disapproved' AND notified = 0");
        $stmt->bind_param("s", $department);
        $stmt->execute();
        $result = $stmt->get_result();
        while ($row = $result->fetch_assoc()) {
            $id = $row['id'];
            $eventType = htmlspecialchars($row['event_type']);
            echo "notifList.innerHTML += `<li class='list-group-item'>Proposal #$id - $eventType was disapproved.</li>`;";
        }
        $stmt->close();
    }
    ?>
    
    // If no notifications:
    if (notifList.children.length === 0) {
        notifList.innerHTML = '<li class="list-group-item text-center text-muted">No new notifications</li>';
    }
}

 document.getElementById('notificationIcon').addEventListener('click', () => {
        const container = document.getElementById('notificationContainer');
        container.style.display = container.style.display === 'block' ? 'none' : 'block';
    });

    document.getElementById('closeNotifBtn').addEventListener('click', () => {
        document.getElementById('notificationContainer').style.display = 'none';
    });

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

                for (let d = new Date(start); d <= end; d.setDate(d.getDate() + 1)) {
                    if (isDateInDisabledRanges(d)) {
                        conflict = true;
                        break;
                    }
                }

                if (conflict) {
                    alert("The selected date range includes dates that already have scheduled events. Please choose another range.");
                    instance.clear();
                }
            }
        }
    });

   // Toggle notification container visibility
    document.getElementById('notificationIcon').addEventListener('click', () => {
        const container = document.getElementById('notificationContainer');
        container.style.display = container.style.display === 'block' ? 'none' : 'block';
    });
 
document.addEventListener('DOMContentLoaded', function() {
  document.getElementById('message').innerHTML = '';
});



</script>


</body>

</html>
