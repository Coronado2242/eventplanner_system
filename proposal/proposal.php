<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Event Proposal</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.css" rel="stylesheet" />
  <script src="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.js"></script>
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

  <style>
    .modal-xl-custom {
      max-width: 1200px;
    }

    .fc {
      font-size: 0.85rem;
    }

    .form-label {
      font-weight: 600;
      margin-bottom: 4px;
    }

    .attachment-column input,
    .bottom-attachments input {
      margin-bottom: 15px;
    }

    .calendar-box {
      border: 1px solid #dee2e6;
      border-radius: 12px;
      padding: 16px;
      background: #fff;
      box-shadow: 0 2px 6px rgba(0,0,0,0.05);
    }

    .calendar-header {
      font-size: 16px;
      font-weight: bold;
      text-align: center;
      margin-bottom: 8px;
    }

    #calendar {
      height: 350px;
      border-radius: 8px;
      overflow: hidden;
    }

    .submit-section {
      text-align: right;
      margin-top: 15px;
    }

    .modal-body {
      background: #f1f3f5;
    }

    .modal-title {
      font-size: 1.3rem;
      font-weight: bold;
    }

    .form-control {
      border-radius: 8px;
    }

    .btn-primary {
      border-radius: 8px;
      padding: 10px 20px;
      font-weight: 600;
    }

    .mb-2 {
      margin-bottom: 1rem !important;
    }
  </style>
</head>
<body>

<!-- Propose Plan Button -->
<div class="text-center my-4">
  <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#proposeModal">
    Propose Plan
  </button>
</div>

<!-- Modal -->
<div class="modal fade" id="proposeModal" tabindex="-1" aria-labelledby="proposeModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-xl modal-xl-custom">
    <div class="modal-content">
      <form action="submit_proposal.php" method="POST" enctype="multipart/form-data">
        <div class="modal-header">
          <h5 class="modal-title" id="proposeModalLabel">Event Proposal Form</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>

        <div class="modal-body">
          <div class="row">
            <!-- Left: Input Fields -->
            <div class="col-md-4">
              <div class="mb-2">
                <label class="form-label">Department:</label>
                <input type="text" class="form-control" name="department" required>
              </div>
              <div class="mb-2">
                <label class="form-label">Type of Event:</label>
                <input type="text" class="form-control" name="event_type" required>
              </div>
              <div class="mb-2">
                <label class="form-label">Start Date:</label>
                <input type="date" class="form-control" name="start_date" id="start_date" required>
              </div>
              <div class="mb-2">
                <label class="form-label">End Date:</label>
                <input type="date" class="form-control" name="end_date" id="end_date" required>
              </div>
              <div class="mb-2">
                <label class="form-label">Venue:</label>
                <input type="text" class="form-control" name="venue" required>
              </div>
              <div class="mb-2">
                <label class="form-label">Time:</label>
                <input type="text" class="form-control" name="time" required>
              </div>
            </div>

            <!-- Center: 4 Attachments -->
            <div class="col-md-4 attachment-column">
              <div>
                <label class="form-label">Adviser Commitment Form:</label>
                <input type="file" class="form-control" name="adviser_form" required>
              </div>
              <div>
                <label class="form-label">Certification from Dean:</label>
                <input type="file" class="form-control" name="certification" required>
              </div>
              <div>
                <label class="form-label">Financial Report:</label>
                <input type="file" class="form-control" name="financial" required>
              </div>
              <div>
                <label class="form-label">Constitution and By-laws:</label>
                <input type="file" class="form-control" name="constitution" required>
              </div>
            </div>

            <!-- Right: Calendar & Attachments -->
            <div class="col-md-4 d-flex flex-column justify-content-between">
              <div class="calendar-box mb-4">
                <div class="calendar-header" id="calendarMonth">MONTH YEAR</div>
                <div id="calendar"></div>
              </div>

              <!-- Bottom 2 Attachments -->
              <div class="bottom-attachments">
                <div class="mb-2">
                  <label class="form-label">Accomplishment Reports:</label>
                  <input type="file" class="form-control" name="reports" required>
                </div>
                <div class="mb-2">
                  <label class="form-label">Letter of Intent:</label>
                  <input type="file" class="form-control" name="letter_attachment" required>
                </div>
              </div>

              <!-- Submit Button -->
              <div class="submit-section">
                <button type="submit" class="btn btn-primary mt-3">Submit Proposal</button>
              </div>
            </div>

          </div>
        </div>

      </form>
    </div>
  </div>
</div>

<!-- FullCalendar Script -->
<script>
  document.addEventListener('DOMContentLoaded', function () {
    var calendarEl = document.getElementById('calendar');
    var calendar = new FullCalendar.Calendar(calendarEl, {
      initialView: 'dayGridMonth',
      selectable: true,
      select: function(info) {
        document.getElementById('start_date').value = info.startStr;
        document.getElementById('end_date').value = info.endStr;
      },
      datesSet: function(info) {
        const monthNames = [
          "JANUARY", "FEBRUARY", "MARCH", "APRIL", "MAY",
          "JUNE", "JULY", "AUGUST", "SEPTEMBER", "OCTOBER",
          "NOVEMBER", "DECEMBER"
        ];
        const currentMonth = monthNames[info.start.getMonth()];
        const currentYear = info.start.getFullYear();
        document.getElementById('calendarMonth').innerText = `${currentMonth} ${currentYear}`;
      },
      eventColor: '#007bff'
    });
    calendar.render();
  });
</script>

</body>
</html>
