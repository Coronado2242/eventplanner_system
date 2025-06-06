<?php
session_start();

// Database connection
$host = 'localhost';
$db   = 'eventplanner';
$user = 'root';
$pass = ''; // Replace with your DB password
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

// Handle AJAX calls
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

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $title = $_POST['title'] ?? '';
  $start = $_POST['start'] ?? '';
  $end   = $_POST['end'] ?? '';
  $status = 'pending';

  if ($title && $start && $end) {
    $stmt = $pdo->prepare("INSERT INTO proposals (status, start_date, end_date) VALUES (?, ?, ?)");
    $stmt->execute([$status, $start, $end]);
    echo json_encode(['status' => 'success']);
  } else {
    echo json_encode(['status' => 'error', 'message' => 'All fields are required.']);
  }
  exit;
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
      left: 0;
      top: 0;
      width: 100%; 
      height: 100%; 
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


<script>
  document.addEventListener('DOMContentLoaded', function() {
    var calendar = new FullCalendar.Calendar(document.getElementById('calendar'), {
      initialView: 'dayGridMonth',
      selectable: true,
      editable: false,
      height: 550,
      events: 'calendar.php?action=fetch',
      select: function(info) {
        document.getElementById('eventStart').value = info.startStr;
        document.getElementById('eventEnd').value = info.endStr;
        document.getElementById('eventModal').style.display = 'block';
      }
    });
    calendar.render();

    var modal = document.getElementById("eventModal");
    var closeModal = document.getElementById("closeModal");
    var closeModalBtn = document.getElementById("closeModalBtn");
    var saveEventBtn = document.getElementById("saveEventBtn");

    closeModal.onclick = closeModalBtn.onclick = function() {
      modal.style.display = "none";
    };

    saveEventBtn.onclick = function() {
      var title = document.getElementById('eventTitle').value;
      var start = document.getElementById('eventStart').value;
      var end = document.getElementById('eventEnd').value;

      if (title && start && end) {
        var formData = new FormData();
        formData.append("title", title);
        formData.append("start", start);
        formData.append("end", end);

        fetch('calendar.php', {
          method: 'POST',
          body: formData
        })
        .then(response => response.json())
        .then(data => {
          if (data.status === 'success') {
            alert("Event submitted as pending.");
            calendar.refetchEvents();
            modal.style.display = "none";
          } else {
            alert("Error: " + data.message);
          }
        })
        .catch(error => {
          console.error("Fetch error:", error);
          alert("An error occurred.");
        });
      } else {
        alert("Please fill out all fields.");
      }
    };

    window.onclick = function(event) {
      if (event.target === modal) {
        modal.style.display = "none";
      }
    };
  });
</script>

</body>
</html>
