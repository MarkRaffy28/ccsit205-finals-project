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
  <link rel="stylesheet" href="stylesheet.css">
  <link rel="stylesheet" href="stylesheet.css">

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
