<?php
$conn = new mysqli("localhost", "root", "", "calendar");
if ($conn->connect_error) die("Connection failed: " . $conn->connect_error);

$sql = "SELECT * FROM proposals ORDER BY start_date DESC";
$result = $conn->query($sql);
function getColor($dept) {
    $colors = [
        'CS' => '#007bff',
        'IT' => '#28a745',
        'BSA' => '#dc3545',
        'HM' => '#6f42c1',
    ];
    return $colors[$dept] ?? '#343a40';
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Proposals</title>
    <style>
        body { font-family: Arial; background: #f8f9fa; margin: 0; padding: 20px; }
        .cards { display: flex; flex-wrap: nowrap; overflow-x: auto; gap: 20px; padding: 10px; }
        .card {
            flex: 0 0 300px;
            background: #fff;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
            padding: 20px;
            position: relative;
        }
        .badge {
            position: absolute;
            top: 15px;
            right: 15px;
            padding: 5px 10px;
            border-radius: 20px;
            color: #fff;
            font-size: 12px;
        }
        .attachments a {
            display: block;
            text-decoration: none;
            color: #007bff;
            font-size: 14px;
        }
        h3 { margin-top: 0; }
        .date-range { color: #555; font-size: 14px; margin-bottom: 10px; }
    </style>
</head>
<body>
