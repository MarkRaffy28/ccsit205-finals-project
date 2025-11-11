<?php
  header('Content-Type: application/json');
  include "config.php";
  
  $sql = "SELECT id, date, start_time, end_time, slot_count FROM date_time_slots WHERE slot_count > 0 ORDER BY date,start_time";
  $result = $conn->query($sql);
  $available = [];
  
  while ($row = $result->fetch_assoc()) {
    $id = $row["id"];
    $date = $row['date'];
    $start = $row['start_time'];
    $end = $row['end_time'];
    $slot = $row['slot_count'];
    
    if (!isset($available[$date])) {
      $available[$date] = [];
    }
    
    $available[$date][] = [
      'id' => $id,
      'start_time' => $start,
      'end_time' => $end,
      'slot_count' => $slot
    ];
  }
  
  echo json_encode($available);
  $conn->close();
?>
