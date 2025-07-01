<?php
session_start();

$conn = new mysqli("localhost", "root", "", "eventplanner");

if (isset($_GET['action']) && $_GET['action'] === 'fetch') {
  $sql = "SELECT id, department, activity_name, start_date, end_date, status FROM sooproposal";
  $result = $conn->query($sql);

  $events = [];

  while ($row = $result->fetch_assoc()) {
      $status = strtolower(trim($row['status']));
      if (strpos($status, 'disapproved') === 0) continue; // skip all types of disapproved

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
      background: transparent;
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
    .fc-day-ended-underline .fc-daygrid-day-number {
      border-bottom: 3px solid red;
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

<!-- WRAPPED LAYOUT WITH SIDEBAR -->
<div style="display: flex; justify-content: center; align-items: flex-start; gap: 20px;">
  <!-- Sidebar Summary -->
  <div id="calendarSummary" style="width: 250px; padding: 20px; background: #f5f5f5; border-radius: 10px; font-family: Arial;">
    <h4>📅 Summary</h4>
    <div><strong>Today:</strong> <span id="countToday">0</span></div>
    <div><strong>Pending:</strong> <span id="countPending">0</span></div>
    <div><strong>Approved:</strong> <span id="countApproved">0</span></div>
    <div><strong>Total Events:</strong> <span id="countTotal">0</span></div>
    <hr>
    <h5>🧭 Departments:</h5>
    <div id="departmentLegend"></div>
  </div>

  <!-- Calendar -->
  <div id='calendar'></div>
</div>

<div class="legend">
  <span style="color:green;">● Completed</span>
  <span style="color:orange;">● Pending</span>
  <span style="color:red;">● Ended</span>
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
let activeDepartmentFilter = null;

const departmentColors = {
  'CCS': '#4CAF50',
  'COE': '#2196F3',
  'CA': '#FF9800',
  'CCJE': '#9C27B0',
  'CBBA': '#E91E63',
  'CFND': '#00BCD4',
  'CHMT': '#795548',
  'CTE': '#3F51B5',
  'CAS': '#8BC34A'
};

function filterByDepartment(dep) {
  if (activeDepartmentFilter === dep) {
    activeDepartmentFilter = null;
  } else {
    activeDepartmentFilter = dep;
  }
  loadProposals();
}

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

    fetch('calendar.php?action=fetch&_=' + new Date().getTime())
      .then(res => res.json())
      .then(events => {
        clearUnderlines();

        let countToday = 0;
        let countPending = 0;
        let countApproved = 0;
        let countTotal = 0;
        let departmentsInUse = {};
        let departmentSummary = {};

        const todayStr = today.toISOString().slice(0, 10);

        events.forEach(event => {
          if (activeDepartmentFilter && event.department !== activeDepartmentFilter) return;

          const start = new Date(event.start);
          const end = new Date(event.end);
          end.setDate(end.getDate() - 1);

          const dep = event.department;
          const status = event.status;

          if (!departmentsInUse[dep]) departmentsInUse[dep] = true;
          if (!departmentSummary[dep]) {
            departmentSummary[dep] = { pending: 0, approved: 0, today: 0 };
          }

          countTotal++;

          if (status === 'pending') {
            countPending++;
            departmentSummary[dep].pending++;
          } else if (status === 'completed') {
            countApproved++;
            departmentSummary[dep].approved++;
          }

          if (start <= today && end >= today) {
            countToday++;
            departmentSummary[dep].today++;
          }

          for (let d = new Date(start); d <= end; d.setDate(d.getDate() + 1)) {
            const ds = d.toISOString().slice(0, 10);
            if (!dateEvents[ds]) dateEvents[ds] = [];
            dateEvents[ds].push(event);

            const cell = document.querySelector(`.fc-daygrid-day[data-date="${ds}"]`);
            if (cell) {
              if (end < today) {
                cell.classList.add('fc-day-ended-underline');
              } else {
                if (status === 'pending') {
                  cell.classList.add('fc-day-pending-underline');
                } else if (status === 'completed') {
                  cell.classList.add('fc-day-approved-underline');
                }
              }
              cell.style.cursor = 'pointer';
            }
          }
        });

        document.getElementById('countToday').textContent = countToday;
        document.getElementById('countPending').textContent = countPending;
        document.getElementById('countApproved').textContent = countApproved;
        document.getElementById('countTotal').textContent = countTotal;

        const legendContainer = document.getElementById('departmentLegend');
        legendContainer.innerHTML = '';

        Object.keys(departmentSummary).forEach(dep => {
          const color = departmentColors[dep] || '#000';
          const data = departmentSummary[dep];
          const isSelected = activeDepartmentFilter === dep;

          const item = document.createElement('div');
          item.style.marginBottom = '6px';
          item.style.cursor = 'pointer';
          item.style.border = isSelected ? '2px solid #333' : 'none';
          item.style.borderRadius = '8px';
          item.style.padding = '5px';
          item.setAttribute('onclick', `filterByDepartment('${dep}')`);

          item.innerHTML = `
            <span style="display:inline-block;width:12px;height:12px;background:${color};border-radius:50%;margin-right:5px;"></span>
            <strong>${dep}</strong><br>
            <span style="margin-left: 18px;">🟢 ${data.approved} Approved</span><br>
            <span style="margin-left: 18px;">🟠 ${data.pending} Pending</span><br>
            <span style="margin-left: 18px;">📅 ${data.today} Today</span>
          `;
          legendContainer.appendChild(item);
        });

        document.querySelectorAll('.fc-daygrid-day').forEach(cell => {
          const date = cell.getAttribute('data-date');
          if (dateEvents[date]) {
            cell.onclick = () => {
              const proposals = dateEvents[date];
              const modalBody = document.getElementById('modalBody');
              const modalDate = document.getElementById('modalDate');
              let html = '';

              proposals.forEach(p => {
                let color = 'orange';
                if (p.status === 'completed') color = 'green';

                const endDate = new Date(p.end);
                endDate.setDate(endDate.getDate() - 1);
                if (endDate < today) color = 'red';

                html += `
                  <div style="margin-bottom: 15px; border-bottom: 1px solid #ccc; padding-bottom: 10px;">
                    <strong>Department:</strong> ${p.department}<br>
                    <strong>Activity:</strong> ${p.event_type}<br>
                    <strong>Start Date:</strong> ${p.start}<br>
                    <strong>End Date:</strong> ${p.end}<br>
                    <strong>Status:</strong> <span style="color:${color};">${p.status}${endDate < today ? ' (Ended)' : ''}</span>
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
    document.querySelectorAll('.fc-day-pending-underline, .fc-day-approved-underline, .fc-day-ended-underline').forEach(el => {
      el.classList.remove('fc-day-pending-underline', 'fc-day-approved-underline', 'fc-day-ended-underline');
      el.style.cursor = '';
    });
  }
});
</script>

</body>
</html>
