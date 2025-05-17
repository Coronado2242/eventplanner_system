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

    /* Customizing event circle appearance */
    .fc-event {
      border-radius: 50px !important; /* Make event round */
      padding: 10px !important; /* Make it larger to create a circular shape */
      color: white !important; /* Ensure the text stands out */
      font-weight: bold;
    }

    /* Custom colors for events */
    .fc-event-approved {
      background-color: green !important; /* Approved event color */
    }

    .fc-event-pending {
      background-color: orange !important; /* Pending event color */
    }

    .fc-event-not-available {
      background-color: red !important; /* Not Available event color */
    }

    /* Modal Styling */
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

    .modal-body input,
    .modal-body textarea {
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

    .close,
    .save-btn {
      padding: 12px 20px;
      font-size: 16px;
      border-radius: 5px;
      cursor: pointer;
    }

    .close {
      background-color: #bbb;
      color: white;
    }

    .close:hover {
      background-color: #777;
    }

    .save-btn {
      background-color: #4CAF50;
      color: white;
    }

    .save-btn:hover {
      background-color: #45a049;
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

<!-- The Modal -->
<div id="eventModal" class="modal">
  <div class="modal-content">
    <div class="modal-header">
      <span class="close" id="closeModal">&times;</span>
      <h2>Add Event</h2>
    </div>
    <div class="modal-body">
      <label for="eventTitle">Event Title:</label>
      <input type="text" id="eventTitle" placeholder="Enter Event Title">
      <label for="eventStart">Start Date:</label>
      <input type="datetime-local" id="eventStart">
      <label for="eventEnd">End Date:</label>
      <input type="datetime-local" id="eventEnd">
    </div>
    <div class="modal-footer">
      <button class="close" id="closeModalBtn">Cancel</button>
      <button class="save-btn" id="saveEventBtn">Save Event</button>
    </div>
  </div>
</div>

<script>
  document.addEventListener('DOMContentLoaded', function() {
    var calendar = new FullCalendar.Calendar(document.getElementById('calendar'), {
      initialView: 'dayGridMonth',
      selectable: true,
      editable: false,
      height: 550,
      events: 'event.php',
      select: function(info) {
        // Open modal when a date is selected
        var modal = document.getElementById("eventModal");
        var eventTitle = document.getElementById('eventTitle');
        var eventStart = document.getElementById('eventStart');
        var eventEnd = document.getElementById('eventEnd');
        
        // Set start and end date for the input fields
        eventStart.value = info.startStr;
        eventEnd.value = info.endStr;

        // Show modal
        modal.style.display = "block";
      }
    });
    calendar.render();

    // Modal related variables
    var modal = document.getElementById("eventModal");
    var closeModal = document.getElementById("closeModal");
    var closeModalBtn = document.getElementById("closeModalBtn");
    var saveEventBtn = document.getElementById("saveEventBtn");

    // Close modal
    closeModal.onclick = function() {
      modal.style.display = "none";
    }

    // Close modal (cancel button)
    closeModalBtn.onclick = function() {
      modal.style.display = "none";
    }

    // Save event
    saveEventBtn.onclick = function() {
      var title = document.getElementById('eventTitle').value;
      var start = document.getElementById('eventStart').value;
      var end = document.getElementById('eventEnd').value;

      if (title && start && end) {
        var formData = new FormData();
        formData.append("title", title);
        formData.append("start", start);
        formData.append("end", end);

        fetch('add_event.php', {
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
          alert("An error occurred while adding the event.");
        });
      } else {
        alert("Please fill out all fields.");
      }
    }

    // Close modal if clicked outside
    window.onclick = function(event) {
      if (event.target === modal) {
        modal.style.display = "none";
      }
    }
  });
</script>

</body>
</html>
