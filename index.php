<?php
  session_start();
  //$_SESSION["username"] = "";
  unset($_SESSION["username"]);

?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width,  initial-scale=1.0"/>
  <title>Page title</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css">
  <script defer src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.bundle.min.js"></script>
  <script src="https://kit.fontawesome.com/69faae9203.js" crossorigin="anonymous"></script>
  
  <link rel="stylesheet" href="stylesheet.css">
  <script src="javascript.js"></script>
</head>
<body>
  <nav class="navbar navbar-expand-lg sticky-top" data-bs-theme="dark">
    <div class="container-fluid">
      <a class="navbar-brand" href="index.html">
        <img src="https://dl.dropbox.com/scl/fi/22oiirmwtu6sa1qcd4e8d/logi.jpg?rlkey=ola10jhyofqvvuf6bpct8nysi&st=tiqqmj0l&dl=0" class="logo">
        <span class="fw-bold navbar-title">DenCare V.A.U.L.T.</span>
      </a>
      <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbar-content">
        <i class="fa-solid fa-burger fa-1.5x"></i>
      </button>
      <div class="collapse navbar-collapse" id="navbar-content">
        <ul class="navbar-nav ms-auto mb-2 mb-lg-0">
          <li class="nav-item">
            <a class="nav-link active fw-bold" href="index.php">Home</a>
          </li>
          <li class="nav-item">
            <a class="nav-link fw-bold" href="services.php">Services</a>
          </li>
          <li class="nav-item">
            <a class="nav-link fw-bold" href="book_an_appointment.html">Book an Appointment</a>
          </li>
          <li class="nav-item">
            <a class="nav-link fw-bold" href="my_appointments.html">My Appointments</a>
          </li>
          <li class="nav-item">
            <a class="nav-link fw-bold" href="profile.html">Profile</a>
          </li>
        </ul>
        <form class="d-flex" role="search">
          <input class="form-control me-2" type="search" placeholder="Search">
          <button class="btn btn-success" type="submit">Search</button>
        </form>
      </div>
    </div>
  </nav>
  
  <main class="py-4">
    <section class="index-overview mx-3 mt-2 text-center">
      <h1 class="my-3 fw-semibold">Experience the <br> <span class="cb-gradient">Finest</span> in Dental Care</h1>
      <p class="fs-7">Trusted, world-class dental clinic since 2017. Discover your options. Visit us today!</p>
      <?php if(isset($_SESSION["username"])): ?>
        <a href="book_an_appointment.php" class="btn btn-lg bg-cyan mt-2 text-light rounded-5">Book an Appointment</a>
      <?php else: ?>
        <button class="btn btn-lg bg-cyan mt-2 text-light rounded-5" data-bs-toggle="modal" data-bs-target="#login-prompt" role="button">Book an Appointment</button>     
      <?php endif; ?>
    </section>
   
    <section class="mt-3 p-3 bg-blue">
      <div class="mx-2 mt-4 mb-5 text-center text-light">
        <h3 class="mb-3 fw-bolder">Our Dental Services</h3>
        <p>Trusted care and advanced solutions to restore, protect, and enhance your smile. From routine checkups to specialized treatments like implants, orthodontic, and cosmetic dentistry, DenCare V.A.U.L.T. is here to keep your smile healthy and beautiful.</p>    
      </div>
      
      <div class="services">
        <?php 
          $json_data = file_get_contents("services.json");
          $data = json_decode($json_data, true);
        
          foreach($data as $index => $service):
            if($index == 4) break;
        ?>
            <div class="service card rounded-4 p-2" role="button" data-bs-toggle="modal" data-bs-target="#modal_<?= $index; ?>">
              <img class="service-icon align-self-end mt-1" src="<?= $service["icon"]; ?>" alt="<?= $service["name"]; ?>"> 
              <h5 class="service-title fw-semibold"> <?= $service["name"] ?> </h5>
              <button class="btn btn-sm btn-primary rounded-3">Book</button>
            </div>
            <div class="modal fade p-3" id="modal_<?= $index; ?>" tabindex="-1">
              <div class="modal-dialog modal-dialog-scrollable modal-dialog-centered">
                <div class="modal-content">
                  <div class="modal-header border-0">
                    <h5 class="modal-title fw-bold">Service Information</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                  </div>     
                  <div class="modal-body">
                    <table class="service-table w-100">
                      <tr>
                        <th>Name:</th>
                        <td> <?= $service["name"]; ?> </td>
                      </tr>
                      <tr>
                         <th>Description:</th>
                        <td> <?= $service["description"]; ?> </td>
                      </tr>
                      <tr>
                        <th>Duration:</th>
                        <td> <?= $service["duration"]; ?> </td>
                      </tr>
                      <tr>
                        <th>Price</th>
                        <td> <?= $service["price"]; ?> </td>
                      </tr>
                    </table>
                  </div>
                  <div class="modal-footer border-0">
                    <button type="button" class="btn btn-secondary rounded-3 px-4" data-bs-dismiss="modal">Close</button>
                    <?php if(isset($_SESSION["username"])): ?>
                      <a href="book_an_appointment.php?service=<?= strtolower(str_replace(' ','_', $service["name"])); ?>" class="btn bg-primary text-light rounded-3 px-4">Book</a>
                    <?php else: ?>
                      <button class="btn bg-primary text-light rounded-3 px-4" data-bs-toggle="modal" data-bs-target="#login-prompt">Book</button>
                    <?php endif; ?>
                  </div>
                </div>
              </div>
            </div>     
        <?php endforeach; ?>
      </div>
      
      <div class="my-5 d-flex justify-content-center">
        <a href="services.php" class="btn btn-md btn-outline-light px-5 py-3 rounded-5">See Our List of Services</a>
      </div>
    </section>
    
    <section class="our-clinic mx-2 mt-5 row">
      <div class="col-md-4">
        <img class="rounded-3" height="200px" src="https://dl.dropbox.com/scl/fi/dbxdtpjwpr0v5aj3o1z5f/1761837597323.jpg?rlkey=76pu8ql6xj1wtvtal2kdjmyy0&st=emaxa2of&dl=0" alt="Procedure">
        <h2 class="fw-semibold my-4">Creating <span class="cb-gradient">Smiles.</span> Changing Lives</h2>
        <p>For us, creating smiles is more than crafting something beautiful. It’s about restoring function and oral health, enabling our patients to eat, speak, and live their lives with confidence. We believe a truly great smile transforms not only appearances, but also quality of life.</p>
        <p>At Dela Vega Dental Clinic, your trust is our greatest achievement, and your smile is our greatest reward.</p>
      </div>  
      
      <div class="clinic-information px-4 mt-5 mt-lg-0 col-md-4">
        <img class="rounded-3" src="https://dl.dropbox.com/scl/fi/6ykhpslsnwf5bdxy1353y/office.jpg?rlkey=3kuv5mtvlugmy92yf0jvn3ur6&st=dgmepuz7&dl=0" alt="Dental Office">
        <div class="clinic-card rounded-4 px-4 py-3">
          <h5>Dela Vega Dental Clinic</h5>
          <table class="mx-1">
            <tr>
              <td><i class="fa-solid fa-location-dot"></i></td>
              <td>ICA Building, Sta. Lucia Poblacion, Narvacan, Ilocos Sur</td>
            </tr>
            <tr>
              <td><i class="fa-solid fa-phone"></i></td>
              <td>(+63) 933-353-2123</td>
            </tr>
            <tr>
              <td><i class="fa-solid fa-phone"></i></td>
              <td>(+63) 955-482-1070</td>
            </tr>
          </table>
        </div>
      </div>      
        
      <div class="col-md-4">
        <div class="call-to-action px-3 py-5 rounded-3 d-flex flex-column align-items-center">
          <h1 class="fw-semibold text-white text-center">Your healthier and brighter smile starts here.</h1>
          <?php if(isset($_SESSION["username"])): ?>
            <a href="book_an_appointment.php" class="btn btn-lg bg-cyan mt-2 text-light rounded-5">Book an Appointment</a>
          <?php else: ?>
            <button class="btn btn-lg bg-cyan mt-2 text-light rounded-5" data-bs-toggle="modal" data-bs-target="#login-prompt" role="button">Book an Appointment</button>     
          <?php endif; ?>  
        </div>
      </div>
    </section>
    
    
    <div class="modal fade p-5" id="login-prompt" tabindex="-1">
      <div class="modal-dialog modal-dialog-scrollable modal-dialog-centered">
        <div class="modal-content rounded-4 shadow">
          <div class="modal-header border-0">
            <h5 class="modal-title fw-bold">LOGIN REQUIRED!</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>     
          <div class="modal-body text-center">
            <i class="fa-solid fa-user-lock fa-3x text-danger mb-3"></i>
            <p class="mb-0">You need to log in to access this feature.</p>
          </div>      
          <div class="modal-footer border-0 d-flex justify-content-center">
            <button type="button" class="btn btn-secondary rounded-3 px-4" data-bs-dismiss="modal">Close</button>
            <a href="login.php" class="btn bg-success text-light rounded-3 px-4">Log In</a>
          </div>
        </div>
      </div>
    </div>
        
  </main>
  
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
