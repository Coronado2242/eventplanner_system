<?php
include 'connection.php';

$result = $conn->query("SELECT * FROM events");
$events = [];

while ($row = $result->fetch_assoc()) {
  $color = 'orange'; // default is pending
  if ($row['type'] == 'approved') $color = 'green';
  if ($row['type'] == 'not available') $color = 'red';

  $events[] = [
    'id' => $row['id'],
    'title' => $row['title'],
    'start' => $row['start'],
    'end' => $row['end'],
    'color' => $color
  ];
}

echo json_encode($events);
?>
