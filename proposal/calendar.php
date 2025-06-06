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
  let dateProposals = {}; // Store events by date

  const calendar = new FullCalendar.Calendar(document.getElementById('calendar'), {
    initialView: 'dayGridMonth',
    editable: false,
    selectable: false,
    height: 550,
    headerToolbar: {
      left: '',
      center: 'title',
      right: ''
    },

    dayHeaderDidMount: function (info) {
      const day = info.date.getDay();
      info.el.style.backgroundColor = day === 0 || day === 6 ? 'red' : 'blue';
      info.el.style.color = 'white';
    },

    dayCellDidMount: function (info) {
      const day = info.date.getDay();
      const el = info.el;
      const number = el.querySelector('.fc-daygrid-day-number');
      if ((day === 0 || day === 6) && number) {
        number.style.color = "red";
      }
    },

    datesSet: function () {
      // Clear old underline classes
      document.querySelectorAll('.fc-day-pending-underline, .fc-day-approved-underline').forEach(el => {
        el.classList.remove('fc-day-pending-underline', 'fc-day-approved-underline');
      });

      dateProposals = {}; // Reset date storage

      // Fetch proposals from PHP backend
   fetch('calendar.php?action=fetch&_=' + new Date().getTime())
  .then(res => res.json())
  .then(events => {
    events.forEach(event => {
      let start = new Date(event.start);
      let end = new Date(event.end);
      end.setDate(end.getDate() + 1); // 👈 make it inclusive

      for (let d = new Date(start); d < end; d.setDate(d.getDate() + 1)) {
        const dateStr = d.toISOString().slice(0, 10);

        if (!dateProposals[dateStr]) {
          dateProposals[dateStr] = [];
        }
        dateProposals[dateStr].push(event);

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

          // Click event on each day
          document.querySelectorAll('.fc-daygrid-day').forEach(cell => {
            const date = cell.getAttribute('data-date');
            if (dateProposals[date]) {
              cell.onclick = () => {
                const proposals = dateProposals[date];
                let msg = `📅 Proposals on ${date}:\n\n`;
                proposals.forEach(p => {
                  msg += `🔸 ${p.title || p.event_type}\nStatus: ${p.status}\nRange: ${p.start} to ${p.end}\n\n`;
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

  calendar.render();
});
</script>


</body>
</html>
