<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Dental Appointment</title>

  <!-- MaterializeCSS -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/materialize/1.0.0/css/materialize.min.css">
  <script src="https://cdnjs.cloudflare.com/ajax/libs/materialize/1.0.0/js/materialize.min.js"></script>
</head>
<body class="container" style="margin-top:50px">

  <h5>Select Appointment Date & Time</h5>

  <!-- Date Picker -->
  <div class="input-field">
    <input type="text" id="datepicker" class="datepicker">
    <label for="datepicker">Choose a date</label>
  </div>

  <!-- Time Dropdown -->
  <div class="input-field">
    <select id="timeSelect" disabled>
      <option value="" disabled selected>Select a time</option>
    </select>
    <label>Available Time Slots</label>
  </div>

  <script>
  document.addEventListener('DOMContentLoaded', function() {
    fetch("get_dates.php")
      .then(res => res.json())
      .then(data => {
        // Extract available dates (keys of the object)
        const availableDates = Object.keys(data);

        // Initialize Materialize datepicker
        const dateElems = document.querySelectorAll('.datepicker');
        const datePicker = M.Datepicker.init(dateElems, {
          format: 'yyyy-mm-dd',
          disableDayFn: date => {
            const d = date.toISOString().split('T')[0]
            return !availableDates.includes(d); // only enable available dates
          },
          onSelect: date => {
            const selectedDate = date.toISOString().split('T')[0];
            updateTimes(selectedDate, data[selectedDate]);
          }
        })[0]; // get the instance

        // Initialize Materialize select
        const timeSelect = document.getElementById('timeSelect');
        M.FormSelect.init(timeSelect);

        // Function to update available time slots
        function updateTimes(selectedDate, times) {
          timeSelect.innerHTML = `<option value="" disabled selected>Select a time</option>`;
          
          if (times && times.length > 0) {
            times.forEach(time => {
              const option = document.createElement("option");
              option.value = time;
              option.textContent = time;
              timeSelect.appendChild(option);
            });
            timeSelect.disabled = false;
          } else {
            const option = document.createElement("option");
            option.textContent = "No available time slots";
            timeSelect.appendChild(option);
            timeSelect.disabled = true;
          }

          M.FormSelect.init(timeSelect);
        }
      });
  });
  </script>
</body>
</html>
