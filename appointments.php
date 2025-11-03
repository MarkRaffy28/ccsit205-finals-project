<?php
  session_start();
  
  include "components.php";
  showHeader("Appointments", "appointments");
  
  if ($_SERVER["REQUEST_METHOD"] == "POST") {
    switch ($_POST["action"]) {
      case "book_appointment":
        $_SESSION["msg"] = ["success", $_POST["booking_service"]];
    }
  }
  
  showAlert();
?>

<main class="py-4" data-bs-theme="light">
  <div class="mx-3 border-bottom border-primary">
    <h1 class="fw-semibold">Appointments</h1>
    
    <ul class="nav nav-pills mt-4" role="tablist">
      <li class="nav-item">
        <button class="nav-link active rounded-top-3 rounded-0 rounded-bottom-0" data-bs-toggle="tab" data-bs-target="#book" type="button">Book</button>
      </li>
      <li class="nav-item">
        <button class="nav-link rounded-top-3 rounded-0 rounded-bottom-0" data-bs-toggle="tab" data-bs-target="#request" type="button">Request</button>
      </li>
    </ul>
  </div>
  
  <section class="tab-content mx-3">
    <div class="tab-pane show fade active" id="book" role="tabpanel">
      <div class="m-3">
        <h5 class="fw-semibold text-center">Book an Appointment</h5>
        <form method="POST" novalidate>
          <input type="hidden" name="action" value="book_appointment">
          <div class="form-floating">
            <select class="form-select mb-2" id="booking_service" name="booking_service" required>
              <option value="" selected disabled>--Select--</option>
              <?php 
                $json_data = file_get_contents("services.json");
                $data = json_decode($json_data, true);
      
                foreach($data as $service) {
                  $service_name = strtolower(str_replace(' ','_', $service["name"]));
                  $selected = (isset($_GET['book_service']) && $_GET["book_service"] == $service_name) ? "selected" : "";
                  
                  echo "
                    <option value='{$service_name}' {$selected}> {$service['name']} </option>
                  ";
                }
              ?>           
            </select>
            <label for="booking_service" class="form-label">Select service</label>
          </div>
          
          <div class="form-floating mb-2">
            <input type="date" class="form-control" id="booking_date" name="booking_date" required>
            <label for="booking_date" class="form-label">Select preferred date</label>
          </div>
          <div class="form-floating mb-2">
            <input type="time" class="form-control" id="booking_time" name="booking_time" required>
            <label for="booking_time" class="form-label">Select preferred time</label>
          </div>
          
          <div class="mt-4 d-flex justify-content-center">
            <input type="submit" class="btn btn-success">
          </div>
        </form>
      </div>
    </div>
    
    <div class="tab-pane show fade" id="request" role="tabpanel">
      request
    </div>
  </section>
</main>

<?php
  showFooter();
?>
