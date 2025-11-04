<?php
header('Content-Type: application/json');
include "config.php";

// Fetch available date-time slots
$sql = "SELECT date, start_time, end_time 
        FROM appointments 
        WHERE is_booked = 0 
        ORDER BY date, start_time";
$result = $conn->query($sql);

$available = [];

while ($row = $result->fetch_assoc()) {
  $date = $row['date'];
  $start = $row['start_time'];
  $end = $row['end_time'];

  if (!isset($available[$date])) {
    $available[$date] = [];
  }

  // Optional: store start & end time together
  $available[$date][] = [
    'start_time' => $start,
    'end_time' => $end
  ];
}

echo json_encode($available);
$conn->close();
?>
