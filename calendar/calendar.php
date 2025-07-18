<?php
session_start();

// Database connection
$host = 'localhost';
$db   = 'eventplanner';
$user = 'root';
$pass = '';
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
  PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
  PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
  PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
  $pdo = new PDO($dsn, $user, $pass, $options);
} catch (PDOException $e) {
  http_response_code(500);
  echo "Database connection failed: " . $e->getMessage();
  exit;
}

// Fetch events for FullCalendar
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['action']) && $_GET['action'] === 'fetch') {
  $stmt = $pdo->query("SELECT id, status, start_date, end_date FROM proposals");
  $events = [];

  while ($row = $stmt->fetch()) {
    $events[] = [
      'id'    => $row['id'],
      'title' => ucfirst($row['status']),
      'start' => $row['start_date'],
      'end'   => $row['end_date'],
      'className' => 'fc-event-' . strtolower(str_replace(' ', '-', $row['status']))
    ];
  }

  header('Content-Type: application/json');
  echo json_encode($events);
  exit;
}

// Add new event
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $title = $_POST['title'] ?? '';
  $start = $_POST['start'] ?? '';
  $end   = $_POST['end'] ?? '';
  $status = 'pending';

  if ($title && $start && $end) {
    $stmt = $pdo->prepare("INSERT INTO proposals (status, start_date, end_date) VALUES (?, ?, ?)");
    $stmt->execute([$status, $start, $end]);
    echo '<script>alert("Event submitted as pending."); window.location.href = "calendar.php";</script>';
  } else {
    echo '<script>alert("All fields are required."); window.location.href = "calendar.php";</script>';
  }
  exit;
}

// Get disabled dates for pending and approved events
$disabledDates = [];
$stmt = $pdo->prepare("SELECT start_date, end_date FROM proposals WHERE status IN ('pending', 'approved')");
$stmt->execute();
while ($row = $stmt->fetch()) {
  $start = new DateTime($row['start_date']);
  $end = new DateTime($row['end_date']);
  $interval = new DateInterval('P1D');
  $range = new DatePeriod($start, $interval, $end->modify('+1 day'));

  foreach ($range as $date) {
    $disabledDates[] = $date->format('Y-m-d');
  }
}
?>

<!DOCTYPE html>
<html>
<head>
  <title>Simple Event Calendar</title>
  <link href='https://cdn.jsdelivr.net/npm/fullcalendar@5.11.0/main.min.css' rel='stylesheet' />
  <script src='https://cdn.jsdelivr.net/npm/fullcalendar@5.11.0/main.min.js'></script>
  <style>
    body {
      font-family: Arial, sans-serif;
      background: transparent;
    }
    #calendar {
      max-width: 700px;
      margin: 40px auto;
      padding: 20px;
      background: #fff;
      border-radius: 10px;
      box-shadow: 0 0 10px rgba(0,0,0,0.1);
    }
    .legend {
      text-align: center;
      margin-top: 10px;
    }
    .legend span {
      margin: 0 15px;
      font-weight: bold;
    }
    .fc-event {
      border-radius: 50px !important;
      padding: 10px !important;
      color: white !important;
      font-weight: bold;
    }
    .fc-event-approved { background-color: green !important; }
    .fc-event-pending { background-color: orange !important; }
    .fc-event-not-available { background-color: red !important; }

    .modal {
      display: none;
      position: fixed;
      z-index: 1;
      left: 0; top: 0;
      width: 100%; height: 100%;
      overflow: auto;
      background-color: rgba(0, 0, 0, 0.4);
      padding-top: 60px;
    }
    .modal-content {
      background-color: #fff;
      margin: 5% auto;
      padding: 40px;
      border-radius: 10px;
      width: 40%;
      box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
    }
    .modal-header {
      font-size: 24px;
      text-align: center;
      margin-bottom: 20px;
    }
    .modal-body input {
      width: 100%;
      padding: 12px;
      margin: 10px 0;
      font-size: 16px;
      border-radius: 5px;
      border: 1px solid #ddd;
    }
    .modal-footer {
      display: flex;
      justify-content: space-between;
      margin-top: 20px;
    }
    .close, .save-btn {
      padding: 12px 20px;
      font-size: 16px;
      border-radius: 5px;
      cursor: pointer;
    }
    .close { background-color: #bbb; color: white; }
    .close:hover { background-color: #777; }
    .save-btn { background-color: #4CAF50; color: white; }
    .save-btn:hover { background-color: #45a049; }
  </style>
</head>
<body>

<h2 style="text-align:center;">Event Scheduler</h2>

<div id='calendar'></div>

<div class="legend">
  <span style="color:green;">● Approved</span>
  <span style="color:orange;">● Pending</span>
</div>

<!-- Modal -->
<div id="eventModal" class="modal">
  <div class="modal-content">
    <div class="modal-header">Add Event</div>
    <div class="modal-body">
      <form method="POST">
        <input type="text" name="title" id="eventTitle" placeholder="Event Title" required>
        <input type="date" name="start" id="eventStart" required>
        <input type="date" name="end" id="eventEnd" required>
        <div class="modal-footer">
          <button type="button" class="close" id="closeModal">Cancel</button>
          <button type="submit" class="save-btn">Save</button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- Modal for blocked date -->
<div id="dateBlockedModal" class="modal">
  <div class="modal-content">
    <div class="modal-header">Date Not Available</div>
    <div class="modal-body">
      <p>Sorry, the selected date is already booked (Pending or Approved).</p>
    </div>
    <div class="modal-footer">
      <button class="close" onclick="document.getElementById('dateBlockedModal').style.display='none'">Close</button>
    </div>
  </div>
</div>

<script>
  const disabledDates = <?= json_encode($disabledDates) ?>;

  document.addEventListener('DOMContentLoaded', function() {
    var calendarEl = document.getElementById('calendar');

    var calendar = new FullCalendar.Calendar(calendarEl, {
      initialView: 'dayGridMonth',
      selectable: true,
      editable: false,
      height: 550,
      events: 'calendar.php?action=fetch',

      selectAllow: function(selectInfo) {
        const start = new Date(selectInfo.startStr);
        const end = new Date(selectInfo.endStr);
        end.setDate(end.getDate() - 1);

        let temp = new Date(start);
        while (temp <= end) {
          const formatted = temp.toISOString().split('T')[0];
          if (disabledDates.includes(formatted)) {
            document.getElementById('dateBlockedModal').style.display = 'block';
            return false;
          }
          temp.setDate(temp.getDate() + 1);
        }
        return true;
      },

      select: function(info) {
        document.getElementById('eventStart').value = info.startStr;
        document.getElementById('eventEnd').value = info.endStr;
        document.getElementById('eventModal').style.display = 'block';
      }
    });

    calendar.render();

    document.getElementById("closeModal").onclick = function() {
      document.getElementById("eventModal").style.display = "none";
    };

    window.onclick = function(event) {
      if (event.target === document.getElementById("eventModal")) {
        document.getElementById("eventModal").style.display = "none";
      }
      if (event.target === document.getElementById("dateBlockedModal")) {
        document.getElementById("dateBlockedModal").style.display = "none";
      }
    };
  });
</script>

</body>
</html>
