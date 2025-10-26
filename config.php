<?php 
  define('DB_SERVER', 'localhost');
  define('DB_USERNAME', 'root');
  define('DB_PASSWORD', '');
  define('DB_NAME', 'dental_clinic');
 

  $conn = mysqli_connect(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);  
  
  if ($conn->connect_error) {
    die("Connection Failed: " . $con->connect_error);
  }
  $conn->set_charset("utf8");
?>