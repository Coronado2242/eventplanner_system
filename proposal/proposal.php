<!DOCTYPE html><html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Propose Event</title>
    <link href='https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.css' rel='stylesheet' />
    <script src='https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.js'></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f4f4f4;
        }
        .form-container {
            width: 80%;
            max-width: 700px;
            margin: 30px auto;
            background: #fff;
            padding: 20px;
            border-radius: 8px;
        }
        h2 {
            text-align: center;
        }
        input, textarea {
            width: 100%;
            padding: 10px;
            margin: 8px 0;
            border: 1px solid #ccc;
            border-radius: 4px;
        }
        button {
            padding: 10px 20px;
            margin-top: 10px;
            background: #007bff;
            color: #fff;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }
        button:hover {
            background: #0056b3;
        }
        .success-message {
            color: green;
            text-align: center;
        }
        #calendar {
            max-width: 700px;
            margin: 0 auto 30px auto;
            background: #fff;
            padding: 10px;
            border-radius: 8px;
        }
    </style>
</head>
<body>
    <div id='calendar'></div><div class="form-container">
    <h2>Event Proposal Form</h2>
    <?php if (isset($_GET['success'])): ?>
        <p class="success-message">Proposal submitted successfully!</p>
    <?php endif; ?>
    <form action="submit_proposal.php" method="POST" enctype="multipart/form-data" class="proposal-form">
        <label>Department:</label>
        <input type="text" name="department" required>

        <label>Type of Event:</label>
        <input type="text" name="event_type" required>

        <label>Start Date:</label>
        <input type="date" name="start_date" id="start_date" required>

        <label>End Date:</label>
        <input type="date" name="end_date" id="end_date" required>

        <label>Venue:</label>
        <input type="text" name="venue" required>

        <label>Time:</label>
        <input type="text" name="time" required>

        <label>Adviser Commitment Form:</label>
        <input type="file" name="adviser_form" required>

        <label>Certification from Dean:</label>
        <input type="file" name="certification" required>

        <label>Financial Report:</label>
        <input type="file" name="financial" required>

        <label>Constitution and By-laws:</label>
        <input type="file" name="constitution" required>

        <label>Accomplishment Reports:</label>
        <input type="file" name="reports" required>

        <label>Letter of Intent:</label>
        <input type="file" name="letter_attachment" required>

        <button type="submit">Submit Proposal</button>
    </form>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        var calendarEl = document.getElementById('calendar');
        var calendar = new FullCalendar.Calendar(calendarEl, {
            initialView: 'dayGridMonth',
            selectable: true,
            events: 'load_events.php',
            select: function(info) {
                document.getElementById('start_date').value = info.startStr;
                document.getElementById('end_date').value = info.endStr;
            },
            eventColor: '#007bff'
        });
        calendar.render();
    });
</script>

</body>
</html>
