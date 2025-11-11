<?php
  session_start();
  
  include "config.php";
  include "components.php";
  
  if (isset($_POST["edit_completed_appointment"])) {
    $edit_appt_id = test_input($_POST["edit_appt_id"]);
    $edit_notes = test_input($_POST["edit_notes"]);
    $edit_payment_amount = test_input($_POST["edit_payment_amount"]);
    
    $stmt_updt_appt = $conn->prepare("UPDATE appointments SET 
        notes = ?, 
        payment_amount = ? 
      WHERE id = ?");
    $stmt_updt_appt->bind_param("sii",  $edit_notes, $edit_payment_amount, $edit_appt_id);
    
    if (!$stmt_updt_appt->execute()) {
      $_SESSION["msg"] = ["danger", "Update error. Please try again later."];
      header ("Location: " . $_SERVER["PHP_SELF"]);
      exit;
    }
    
    $_SESSION["msg"] = ["success", "Appointment updated successfully"];
    header ("Location: " . $_SERVER["PHP_SELF"]);
    exit;
  }
  
  if (isset($_POST["complete_appointment"])) {
    $complete_appt_id = test_input($_POST["complete_appt_id"]);
    $notes = test_input($_POST["notes"]);
    $payment_amount = test_input($_POST["payment_amount"]);
    
    $stmt_complete_appt = $conn->prepare("UPDATE appointments SET 
        notes = ?,
        payment_amount = ?,
        status = 'Completed' 
      WHERE id = ?");
    $stmt_complete_appt->bind_param("sii", $notes, $payment_amount, $complete_appt_id);
    
    if (!$stmt_complete_appt->execute()) {
      $_SESSION["msg"] = ["danger", "Complete error. Please try again later."];
      header ("Location: " . $_SERVER["PHP_SELF"]);
      exit;
    }
    
    $_SESSION["msg"] = ["success", "Appointment completed successfully"];
    header ("Location: " . $_SERVER["PHP_SELF"]);
    exit;
  }
  
  showAdminSidebar("Completed - Appointments")
?>

<section class="m-4">
  <?php showAlert(); ?>
  <div class="d-flex align-items-center">
    <h4 class="w-1oo fw-semibold m-0 p-0">Completed Appointments</h4>
    <div class="position-relative ms-auto me-3 search-container">
      <input type="text" id="search_input" class="form-control ps-5" placeholder="Search...">
      <span class="position-absolute top-50 start-0 translate-middle-y ps-3 text-muted">
        <i class="bi bi-search"></i>
      </span>
    </div>
    <div class="d-inline-block bg-secondary px-2 py-1 rounded">
			<div class="form-check form-switch">
				<input class="form-check-input" type="checkbox" role="switch" id="completed_appointments_switch">
				<label class="form-check-label text-white" for="completed_appointments_switch">View Full Table</label>
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
            <th class="text-center">Notes</th>
            <th class="text-center">Payment Amount</th>
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
                a.notes,
                a.payment_amount,
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
                d.end_time
              FROM appointments a
              JOIN users u ON a.patient_id = u.id
              JOIN date_time_slots d ON a.slot_id = d.id
              WHERE a.status = 'Completed'
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
              <td class="text-center"> <?= $row["notes"]; ?> </td>
              <td class="text-center"> ₱<?= number_format($row["payment_amount"] ?? 0, thousands_separator: ", "); ?> </td>
              <td class="text-center">
                <div class="d-flex gap-2">
                  <button class="edit-button btn btn-sm btn-warning" 
                    data-id="<?= $row["appointment_id"] ?>"
                    data-notes="<?= $row["notes"] ?>"
                    data-paymentamount="<?= $row["payment_amount"] ?>"
                  >
                    <i class="bi bi-pencil me-1"></i>Edit
                  </button>
                  <button class="revert-button btn btn-sm btn-secondary" data-id="<?= $row["appointment_id"] ?>">
                    <i class="bi bi-arrow-counterclockwise me-1"></i>Revert
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


<div class="modal fade p-4" id="edit_completed_appointment" tabindex="-1">  
  <div class="modal-dialog modal-dialog-scrollable modal-dialog-centered">
    <div class="modal-content rounded-4 shadow">
      <div class="modal-header border-0">
        <h5 class="modal-title w-100 text-center fw-bold">EDIT COMPLETED APPOINTMENT</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>     
      <div class="modal-body text-center">
        <form method="POST" novalidate>
          <input type="hidden" name="edit_appt_id" id="edit_appt_id">
          <div class="row mb-2 gx-3 gy-2">
            <div class="col-sm form-floating">
              <textarea type="text" class="form-control" id="edit_notes" name="edit_notes" placeholder="Notes"></textarea>
              <label for="edit_notes" class="form-label">Notes</label>
            </div>
          </div>
          <div class="row mb-2 gx-3 gy-2">
            <div class="col-sm form-floating">
              <input type="number" class="form-control" id="edit_payment_amount" name="edit_payment_amount" placeholder="Payment Amount (Whole Number)" min="0" max="2147483647" step="1" required>
              <label for="edit_payment_amount" class="form-label">Payment Amount (Whole Number)</label>
            </div>    
          </div>
          
          <div class="mt-4 d-flex justify-content-center">
            <button type="button" class="btn btn-md btn-danger rounded-3 px-3 me-3" data-bs-dismiss="modal">Cancel</button>
            <input type="submit" name="edit_completed_appointment" value="Update" class="btn btn-success">
          </div>
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
          <input type="submit" name="revert_appointment" value="Yes, Revert" class="btn bg-success text-light rounded-3 px-4">
        </form>
      </div>
    </div>
  </div>
</div>

<script>
  const switchInput = document.getElementById('completed_appointments_switch');
  const hiddenColumns = document.querySelectorAll('.hidden-column');
  
  switchInput.addEventListener('change', () => {
    hiddenColumns.forEach(column => {
    column.classList.toggle('hidden', !switchInput.checked);
    });
  });
  
  document.querySelectorAll(".edit-button").forEach(btn => {
    btn.addEventListener("click", () => {
      document.getElementById("edit_appt_id").value = btn.dataset.id;
      document.getElementById("edit_notes").value = btn.dataset.notes;
      document.getElementById("edit_payment_amount").value = btn.dataset.paymentamount;
      
      const modal = new bootstrap.Modal(document.getElementById("edit_completed_appointment"));
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