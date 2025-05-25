<?php
session_start();
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
  document.addEventListener('DOMContentLoaded', function() {
    var calendar = new FullCalendar.Calendar(document.getElementById('calendar'), {
      initialView: 'dayGridMonth',
      editable: false,
      selectable: false,
      height: 550,
      headerToolbar: {
        left: '',
        center: 'title',
        right: ''
      },

      dayHeaderDidMount: function(info) {
        const day = info.date.getDay();
        if (day === 0 || day === 6) {
          info.el.style.backgroundColor = 'red';
          info.el.style.color = 'white';
        } else {
          info.el.style.backgroundColor = 'blue';
          info.el.style.color = 'white';
        }
      },

      dayCellDidMount: function(info) {
        const day = info.date.getDay();
        const el = info.el;
        if (day === 0 || day === 6) {
          const number = el.querySelector('.fc-daygrid-day-number');
          if (number) number.style.color = "red";
        }
      },

      datesSet: function(info) {
        // Remove all previous underlines
        document.querySelectorAll('.fc-day-pending-underline').forEach(el => {
          el.classList.remove('fc-day-pending-underline');
        });

        // Fetch pending event dates from server via AJAX
        fetch('submit_proposal.php?action=fetch')
          .then(response => response.json())
          .then(events => {
            events.forEach(event => {
              if(event.status === 'Pending') {
                let start = new Date(event.start);
                let end = event.end ? new Date(event.end) : new Date(event.start);
                // Adjust end date to be inclusive of last day
                end.setDate(end.getDate() - 1);

                for(let d = new Date(start); d <= end; d.setDate(d.getDate() + 1)) {
                  let dateStr = d.toISOString().slice(0,10);
                  let dayCell = document.querySelector(`[data-date="${dateStr}"]`);
                  if(dayCell) {
                    dayCell.classList.add('fc-day-pending-underline');
                  }
                }
              }
            });
          });
      },
    });

    calendar.render();
  });
</script>

</body>
</html>
