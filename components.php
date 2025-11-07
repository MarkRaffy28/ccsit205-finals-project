<?php 
  function test_input($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
  } 

//
  function showHeader($pageTitle) {
    $pageName = ($pageTitle == "Home") ? "index" : strtolower(str_replace(" ","_",$pageTitle));
?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
      <meta charset="UTF-8">
      <meta name="viewport" content="width=device-width,  initial-scale=1.0"/>
      <title> <?= ($pageTitle) ? "$pageTitle | DenCare V.A.U.L.T." : "DenCare V.A.U.L.T." ?> </title>
      <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css">
      <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
      <script defer src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.bundle.min.js"></script>
      <script src="https://kit.fontawesome.com/69faae9203.js" crossorigin="anonymous"></script>
      
      <link rel="stylesheet" href="stylesheet.css">
      <script defer src="javascript.js"></script>
    </head>
    <body>
      <nav class="navbar navbar-expand-lg sticky-top" data-bs-theme="dark">
        <div class="container-fluid">
          <a class="navbar-brand" href="index.php">
            <img src="https://dl.dropbox.com/scl/fi/22oiirmwtu6sa1qcd4e8d/logi.jpg?rlkey=ola10jhyofqvvuf6bpct8nysi&st=tiqqmj0l&dl=0" class="logo">
            <span class="fw-bold navbar-title">DenCare V.A.U.L.T.</span>
          </a>
          <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbar-content">
            <i class="fa-solid fa-burger fa-1.5x"></i>
          </button>
          <div class="collapse navbar-collapse" id="navbar-content">
            <ul class="navbar-nav ms-auto mb-2 mb-lg-0">
              <li class="nav-item">
                <a class="nav-link <?= ($pageName == "index") ? "active" : "" ?> fw-bold" href="index.php">Home</a>
              </li>
              <li class="nav-item">
                <a class="nav-link <?= ($pageName == "services") ? "active" : "" ?> fw-bold" href="services.php">Services</a>
              </li>
              <li class="nav-item">
                <a class="nav-link <?= ($pageName == "appointments") ? "active" : "" ?> fw-bold" href="appointments.php">Appointments</a>
              </li>
              <li class="nav-item">
                <a class="nav-link <?= ($pageName == "profile") ? "active" : "" ?> fw-bold" href="profile.php">Profile</a>
              </li>
            </ul>
            <?php if(isset($_SESSION["id"]) && isset($_SESSION["username"])): ?>
              <form method="POST" action="logout.php">
                <input type="submit" value="Logout" class="btn btn-sm btn-danger ms-lg-2">
              </form>
            <?php else: ?>
              <a href="login.php" class="btn btn-sm btn-success ms-lg-2">Login</a>
            <?php endif; ?>
          </div>
        </div>
      </nav>
<?php
  }


