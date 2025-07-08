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
      'department' => $row['department']
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
    body { font-family: Arial, sans-serif; margin: 0; padding: 0;  overflow: hidden;}
    .container { display: flex; justify-content: center; align-items: flex-start; gap: 20px; padding-top: 30px; }
    #calendar { max-width: 800px; flex-grow: 1; background: #fff; padding: 20px; border-radius: 10px; box-shadow: 0 0 10px rgba(0,0,0,0.1); }
    .sidebar { width: 260px; padding: 20px; }
    .summary-box { background: #fff; border-radius: 6px; padding: 25px 30px; margin-bottom: 15px; box-shadow: 0 0 5px rgba(0,0,0,0.1); font-size: 1.1em;}
    #departmentLegendSidebar {display: grid; grid-template-columns: 1fr 1fr; gap: 5px 10px;}
    .summary-title { font-weight: bold; margin-bottom: 5px; }
    .legend { text-align: center; margin: 20px auto 10px; font-size: 14px; }
    .legend span { margin: 0 10px; font-weight: bold; }
    .modal { display: none; position: fixed; z-index: 999; left: 0; top: 0; width: 100%; height: 100%; overflow: auto; background-color: transparent; }
    .modal-content { background: #f0f0f0; margin: 10% auto; padding: 20px; border-radius: 10px; width: 50%; max-width: 600px; position: relative; }
    .modal-content .close { position: absolute; top: 10px; right: 20px; color: #aaa; font-size: 28px; font-weight: bold; cursor: pointer; }
    .modal-content .close:hover { color: black; }
    .fc-daygrid-day-number { position: relative; padding-bottom: 10px; }
    .fc-daygrid-day-number::after { content: ''; position: absolute; left: 0; right: 0; bottom: 0; height: 3px; background-color: var(--status-color, transparent); }
    .fc-daygrid-day { position: relative; }
    .fc-daygrid-day::after { content: ''; position: absolute; left: 0; right: 0; bottom: 0; height: 5px; background-color: var(--dept-color, transparent);}
    .fc .fc-daygrid-day-frame { min-height: 60px; }
  </style>
</head>
<body>
<div class="container">
  <div class="sidebar">
    <div class="summary-box">
      <div class="summary-title">🟢 Up Coming Events</div>
      <div><span id="countUpcoming">0</span> this week</div>
    </div>
    <div class="summary-box">
      <div class="summary-title">🟠 Pending Events</div>
      <div><span id="countPending">0</span> total</div>
    </div>
    <br>
    <div class="summary-box">
      <div class="summary-title">🏢 Departments:</div><br>
      <div id="departmentLegendSidebar"></div>
    </div>
  </div>
  <div id="calendar"></div>
</div>

<div class="legend">
  <span style="color: green;">● Approved</span>
  <span style="color: red;">● End</span>
  <span style="color: orange;">● Pending</span>
</div>

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
    eventContent: function(arg) {
      const inner = document.createElement('div');
      inner.style.whiteSpace = 'normal';
      inner.style.wordBreak = 'break-word';
      inner.style.fontSize = '12px';
      inner.style.lineHeight = '1.2';
      inner.style.maxHeight = '2.4em'; // 2 lines
      inner.style.overflow = 'hidden';
      inner.textContent = arg.event.title;

      const wrapper = document.createElement('div');
      wrapper.appendChild(inner);
      wrapper.title = arg.event.title;

      return { domNodes: [wrapper] };
    },
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
        let countPending = 0;
        let countUpcoming = 0;
        const groupedDates = {};

        events.forEach(event => {
          const start = new Date(event.start);
          const end = new Date(event.end);
          end.setDate(end.getDate() - 1);

          let status = event.status;
          if (end < today) status = 'ended';

          if (status === 'pending') countPending++;
          if (start >= startOfWeek && start <= endOfWeek && status === 'approved') countUpcoming++;

          for (let d = new Date(start); d <= end; d.setDate(d.getDate() + 1)) {
            const dStr = d.toISOString().slice(0, 10);
            if (!groupedDates[dStr]) groupedDates[dStr] = [];
            groupedDates[dStr].push({
              title: event.title,
              status: status,
              department: event.department
            });
          }
        });

        const calendarEvents = [];
        Object.entries(groupedDates).forEach(([dateStr, items]) => {
          const first = items[0];
          const status = first.status;
const bgColor =
  status === 'pending' ? 'orange' :
  status === 'approved' ? 'green' :
  status === 'completed' ? 'green' :
  status === 'ended' ? 'red' : 'gray';


          let title = first.title;
          if (items.length > 1) {
            title = `${items.length} Activities`;
          }

          calendarEvents.push({
            title: title,
            start: dateStr,
            backgroundColor: bgColor,
            borderColor: 'transparent'
          });
        });

        document.getElementById('countPending').textContent = countPending;
        document.getElementById('countUpcoming').textContent = countUpcoming;

        calendar.removeAllEvents();
        calendar.addEventSource(calendarEvents);

        document.querySelectorAll('.fc-daygrid-day').forEach(cell => {
          const date = cell.getAttribute('data-date');
          const dayEvents = groupedDates[date];
          if (dayEvents) {
            const first = dayEvents[0];
            const cellNum = cell.querySelector('.fc-daygrid-day-number');

            if (cellNum) {
              let statusColor = 'transparent';
if (first.status === 'pending') statusColor = 'orange';
else if (first.status === 'approved' || first.status === 'completed') statusColor = 'green';
else if (first.status === 'ended') statusColor = 'red';

              cellNum.style.setProperty('--status-color', statusColor);
            }

            const deptColor = departmentColors[first.department] || "transparent";
            cell.style.setProperty('--dept-color', deptColor);
            cell.style.cursor = 'pointer';

            cell.onclick = () => {
              if (dayEvents.every(p => p.status === 'ended')) return;

              const modalBody = document.getElementById('modalBody');
              const modalDate = document.getElementById('modalDate');
              let html = '';
              dayEvents.forEach(p => {
                const deptColor = departmentColors[p.department] || '#000';
                html += `
                  <div style="margin-bottom: 15px; border-bottom: 1px solid #ccc; padding-bottom: 10px;">
                    <strong>Department:</strong> ${p.department} <span style="color:${deptColor};">●</span><br>
                    <strong>Activity:</strong> ${p.title}<br>
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
