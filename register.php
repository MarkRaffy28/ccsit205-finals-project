<?php
  session_start();
  include "config.php";
  include "components.php";
  
  if (isset($_POST["check_username"])) {
    $username = test_input($_POST["username"]);
    $password = test_input($_POST["password"]);
    
    $stmt = $conn->prepare("SELECT * FROM users WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
      $_SESSION["msg"] = ["danger", "Username already exists."];
    } else {
      $_SESSION["temp_username"] = $username;
      $_SESSION["temp_password"] = password_hash($password, PASSWORD_DEFAULT);
      $show_second_form = true;
    }
    $stmt->close();
  }
  
  if (isset($_POST["return"])) { 
    unset($show_second_form); 
    unset($_SESSION["temp_username"]);
    unset($_SESSION["temp_password"]);
  }
  
  if (isset($_POST["complete_registration"])) {
    if (isset($_SESSION["temp_username"]) && isset($_SESSION["temp_password"])) {
      $username = $_SESSION["temp_username"];
      $password = $_SESSION["temp_password"];
      $first_name = test_input($_POST["first_name"]);
      $middle_name = test_input($_POST["middle_name"]);
      $last_name = test_input($_POST["last_name"]);
      $extension_name = test_input($_POST["extension_name"]);
      $age = test_input($_POST["age"]);
      $gender = test_input($_POST["gender"]);
      $birth_date = test_input($_POST["birth_date"]);
      $contact_number = test_input($_POST["contact_number"]);
      $email_address = test_input($_POST["email_address"]);
      $address = test_input($_POST["address"]);
    
      $insert = $conn->prepare("INSERT INTO users(username, password, first_name, middle_name, last_name, extension_name, age, gender, birth_date, contact_number, email_address, address) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
      $insert->bind_param("ssssssisssss", $username, $password, $first_name, $middle_name, $last_name, $extension_name, $age, $gender, $birth_date, $contact_number, $email_address, $address);
      
      if ($insert->execute()) {
        $_SESSION["msg"] = ["success", "Registration Successfull."];
      } else {
        $_SESSION["msg"] = ["danger", "Registration Failed."];
      }
    } else {
      $_SESSION["msg"] = ["danger", "Session Expired. Please try again."];
    }
  }
  $conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width,initial-scale=1" />
  <title>Page Title</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
  <script defer src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.bundle.min.js"></script>
  <script src="https://kit.fontawesome.com/69faae9203.js" crossorigin="anonymous"></script>
  <link rel="stylesheet" href="stylesheet.css">
  <script defer src="JavaScript.js"></script>
  <style>
    main {
      min-height: 100vh;
      background:
        linear-gradient(rgba(255,255,255,0.8), rgba(255,255,255,0.8)), url("https://dl.dropbox.com/scl/fi/8c99mzgw618jhoy3woufr/login_background.jpg?rlkey=fmrp839osv7ywffxdwqz66bez&st=66ol4xqi&dl=0");
      background-position: center;
      background-size: cover;
      background-repeat: no-repeat;
    }
  </style>
</head>
<body>
  <main>
    <div class="modal show" style="display: block !important">
      <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable p-4">
        <div class="modal-content">
          <div class="modal-header border-0">
            <h4 class="modal-title w-100 text-center fw-bold m-0 p-0">Create an Account</h4>  
          </div>
          
          <div class="modal-body">
            <?= showAlert(); ?>
            <form method="POST" novalidate>
              <?php if(empty($show_second_form)): ?>
                <div class="row mb-2">
                  <div class="col-sm form-floating">
                    <input type="text" class="form-control" id="username" name="username" placeholder="Username" required>
                    <label for="username" class="form-label ps-4">Username</label>
                  </div>
                </div>
                <div class="row mb-2">
                  <div class="col-sm form-floating">
                    <input type="password" class="form-control" id="password" name="password" placeholder="Password" required>
                    <label for="password" class="form-label ps-4">Password</label>
                    <i class="bi bi-eye fs-4 eye"></i>
                  </div>
                </div>
                <div class="row m-2">
                <span class="text-end fs-7">Already Have an Account?<a href="login.php" class="link-primary"> Log In</a></span>
                <div class="d-flex justify-content-center mb-2">
                  <input type="submit" name="check_username" value="Next" class="btn btn-success mt-4">
                </div>                
                
              <?php else: ?>
                <div class="row mb-2 gx-3 gy-2">
                  <div class="col-sm form-floating">
                    <input type="text" class="form-control" id="first_name" name="first_name" placeholder="First Name" required>
                    <label for="first_name" class="form-label ps-4">First Name</label>
                  </div>
                  <div class="col-sm form-floating">
                    <input type="text" class="form-control" id="middle_name" name="middle_name" placeholder="Middle Name" required>
                    <label for="middle_name" class="form-label ps-4">Middle Name</label>
                  </div>
                </div>
                
                <div class="row mb-2 gx-3 gy-2">
                  <div class="col-sm form-floating">
                    <input type="text" class="form-control" id="last_name" name="last_name" placeholder="Last Name" required>
                    <label for="last_name" class="form-label ps-4">Last Name</label>
                  </div>
                  <div class="col-sm form-floating">
                    <input type="text" class="form-control" id="extension_name" name="extension_name" placeholder="Extension Name">
                    <label for="extension_name" class="form-label ps-4">Extension Name</label>
                  </div>
                </div>
                
                <div class="row mb-2 gx-3 gy-2">
                  <div class="col-sm form-floating">
                    <input type="number" class="form-control" id="age" name="age" placeholder="Age" required>
                    <label for="age" class="form-label ps-4">Age</label>
                  </div>
                  <div class="col-sm form-floating">
                    <select id="gender" name="gender" class="form-select ps-4" required>
                      <option value="" selected disabled>Select</option>
                      <option value="Male">Male</option>
                      <option value="Female">Female</option>
                      <option value="Other">Other</option>
                    </select>
                    <label for="gender" class="form-label ps-4">Select Gender</label>
                  </div>
                </div>
                
                <div class="row mb-2 gx-3 gy-2">
                  <div class="col-sm form-floating">
                    <input type="date" class="form-control" id="birth_date" name="birth_date" placeholder="Birth Date" required>
                    <label for="birth_date" class="form-label ps-4">Birth Date</label>
                  </div>
                  <div class="input-group mb-2">
                    <span class="input-group-text">+63</span>
                    <div class="form-floating flex-grow-1">
                      <input type="text" class="form-control" id="contact_number" name="contact_number" placeholder="Contact Number" required pattern="\d{10}" maxlength="10">
                      <label for="contact_nunber">Contact Number</label>
                    </div>
                  </div>
                </div>
                
                <div class="row mb-2 gx-3 gy-2">
                  <div class="col-sm form-floating">
                    <input type="email" class="form-control" id="email_address" name="email_address" placeholder="E-mail Address" required>
                    <label for="email_address" class="form-label ps-4">E-mail Address</label>
                  </div>
                  <div class="col-sm form-floating">
                    <textarea class="form-control" id="address" name="address" placeholder="Address" required></textarea>
                    <label for="address" class="form-label ps-4">Address</label>
                  </div>
                </div>
                
                <div class="row m-2">
                <span class="text-end fs-7">Already Have an Account?<a href="login.php" class="link-primary"> Log In</a></span>
                <div class="d-flex justify-content-center mb-2">
                  <input type="submit" name="return" value="Return" class="btn btn-danger mt-4 me-3" formnovalidate>
                  <input type="submit" name="complete_registration" value="Complete Registration" class="btn btn-success mt-4">
                </div>         
              <?php endif; ?>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>
  </main>
</body>
</html>
