<?php
  session_start();
  
  include "config.php";
  include "components.php";
  
  if (isset($_POST["approve_appointment"])) {
    $approve_appt_id = test_input($_POST["approve_appointment"]);
    
    $stmt_approve_appt = $conn->prepare("UPDATE appointments SET status = 'Approved' WHERE id = ?");
    $stmt_approve_appt->bind_param("i", $approve_appt_id);
    
    if (!$stmt_approve_appt->execute()) {
      $_SESSION["msg"] = ["danger", "Approve error. Please try again later."];
      header ("Location: " . $_SERVER["PHP_SELF"]);
      exit;
    }
    
    $_SESSION["msg"] = ["success", "Appointment approved successfully"];
    header ("Location: " . $_SERVER["PHP_SELF"]);
    exit;
  }
  
  if (isset($_POST["edit_appointment_request"])) {
    $edit_patient_id = test_input($_POST["edit_user_id"]);
    $edit_appt_id = test_input($_POST["edit_appt_id"]);
    $edit_appt_service_id = test_input($_POST["edit_appt_service"]);
    $edit_appt_slot_id = test_input($_POST["edit_appt_time"]);;
    
    $stmt_check_same_serv = $conn->prepare("SELECT id FROM appointments WHERE patient_id = ? AND service_id = ? AND id != ? AND status IN ('Pending', 'Approved')");
    $stmt_check_same_serv->bind_param("iii", $edit_patient_id, $edit_appt_service_id, $edit_appt_id);
    $stmt_check_same_serv->execute();
    $stmt_check_same_serv->store_result();
    
    if ($stmt_check_same_serv->num_rows > 0) {
      $_SESSION["msg"] = ["danger", "This user already have an active appointment for this service."];
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
        $_SESSION["msg"] = ["danger", "This user already have an active appointment for this slot."];
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
  
  if (isset($_POST["decline_appointment"])) {
    $decline_appt_id = test_input($_POST["decline_appt_id"]);
    
    $stmt_decline_appt = $conn->prepare("UPDATE appointments SET status = 'Declined' WHERE id = ?");
    $stmt_decline_appt->bind_param("i", $decline_appt_id);
    
    if (!$stmt_decline_appt->execute()) {
      $_SESSION["msg"] = ["danger", "Decline error. Please try again later."];
      header ("Location: " . $_SERVER["PHP_SELF"]);
      exit;
    }
    
    $stmt_get_curr_slot_id = $conn->prepare("SELECT slot_id FROM appointments WHERE id = ?");
    $stmt_get_curr_slot_id->bind_param("i", $decline_appt_id);
    $stmt_get_curr_slot_id->execute();
    $stmt_get_curr_slot_id->bind_result($current_slot_id);
    $stmt_get_curr_slot_id->fetch();
    $stmt_get_curr_slot_id->close();
    
    $stmt_incr_slot_count = $conn->prepare("UPDATE date_time_slots SET slot_count = slot_count + 1 WHERE id = ?");
    $stmt_incr_slot_count->bind_param("i", $current_slot_id);
    $stmt_incr_slot_count->execute();
    
    $_SESSION["msg"] = ["success", "Appointment declined successfully"];
    header ("Location: " . $_SERVER["PHP_SELF"]);
    exit;
  }
  
  showAdminSidebar("Requests - Appointments")
?>

<section class="m-4">
  <?php showAlert(); ?>
  <div class="d-flex align-items-center">
    <h4 class="w-1oo fw-semibold m-0 p-0">Appointment Requests</h4>
    <div class="position-relative ms-auto me-3 search-container">
      <input type="text" id="search_input" class="form-control ps-5" placeholder="Search...">
      <span class="position-absolute top-50 start-0 translate-middle-y ps-3 text-muted">
        <i class="bi bi-search"></i>
      </span>
    </div>
    <div class="d-inline-block bg-secondary px-2 py-1 rounded">
			<div class="form-check form-switch">
				<input class="form-check-input" type="checkbox" role="switch" id="appointment_requests_switch">
				<label class="form-check-label text-white" for="appointment_requests_switch">View Full Table</label>
			</div>
		</div>
  </div>
  
  <div class="container-fluid py-4">
    <div class="table-responsive">
      <table class="table table-hover align-middle">
        <thead class="border-primary border-bottom-4">
          <tr class="align-middle">
            <th class="text-center">ID</th>
            <th class="text-center hidden-column hidden">User ID</th>
            <th class="text-center hidden-column hidden">Username</th>
            <th class="text-center">Name</th>
            <th class="text-center hidden-column hidden">Age</th>
            <th class="text-center hidden-column hidden">Gender</th>
            <th class="text-center hidden-column hidden">Birth Date</th>
            <th class="text-center hidden-column hidden">Contact Number</th>
            <th class="text-center hidden-column hidden">E-mail Address</th>
            <th class="text-center hidden-column hidden">Address</th>
            <th class="text-center">Service</th>
            <th class="text-center">Schedule</th>
            <th class="text-center">Actions</th>
        </thead>
        <tbody>
          <?php
            $json_data = file_get_contents("services.json");
            $services = json_decode($json_data, true);
            
            $stmt_fetch_datetime = $conn->prepare("SELECT
                a.id AS appointment_id,
                u.id AS user_id,
                a.service_id,
                a.slot_id,
                u.username,
                u.first_name,
                u.middle_name,
                u.last_name,
                u.extension_name,
                u.age,
                u.gender,
                u.birth_date,
                u.contact_number,
                u.email_address,
                u.address,  
                d.date,
                d.start_time,
                d.end_time,
                d.slot_count
              FROM appointments a
              JOIN users u ON a.patient_id = u.id
              JOIN date_time_slots d ON a.slot_id = d.id
              WHERE a.status = 'Pending'
              ORDER BY d.date DESC, d.start_time ASC");
            $stmt_fetch_datetime->execute();
            $result = $stmt_fetch_datetime->get_result();
            
            if ($result->num_rows === 0) {
              echo '<tr><td colspan="13" class="text-center text-muted fw-semibold mt-4">No appointment requests available.</td></tr>';
            }
            while($row = $result->fetch_assoc()):
              $service_name = "Unknown Service";
              foreach ($services as $service) {
                  if ($service["id"] == $row["service_id"]) {
                      $service_name = $service["name"];
                      break;
                  }
              }
          ?>
            <tr class="text-nowrap">
              <td class="text-center"> <?= $row["appointment_id"]; ?> </td>
              <td class="text-center hidden-column hidden"> <?= $row["user_id"]; ?> </td>
              <td class="text-center hidden-column hidden"> <?= $row["username"]; ?> </td>
              <td class="text-center"> <?= $row["first_name"] . " " . $row["middle_name"] . " " . $row["last_name"] . " " . $row["extension_name"] ?> </td>
              <td class="text-center hidden-column hidden"> <?= $row["age"]; ?> </td>
              <td class="text-center hidden-column hidden"> <?= $row["gender"]; ?> </td>
              <td class="text-center hidden-column hidden"> <?= date("F j, Y", strtotime($row["birth_date"])); ?> </td>
              <td class="text-center hidden-column hidden"> <?= $row["contact_number"]; ?> </td>
              <td class="text-center hidden-column hidden"> <?= $row["email_address"]; ?> </td>
              <td class="text-center hidden-column hidden"> <?= $row["address"]; ?> </td>
              <td class="text-center"> <?= $service_name; ?> </td>
              <td class="text-center"> <?= date("F j, Y", strtotime($row["date"])) . ": " . date("g:i A", strtotime($row["start_time"])) . " - " . date("g:i A", strtotime($row["end_time"])) ?> </td>
              <td class="text-center">
                <div class="d-flex gap-2">
                  <button class="approve-button btn btn-sm btn-primary" data-id="<?= $row["appointment_id"] ?>">
                    <i class="bi bi-check-circle me-1"></i>Approve
                  </button>
                  <button class="edit-button btn btn-sm btn-warning"
                    data-id="<?= $row["appointment_id"] ?>"
                    data-userid="<?= $row["user_id"] ?>"
                    data-serviceid="<?= $row["service_id"] ?>"
                    data-slotid="<?= $row["slot_id"] ?>"
                    data-date="<?= $row["date"] ?>"
                    data-starttime="<?= $row["start_time"] ?>"
                    data-endtime="<?= $row["end_time"] ?>"
                    data-slotcount="<?= $row["slot_count"] ?>"
                  >
                    <i class="bi bi-pencil me-1"></i>Edit
                  </button>
                  <button class="decline-button btn btn-sm btn-danger" data-id="<?= $row["appointment_id"] ?>">
                    <i class="bi bi-ban  me-1"></i>Decline
                  </button>
                </div>
              </td>
            </tr>
          <?php
            endwhile;
          ?>
        </tbody>
      </table>
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
            <input type="hidden" id="edit_user_id" name="edit_user_id">
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

<div class="modal fade p-4" id="decline_appointment" tabindex="-1">
  <div class="modal-dialog modal-dialog-scrollable modal-dialog-centered">
    <div class="modal-content rounded-4 shadow">
      <div class="modal-header border-0">
        <h5 class="modal-title w-100 text-center fw-bold">DECLINE APPOINTMENT?</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>     
      <div class="modal-body text-center">
        <i class="fa-solid fa-triangle-exclamation fa-4x text-danger mb-3"></i>
        <p class="mb-0 px-2">Are you sure you want to decline this appointment? This action cannot be undone.</p>
      </div>      
      <div class="modal-footer border-0 d-flex justify-content-center">
        <button type="button" class="btn btn-secondary rounded-3 px-4" data-bs-dismiss="modal">Close</button>
        <form method="POST">
          <input type="hidden" name="decline_appt_id" id="decline_appt_id">
          <input type="submit" name="decline_appointment" value="Yes, Decline" class="btn bg-danger text-light rounded-3 px-4">
        </form>
      </div>
    </div>
  </div>
</div>

<script>
  const switchInput = document.getElementById('appointment_requests_switch');
  const hiddenColumns = document.querySelectorAll('.hidden-column');
  
  switchInput.addEventListener('change', () => {
    hiddenColumns.forEach(column => {
    column.classList.toggle('hidden', !switchInput.checked);
    });
  });

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
        document.getElementById("edit_user_id").value = btn.dataset.userid;
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
        
        const modal = new bootstrap.Modal(document.getElementById("edit_appointment_request"));
        modal.show();
      });
    });
  })
  
  document.querySelectorAll(".decline-button").forEach(btn => {
    btn.addEventListener("click", ()=> {
      document.getElementById("decline_appt_id").value = btn.dataset.id;
      
      const modal = new bootstrap.Modal(document.getElementById("decline_appointment"));
      modal.show();
    });
  });
  
  document.querySelectorAll(".approve-button").forEach(btn => {
    btn.addEventListener("click", () => {
      const form = document.createElement("form");
      form.method = "POST";
      
      const hiddenInput = document.createElement("input");
      hiddenInput.type = "hidden";
      hiddenInput.name = "approve_appointment";
      hiddenInput.value = btn.dataset.id;
      
      form.appendChild(hiddenInput);
      document.body.appendChild(form);
      
      form.submit();
    });
  });
  
  function applyFilters() {
    const query = document.getElementById("search_input").value.toLowerCase();
    const table = document.querySelector("table tbody");
    
    table.querySelectorAll("tr").forEach(row => {
      const rowText = Array.from(row.cells)
                            .map(cell => cell.textContent.toLowerCase())
                            .join(" ");
      
      if (query === "" || rowText.includes(query)) {
        row.style.display = "";
      } else {
        row.style.display = "none";
      }
    });
  }
  
  document.getElementById("search_input").addEventListener("input", applyFilters);
</script>

<?php
  showAdminFooter();  
?>