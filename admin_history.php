<?php
  session_start();
  
  include "config.php";
  include "components.php";
  
  showAdminSidebar("History")
?>

<section class="m-4">
  <?php showAlert(); ?>
  <div class="d-flex align-items-center gap-3">
    <h4 class="w-1oo fw-semibold m-0 p-0">History</h4>
    <div class="d-flex align-items-center gap-3 ms-auto">
      <label for="status_filter" class="form-label m-0 p-0">Filter Status</label>
      <select class="form-select d-inline-block w-auto" name="status_filter" id="status_filter" >
        <option value="all" selected>All</option>
        <option value="Declined">Declined</option>
        <option value="Pending">Pending</option>
        <option value="Approved">Approved</option>
        <option value="Cancelled">Cancelled</option>
        <option value="Completed">Completed</option>
      </select>
    </div>
    <div class="position-relative search-container">
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
            <th class="text-center">Status</th>
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
                a.status,
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
                d.end_time,
                d.slot_count
              FROM appointments a
              JOIN users u ON a.patient_id = u.id
              JOIN date_time_slots d ON a.slot_id = d.id
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
                <div>
                  <?php
                    switch ($row["status"]) {
                      case "Declined": $bg_text_color = "bg-danger text-white"; $icon = "ban"; break;
                      case "Pending": $bg_text_color = "bg-warning text-dark"; $icon = "hourglass-split"; break;
                      case "Approved": $bg_text_color = "bg-primary text-white"; $icon = "check-circle"; break;
                      case "Completed": $bg_text_color = "bg-success text-white"; $icon = "check-square"; break;
                      case "Cancelled": $bg_text_color = "bg-secondary text-white"; $icon = "x-circle"; break;
                    }
                  ?>
                  <span class="badge <?= $bg_text_color ?> align-middle px-3 py-2">
                    <i class="bi bi-<?= $icon; ?>"></i> 
                    <?= $row["status"] ?> 
                  </span>
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

<script>
  const switchInput = document.getElementById('completed_appointments_switch');
  const hiddenColumns = document.querySelectorAll('.hidden-column');
  
  switchInput.addEventListener('change', () => {
    hiddenColumns.forEach(column => {
    column.classList.toggle('hidden', !switchInput.checked);
    });
  });
  
  function applyFilters() {
    const query = document.getElementById("search_input").value.toLowerCase();
    const statusFilter = document.getElementById("status_filter").value.toLowerCase();
    const table = document.querySelector("table tbody");
    
    table.querySelectorAll("tr").forEach(row => {
      const statusCell = row.querySelector("td:last-child");
      const statusText = statusCell.textContent.toLowerCase();
      
      const rowText = Array.from(row.cells)
                            .map(cell => cell.textContent.toLowerCase())
                            .join(" ");
      
      if ((statusFilter === "all" || statusText.includes(statusFilter)) &&
          (query === "" || rowText.includes(query))) {
        row.style.display = "";
      } else {
        row.style.display = "none";
      }
    });
  }
  
  document.getElementById("search_input").addEventListener("input", applyFilters);
  document.getElementById("status_filter").addEventListener("change", applyFilters);
</script>

<?php
  showAdminFooter();  
?>