<?php
  session_start();
  
  include "config.php";
  include "components.php";
  
  showAdminSidebar("Sales")
?>

<section class="m-4">
  <?php showAlert(); ?>
  <div class="d-flex align-items-center gap-3">
    <h4 class="w-1oo fw-semibold m-0 p-0">Sales</h4>
    <div class="d-flex align-items-center gap-3 ms-auto">
      <label for="filter_date" class="form-label m-0 p-0">Filter Date</label>
      <select name="filter_date" id="filter_date" class="form-select w-auto" onchange="this.form.submit()">
        <option value="all">All Time</option>
        <option value="today">Today</option>
        <option value="week">This Week</option>
        <option value="month">This Month</option>
        <option value="year">This Year</option>
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
        </thead>
        <tbody>
          <?php
            $json_data = file_get_contents("services.json");
            $services = json_decode($json_data, true);
            
            $filter = $_GET['filter_date'] ?? 'all';
            
            $sql = "SELECT
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
                d.end_time,
                d.slot_count
              FROM appointments a
              JOIN users u ON a.patient_id = u.id
              JOIN date_time_slots d ON a.slot_id = d.id
              WHERE a.status = 'Completed'";
            
            switch($filter) {
              case 'today': $sql .= " AND DATE(d.date) = CURDATE()"; break;
              case 'week': $sql .= " AND YEARWEEK(d.date, 1) = YEARWEEK(CURDATE(), 1)"; break;
              case 'month': $sql .= " AND YEAR(d.date) = YEAR(CURDATE()) AND MONTH(d.date) = MONTH(CURDATE())"; break;
              case 'year': $sql .= " AND YEAR(d.date) = YEAR(CURDATE())"; break;
              case 'all': break;
            }
            
            $sql .= " ORDER BY d.date DESC, d.start_time ASC";
            
            $stmt_fetch_datetime = $conn->prepare($sql);
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
            </tr>
          <?php
            endwhile;
            
            $sum_sql = "SELECT SUM(a.payment_amount) AS total_sales
              FROM appointments a
              JOIN date_time_slots d ON a.slot_id = d.id
              WHERE a.status = 'Completed'";
            
            switch($filter) {
              case 'today': $sum_sql .= " AND DATE(d.date) = CURDATE()"; break;
              case 'week': $sum_sql .= " AND YEARWEEK(d.date, 1) = YEARWEEK(CURDATE(), 1)"; break;
              case 'month': $sum_sql .= " AND YEAR(d.date) = YEAR(CURDATE()) AND MONTH(d.date) = MONTH(CURDATE())"; break;
              case 'year': $sum_sql .= " AND YEAR(d.date) = YEAR(CURDATE())"; break;
              case 'all': break;
            }
            
            $stmt_sum = $conn->prepare($sum_sql);
            $stmt_sum->execute();
            $sum_result = $stmt_sum->get_result();
            $total_sales = $sum_result->fetch_assoc()['total_sales'] ?? 0;
          ?>
        </tbody>
        <tfoot class="position-sticky bottom-0 bg-light tfoot-border-top">
          <tr>
            <td colspan="14" class="text-end fw-bold"> Total Sales: <span class="text-success ms-2"> ₱<?= number_format($total_sales, 2, '.', ','); ?> </span> </td>
          </tr>
        </tfoot>
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
  
  const select = document.getElementById('filter_date');
  const currentFilter = new URLSearchParams(window.location.search).get('filter_date') || 'all';
  select.value = currentFilter;
  
  select.addEventListener('change', function () {
    const selected = this.value;
    const params = new URLSearchParams(window.location.search);
    params.set('filter_date', selected);
    window.location.search = params.toString(); 
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