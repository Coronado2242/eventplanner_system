<?php
$conn = new mysqli("localhost", "root", "", "eventplanner");
if ($conn->connect_error) die("Connection failed: " . $conn->connect_error);

// Sample: use session to get dean department
session_start();
$dean_department = $_SESSION['department'] ?? null;

$sql = "SELECT * FROM proposals WHERE status = 'Pending' AND department = '$dean_department' ORDER BY start_date DESC";
$result = $conn->query($sql);

function getColor($dept) {
    $colors = [
        'CS' => '#007bff',
        'IT' => '#28a745',
        'BSA' => '#dc3545',
        'HM' => '#6f42c1',
    ];
    return $colors[$dept] ?? '#343a40';
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Pending Event Proposals</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f5f7fa;
            padding: 20px;
        }

        h1 {
            font-size: 24px;
            margin-bottom: 20px;
        }

        .cards {
            display: flex;
            flex-direction: column; 
            gap: 20px; 
            align-items: center; 
        }

        .card {
            background: #fff;
            border-radius: 12px;
            box-shadow: 0 6px 12px rgba(0, 0, 0, 0.08);
            padding: 24px;
            width: 100%;
            max-width: 650px;
            position: relative;
        }

        .badge {
            position: absolute;
            top: 16px;
            right: 16px;
            background-color: #FFA500;
            color: #fff;
            padding: 6px 14px;
            border-radius: 20px;
            font-size: 14px;
            font-weight: bold;
            text-transform: lowercase;
        }

        .card h3 {
            margin: 0 0 16px;
            font-size: 22px;
            font-weight: bold;
        }

        .info-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 12px 24px;
        }

        .info-item {
            font-size: 14px;
        }

        .info-item strong {
            display: block;
            margin-bottom: 4px;
            color: #222;
        }

        .attachments {
            margin-top: 20px;
        }

        .attachments a {
            display: inline-block;
            text-decoration: none;
            background-color: #0048ba;
            color: #fff;
            padding: 6px 12px;
            border-radius: 6px;
            font-size: 14px;
            margin-right: 8px;
            margin-bottom: 6px;
        }

        .approval-buttons {
            margin-top: 20px;
        }

        .approval-buttons button {
            padding: 6px 14px;
            border: none;
            border-radius: 6px;
            font-size: 14px;
            margin-right: 10px;
            cursor: pointer;
        }

        .approve-btn {
            background-color: #28a745;
            color: white;
        }

        .disapprove-btn {
            background-color: #dc3545;
            color: white;
        }
    </style>
</head>
<body>

<h1>Pending Event Proposals</h1>

<div class="cards">
<?php
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $status = strtolower(htmlspecialchars($row['status']));
        $event_type = htmlspecialchars($row['event_type']);
        $colleges = htmlspecialchars($row['colleges'] ?? 'N/A');
        $venue = htmlspecialchars($row['venue']);
        $start_date = htmlspecialchars($row['start_date']);
        $end_date = htmlspecialchars($row['end_date']);
        $department = htmlspecialchars($row['department']);
        $time = htmlspecialchars($row['time']);
        $proposal_id = $row['id'];

        $attachments = [];
        if (!empty($row['adviser_form'])) {
            $attachments[] = '<a href="' . htmlspecialchars($row['adviser_form']) . '" target="_blank">Adviser Commitment Form</a>';
        }
        if (!empty($row['certification'])) {
            $attachments[] = '<a href="' . htmlspecialchars($row['certification']) . '" target="_blank">Dean Certification</a>';
        }
        if (!empty($row['financial'])) {
            $attachments[] = '<a href="' . htmlspecialchars($row['financial']) . '" target="_blank">Financial Report</a>';
        }
        if (!empty($row['constitution'])) {
            $attachments[] = '<a href="' . htmlspecialchars($row['constitution']) . '" target="_blank">By-laws</a>';
        }
        if (!empty($row['reports'])) {
            $attachments[] = '<a href="' . htmlspecialchars($row['reports']) . '" target="_blank">Accomplishment Report</a>';
        }
        if (!empty($row['letter_attachment'])) {
            $attachments[] = '<a href="' . htmlspecialchars($row['letter_attachment']) . '" target="_blank">Letter of Intent</a>';
        }
        $attachments_html = implode(' ', $attachments);

        echo <<<HTML
        <div class="card">
            <span class="badge">$status</span>
            <h3>$event_type</h3>
            <div class="info-grid">
                <div class="info-item"><strong>Colleges</strong>$colleges</div>
                <div class="info-item"><strong>Venue</strong>$venue</div>
                <div class="info-item"><strong>Date</strong>$start_date - $end_date</div>
                <div class="info-item"><strong>Department</strong>$department</div>
                <div class="info-item"><strong>Time</strong>$time</div>
                <div class="info-item"><strong>Requirements</strong>
                    <div class="attachments">$attachments_html</div>
                </div>
            </div>
            <div class="approval-buttons">
                <form method="post" action="process_approval.php">
                    <input type="hidden" name="proposal_id" value="$proposal_id">
                    <button type="submit" name="action" value="approve" class="approve-btn">Approve</button>
                    <button type="submit" name="action" value="disapprove" class="disapprove-btn">Disapprove</button>
                </form>
            </div>
        </div>
        HTML;
    }
} else {
    echo '<p>No pending proposals found.</p>';
}
$conn->close();
?>
</div>

</body>
</html>
