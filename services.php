<?php
  session_start();

  include "components.php";

  showHeader("Services","services")
?>

<main class="py-4">
  <section class="services-overview">
    <div class="px-3">
      <h1>Our Dental Services</h1>
      <p>Trusted care and advanced solutions to restore, protect, and enhance your smile. From routine checkups to specialized treatments like implants, orthodontic, and cosmetic dentistry, DenCare V.A.U.L.T. is here to keep your smile healthy and beautiful.</p>
      <?php if(isset($_SESSION["username"])): ?>
        <a href="appointments.php" class="btn btn-lg bg-cyan mt-2 text-light rounded-5">Book an Appointment</a>
      <?php else: ?>
        <button class="btn btn-lg bg-cyan mt-2 text-light rounded-5" data-bs-toggle="modal" data-bs-target="#login-prompt" role="button">Book an Appointment</button>     
      <?php endif; ?>
    </div>
  
    <div class="office-image mt-4 mt-lg-0 me-lg-3">
      <img src="https://dl.dropbox.com/scl/fi/6ykhpslsnwf5bdxy1353y/office.jpg?rlkey=3kuv5mtvlugmy92yf0jvn3ur6&st=dgmepuz7&dl=0" alt="Dental Office">
    </div>
  </section>
  
  <section class="services p-3 mt-lg-3">
    <?php 
      $json_data = file_get_contents("services.json");
      $data = json_decode($json_data, true);
      
      foreach($data as $index => $service):
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
                <a href="appointments.php?book_service=<?= strtolower(str_replace(' ','_', $service["name"])); ?>" class="btn bg-primary text-light rounded-3 px-4">Book</a>
              <?php else: ?>
                <button class="btn bg-primary text-light rounded-3 px-4" data-bs-toggle="modal" data-bs-target="#login-prompt">Book</button>
              <?php endif; ?>
            </div>
          </div>
        </div>
      </div>     
    <?php endforeach; ?>
  </section> 
  
  <section class="our-clinic mx-2 mt-4 row">
    <div class="px-4 col-md-4">
      <h1 class="fw-semibold text-center">Our Clinic</h1>
      <p class="text-center">Dela Vega Dental Clinic, we are dedicated to providing exceptional dental care in a warm and comfortable environment. Our team of skilled professionals combines advanced technology with personalized treatment to ensure every patient enjoys a healthy and confident smile. We focus on gentle, patient-centered care that makes every visit a positive experience since 2017.</p>
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
          <a href="appointments.php" class="btn btn-lg bg-cyan mt-2 text-light rounded-5">Book an Appointment</a>
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

<?php
  showFooter();
?>
