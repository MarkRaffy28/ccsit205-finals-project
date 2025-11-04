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

    if (isset($_POST["invalid_date"])) {
      $_SESSION["msg"] = ["danger", "This date is not available for booking."];
      header("Location: " . $_SERVER["PHP_SELF"]);
    exit;
    }
  showAlert();
?>

<main class="py-4" data-bs-theme="light">
  <div class="mx-3">
    <h1 class="fw-semibold">Appointments</h1>
    <div class=" px-lg-6">
      <ul class="nav nav-pills mt-4 border-bottom border-primary" role="tablist">
        <li class="nav-item">
          <button class="nav-link active rounded-top-3 rounded-0 rounded-bottom-0" data-bs-toggle="tab" data-bs-target="#book" type="button">Book</button>
        </li>
        <li class="nav-item">
          <button class="nav-link rounded-top-3 rounded-0 rounded-bottom-0" data-bs-toggle="tab" data-bs-target="#request" type="button">Request</button>
        </li>
        <li class="nav-item">
          <button class="nav-link rounded-top-3 rounded-0 rounded-bottom-0" data-bs-toggle="tab" data-bs-target="#upcoming" type="button">Upcoming</button>
        </li>
        <li class="nav-item">
          <button class="nav-link rounded-top-3 rounded-0 rounded-bottom-0" data-bs-toggle="tab" data-bs-target="#history" type="button">History</button>
        </li>
      </ul>
    </div>
  </div>
  
  <section class="tab-content mx-3 px-lg-6">
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
            <input type="date" class="form-control datepicker" id="booking_date" name="booking_date" required>
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

    <div class="tab-pane show fade" id="upcoming" role="tabpanel">
      upcoming
    </div>

    <div class="tab-pane show fade" id="history" role="tabpanel">
      history
    </div>
  </section>
</main>

<script>
fetch("get_dates.php")
  .then(res => res.json())
  .then(data => {
    const availableDates = Object.keys(data);
    const dateInput = document.getElementById('booking_date');
    const form = dateInput.closest('form');

    dateInput.addEventListener('input', function () {
      const selectedDate = this.value;
      if (!availableDates.includes(selectedDate)) {
        // Create a hidden input to send error to PHP
        const errorInput = document.createElement('input');
        errorInput.type = 'hidden';
        errorInput.name = 'invalid_date';
        errorInput.value = selectedDate;
        form.appendChild(errorInput);

        // Submit the form automatically
        form.submit();
      } else {
        updateTimes(selectedDate, data[selectedDate]);
      }
    });
  })
  .catch(err => console.error("Error loading dates:", err));

function updateTimes(selectedDate, availableTimes) {
  const timeInput = document.getElementById('booking_time');
  console.log("Available times for", selectedDate, ":", availableTimes);
}
</script>



<?php
  showFooter();
?>
