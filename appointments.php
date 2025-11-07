<?php
  session_start();

  if(!isset($_SESSION["id"]) || !isset($_SESSION["username"])) {
    header ("Location: index.php");
    exit();
  } elseif($_SESSION["username"] == "admin") {
    header ("Location: admin_dashboard.php");
    exit();
  }
  
  include "config.php";
  include "components.php";
  
  if (isset($_POST["invalid_date"])) {
    $_SESSION["msg"] = ["danger", "This date is not available for booking."];
    header("Location: " . $_SERVER["PHP_SELF"]);
    exit;
  }
  showAlert();
  
  if (isset($_POST["book_appointment"])) {
    $booking_patient_id = $_SESSION["id"];
    $booking_service_id = test_input($_POST["booking_service"]);
    $booking_slot_id = test_input($_POST["booking_time"]);
    
    $stmt_check_same_service_slot = $conn->prepare("SELECT id FROM appointments WHERE patient_id = ? AND slot_id = ? AND status != 'Cancelled'");
    $stmt_check_same_service_slot->bind_param("ii", $booking_patient_id, $booking_slot_id);
    $stmt_check_same_service_slot->execute();
    $stmt_check_same_service_slot->store_result();
    
    if ($stmt_check_same_service_slot->num_rows > 0) {
      $_SESSION["msg"] = ["danger", "You already have an active appointment for this slot. Please complete or cancel it before booking again."];
      header ("Location: " . $_SERVER["PHP_SELF"]);
      exit;
    }
    
    $stmt_check = $conn->prepare("SELECT id FROM appointments WHERE patient_id = ? AND service_id = ? AND status IN ('Pending', 'Approved')");
    $stmt_check->bind_param("ii", $booking_patient_id, $booking_service_id);
    $stmt_check->execute();
    $stmt_check->store_result();
    
    if ($stmt_check->num_rows > 0) {
      $_SESSION["msg"] = ["danger", "You already have an active appointment for this service. Please complete or cancel it before booking again."];
      header ("Location: " . $_SERVER["PHP_SELF"]);
      exit;
    } 
    
    $stmt_add_booking = $conn->prepare("INSERT INTO appointments(patient_id, service_id, slot_id) VALUES(?, ?, ?)");
    $stmt_add_booking->bind_param("iii", $booking_patient_id, $booking_service_id, $booking_slot_id);
    
    if (!$stmt_add_booking->execute()) {
      $_SESSION["msg"] = ["danger", "Booking error. Please try again later."];
      header ("Location: " . $_SERVER["PHP_SELF"]);
      exit;
    }
    
    $stmt_decrease_slot_count = $conn->prepare("UPDATE date_time_slots SET slot_count = slot_count - 1 WHERE id = ? AND slot_count > 0");
    $stmt_decrease_slot_count->bind_param("i", $booking_slot_id);
    
    if (!$stmt_decrease_slot_count->execute()) {
      $_SESSION["msg"] = ["danger", "Booking error. Please try again later."];
      header ("Location: " . $_SERVER["PHP_SELF"]);
      exit;
    }
    
    $_SESSION["msg"] = ["success", "Appointment request submitted successfully"];
    header ("Location: " . $_SERVER["PHP_SELF"]);
    exit;
  }  
  
  showHeader("Appointments");
?>

