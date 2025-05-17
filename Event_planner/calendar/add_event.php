<?php
include 'connection.php';

$title = $_POST['title'];
$start = $_POST['start'];
$end = $_POST['end'];
$type = 'pending';

$check = "SELECT * FROM events WHERE (start <= '$end' AND end >= '$start') AND type != 'rejected'";
$result = $conn->query($check);

if ($result->num_rows > 0) {
  echo json_encode(["status" => "error", "message" => "Date already booked!"]);
} else {
  $sql = "INSERT INTO events (title, start, end, type) VALUES ('$title', '$start', '$end', '$type')";
  if ($conn->query($sql)) {
    echo json_encode(["status" => "success"]);
  } else {
    echo json_encode(["status" => "error", "message" => "Insert failed."]);
  }
}
?>
