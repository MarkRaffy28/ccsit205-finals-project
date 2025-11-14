<?php
  session_start();
  
  include "config.php";
  include "components.php";
  
  $result1 = $conn->query("SELECT COUNT(*) AS total FROM appointments WHERE status = 'Pending'");
  $requests = $result1->fetch_assoc()['total'];
  
  $result2 = $conn->query("SELECT COUNT(*) AS total FROM appointments a JOIN date_time_slots d ON a.slot_id = d.id WHERE a.status = 'Approved' AND d.date >= CURDATE()");
  $upcoming = $result2->fetch_assoc()['total'];
  
  $result3 = $conn->query("SELECT COUNT(*) AS total FROM users");
  $patients = $result3->fetch_assoc()['total'];
  
  $sales_sql = "SELECT SUM(a.payment_amount) AS total FROM appointments a JOIN date_time_slots d ON a.slot_id = d.id";
  
  $filter = $_GET['filter_date'] ?? 'week';
  switch($filter) {
    case 'today': $sales_sql .= " AND DATE(d.date) = CURDATE()"; break;
    case 'week': $sales_sql .= " AND YEARWEEK(d.date, 1) = YEARWEEK(CURDATE(), 1)"; break;
    case 'month': $sales_sql .= " AND YEAR(d.date) = YEAR(CURDATE()) AND MONTH(d.date) = MONTH(CURDATE())"; break;
    case 'year': $sales_sql .= " AND YEAR(d.date) = YEAR(CURDATE())"; break;
    case 'all': break;
  }
  
  $result4 = $conn->query($sales_sql);
  $sales = $result4->fetch_assoc()['total'];
  
  showAdminSidebar("Dashboard");
  showAlert();
?>

<div class="admin-dashboard m-5 d-flex">
  <div class="container-fluid">
    <div class="row">
      <div class="bg-gradient card p-4 d-flex flex-column flex-lg-row justify-content-center align-items-center gap-4">
        <div>        
          <img src="https://dl.dropbox.com/scl/fi/22oiirmwtu6sa1qcd4e8d/logi.jpg?rlkey=ola10jhyofqvvuf6bpct8nysi&st=tiqqmj0l&dl=0" class="admin-logo">
        </div>
        <div>
          <h3 class="fw-bold text-center text-light">DenCare V.A.U.L.T.</h3>
          <h4 class="fw-bold text-center text-light">Booking and Sales Management System</h4>
        </div>
      </div>
    </div>
    <div class="row d-flex align-items-stretch mt-4 gy-4 admin-dashboard-cards">
      <div class="col-12 col-md-6 col-lg-3 px-4">
        <div class="card shadow-sm p-3 h-100 d-flex flex-column justify-content-center" onclick="window.location.href='admin_appointments_requests.php';">
          <h1 class="fw-bold text-center"> <?= $requests; ?> </h1>
          <h5 class="cb-gradient fw-semibold text-center">Appointment Request<?= ($requests > 1) ? "s" : "" ?> </h5>
        </div>
      </div>
      
      <div class="col-12 col-md-6 col-lg-3 px-4">
        <div class="card shadow-sm p-3 h-100 d-flex flex-column justify-content-center" onclick="window.location.href='admin_appointments_upcoming.php';">
          <h1 class="fw-bold text-center"> <?= $upcoming; ?> </h1>
          <h5 class="cb-gradient fw-semibold text-center">Upcoming Appointment<?= ($upcoming > 1) ? "s" : "" ?> </h5>
        </div>
      </div>
      
      <div class="col-12 col-md-6 col-lg-3 px-4">
        <div class="card shadow-sm p-3 h-100 d-flex flex-column justify-content-center" onclick="window.location.href='admin_sales.php';">
          <h1 class="fw-bold text-center"> ₱<?= number_format($sales ?? 0, thousands_separator: ",") ?> </h1>
          <h5 class="cb-gradient fw-semibold text-center">Generated Sales</h5>
          <select name="filter_date" id="filter_date" class="form-select w-auto" onchange="this.form.submit()" onclick="event.stopPropagation()">
            <option value="all">All Time</option>
            <option value="today">Today</option>
            <option value="week">This Week</option>
            <option value="month">This Month</option>
            <option value="year">This Year</option>
          </select>
        </div>
      </div>
      
      <div class="col-12 col-md-6 col-lg-3 px-4">
        <div class="card shadow-sm p-3 h-100 d-flex flex-column justify-content-center" onclick="window.location.href='admin_users.php';">
          <h1 class="fw-bold text-center"> <?= $patients; ?> </h1>
          <h5 class="cb-gradient fw-semibold text-center">Total Patient<?= ($patients > 1) ? "s" : "" ?></h5>
        </div>
      </div>
      
    </div>
  </div>
</div>

<script>
  const select = document.getElementById('filter_date');
  const currentFilter = new URLSearchParams(window.location.search).get('filter_date') || 'week';
  select.value = currentFilter;
  
  select.addEventListener('change', function () {
    const selected = this.value;
    const params = new URLSearchParams(window.location.search);
    params.set('filter_date', selected);
    window.location.search = params.toString(); 
  });
</script>

<?php
  showAdminFooter();  
?>