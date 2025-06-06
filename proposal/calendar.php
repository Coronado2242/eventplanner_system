<?php
session_start();


$conn = new mysqli("localhost", "root", "", "eventplanner");

if (isset($_GET['action']) && $_GET['action'] === 'fetch') {

    $sql = "SELECT id, department, event_type, start_date, end_date, time, status FROM proposals";
    $result = $conn->query($sql);

    $events = [];

    while ($row = $result->fetch_assoc()) {
        $events[] = [
            'id' => $row['id'],
            'title' => $row['event_type'],  // Event name/title sa calendar
            'start' => $row['start_date'] . 'T' . $row['time'],  // Combine date + time
            'end' => date('Y-m-d', strtotime($row['end_date'] . ' +1 day')) . 'T' . $row['time'], // End date +1 day + time
            'status' => $row['status'],
            'department' => $row['department'], // Optional: pwede gamitin sa tooltip or alert
        ];
    }

    echo json_encode($events);
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
      background: #f5f5f5;
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

    /* Orange underline for pending dates */
    .fc-day-pending-underline .fc-daygrid-day-number {
      border-bottom: 3px solid orange;
      padding-bottom: 2px;
    }

    #fc-dom-1 {
      color: black;
      margin-right: 500px;
      text-transform: uppercase;
      font-size: 33px;
      font-weight: bold;
    }

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

    .modal-body p {
      font-size: 16px;
      margin: 10px 0;
    }

    .close {
      padding: 12px 20px;
      font-size: 16px;
      border-radius: 5px;
      cursor: pointer;
      background-color: #bbb;
      color: white;
      float: right;
    }

    .close:hover {
      background-color: #777;
    }


  </style>
</head>
<body>

<h2 style="text-align:center;">Event Scheduler</h2>

<div id='calendar'></div>

<div class="legend">
  <span style="color:green;">● Approved</span>
  <span style="color:orange;">● Pending</span>
  <span style="color:red;">● Not Available</span>
</div>


  <script>
document.addEventListener('DOMContentLoaded', function () {
  const calendarEl = document.getElementById('calendar');
  const calendar = new FullCalendar.Calendar(calendarEl, {
    initialView: 'dayGridMonth',
    height: "auto",
    events: [], // Empty so no event titles shown
    datesSet: function () {
      loadAndMarkProposals();
    }
  });

  calendar.render();

  function loadAndMarkProposals() {
    fetch('calendar.php?action=fetch&_=' + new Date().getTime())
      .then(res => res.json())
      .then(events => {
        console.log('Proposals loaded:', events);
        clearUnderlines();

        events.forEach(event => {
          const start = new Date(event.start);
          const endRaw = new Date(event.end);

          const isFullDay = endRaw.getHours() === 0 && endRaw.getMinutes() === 0 && endRaw.getSeconds() === 0;

          let end = new Date(endRaw);
          if (!isFullDay) {
            end.setDate(end.getDate() - 1);
          }

          for (let d = new Date(start); d <= end; d.setDate(d.getDate() + 1)) {
            const dateStr = d.toISOString().slice(0, 10);
            const cell = document.querySelector(`.fc-daygrid-day[data-date="${dateStr}"]`);
            if (cell) {
              const status = event.status.toLowerCase();
              if (status === 'pending') {
                cell.classList.add('fc-day-pending-underline');
              } else if (status === 'approved') {
                cell.classList.add('fc-day-approved-underline');
              }
              cell.style.cursor = 'pointer';
            }
          }
        });
      })
      .catch(err => console.error('Fetch error:', err));
  }

  function clearUnderlines() {
    document.querySelectorAll('.fc-day-pending-underline, .fc-day-approved-underline').forEach(el => {
      el.classList.remove('fc-day-pending-underline', 'fc-day-approved-underline');
      el.style.cursor = '';
    });
  }
});
</script>

</body>
</html>
