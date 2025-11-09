<?php
  session_start();
  
  include "config.php";
  include "components.php";
  
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
  
  if (isset($_POST["revert_appointment"])) {
    $revert_appt_id = test_input($_POST["revert_appt_id"]);
    
    $stmt_revert_appt = $conn->prepare("UPDATE appointments SET status = 'Pending' WHERE id = ?");
    $stmt_revert_appt->bind_param("i", $revert_appt_id);
    
    if (!$stmt_revert_appt->execute()) {
      $_SESSION["msg"] = ["danger", "Revert error. Please try again later."];
      header ("Location: " . $_SERVER["PHP_SELF"]);
      exit;
    }
    
    $_SESSION["msg"] = ["success", "Appointment reverted successfully"];
    header ("Location: " . $_SERVER["PHP_SELF"]);
    exit;
  }
  
  showAdminSidebar("Upcoming - Appointments")
?>

<section class="m-4">
  <?php showAlert(); ?>
  <div class="d-flex align-items-center">
    <h4 class="w-1oo fw-semibold m-0 p-0">Upcoming Appointments</h4>
    <div class="d-inline-block bg-secondary ms-auto px-2 py-1 rounded">
			<div class="form-check form-switch">
				<input class="form-check-input" type="checkbox" role="switch" id="upcoming_appointments_switch">
				<label class="form-check-label text-white" for="upcoming_appointments_switch">View Full Table</label>
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
              WHERE a.status = 'Approved'
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
              <td class="text-center"> <?= date("F j, Y", strtotime($row["birth_date"])) . ": " . date("g:i A", strtotime($row["start_time"])) . " - " . date("g:i A", strtotime($row["end_time"])) ?> </td>
              <td class="text-center">
                <div class="d-flex gap-2">
                  <button class="revert-button btn btn-sm btn-warning" data-id="<?= $row["appointment_id"] ?>">
                    <i class="bi bi-arrow-counterclockwise me-1"></i>Revert
                  </button>   
                  <button class="decline-button btn btn-sm btn-danger" data-id="<?= $row["appointment_id"] ?>">
                    <i class="bi bi-ban me-1"></i>Decline
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

<div class="modal fade p-4" id="revert_appointment" tabindex="-1">
  <div class="modal-dialog modal-dialog-scrollable modal-dialog-centered">
    <div class="modal-content rounded-4 shadow">
      <div class="modal-header border-0">
        <h5 class="modal-title w-100 text-center fw-bold">REVERT APPOINTMENT?</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>     
      <div class="modal-body text-center">
        <i class="fa-solid fa-triangle-exclamation fa-4x text-danger mb-3"></i>
        <p class="mb-0 px-2">Are you sure you want to revert this appointment? This action cannot be undone.</p>
      </div>      
      <div class="modal-footer border-0 d-flex justify-content-center">
        <button type="button" class="btn btn-secondary rounded-3 px-4" data-bs-dismiss="modal">Close</button>
        <form method="POST">
          <input type="hidden" name="revert_appt_id" id="revert_appt_id">
          <input type="submit" name="revert_appointment" value="Yes, Revert" class="btn bg-warning text-dark rounded-3 px-4">
        </form>
      </div>
    </div>
  </div>
</div>

<script>
  const switchInput = document.getElementById('upcoming_appointments_switch');
  const hiddenColumns = document.querySelectorAll('.hidden-column');
  
  switchInput.addEventListener('change', () => {
    hiddenColumns.forEach(column => {
    column.classList.toggle('hidden', !switchInput.checked);
    });
  });
  
  document.querySelectorAll(".decline-button").forEach(btn => {
    btn.addEventListener("click", ()=> {
      document.getElementById("decline_appt_id").value = btn.dataset.id;
      
      const modal = new bootstrap.Modal(document.getElementById("decline_appointment"));
      modal.show();
    });
  });
  
  document.querySelectorAll(".revert-button").forEach(btn => {
    btn.addEventListener("click", ()=> {
      document.getElementById("revert_appt_id").value = btn.dataset.id;
      
      const modal = new bootstrap.Modal(document.getElementById("revert_appointment"));
      modal.show();
    });
  });
</script>

<?php
  showAdminFooter();  
?>