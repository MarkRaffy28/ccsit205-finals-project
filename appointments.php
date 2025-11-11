<?php
  session_start();
  
  if(!isset($_SESSION["id"]) || !isset($_SESSION["username"])) {
    header ("Location: index.php");
    exit;
  } elseif($_SESSION["username"] == "admin") {
    header ("Location: admin_dashboard.php");
    exit;
  }
  
  include "config.php";
  include "components.php";
  
  if (isset($_POST["book_appointment"])) {
    $booking_patient_id = $_SESSION["id"];
    $booking_service_id = test_input($_POST["booking_service"]);
    $booking_slot_id = test_input($_POST["booking_time"]);
    
    $stmt_check_same_serv = $conn->prepare("SELECT id FROM appointments WHERE patient_id = ? AND service_id = ? AND status IN ('Pending', 'Approved')");
    $stmt_check_same_serv->bind_param("ii", $booking_patient_id, $booking_service_id);
    $stmt_check_same_serv->execute();
    $stmt_check_same_serv->store_result();
    
    if ($stmt_check_same_serv->num_rows > 0) {
      $_SESSION["msg"] = ["danger", "You already have an active appointment for this service. Please complete or cancel it before booking again."];
      header ("Location: " . $_SERVER["PHP_SELF"]);
      exit;
    } 
    
    $stmt_check_same_slot = $conn->prepare("SELECT id FROM appointments WHERE patient_id = ? AND slot_id = ? AND status IN ('Pending', 'Approved')");
    $stmt_check_same_slot->bind_param("ii", $booking_patient_id, $booking_slot_id);
    $stmt_check_same_slot->execute();
    $stmt_check_same_slot->store_result();
    
    if ($stmt_check_same_slot->num_rows > 0) {
      $_SESSION["msg"] = ["danger", "You already have an active appointment for this slot. Please complete or cancel it before booking again."];
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
    
    $stmt_dec_slot_count = $conn->prepare("UPDATE date_time_slots SET slot_count = slot_count - 1 WHERE id = ? AND slot_count > 0");
    $stmt_dec_slot_count->bind_param("i", $booking_slot_id);
    
    if (!$stmt_dec_slot_count->execute()) {
      $_SESSION["msg"] = ["danger", "Booking error. Please try again later."];
      header ("Location: " . $_SERVER["PHP_SELF"]);
      exit;
    }
    
    $_SESSION["msg"] = ["success", "Appointment request submitted successfully"];
    header ("Location: " . $_SERVER["PHP_SELF"]);
    exit;
  }
  
  if (isset($_POST["edit_appointment_request"])) {
    $edit_patient_id = $_SESSION["id"];
    $edit_appt_id = test_input($_POST["edit_appt_id"]);
    $edit_appt_service_id = test_input($_POST["edit_appt_service"]);
    $edit_appt_slot_id = test_input($_POST["edit_appt_time"]);;
    
    $stmt_check_same_serv = $conn->prepare("SELECT id FROM appointments WHERE patient_id = ? AND service_id = ? AND id != ? AND status IN ('Pending', 'Approved')");
    $stmt_check_same_serv->bind_param("iii", $edit_patient_id, $edit_appt_service_id, $edit_appt_id);
    $stmt_check_same_serv->execute();
    $stmt_check_same_serv->store_result();
    
    if ($stmt_check_same_serv->num_rows > 0) {
      $_SESSION["msg"] = ["danger", "You already have an active appointment for this service. Please complete or cancel it before booking again."];
      header ("Location: " . $_SERVER["PHP_SELF"]);
      exit;
    }
    
    $stmt_get_curr_slot_id = $conn->prepare("SELECT slot_id FROM appointments WHERE id = ?");
    $stmt_get_curr_slot_id->bind_param("i", $edit_appt_id);
    $stmt_get_curr_slot_id->execute();
    $stmt_get_curr_slot_id->bind_result($current_slot_id);
    $stmt_get_curr_slot_id->fetch();
    $stmt_get_curr_slot_id->close();
    
    if ($current_slot_id != $edit_appt_slot_id) {
        $stmt_check_same_slot = $conn->prepare("SELECT id FROM appointments WHERE patient_id = ? AND slot_id = ? AND status IN ('Pending', 'Approved')");
        $stmt_check_same_slot->bind_param("ii", $edit_patient_id, $edit_appt_slot_id);
        $stmt_check_same_slot->execute();
        $stmt_check_same_slot->store_result();
        
        if ($stmt_check_same_slot->num_rows > 0) {
          $_SESSION["msg"] = ["danger", "You already have an active appointment for this slot. Please complete or cancel it before booking again."];
          header ("Location: " . $_SERVER["PHP_SELF"]);
          exit;
        } 
        
        $stmt_incr_slot_count = $conn->prepare("UPDATE date_time_slots SET slot_count = slot_count + 1 WHERE id = ?");
        $stmt_incr_slot_count->bind_param("i", $current_slot_id);
        $stmt_incr_slot_count->execute();
        
        $stmt_dec_slot_count = $conn->prepare("UPDATE date_time_slots SET slot_count = slot_count - 1 WHERE id = ? AND slot_count > 0");
        $stmt_dec_slot_count->bind_param("i", $edit_appt_slot_id);
        $stmt_dec_slot_count->execute();
    }
    
    $stmt_updt_appt_reqs = $conn->prepare("UPDATE appointments SET
        service_id = ?,
        slot_id = ?
      WHERE id = ?");
    $stmt_updt_appt_reqs->bind_param("iii", $edit_appt_service_id, $edit_appt_slot_id, $edit_appt_id);
    
    if (!$stmt_updt_appt_reqs->execute()) {
      $_SESSION["msg"] = ["danger", "Update error. Please try again later."];
      header ("Location: " . $_SERVER["PHP_SELF"]);
      exit;
    }
    
    $_SESSION["msg"] = ["success", "Appointment request updated successfully"];
    header ("Location: " . $_SERVER["PHP_SELF"]);
    exit;
  }
  
  if (isset($_POST["cancel_appointment"])) {
    $cancel_appt_id = test_input($_POST["cancel_appt_id"]);
    
    $stmt_cancel_appt = $conn->prepare("UPDATE appointments SET status = 'Cancelled' WHERE id = ?");
    $stmt_cancel_appt->bind_param("i", $cancel_appt_id);
    
    if (!$stmt_cancel_appt->execute()) {
      $_SESSION["msg"] = ["danger", "Cancel error. Please try again later."];
      header ("Location: " . $_SERVER["PHP_SELF"]);
      exit;
    }
    
    $stmt_incr_slot_count = $conn->prepare("UPDATE date_time_slots SET slot_count = slot_count + 1 WHERE id = ?");
    $stmt_incr_slot_count->bind_param("i", $current_slot_id);
    $stmt_incr_slot_count->execute();
    
    $_SESSION["msg"] = ["success", "Appointment cancelled successfully"];
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
    <div class="tab-pane show fade active" id="book" role="tabpanel">
      <div class="m-4">
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
              <input type="text" class="form-control" id="booking_date" required>
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
                a.service_id,
                a.slot_id,
                d.date,
                d.start_time,
                d.end_time,
                d.slot_count,
                a.status
              FROM appointments a
              JOIN date_time_slots d ON a.slot_id = d.id
              WHERE a.patient_id = ? AND a.status = 'Pending'
              ORDER BY d.date DESC, d.start_time ASC");
            $stmt_show_appt_requests->bind_param("i", $_SESSION["id"]);
            $stmt_show_appt_requests->execute();
            $requests_result = $stmt_show_appt_requests->get_result();
            
            if ($requests_result->num_rows === 0) {
              echo '<p class="text-center text-muted fw-semibold mt-4">No appointments available.</p>';
            }
            while ($request_row = $requests_result->fetch_assoc()):
              $service_name = "Unknown Service";
              foreach ($services as $service) {
                  if ($service["id"] == $request_row["service_id"]) {
                      $service_name = $service["name"];
                      break;
                  }
              }
              
              $date = date("F j, Y", strtotime($request_row["date"]));
              $start_time =  date("h: i A", strtotime($request_row["start_time"]));
              $end_time =  date("h: i A", strtotime($request_row["end_time"]));
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
                          <button class="edit-button btn btn-sm btn-warning"
                            data-id="<?= $request_row["appointment_id"] ?>"
                            data-serviceid="<?= $request_row["service_id"] ?>"
                            data-slotid="<?= $request_row["slot_id"] ?>"
                            data-date="<?= $request_row["date"] ?>"
                            data-starttime="<?= $request_row["start_time"] ?>"
                            data-endtime="<?= $request_row["end_time"] ?>"
                            data-slotcount="<?= $request_row["slot_count"] ?>"
                          >
                            <i class="bi bi-pencil me-1"></i>Edit
                          </button>
                          <button class="cancel-button btn btn-sm btn-danger" data-id="<?= $request_row["appointment_id"] ?>">
                            <i class="bi bi-x-circle me-1"></i>Cancel
                          </button>
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
            
            $stmt_show_upcoming_appts = $conn->prepare("SELECT
                a.id AS appointment_id,
                a.service_id,
                d.date,
                d.start_time,
                d.end_time,
                a.status
              FROM appointments a
              JOIN date_time_slots d ON a.slot_id = d.id
              WHERE a.patient_id = ? AND a.status = 'Approved'
              ORDER BY d.date DESC, d.start_time ASC");
            $stmt_show_upcoming_appts->bind_param("i", $_SESSION["id"]);
            $stmt_show_upcoming_appts->execute();
            $requests_result = $stmt_show_upcoming_appts->get_result();
            
            if ($requests_result->num_rows === 0) {
              echo '<p class="text-center text-muted fw-semibold mt-4">No appointments available.</p>';
            }
            while ($request_row = $requests_result->fetch_assoc()):
              $service_name = "Unknown Service";
              foreach ($services as $service) {
                  if ($service["id"] == $request_row["service_id"]) {
                      $service_name = $service["name"];
                      break;
                  }
              }
              
              $date = date("F j, Y", strtotime($request_row["date"]));
              $start_time =  date("h: i A", strtotime($request_row["start_time"]));
              $end_time =  date("h: i A", strtotime($request_row["end_time"]));
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
                          <button class="cancel-button btn btn-sm btn-danger" data-id="<?= $request_row["appointment_id"] ?>"><i class="bi bi-x-circle me-1"></i>Cancel</button>
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
        <h5 class="fw-semibold text-center">Appointments History</h5>
        <div class="container my-4">
          <?php
            $json_data = file_get_contents("services.json");
            $services = json_decode($json_data, true);
            
            $stmt_show_appt_history = $conn->prepare("SELECT
                a.id AS appointment_id,
                a.service_id,
                d.date,
                d.start_time,
                d.end_time,
                a.status,
                a.payment_amount
              FROM appointments a
              JOIN date_time_slots d ON a.slot_id = d.id
              WHERE a.patient_id = ?
              ORDER BY d.date DESC, d.start_time ASC");
            $stmt_show_appt_history->bind_param("i", $_SESSION["id"]);
            $stmt_show_appt_history->execute();
            $requests_result = $stmt_show_appt_history->get_result();
            
            if ($requests_result->num_rows === 0) {
              echo '<p class="text-center text-muted fw-semibold mt-4">No appointments available.</p>';
            }
            while ($request_row = $requests_result->fetch_assoc()):
              $service_name = "Unknown Service";
              foreach ($services as $service) {
                  if ($service["id"] == $request_row["service_id"]) {
                      $service_name = $service["name"];
                      break;
                  }
              }
              
              $date = date("F j, Y", strtotime($request_row["date"]));
              $start_time =  date("h: i A", strtotime($request_row["start_time"]));
              $end_time =  date("h: i A", strtotime($request_row["end_time"]));
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
                              case "Declined": $bg_text_color = "bg-danger text-white"; $icon = "ban"; break;
                              case "Pending": $bg_text_color = "bg-warning text-dark"; $icon = "hourglass-split"; break;
                              case "Approved": $bg_text_color = "bg-primary text-white"; $icon = "check-circle"; break;
                              case "Completed": $bg_text_color = "bg-success text-white"; $icon = "check-square"; break;
                              case "Cancelled": $bg_text_color = "bg-secondary text-white"; $icon = "x-circle"; break;
                            }
                          ?>
                          <span class="badge <?= $bg_text_color ?> align-middle px-3 py-2">
                            <i class="bi bi-<?= $icon; ?>"></i> 
                            <?= $request_row["status"] ?> 
                          </span>
                        </div>
                        <div>
                          <span class="badge bg-purple text-white px-3 py-2"> <?= "₱" . number_format($request_row["payment_amount"] ?? "0", thousands_separator: ", ") ?> </span>
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
  
  
  <div class="modal fade p-4" id="edit_appointment_request" tabindex="-1">  
    <div class="modal-dialog modal-dialog-scrollable modal-dialog-centered">
      <div class="modal-content rounded-4 shadow">
        <div class="modal-header border-0">
          <h5 class="modal-title w-100 text-center fw-bold">EDIT APPOINTMENT REQUEST</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>     
        <div class="modal-body text-center">
          <form method="POST" novalidate>
            <input type="hidden" id="edit_appt_id" name="edit_appt_id">
            <div class="row mb-2 gx-3 gy-2">
              <div class="col-sm form-floating">
                <select class="form-select" id="edit_appt_service" name="edit_appt_service" required>
                  <option value="" selected disabled>--Select--</option>
                  <?php 
                    $json_data = file_get_contents("services.json");
                    $data = json_decode($json_data, true);
                    foreach($data as $service) {
                      echo "<option value='{$service["id"]}'>{$service["name"]}</option>";
                    }
                  ?>           
                </select>
                <label for="edit_appt_service" class="form-label">Select service</label>
              </div>
            </div>
            
            <div class="row mb-2 gx-3 gy-2">
              <div class="col-sm form-floating">
                <input type="text" class="form-control" id="edit_appt_date" required>
                <label for="edit_appt_date" class="form-label">Select preferred date</label>
              </div>
              <div class="col-sm form-floating">
                <select class="form-select" id="edit_appt_time" name="edit_appt_time" required>
                  <option value="" selected disabled>-- Select a time --</option>
                </select>
                <label for="edit_appt_time" class="form-label">Select preferred time</label>
              </div>    
            </div>
            
            <div class="mt-4 d-flex justify-content-center">
              <button type="button" class="btn btn-md btn-danger rounded-3 px-3 me-3" data-bs-dismiss="modal">Cancel</button>
              <input type="submit" name="edit_appointment_request" value="Update" class="btn btn-success">
            </div>
          </form>
        </div>      
      </div>
    </div>
  </div>
  
  <div class="modal fade p-4" id="cancel_appointment" tabindex="-1">
    <div class="modal-dialog modal-dialog-scrollable modal-dialog-centered">
      <div class="modal-content rounded-4 shadow">
        <div class="modal-header border-0">
          <h5 class="modal-title w-100 text-center fw-bold">CANCEL APPOINTMENT?</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>     
        <div class="modal-body text-center">
          <i class="fa-solid fa-triangle-exclamation fa-4x text-danger mb-3"></i>
          <p class="mb-0 px-2">Are you sure you want to this appointment? This action cannot be undone.</p>
        </div>      
        <div class="modal-footer border-0 d-flex justify-content-center">
          <button type="button" class="btn btn-secondary rounded-3 px-4" data-bs-dismiss="modal">Close</button>
          <form method="POST">
            <input type="hidden" name="cancel_appt_id" id="cancel_appt_id">
            <input type="submit" name="cancel_appointment" value="Yes, Cancel" class="btn bg-danger text-light rounded-3 px-4">
          </form>
        </div>
      </div>
    </div>
  </div>
</main>

<script>
  document.getElementById('booking_time').disabled = true;
  
  $(document).ready(function() {
    const now = new Date();
    const utc8 = new Date(now.getTime() + (8 * 60 - now.getTimezoneOffset()) * 60000);
    
    const yyyy = utc8.getFullYear();
    const mm = String(utc8.getMonth() + 1).padStart(2, '0');
    const dd = String(utc8.getDate()).padStart(2, '0');
    const hh = String(utc8.getHours()).padStart(2, '0');
    const min = String(utc8.getMinutes()).padStart(2, '0');
    const ss = String(utc8.getSeconds()).padStart(2, '0');      
    const todayStr = `${yyyy}-${mm}-${dd}T${hh}:${min}:${ss}+08:00`;
    
    const dateInput = $("#booking_date");
    const timeSelect = $("#booking_time");
    
    fetch("get_dates.php")
      .then(res => res.json())
      .then(data => {
        const availableDates = Object.keys(data);
        
        if (availableDates.length === 0) {
          dateInput.prop("disabled", true);
          dateInput.attr("placeholder", "No available dates");
          return;
        }
        
        dateInput.datepicker({
          format: "MM dd, yyyy",
          autoclose: true,
          startDate: todayStr,
          todayHighlight: true,
          orientation: "bottom",
          todayBtn: true,
          beforeShowDay: function(date) {
            const utc8Date = new Date(date.getTime() + 8 * 60 * 60 * 1000);
            const formatted = utc8Date.toISOString().split("T")[0];
            return (availableDates.includes(formatted)) ? { enabled: true } : false;
          }
        });
        
        dateInput.attr("placeholder", "Select available date");
        dateInput.on("changeDate", function(e) {
          const selectedDateUTC8 = new Date(e.date.getTime() + 8 * 60 * 60 * 1000);
          const selectedDate = selectedDateUTC8.toISOString().split("T")[0];
          
          if (data[selectedDate]) {
            updateTimes(selectedDate, data[selectedDate]);
          } else {
            timeSelect.html('<option value="" selected disabled>-- Select a time --</option>');
            timeSelect.prop("disabled", true);
          }
        });
      })
    .catch(err => console.error("Error loading dates:", err));
    
    function updateTimes(selectedDate, availableTimes) {
      timeSelect.html('<option value="" selected disabled>-- Select a time --</option>');
      availableTimes.forEach(slot => {
        const option = $("<option>")
          .val(slot.id)
          .text(`${formatTime(slot.start_time)} - ${formatTime(slot.end_time)} | Available: ${slot.slot_count}`);
        timeSelect.append(option);
      });
      timeSelect.prop("disabled", availableTimes.length === 0);
    }
    
    function formatTime(timeStr) {
      const [hour, minute] = timeStr.split(':');
      let h = parseInt(hour);
      const ampm = h >= 12 ? 'PM' : 'AM';
      h = h % 12 || 12;
      return `${h}:${minute} ${ampm}`;
    }
    
    document.querySelectorAll(".edit-button").forEach(btn => {
      btn.addEventListener("click", () => {
        const editSlotId = btn.dataset.slotid;
        const editDate = btn.dataset.date;
        const editServiceId = btn.dataset.serviceid;
        
        document.getElementById("edit_appt_id").value = btn.dataset.id;
        document.getElementById("edit_appt_service").value = editServiceId;
        
        const editDateInput = $("#edit_appt_date");
        const editTimeSelect = $("#edit_appt_time");
        
        fetch("get_dates.php")
          .then(res => res.json())
          .then(data => {
            const availableDates = Object.keys(data);
            
            editDateInput.datepicker({
              format: "MM dd, yyyy",
              autoclose: true,
              startDate: todayStr, 
              todayHighlight: true,
              orientation: "top",
              clearBtn: true,
              todayBtn: true,
              beforeShowDay: function(date) {
                const formatted = new Date(date.getTime() + 8*60*60*1000).toISOString().split("T")[0];
                return availableDates.includes(formatted) ? { enabled: true } : false;
              }
            });
            
            editDateInput.datepicker("update", new Date(editDate + "T00:00:00+08:00"));
            
            const slots = data[editDate] || [];
            editTimeSelect.html('<option value="" selected disabled>-- Select a time --</option>');
            slots.forEach(slot => {
              const option = $("<option>")
                .val(slot.id)
                .text(`${formatTime(slot.start_time)} - ${formatTime(slot.end_time)} | Available: ${slot.slot_count}`);
              if (slot.id == editSlotId) option.prop("selected", true);
              editTimeSelect.append(option);
            });
            editTimeSelect.prop("disabled", slots.length === 0);
            
            editDateInput.on("changeDate", function(e) {
              const selectedDate = new Date(e.date.getTime() + 8*60*60*1000).toISOString().split("T")[0];
              const slots = data[selectedDate] || [];
              editTimeSelect.html('<option value="" selected disabled>-- Select a time --</option>');
              slots.forEach(slot => {
                const option = $("<option>")
                  .val(slot.id)
                  .text(`${formatTime(slot.start_time)} - ${formatTime(slot.end_time)} | Available: ${slot.slot_count}`);
                editTimeSelect.append(option);
              });
              editTimeSelect.prop("disabled", slots.length === 0);
            });
          })
          .catch(err => console.error("Error loading dates:", err));
        now
        const modal = new bootstrap.Modal(document.getElementById("edit_appointment_request"));
        modal.show();
      });
    });
  })
  
  document.querySelectorAll(".cancel-button").forEach(btn => {
    btn.addEventListener("click", ()=> {
      document.getElementById("cancel_appt_id").value = btn.dataset.id;
      
      const modal = new bootstrap.Modal(document.getElementById("cancel_appointment"));
      modal.show();
    });
  });
</script>

<?php
  showFooter();
?>
