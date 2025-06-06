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

// === Clear form data if no proposal id ===
if (!isset($_SESSION['proposal_id']) && isset($_SESSION['form_data'])) {
    unset($_SESSION['form_data']);
}

// === Check if there's an approved budget ===
$budgetApproved = false;
$budgetAmount = null;
$budgetFile = null;

if (isset($_SESSION['proposal_id'])) {
    $stmt = $conn->prepare("SELECT budget_approved, budget_amount, department, budget_file FROM proposals WHERE id = ?");
    $stmt->bind_param("i", $_SESSION['proposal_id']);
    $stmt->execute();
    $stmt->bind_result($approved, $amount, $proposal_dept, $file);
    if ($stmt->fetch()) {
        $budgetApproved = $approved;
        $budgetAmount = $amount;
        $budgetFile = $file;
    }
    $stmt->close();
}

$sql = "SELECT start_date, end_date FROM proposals";
$result = $conn->query($sql);

$disabledDateRanges = [];

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





function e($str) {
    return htmlspecialchars($str, ENT_QUOTES, 'UTF-8');
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
</style>
<body class="p-5">
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
                    foreach (['letter_attachment', 'constitution', 'reports', 'adviser_form', 'certification', 'financial'] as $file) {
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


</script>


</body>

</html>
