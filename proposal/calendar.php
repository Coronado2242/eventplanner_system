<?php
session_start();

// Database connection
$conn = new mysqli("localhost", "root", "", "eventplanner");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch events
if (isset($_GET['action']) && $_GET['action'] === 'fetch') {
    $sql = "SELECT id, status, start_date, end_date FROM proposals";
    $result = $conn->query($sql);
    $events = [];

    while ($row = $result->fetch_assoc()) {
        $events[] = [
            'id' => $row['id'],
            'title' => ucfirst($row['status']), 
            'start' => $row['start_date'],
            'end' => date('Y-m-d', strtotime($row['end_date'] . ' +1 day')),
            'status' => strtolower($row['status'])
        ];
    }

    echo json_encode($events);
    exit;
}
?>

<!DOCTYPE html>
<html>
<head>
  <title>Event Calendar</title>
  <link href='https://cdn.jsdelivr.net/npm/fullcalendar@5.11.0/main.min.css' rel='stylesheet' />
  <script src='https://cdn.jsdelivr.net/npm/fullcalendar@5.11.0/main.min.js'></script>
  <style>
    body {
      font-family: Arial, sans-serif;
      background: #f0f0f0;
    }

    #calendar {
      max-width: 800px;
      margin: 40px auto;
      padding: 20px;
      background: #fff;
      border-radius: 10px;
      box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    }

    .legend {
      text-align: center;
      margin-top: 15px;
    }

    .legend span {
      margin: 0 10px;
      font-weight: bold;
    }

    .fc-day-pending .fc-daygrid-day-number {
      border-bottom: 4px solid orange;
      padding-bottom: 2px;
    }

    .fc-day-approved .fc-daygrid-day-number {
      border-bottom: 4px solid green;
      padding-bottom: 2px;
    }
  </style>
</head>
<body>

<h2 style="text-align:center;">Event Scheduler</h2>

<div id="calendar"></div>

<div class="legend">
  <span style="color:green;">● Approved</span>
  <span style="color:orange;">● Pending</span>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
  const calendarEl = document.getElementById('calendar');
  const calendar = new FullCalendar.Calendar(calendarEl, {
    initialView: 'dayGridMonth',
    height: 'auto',
    datesSet: function () {
      loadEvents();
    }
  });

  calendar.render();

  function loadEvents() {
    const dateEvents = {};

    fetch('calendar.php?action=fetch&_=' + new Date().getTime())
      .then(res => res.json())
      .then(events => {
        events.forEach(event => {
          const start = new Date(event.start);
          const end = new Date(event.end);
          end.setDate(end.getDate() - 1); // adjust for fullCalendar end exclusive

          for (let d = new Date(start); d <= end; d.setDate(d.getDate() + 1)) {
            const dateStr = d.toISOString().slice(0, 10);

            if (!dateEvents[dateStr]) {
              dateEvents[dateStr] = [];
            }

            dateEvents[dateStr].push(event);

            const cell = document.querySelector(`.fc-daygrid-day[data-date="${dateStr}"]`);
            if (cell && !cell.classList.contains('fc-day-' + event.status)) {
              cell.classList.add('fc-day-' + event.status);
              cell.style.cursor = 'pointer';
            }
          }
        });

        // Add click to show proposals
        document.querySelectorAll('.fc-daygrid-day').forEach(cell => {
          const date = cell.getAttribute('data-date');
          if (dateEvents[date]) {
            cell.onclick = () => {
              const proposals = dateEvents[date];
              let msg = `📅 Proposals on ${date}:\n\n`;
              proposals.forEach(p => {
                msg += `🔸 Status: ${p.status}\nFrom: ${p.start} to ${p.end}\n\n`;
              });
              alert(msg);
            };
          } else {
            cell.onclick = null;
          }
        });
      })
      .catch(err => console.error('Fetch error:', err));
  }
});
</script>

</body>
</html>
