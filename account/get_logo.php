<?php
$conn = new mysqli("localhost", "root", "", "eventplanner");
$res = $conn->query("SELECT * FROM site_logo ORDER BY date_uploaded DESC LIMIT 1");
if ($res && $res->num_rows > 0) {
  $logo = $res->fetch_assoc();
  header('Content-Type: application/json');
  echo json_encode($logo);
} else {
  echo json_encode(null);
}
?>
