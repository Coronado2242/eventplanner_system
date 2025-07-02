<?php
session_start();
$conn = new mysqli("localhost", "root", "", "eventplanner");

if (isset($_GET['action']) && $_GET['action'] === 'fetch') {
  $sql = "SELECT id, department, activity_name, start_date, end_date, status FROM sooproposal";
  $result = $conn->query($sql);
  $events = [];

  while ($row = $result->fetch_assoc()) {
    $status = strtolower(trim($row['status']));
    if (strpos($status, 'disapproved') === 0) continue;

    $events[] = [
      'id' => $row['id'],
      'title' => $row['activity_name'],
      'start' => $row['start_date'],
      'end' => date('Y-m-d', strtotime($row['end_date'] . ' +1 day')),
      'status' => $status,
      'department' => $row['department'],
      'event_type' => $row['activity_name']
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
      margin: 0;
      padding: 0;
    }
    .container {
      display: flex;
      justify-content: center;
      align-items: flex-start;
      gap: 20px;
      padding: 30px;
    }
    #calendar {
      max-width: 800px;
      flex-grow: 1;
      background: #fff;
      padding: 20px;
      border-radius: 10px;
      box-shadow: 0 0 10px rgba(0,0,0,0.1);
    }
    .sidebar {
      width: 260px;
      background: #f5f5f5;
      padding: 20px;
      border-radius: 10px;
      box-shadow: 0 0 10px rgba(0,0,0,0.05);
    }
    .summary-box {
      background: #fff;
      border-radius: 6px;
      padding: 10px 15px;
      margin-bottom: 15px;
      box-shadow: 0 0 5px rgba(0,0,0,0.1);
    }
    .summary-title {
      font-weight: bold;
      margin-bottom: 5px;
    }
    .legend {
      text-align: center;
      margin: 20px auto 10px;
      font-size: 14px;
    }
    .legend span {
      margin: 0 10px;
      font-weight: bold;
    }
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
<div class="container">
  <div class="sidebar">
    <div class="summary-box">
      <div class="summary-title">📅 Today</div>
      <div><span id="countToday">0</span> new today</div>
    </div>
    <div class="summary-box">
      <div class="summary-title">🟢 Up Coming</div>
      <div><span id="countUpcoming">0</span> this week</div>
    </div>
    <div class="summary-box">
      <div class="summary-title">🟠 Pending</div>
      <div><span id="countPending">0</span> total</div>
    </div>
    <hr>
    <div class="summary-title">🏢 Departments:</div>
    <div id="departmentLegendSidebar"></div>
  </div>
  <div id="calendar"></div>
</div>

<!-- LEGEND SA BABA -->
<div class="legend">
  <span style="color: green;">● Available Schedule</span>
  <span style="color: red;">● Not Available</span>
  <span style="color: orange;">● Pending</span>
  <span style="color: blue;">● Today</span>
</div>

<!-- WALA NA DEPARTMENT LEGEND SA BABA -->

<div id="eventModal" class="modal">
  <div class="modal-content">
    <span class="close" onclick="document.getElementById('eventModal').style.display='none'">&times;</span>
    <h2>Proposals on <span id="modalDate"></span></h2>
    <div id="modalBody"></div>
  </div>
</div>

<script>
const departmentColors = {
  "CCS": "#3498db", "COE": "#27ae60", "CA": "#e67e22",
  "CCJE": "#8e44ad", "CBBA": "#c0392b", "CFND": "#f39c12",
  "CHMT": "#1abc9c", "CTE": "#e84393", "CAS": "#2c3e50"
};

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
    const today = new Date();
    today.setHours(0, 0, 0, 0);
    const startOfWeek = new Date(today);
    startOfWeek.setDate(today.getDate() - today.getDay());
    const endOfWeek = new Date(startOfWeek);
    endOfWeek.setDate(endOfWeek.getDate() + 6);

    fetch('calendar.php?action=fetch&_=' + new Date().getTime())
      .then(res => res.json())
      .then(events => {
        document.querySelectorAll('.fc-daygrid-day-number').forEach(el => {
          el.style.borderBottom = '';
        });

        let countToday = 0;
        let countPending = 0;
        let countUpcoming = 0;

        events.forEach(event => {
          const start = new Date(event.start);
          const end = new Date(event.end);
          end.setDate(end.getDate() - 1);

          if (event.status === 'pending') countPending++;
          if (start <= today && end >= today) countToday++;
          if (start >= startOfWeek && start <= endOfWeek && event.status === 'completed') countUpcoming++;

          for (let d = new Date(start); d <= end; d.setDate(d.getDate() + 1)) {
            const dStr = d.toISOString().slice(0, 10);
            const cell = document.querySelector(`.fc-daygrid-day[data-date="${dStr}"]`);
            if (cell) {
              const deptColor = departmentColors[event.department] || "#999";
              const number = cell.querySelector('.fc-daygrid-day-number');
              if (number) {
                if (event.status === 'pending') {
                  number.style.borderBottom = "3px solid orange";
                } else if (dStr === today.toISOString().slice(0, 10)) {
                  number.style.borderBottom = "3px solid blue";
                } else {
                  number.style.borderBottom = `3px solid ${deptColor}`;
                }
                number.style.paddingBottom = '2px';
              }
              cell.style.cursor = 'pointer';
            }

            if (!dateEvents[dStr]) dateEvents[dStr] = [];
            dateEvents[dStr].push(event);
          }
        });

        document.getElementById('countToday').textContent = countToday;
        document.getElementById('countPending').textContent = countPending;
        document.getElementById('countUpcoming').textContent = countUpcoming;

        document.querySelectorAll('.fc-daygrid-day').forEach(cell => {
          const date = cell.getAttribute('data-date');
          if (dateEvents[date]) {
            cell.onclick = () => {
              const proposals = dateEvents[date];
              const modalBody = document.getElementById('modalBody');
              const modalDate = document.getElementById('modalDate');
              let html = '';
              proposals.forEach(p => {
                const deptColor = departmentColors[p.department] || '#000';
                html += `
                  <div style="margin-bottom: 15px; border-bottom: 1px solid #ccc; padding-bottom: 10px;">
                    <strong>Department:</strong> ${p.department} <span style="color:${deptColor};">●</span><br>
                    <strong>Activity:</strong> ${p.event_type}<br>
                    <strong>Start Date:</strong> ${p.start}<br>
                    <strong>End Date:</strong> ${p.end}<br>
                    <strong>Status:</strong> ${p.status}
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

        // Sidebar department legend only
        const sidebar = document.getElementById('departmentLegendSidebar');
        sidebar.innerHTML = '';
        Object.entries(departmentColors).forEach(([dept, color]) => {
          sidebar.innerHTML += `<div><span style="color:${color};">●</span> ${dept}</div>`;
        });
      });
  }
});
</script>
</body>
</html>
