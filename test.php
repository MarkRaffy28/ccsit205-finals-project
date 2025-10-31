<?php
  session_start();
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Page title</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css">
  <script defer src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.bundle.min.js"></script>
  <script src="https://kit.fontawesome.com/69faae9203.js" crossorigin="anonymous"></script>
</head>
<body>
  <?php
    include "components.php";
    
    $_SESSION["msg"] = ["success", "Login successfully"];
    
    showAlert();
    
    
  ?>
  msg
</body>
</html>