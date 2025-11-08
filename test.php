<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Modern Bootstrap Datepicker</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">

  <!-- Bootstrap 5 -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

  <!-- Bootstrap Datepicker -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-datepicker@1.10.0/dist/css/bootstrap-datepicker.min.css" rel="stylesheet">

  <style>
    body {
      background-color: #f8f9fa;
      display: flex;
      justify-content: center;
      align-items: center;
      min-height: 100vh;
    }

    .card {
      max-width: 420px;
      border: none;
      border-radius: 1rem;
      box-shadow: 0 6px 18px rgba(0, 0, 0, 0.1);
    }

    .card-header {
      background: linear-gradient(135deg, #0d6efd, #4dabf7);
      color: #fff;
      text-align: center;
      font-weight: 600;
      border-radius: 1rem 1rem 0 0;
      padding: 1rem;
    }

    .datepicker {
      border: none !important;
      border-radius: 1rem !important;
      box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
      overflow: hidden;
    }

    .datepicker-dropdown {
      padding: 0.5rem !important;
    }

    .datepicker table tr td,
    .datepicker table tr th {
      /* border-radius: 8px; */
      text-align: center;
      transition: 0.2s;
    }

    .datepicker table tr td.day:hover,
    .datepicker table tr td.today:hover {
      background-color: #e7f1ff !important;
      background-image: none !important;
      color: #0d6efd !important;
      cursor: pointer;
      transform: scale(1.05);
    }

    .datepicker table tr td.active,
    .datepicker table tr td.active:hover {
      background-color: #0d6efd !important;
      background-image: none !important; 
      font-weight: 500;
      color: #fff !important;
    }

    .datepicker table tr td.today {
      border: 1px solid black !important;
      background-color: #fff !important;
      background-image: none !important; 
      color: #000 !important;
    }

    .datepicker table tr td.disabled,
    .datepicker table tr td.disabled:hover {
      color: #adb5bd !important;
      background: none !important;
      opacity: 0.5;
      cursor: not-allowed;
    }

    .form-control:focus {
      box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.25);
    }

    .form-control[disabled] {
      background-color: #e9ecef !important;
    }
  </style>
</head>

<body>
  <div class="card">
    <div class="card-header">
      Select Appointment Date
    </div>
    <div class="card-body p-4">
      <div class="mb-3">
        <label for="booking_date" class="form-label fw-semibold">Appointment Date</label>
        <input type="text" id="booking_date" class="form-control" placeholder="Loading available dates...">
      </div>
      <p class="text-muted small mb-0">Only available appointment dates can be selected.</p>
    </div>
  </div>

  <!-- jQuery -->
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

  <!-- Bootstrap JS -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

  <!-- Bootstrap Datepicker JS -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap-datepicker@1.10.0/dist/js/bootstrap-datepicker.min.js"></script>

  <script>
    const availableDates = [
      "2025-11-08",
      "2025-11-09",
      "2025-11-10",
      "2025-11-12",
      "2025-11-15"
    ];

    const dateInput = $("#booking_date");

    setTimeout(() => {
      if (availableDates.length === 0) {
        dateInput.prop("disabled", true);
        dateInput.attr("placeholder", "No available dates");
        return;
      }

      dateInput.datepicker({
        format: "MM dd, yyyy",
        autoclose: true,
        todayHighlight: true,
        beforeShowDay: function (date) {
          const formatted = date.toISOString().split("T")[0];
          return availableDates.includes(formatted)
            ? { enabled: true}
            : false;
        }
      });

      dateInput.attr("placeholder", "Select available date");
    }, 800);
  </script>
</body>
</html>