//
  function showFooter() {
?>
      <footer class="pt-4">
        <div class="container">
          <div class="row">
            <div class="col-md-3 mb-3">
              <h5 class="fw-bold">About Us</h5>
              <p>Our clinic is dedicated to providing quality and compassionate care for every patient.</p>
            </div>
            <div class="contact-us col-md-3 mb-3">
              <h5 class="fw-bold">Contact Us</h5>
              <ul class="list-unstyled">
                <li>
                  <a href="#"><i class="fa-solid fa-location-dot"></i>ICA Building, Sta. Lucia Poblacion, Narvacan, Ilocos Sur</a>     
                </li>      
                <li>
                  <a href="#"><i class="fa-solid fa-phone"></i>(+63) 933-353-2123</a>
                </li>
                <li>
                  <a href="#"><i class="fa-solid fa-phone"></i>(+63) 955-482-1070</a>
                </li>             
              </ul>
            </div>
            <div class="col-md-3 mb-4">
              <h5 class="fw-bold">Follow Us</h5>
              <a href=""><i class="fa-brands fa-facebook"></i></a>
            </div>
            <div class="col-md-3 mb-3">
              <h5 class="fw-bold">Legal</h5>
              <ul class="list-unstyled">
                <li><a href="#">Privacy</a></li>
                <li><a href="#">Terms of Use</a></li>
              </ul>
            </div>
          </div>
          <div class="text-center py-3 border-top mt-3">
            © 2025 DenCare V.A.U.L.T.
          </div>
        </div>
      </footer>  
    </body>
    </html>
<?php
  }


  //
  function showAlert() {  
    if(isset($_SESSION["msg"])): 
      $type = $_SESSION["msg"][0];
      $message = $_SESSION["msg"][1];
      $icon = ($type == "success") ? "check-circle-fill" : "exclamation-triangle-fill"
?>
      <svg xmlns="http://www.w3.org/2000/svg" class="d-none">
        <symbol id="check-circle-fill" viewBox="0 0 16 16">
          <path d="M16 8A8 8 0 1 1 0 8a8 8 0 0 1 16 0zm-3.97-3.03a.75.75 0 0 0-1.08.022L7.477 9.417 5.384 7.323a.75.75 0 0 0-1.06 1.06L6.97 11.03a.75.75 0 0 0 1.079-.02l3.992-4.99a.75.75 0 0 0-.01-1.05z" fill="currentColor"/>
        </symbol>
        <symbol id="exclamation-triangle-fill" viewBox="0 0 16 16">
          <path d="M8.982 1.566a1.13 1.13 0 0 0-1.96 0L.165 13.233c-.457.778.091 1.767.98 1.767h13.713c.889 0 1.438-.99.98-1.767L8.982 1.566zM8 5c.535 0 .954.462.9.995l-.35 3.507a.552.552 0 0 1-1.1 0L7.1 5.995A.905.905 0 0 1 8 5zm.002 6a1 1 0 1 1 0 2 1 1 0 0 1 0-2z" fill="currentColor"/>
        </symbol>
      </svg>
      
      <div class="alert show fade m-2 alert-<?= $type; ?> d-flex align-items-center" role="alert">
        <svg width="20" height="20" class="bi text-<?= $type ?> flex-shrink-0 me-2" role="img" aria-label="Info:"><use xlink:href="#<?= $icon ?>"/></svg>
        <div class="text-<?= $type ?>"> <?= $message; ?> </div>
        <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert" aria-label="Close"></button>
      </div>
      
      <script>
        setTimeout(() => {
          var alert = document.querySelector(".alert");
          if(alert) {
            var bsAlert = new bootstrap.Alert(alert);
            bsAlert.close();
          }
        }, 3000)
      </script>
<?php
      unset($_SESSION["msg"]);
    endif;
  }


  //
  function showAdminSidebar($pageTitle) {
    if (!isset($_SESSION["username"]) && !($_SESSION["username"]) == "admin") {
      header ("Location: index.php");
      exit();
    }

    $pageName = ($pageTitle == "Home") ? "index" : strtolower(str_replace(" ","_",$pageTitle));
?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
      <meta charset="UTF-8">
      <meta name="viewport" content="width=device-width,  initial-scale=1.0"/>
      <title> <?= ($pageTitle) ? "Admin $pageTitle | DenCare V.A.U.L.T." : "DenCare V.A.U.L.T." ?> </title>
      <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css">
      <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">

      <script defer src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.bundle.min.js"></script>
      <script src="https://kit.fontawesome.com/69faae9203.js" crossorigin="anonymous"></script>
      
      <link rel="stylesheet" href="stylesheet.css?v=<?= time(); ?>">
      <script defer src="javascript.js"></script>
    </head>
    <body>
      <main>
        <div class="container-fluid">
          <div class="row flex-nowrap">
            <div class="col-2 col-md-2 col-xl-2 px-sm-2 px-0 bg-dark">
              <div class="d-flex flex-column align-items-sm-start px-3 pt-2 text-white min-vh-100">
                <a href="#" class="d-flex align-items-center pb-3 mb-md-0 me-md-auto text-white text-decoration-none">
                  <span class="fs-5 d-none d-sm-inline">Admin Panel</span>
                </a>
                <ul class="nav nav-pills flex-column mb-sm-auto mb-0 align-items-sm-start" id="menu">
                  <li class="nav-item">
                    <a href="admin_dashboard.php" class="nav-link <?= ($pageName == "dashboard") ? "fw-bold" : "" ?> text-white px-0 align-middle">
                      <span><i class="bi bi-speedometer"></i> <span class="ms-1 d-none d-sm-inline">Dashboard</span></span>  
                  </a>
                  </li>
                  
                  <li>
                    <a href="admin_users.php" class="nav-link <?= ($pageName == "users") ? "fw-bold" : "" ?> text-white px-0 align-middle">
                      <i class="bi bi-people"></i> <span class="ms-1 d-none d-sm-inline">Users</span>
                    </a>
                  </li>
                  
                  <li>
                    <a class="nav-link text-white px-0 align-middle d-flex justify-content-between" data-bs-toggle="collapse" href="#submenuAppointments" role="button" aria-expanded="false" aria-controls="submenuAppointments">
                      <span><i class="bi bi-calendar"></i> <span class="ms-1 d-none d-sm-inline">Appointments</span></span>
                      <span class="ps-1"><i class="bi bi-chevron-down"></i></span>
                    </a>
                  
                    <div class="collapse ps-1" id="submenuAppointments">
                      <ul class="nav flex-column">
                        <li>
                          <a href="admin_appointments_book.php" class="nav-link <?= ($pageName == "book_-_appointments") ? "fw-bold" : "" ?> text-white px-0">
                          <span><i class="bi bi-book"></i> <span class="ms-1 d-none d-sm-inline">Book</span></span>
                          </a>
                        </li>
                      </ul>
                    </div>
                  </li>  
                  
                  <li>
                    <a href="#" class="nav-link text-white px-0 align-middle">
                      <i class="bi bi-currency-dollar"></i> <span class="ms-1 d-none d-sm-inline">Sales</span>
                    </a>
                  </li>
                  
                  <li>
                    <a href="#" class="nav-link text-white px-0 align-middle">
                      <i class="bi bi-hourglass"></i> <span class="ms-1 d-none d-sm-inline">History</span>
                    </a>
                  </li>
                  
                  <li>
                    <a href="admin_datetime_slots.php" class="nav-link <?= ($pageName == "date_&_time_slots") ? "fw-bold" : "" ?> text-white px-0 align-middle">
                      <i class="bi bi-clock"></i> <span class="ms-1 d-none d-sm-inline">Date & Time Slots</span>
                    </a>
                  </li>

                  <li>
                    <a href="logout.php" class="nav-link text-danger px-0 align-middle">
                      <i class="bi bi-box-arrow-right"></i> <span class="ms-1 d-none d-sm-inline">Logout</span>
                    </a>
                  </li>
                </ul>
              </div>
            </div>
            <div class="col-10 col-md-10 col-xl-10">
<?php
  }


  //
  function showAdminFooter() {
?>
            </div>
          </div>
        </div>
      </main>
    </body>
    </html>
<?php
  }
?>