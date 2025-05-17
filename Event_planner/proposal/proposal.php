<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Event Proposal</title>
  <style>
    * {
      box-sizing: border-box;
    }

    body {
      font-family: 'Segoe UI', sans-serif;
      background: #f0f2f5;
      margin: 0;
      padding: 0;
    }

    .sidebar {
      width: 220px;
      height: 100vh;
      background: linear-gradient(180deg, #003366, #002244);
      position: fixed;
      color: #fff;
      padding-top: 30px;
      box-shadow: 2px 0 10px rgba(0, 0, 0, 0.2);
    }

    .sidebar h2 {
      text-align: center;
      margin-bottom: 30px;
      font-size: 1.5rem;
      letter-spacing: 1px;
    }

    .sidebar a {
      display: block;
      color: #fff;
      padding: 12px 30px;
      text-decoration: none;
      font-size: 0.95rem;
      transition: background 0.3s;
    }

    .sidebar a:hover {
      background-color: #005599;
    }

    .topbar {
      margin-left: 220px;
      padding: 15px 30px;
      background-color: #ffffff;
      border-bottom: 1px solid #ddd;
      display: flex;
      justify-content: space-between;
      align-items: center;
    }

    .topbar strong {
      font-size: 1.2rem;
    }

    .container {
      margin-left: 220px;
      padding: 30px;
    }

    .tabs {
      display: flex;
      gap: 12px;
      margin-bottom: 25px;
    }

    .tab {
      padding: 10px 20px;
      background: #e0e0e0;
      border-radius: 6px;
      cursor: pointer;
      font-weight: 500;
      transition: all 0.3s;
    }

    .tab:hover {
      background: #d0d0d0;
    }

    .tab.active {
      background: #007bff;
      color: #fff;
      box-shadow: 0 2px 5px rgba(0, 123, 255, 0.5);
    }

    .proposal-card {
      background: #ffffff;
      padding: 25px;
      margin-bottom: 20px;
      border-radius: 10px;
      border-left: 6px solid #007bff;
      box-shadow: 0 4px 12px rgba(0, 0, 0, 0.06);
      transition: transform 0.2s;
    }

    .proposal-card:hover {
      transform: translateY(-3px);
    }

    .status {
      float: right;
      font-weight: bold;
      color: orange;
    }

    .status.disapproved {
      color: #e74c3c;
    }

    .notification {
      position: fixed;
      top: 100px;
      right: 20px;
      background-color: #333;
      color: #fff;
      padding: 15px 20px;
      border-radius: 10px;
      display: none;
      z-index: 999;
      animation: fadeInOut 6s ease-in-out forwards;
    }

    @keyframes fadeInOut {
      0% { opacity: 0; transform: translateY(-20px); }
      10%, 90% { opacity: 1; transform: translateY(0); }
      100% { opacity: 0; transform: translateY(-20px); }
    }

    button.view-reason {
      margin-top: 10px;
      padding: 8px 14px;
      background: #dc3545;
      color: white;
      border: none;
      border-radius: 6px;
      cursor: pointer;
      transition: background 0.3s;
    }

    button.view-reason:hover {
      background: #c82333;
    }

    .tab-content p {
      color: #555;
    }
  </style>
</head>
<body>

<div class="sidebar">
  <h2>EventSync</h2>
  <a href="#">Dashboard</a>
  <a href="#">Proposal</a>
</div>

<div class="topbar">
  <strong>Event Proposal</strong>
  <span>Hello, Juan Dela Cruz</span>
</div>

<div class="container">
  <div class="tabs">
    <div class="tab active" onclick="showTab(event, 'event')">Event Proposal</div>
    <div class="tab" onclick="showTab(event, 'completed')">Completed Request</div>
  </div>

  <div id="event" class="tab-content">
    <div class="proposal-card">
      <span class="status">Pending</span>
      <h3>CCS Night</h3>
      <p><strong>Colleges:</strong> College of Computer Studies</p>
      <p><strong>Date:</strong> May 26–27, 2025</p>
      <p><strong>Time:</strong> 5:00 PM – 4:00 AM</p>
      <p><strong>Venue:</strong> Gym at Laguna State Polytechnic University</p>
      <p><strong>Department:</strong> CCS</p>
      <p><strong>Requirements:</strong> <a href="#">View Attachment</a></p>
    </div>

    <div class="proposal-card">
      <span class="status disapproved">Disapproved</span>
      <h3>CCS Night</h3>
      <p><strong>Colleges:</strong> College of Computer Studies</p>
      <p><strong>Date:</strong> May 26–27, 2025</p>
      <p><strong>Time:</strong> 5:00 PM – 4:00 AM</p>
      <p><strong>Venue:</strong> Gym at Laguna State Polytechnic University</p>
      <p><strong>Department:</strong> CCS</p>
      <p><strong>Requirements:</strong> <a href="#">View Attachment</a></p>
      <button class="view-reason" onclick="showNotif('Your proposal is disapproved by Admin Juan. Please revise and resubmit.')">View Reason</button>
    </div>
  </div>

  <div id="completed" class="tab-content" style="display:none;">
    <p>No completed requests yet.</p>
  </div>
</div>

<div class="notification" id="notifBox">
  <strong>NOTIFICATION</strong><br>
  <span id="notifText"></span>
</div>

<script>
  function showTab(event, tabId) {
    document.querySelectorAll('.tab-content').forEach(el => el.style.display = 'none');
    document.querySelectorAll('.tab').forEach(el => el.classList.remove('active'));
    document.getElementById(tabId).style.display = 'block';
    event.target.classList.add('active');
  }

  function showNotif(message) {
    const notif = document.getElementById('notifBox');
    document.getElementById('notifText').innerText = message;
    notif.style.display = 'block';
    setTimeout(() => {
      notif.style.display = 'none';
    }, 6000);
  }
</script>

</body>
</html>
