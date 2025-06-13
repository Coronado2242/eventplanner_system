<?php
session_start();

$conn = new mysqli("localhost", "root", "", "eventplanner");

if (isset($_GET['action']) && $_GET['action'] === 'fetch') {
  $sql = "SELECT id, department, event_type, start_date, end_date, status FROM proposals";
  $result = $conn->query($sql);

  $events = [];

  while ($row = $result->fetch_assoc()) {
      $status = strtolower(trim($row['status']));
      if (strpos($status, 'disapproved') === 0) continue; // skip all types of disapproved

      $events[] = [
          'id' => $row['id'],
          'title' => $row['event_type'],
          'start' => $row['start_date'],
          'end' => date('Y-m-d', strtotime($row['end_date'] . ' +1 day')),
          'status' => $status,
          'department' => $row['department'],
          'event_type' => $row['event_type']
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
      background: transparent;
    }
    #calendar {
      max-width: 800px;
      margin: 50px auto;
      background: #fff;
      padding: 20px;
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

    .fc-day-pending-underline .fc-daygrid-day-number {
      border-bottom: 3px solid orange;
      padding-bottom: 2px;
    }

    .fc-day-approved-underline .fc-daygrid-day-number {
      border-bottom: 3px solid green;
      padding-bottom: 2px;
    }

    /* Modal Styles */
    .modal {
      display: none;
      position: fixed;
      z-index: 999;
      left: 0;
      top: 0;
      width: 100%;
      height: 100%;
      overflow: auto;
      background-color: rgba(0,0,0,0.4);
    }

    .modal-content {
      background-color: #fff;
      margin: 10% auto;
      padding: 20px;
      border-radius: 10px;
      width: 50%;
      max-width: 600px;
      position: relative;
    }

    .modal-content h2 {
      margin-top: 0;
    }

    .modal-content .close {
      position: absolute;
      top: 10px;
      right: 20px;
      color: #aaa;
      font-size: 28px;
      font-weight: bold;
      cursor: pointer;
    }

    .modal-content .close:hover {
      color: black;
    }
  </style>
</head>
<body>

<h2 style="text-align:center;"></h2>

<div id='calendar'></div>

<div class="legend">
  <span style="color:green;">● Approved</span>
  <span style="color:orange;">● Pending</span>
</div>

<!-- Modal -->
<div id="eventModal" class="modal">
  <div class="modal-content">
    <span class="close" onclick="document.getElementById('eventModal').style.display='none'">&times;</span>
    <h2>Proposals on <span id="modalDate"></span></h2>
    <div id="modalBody"></div>
  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
  const calendarEl = document.getElementById('calendar');
  const calendar = new FullCalendar.Calendar(calendarEl, {
    initialView: 'dayGridMonth',
    height: "auto",
    events: [],
    datesSet: function () {
      loadProposals();
    }
  });

  calendar.render();

  function loadProposals() {
    const dateEvents = {};

    fetch('calendar.php?action=fetch&_=' + new Date().getTime())
      .then(res => res.json())
      .then(events => {
        clearUnderlines();

        events.forEach(event => {
          const start = new Date(event.start);
          const end = new Date(event.end);
          end.setDate(end.getDate() - 1); // Adjust to include actual end date

          for (let d = new Date(start); d <= end; d.setDate(d.getDate() + 1)) {
            const dateStr = d.toISOString().slice(0, 10);
            if (!dateEvents[dateStr]) {
              dateEvents[dateStr] = [];
            }
            dateEvents[dateStr].push(event);

            const cell = document.querySelector(`.fc-daygrid-day[data-date="${dateStr}"]`);
            if (cell) {
              if (event.status === 'pending') {
                cell.classList.add('fc-day-pending-underline');
              } else if (event.status === 'approved') {
                cell.classList.add('fc-day-approved-underline');
              }
              cell.style.cursor = 'pointer';
            }
          }
        });

        // Show modal on cell click
        document.querySelectorAll('.fc-daygrid-day').forEach(cell => {
          const date = cell.getAttribute('data-date');
          if (dateEvents[date]) {
            cell.onclick = () => {
              const proposals = dateEvents[date];
              const modalBody = document.getElementById('modalBody');
              const modalDate = document.getElementById('modalDate');
              let html = '';

              proposals.forEach(p => {
                let color = p.status === 'approved' ? 'green' : 'orange';
                html += `
                  <div style="margin-bottom: 15px; border-bottom: 1px solid #ccc; padding-bottom: 10px;">
                    <strong>Department:</strong> ${p.department}<br>
                    <strong>Event Type:</strong> ${p.event_type}<br>
                    <strong>Start Date:</strong> ${p.start}<br>
                    <strong>End Date:</strong> ${p.end}<br>
                    <strong>Status:</strong> <span style="color:${color};">${p.status}</span>
                  </div>
                `;
              });

              modalDate.textContent = date;
              modalBody.innerHTML = html;
              document.getElementById('eventModal').style.display = 'block';
            };
          } else {
            cell.onclick = null;
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
