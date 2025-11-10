<?php
  session_start();
  
  include "config.php";
  include "components.php";
  
  if (!isset($_GET["user_id"])) {
    $_SESSION["msg"] = ["danger", "Missing parameter: User ID."];
    header ("Location: admin_dashboard.php");
    exit;
  }
  
  $stmt_chck_user_id = $conn->prepare("SELECT id FROM users WHERE id = ?");
  $stmt_chck_user_id->bind_param("i", $_GET["user_id"]);
  $stmt_chck_user_id->execute();
  $stmt_chck_user_id->store_result();
  
  if ($stmt_chck_user_id->num_rows <= 0) {
    $_SESSION["msg"] = ["danger", "User does not exist"];
    header ("Location: admin_users.php");
    exit;
  }
  
  if (isset($_POST["book_appointment"])) {
      $booking_patient_id = $_GET["user_id"];
      $booking_service_id = test_input($_POST["booking_service"]);
      $booking_slot_id = test_input($_POST["booking_time"]);
      
      $stmt_check_same_serv = $conn->prepare("SELECT id FROM appointments WHERE patient_id = ? AND service_id = ? AND status IN ('Pending', 'Approved')");
      $stmt_check_same_serv->bind_param("ii", $booking_patient_id, $booking_service_id);
      $stmt_check_same_serv->execute();
      $stmt_check_same_serv->store_result();
      
      if ($stmt_check_same_serv->num_rows > 0) {
        $_SESSION["msg"] = ["danger", "This user already have an active appointment for this service."];
        header ("Location: admin_users.php");
        exit;
      } 
      
      $stmt_check_same_slot = $conn->prepare("SELECT id FROM appointments WHERE patient_id = ? AND slot_id = ? AND status IN ('Pending', 'Approved')");
      $stmt_check_same_slot->bind_param("ii", $booking_patient_id, $booking_slot_id);
      $stmt_check_same_slot->execute();
      $stmt_check_same_slot->store_result();
      
      if ($stmt_check_same_slot->num_rows > 0) {
        $_SESSION["msg"] = ["danger", "This user already have an active appointment for this slot."];
        header ("Location: admin_users.php");
        exit;
      } 
      
      $stmt_add_booking = $conn->prepare("INSERT INTO appointments(patient_id, service_id, slot_id) VALUES(?, ?, ?)");
      $stmt_add_booking->bind_param("iii", $booking_patient_id, $booking_service_id, $booking_slot_id);
      
      if (!$stmt_add_booking->execute()) {
        $_SESSION["msg"] = ["danger", "Booking error. Please try again later."];
        header ("Location: admin_users.php");
        exit;
      }
      
      $stmt_dec_slot_count = $conn->prepare("UPDATE date_time_slots SET slot_count = slot_count - 1 WHERE id = ? AND slot_count > 0");
      $stmt_dec_slot_count->bind_param("i", $booking_slot_id);
      
      if (!$stmt_dec_slot_count->execute()) {
        $_SESSION["msg"] = ["danger", "Booking error. Please try again later."];
        header ("Location: admin_users.php");
        exit;
      }
      
      $_SESSION["msg"] = ["success", "Appointment request submitted successfully"];
      header ("Location: admin_appointments_requests.php");
      exit;
    }
  
  showAdminSidebar("Book - Appointments")
?>

<section class="m-4">
  <h4 class="w-1oo fw-semibold">Book an Appointment for a User</h4>
  
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
</section>

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
  })
</script>

<?php
  showAdminFooter();  
?>