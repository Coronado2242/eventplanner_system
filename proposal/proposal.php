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
if (isset($_SESSION['proposal_id'])) {
    $stmt = $conn->prepare("SELECT budget_approved, budget_amount, department FROM proposals WHERE id = ?");
    $stmt->bind_param("i", $_SESSION['proposal_id']);
    $stmt->execute();
    $stmt->bind_result($approved, $amount, $proposal_dept);
    if ($stmt->fetch()) {
        $budgetApproved = $approved;
        $budgetAmount = $amount;

        // Department check for Dean: Only allow viewing proposals from their department
        if ($_SESSION['role'] === 'dean' && $_SESSION['department'] !== $proposal_dept) {
            die("Access denied: You cannot view proposals outside your department.");
        }
    }
    $stmt->close();
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
    /* Existing styles here */
</style>
<body class="p-5">
<div class="container-fluid">
    <div class="row">
        <!-- Left Side: Form -->
        <div class="col-md-6">
            <form action="<?= $budgetApproved ? 'submit_proposal.php' : 'request_budget.php' ?>" method="POST" enctype="multipart/form-data">
                <!-- CSRF Token -->
                <input type="hidden" name="csrf_token" value="<?= e($_SESSION['csrf_token']) ?>">
                
                <div class="mb-3">
                    <select name="department" class="form-control" required>
                        <option value="">Select Department</option>
                        <?php
                        $departments = ["CHMT", "CCS", "CTE", "COE", "CCJE", "CA", "CBBA", "CFMD"];
                        foreach ($departments as $dept) {
                            // If Dean, force department select to their own (readonly)
                            if ($_SESSION['role'] === 'dean' && $_SESSION['department'] !== $dept) {
                                continue;
                            }
                            $selected = (($_SESSION['form_data']['department'] ?? '') === $dept) ? 'selected' : '';
                            echo "<option value='" . e($dept) . "' $selected>" . e($dept) . "</option>";
                        }
                        ?>
                    </select>
                </div>
                <div class="mb-3">
                    <input type="text" name="event_type" class="form-control" placeholder="Type of Event" required
                           value="<?= e($_SESSION['form_data']['event_type'] ?? '') ?>">
                </div>
                <div class="mb-3">
                    <input type="text" name="date_range" class="form-control" placeholder="Date Range" required
                           value="<?= e($_SESSION['form_data']['date_range'] ?? '') ?>">
                </div>
                <div class="mb-3">
                    <input type="text" name="venue" class="form-control" placeholder="Venue" required
                           value="<?= e($_SESSION['form_data']['venue'] ?? '') ?>">
                </div>
                <div class="mb-3">
                    <input type="text" name="time" class="form-control" placeholder="Time" required
                           value="<?= e($_SESSION['form_data']['time'] ?? '') ?>">
                </div>

                <?php
                $files = ['letter_attachment', 'constitution', 'reports', 'adviser_form', 'certification', 'financial'];
                foreach ($files as $file) {
                    echo "<div class='mb-3'>
                            <label>" . ucfirst(str_replace('_', ' ', $file)) . "</label>
                          <input type='file' name='$file' class='form-control' accept='.pdf,.doc,.docx' " . (isset($_SESSION['uploaded'][$file]) ? '' : 'required') . " /> ";
                    if (isset($_SESSION['uploaded'][$file])) {
                        echo "<small>File already uploaded: " . e(basename($_SESSION['uploaded'][$file])) . "</small>";
                    }
                    echo "</div>";
                }
                ?>

                <div class="text-center">
                    <?php if ($budgetApproved && $budgetAmount): ?>
                        <p class="alert alert-success">Approved Budget: ₱<?= e($budgetAmount) ?></p>
                        <button type="submit" class="btn btn-primary">Submit Proposal</button>
                    <?php else: ?>
                        <button type="submit" class="btn btn-warning">Request Budget</button>
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

<script>
flatpickr("input[name='date_range']", {
    mode: "range",
    dateFormat: "m/d/Y"
});

document.addEventListener("DOMContentLoaded", function () {
    document.getElementById("calendarFrame").src = "../calendar/calendar.php";
});
</script>
</body>

</html>
