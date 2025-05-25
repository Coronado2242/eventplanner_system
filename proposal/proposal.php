<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>Event Proposal Modal</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
  <link href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css" rel="stylesheet" />
  <style>
    .input-rounded {
      background-color: #e5e5e5;
      border: none;
      border-radius: 30px;
      padding: 10px 20px;
      font-size: 16px;
      color: #333;
      width: 100%;
    }
    .file-box {
      background-color: #e5e5e5;
      border-radius: 25px;
      padding: 15px;
      margin-bottom: 15px;
    }
    .file-box label {
      font-weight: 600;
      font-size: 14px;
      display: block;
      margin-bottom: 8px;
      color: #333;
    }
    .upload-btn {
      background-color: #0d6efd;
      color: white;
      border: none;
      border-radius: 10px;
      padding: 6px 15px;
      font-size: 14px;
      margin-top: 8px;
    }
    iframe {
      width: 100%;
      height: 300px;
      border: none;
      border-radius: 15px;
    }
    .submit-btn {
      background-color: #0056d2;
      color: white;
      padding: 10px 20px;
      border: none;
      border-radius: 8px;
      font-size: 16px;
      font-weight: bold;
    }
    .modal-header .btn-close {
      font-size: 1.5rem;
    }
  </style>
</head>
<body>

<div class="container mt-5">
  <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#proposalModal">
    Open Proposal Form
  </button>
</div>

<div class="modal fade" id="proposalModal" tabindex="-1" aria-labelledby="proposalModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-xl modal-dialog-scrollable">
    <div class="modal-content">
      <div class="modal-header">
        <h3 class="fw-bold text-uppercase mb-0" id="proposalModalLabel">Propose Plan</h3>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>

      <div class="modal-body">
        <!-- Change action to your PHP handler file -->
        <form action="submit_proposal.php" method="post" enctype="multipart/form-data">
          <div class="row">

            <!-- LEFT COLUMN -->
            <div class="col-md-4">
              <div class="mb-3">
                <select name="department" class="input-rounded" required>
                  <option value="">Department: *Please Select*</option>
                  <option value="CHMT">CHMT</option>
                  <option value="CCS">CCS</option>
                  <option value="CTE">CTE</option>
                  <option value="COE">COE</option>
                  <option value="CCJE">CCJE</option>
                  <option value="CA">CA</option>
                  <option value="CBBA">CBBA</option>
                  <option value="CFMD">CFMD</option>
                </select>
              </div>

              <div class="mb-3">
                <input type="text" name="event_type" class="input-rounded" placeholder="Type of Event:" required />
              </div>

              <div class="mb-3">
                <input
                  type="text"
                  name="date_range"
                  class="input-rounded"
                  placeholder="Date: mm/dd/yyyy - mm/dd/yyyy"
                  required
                />
              </div>

              <div class="mb-3">
                <input type="text" name="venue" class="input-rounded" placeholder="Venue:" required />
              </div>

              <div class="mb-3">
                <input type="text" name="time" class="input-rounded" placeholder="Time:" required />
              </div>
            </div>

            <!-- MIDDLE COLUMN (Uploads) -->
            <div class="col-md-4">
              <div class="file-box">
                <label>Requirement*<br />Letter Attachment</label>
                <input type="file" name="letter_attachment" required />
                <button type="button" class="upload-btn">Upload 📎</button>
              </div>

              <div class="file-box">
                <label>Requirement*<br />Constitution and by-laws of the Org.</label>
                <input type="file" name="constitution" required />
                <button type="button" class="upload-btn">Upload 📎</button>
              </div>

              <div class="file-box">
                <label>Requirement*<br />Accomplishment reports</label>
                <input type="file" name="reports" required />
                <button type="button" class="upload-btn">Upload 📎</button>
              </div>

              <div class="file-box">
                <label>Requirement*<br />Adviser Commitment form</label>
                <input type="file" name="adviser_form" required />
                <button type="button" class="upload-btn">Upload 📎</button>
              </div>
            </div>

            <!-- RIGHT COLUMN (Calendar + 2 uploads) -->
            <div class="col-md-4">
              <div class="mb-3">
                <iframe
                  src="https://calendar.google.com/calendar/embed?src=en.philippines%23holiday%40group.v.calendar.google.com&ctz=Asia%2FManila"
                ></iframe>
              </div>

              <div class="file-box">
                <label>Requirement*<br />Certification from Responsive Dean/Associate Dean</label>
                <input type="file" name="certification" required />
                <button type="button" class="upload-btn">Upload 📎</button>
              </div>

              <div class="file-box">
                <label>Requirement*<br />Financial Report</label>
                <input type="file" name="financial" required />
                <button type="button" class="upload-btn">Upload 📎</button>
              </div>

              <div class="text-center mt-3">
                <button type="submit" class="submit-btn">Submit Proposal</button>
              </div>
            </div>

          </div>
        </form>
      </div>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script>
  flatpickr("input[name='date_range']", {
    mode: "range",
    dateFormat: "m/d/Y"
  });
</script>

</body>
</html>