<main class="py-4" data-bs-theme="light">
  <div class="mx-3">
    <?= showAlert(); ?>
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
    <div class="tab-pane show fade" id="book" role="tabpanel">
      <div class="m-3">
        <h5 class="fw-semibold text-center">Book an Appointment</h5>
        <form method="POST" class="mx-md-5 my-md-4 px-md-5" novalidate>
          <div class="row mb-2 gx-3 gy-2">
            <div class="col-sm form-floating">
              <select class="form-select" id="booking_service" name="booking_service" required>
                <option value="" selected disabled>--Select--</option>
                <?php 
                  $json_data = file_get_contents("services.json");
                  $data = json_decode($json_data, true);
        
                  foreach($data as $service) {
                    $service_id = $service["id"];
                    $service_name = strtolower(str_replace(' ','_', $service["name"]));
                    $selected = (isset($_GET['book_service']) && $_GET["book_service"] == $service_name) ? "selected" : "";
                    
                    echo "
                      <option value='{$service_id}' {$selected}> {$service['name']} </option>
                    ";
                  }
                ?>           
              </select>
              <label for="booking_service" class="form-label">Select service</label>
            </div>
          </div>
          <div class="row mb-2 gx-3 gy-2">
            <div class="col-sm form-floating">
              <input type="date" class="form-control datepicker" id="booking_date" required>
              <label for="booking_date" class="form-label">Select preferred date</label>
            </div>
            <div class="col-sm form-floating">
              <select class="form-select" id="booking_time" name="booking_time" required>
                <option value="" selected disabled>-- Select a time --</option>
              </select>
              <label for="booking_time" class="form-label">Select preferred time</label>
            </div>    
          </div>
          <div class="mt-4 d-flex justify-content-center">
            <input type="submit" name="book_appointment" class="btn btn-success">
          </div>
        </form>
      </div>
    </div>
    
    <div class="tab-pane show fade" id="request" role="tabpanel">
      <div class="m-3">
        <h5 class="fw-semibold text-center">Appointment Request(s)</h5>
        <div class="container my-4">
          <?php
            $json_data = file_get_contents("services.json");
            $services = json_decode($json_data, true);

            $stmt_show_appt_requests = $conn->prepare("SELECT
                a.id AS appointment_id,
                service_id,
                d.date,
                d.start_time,
                d.end_time,
                a.status
              FROM appointments a
              JOIN date_time_slots d ON a.slot_id = d.id
              WHERE a.patient_id = ? AND a.status = 'Pending'
              ORDER BY d.date DESC, d.start_time ASC");
            $stmt_show_appt_requests->bind_param("i", $_SESSION["id"]);
            $stmt_show_appt_requests->execute();
            $requests_result = $stmt_show_appt_requests->get_result();
            
            while ($request_row = $requests_result->fetch_assoc()):
              $service_name = "Unknown Service";
              foreach ($services as $service) {
                  if ($service["id"] == $request_row["service_id"]) {
                      $service_name = $service["name"];
                      break;
                  }
              }
              
              $date = date("F j, Y", strtotime($request_row["date"]));
              $start_time =  date("H:i A", strtotime($request_row["start_time"]));
              $end_time =  date("H:i A", strtotime($request_row["end_time"]));
          ?>
              <div class="card border-0 shadow-sm rounded-4 mb-3">
                  <div class="card-body">
                    <div class="row justify-content-between align-items-center">
                      <div class="col-md-7">
                        <div class="d-flex justify-content-between align-items-center gap-2">
                          <h6 class="mb-0 fw-semibold"> <?= $service_name ?> </h6>
                          <small class="text-muted text-end text-md-center">
                            <span class="d-inline-block"> <?= "$date: " ?> </span>
                            <span class="d-inline-block"> <?= "$start_time - $end_time" ?> </span>
                          </small>
                        </div>
                      </div>
                      <div class="col-md-4 d-flex justify-content-between align-items-center flex-shrink-0 gap-2 mt-2 mt-md-0">
                        <span class="badge bg-warning text-dark px-3 py-2"> <?= $request_row["status"] ?> </span>
                        <div class="d-flex gap-2">
                          <button class="btn btn-sm btn-warning"><i class="bi bi-pencil me-1"></i>Edit</button>
                          <button class="btn btn-sm btn-danger"><i class="bi bi-x-circle me-1"></i>Cancel</button>
                        </div>
                      </div>
                    </div>
                  </div>
              </div>
          <?php
            endwhile;
          ?>
        </div>
      </div>
    </div>

    <div class="tab-pane show fade" id="upcoming" role="tabpanel">
      <div class="m-3">
        <h5 class="fw-semibold text-center">Upcoming Appointment(s)</h5>
        <div class="container my-4">
          <?php
            $json_data = file_get_contents("services.json");
            $services = json_decode($json_data, true);

            $stmt_show_appt_requests = $conn->prepare("SELECT
                a.id AS appointment_id,
                service_id,
                d.date,
                d.start_time,
                d.end_time,
                a.status
              FROM appointments a
              JOIN date_time_slots d ON a.slot_id = d.id
              WHERE a.patient_id = ? AND a.status = 'Approved'
              ORDER BY d.date DESC, d.start_time ASC");
            $stmt_show_appt_requests->bind_param("i", $_SESSION["id"]);
            $stmt_show_appt_requests->execute();
            $requests_result = $stmt_show_appt_requests->get_result();
            
            while ($request_row = $requests_result->fetch_assoc()):
              $service_name = "Unknown Service";
              foreach ($services as $service) {
                  if ($service["id"] == $request_row["service_id"]) {
                      $service_name = $service["name"];
                      break;
                  }
              }
              
              $date = date("F j, Y", strtotime($request_row["date"]));
              $start_time =  date("H:i A", strtotime($request_row["start_time"]));
              $end_time =  date("H:i A", strtotime($request_row["end_time"]));
          ?>
              <div class="card border-0 shadow-sm rounded-4 mb-3">
                  <div class="card-body">
                    <div class="row justify-content-between align-items-center">
                      <div class="col-md-7">
                        <div class="d-flex justify-content-between align-items-center gap-2">
                          <h6 class="mb-0 fw-semibold"> <?= $service_name ?> </h6>
                          <small class="text-muted text-end text-md-center">
                            <span class="d-inline-block"> <?= "$date: " ?> </span>
                            <span class="d-inline-block"> <?= "$start_time - $end_time" ?> </span>
                          </small>
                        </div>
                      </div>
                      <div class="col-md-4 d-flex justify-content-between align-items-center flex-shrink-0 gap-2 mt-2 mt-md-0">
                        <span class="badge bg-primary text-light px-3 py-2"> <?= $request_row["status"] ?> </span>
                        <div class="d-flex gap-2">
                          <button class="btn btn-sm btn-warning"><i class="bi bi-pencil me-1"></i>Edit</button>
                          <button class="btn btn-sm btn-danger"><i class="bi bi-x-circle me-1"></i>Cancel</button>
                        </div>
                      </div>
                    </div>
                  </div>
              </div>
          <?php
            endwhile;
          ?>
        </div>
      </div>
    </div>

    <div class="tab-pane show fade" id="history" role="tabpanel">
      <div class="m-3">
        <h5 class="fw-semibold text-center">Appointment Request(s)</h5>
        <div class="container my-4">
          <?php
            $json_data = file_get_contents("services.json");
            $services = json_decode($json_data, true);

            $stmt_show_appt_requests = $conn->prepare("SELECT
                a.id AS appointment_id,
                service_id,
                d.date,
                d.start_time,
                d.end_time,
                a.status,
                a.payment_amount
              FROM appointments a
              JOIN date_time_slots d ON a.slot_id = d.id
              WHERE a.patient_id = ?
              ORDER BY d.date DESC, d.start_time ASC");
            $stmt_show_appt_requests->bind_param("i", $_SESSION["id"]);
            $stmt_show_appt_requests->execute();
            $requests_result = $stmt_show_appt_requests->get_result();
            
            while ($request_row = $requests_result->fetch_assoc()):
              $service_name = "Unknown Service";
              foreach ($services as $service) {
                  if ($service["id"] == $request_row["service_id"]) {
                      $service_name = $service["name"];
                      break;
                  }
              }
              
              $date = date("F j, Y", strtotime($request_row["date"]));
              $start_time =  date("H:i A", strtotime($request_row["start_time"]));
              $end_time =  date("H:i A", strtotime($request_row["end_time"]));
          ?>
              <div class="card border-0 shadow-sm rounded-4 mb-3">
                  <div class="card-body">
                    <div class="row justify-content-between align-items-center">
                      <div class="col-md-7">
                        <div class="d-flex justify-content-between align-items-center gap-2">
                          <h6 class="mb-0 fw-semibold"> <?= $service_name ?> </h6>
                          <small class="text-muted text-end text-md-center">
                            <span class="d-inline-block"> <?= "$date: " ?> </span>
                            <span class="d-inline-block"> <?= "$start_time - $end_time" ?> </span>
                          </small>
                        </div>
                      </div>
                      <div class="col-md-4 d-flex justify-content-md-around align-items-center flex-shrink-0 gap-2 mt-2 mt-md-0">
                        <div>
                          <?php
                            switch ($request_row["status"]) {
                              case "Declined": $bg_text_color = "bg-danger text-white"; break;
                              case "Pending": $bg_text_color = "bg-warning text-dark"; break;
                              case "Approved": $bg_text_color = "bg-primary text-white"; break;
                              case "Completed": $bg_text_color = "bg-success text-white"; break;
                              case "Cancelled": $bg_text_color = "bg-secondary text-white"; break;
                            }
                          ?>
                          <span class="badge <?= $bg_text_color ?> px-3 py-2"> <?= $request_row["status"] ?> </span>
                        </div>
                        <div>
                          <span class="badge bg-purple text-white px-3 py-2"> <?= "$" . number_format($request_row["payment_amount"] ?? "0", thousands_separator: ", ") ?> </span>
                        </div>
                      </div>
                    </div>
                  </div>
              </div>
          <?php
            endwhile;
          ?>
        </div>
      </div>
    </div>
  </section>
</main>

<script>
  document.getElementById('booking_time').disabled = true;
  fetch("get_dates.php")
    .then(res => res.json())
    .then(data => {
      const availableDates = Object.keys(data);
      const dateInput = document.getElementById('booking_date');
      const form = dateInput.closest('form');
      
      const minDate = availableDates[0];
      const maxDate = availableDates[availableDates.length - 1];
      dateInput.min = minDate;
      dateInput.max = maxDate;
      
      dateInput.addEventListener('input', function () {
        const selectedDate = this.value;
        if (!availableDates.includes(selectedDate)) {
          const errorInput = document.createElement('input');
          errorInput.type = 'hidden';
          errorInput.name = 'invalid_date';
          errorInput.value = selectedDate;
          form.appendChild(errorInput);
          
          form.submit();
        } else {
          updateTimes(selectedDate, data[selectedDate]);
        }
      });
    })
    .catch(err => console.error("Error loading dates:", err));

  function updateTimes(selectedDate, availableTimes) {
    const timeSelect = document.getElementById('booking_time');
    timeSelect.innerHTML = '<option value="" selected disabled>-- Select a time --</option>';
    availableTimes.forEach(slot => {
      const option = document.createElement('option');
      option.value = slot.id;
      option.textContent = `${formatTime(slot.start_time)} - ${formatTime(slot.end_time)} | Available: ${slot.slot_count}`;
      timeSelect.appendChild(option);
    });
    timeSelect.disabled = availableTimes.length === 0;
  }
  
  function formatTime(timeStr) {
    const [hour, minute] = timeStr.split(':');
    let h = parseInt(hour);
    const ampm = h >= 12 ? 'PM' : 'AM';
    h = h % 12 || 12;
    return `${h}:${minute} ${ampm}`;
  }
</script>

<?php
  showFooter();
?>
