<?php
  session_start();

  include "config.php";
  include "components.php";

  if ($_SERVER["REQUEST_METHOD"] == "POST") {
    switch ($_POST["action"]) {
      case "add_datetime_slot":
        $date = test_input($_POST["date"]);
        $slot_count = test_input($_POST["slot_count"]);
        $start_time = test_input($_POST["start_time"]);
        $end_time = test_input($_POST["end_time"]);

        $stmt_add_dts = $conn->prepare("INSERT INTO date_time_slots(date, start_time, end_time) VALUES (?, ?, ?)");
        $stmt_add_dts->bind_param("sss", $date, $start_time, $end_time);

        $stmt_add_dts_success = true;
        for ($i = $slot_count; $i > 0; $i--) {
          if (!$stmt_add_dts->execute()) {
            $stmt_add_dts_success = false;
            break;
          }
        }

        if ($stmt_add_dts_success) {
          $_SESSION["msg"] = ["success", "Date & Time Slots added successfully."];
        } else {
          $_SESSION["msg"] = ["danger", "Error adding Date & Time Slots."];
        }
    }
  }

  showAdminSidebar("Date & Time Slots")


?>

<section class="m-4">
  <div class="d-flex justify-content-between">
    <h4 class="w-1oo fw-semibold">Date & Time Slots</h4>
    <button class="btn btn-success px-3" data-bs-toggle="modal" data-bs-target="#add_datetime_slot" type="button">Add</button>
  </div>

  <div class="container-fluid py-4">
    <div class="table-responsive">
      <table class="table table-hover align-middle">
        <thead class="border-primary border-bottom-4">
          <tr>
            <th class="text-center">ID</th>
            <th class="text-center">Date</th>
            <th class="text-center">Start Time</th>
            <th class="text-center">End Time</th>
            <th class="text-center">Is Booked</th>
            <th class="text-center">Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php
            $stmt_fetch_datetime = $conn->prepare("SELECT * FROM date_time_slots");
            $stmt_fetch_datetime->execute();
            $result = $stmt_fetch_datetime->get_result();

            while($row = $result->fetch_assoc()):
          ?>
            <tr>
              <th class="text-center"> <?= $row["id"]; ?> </th>
              <td class="text-center"> <?= date("F j, Y", strtotime($row["date"])); ?> </td>
              <td class="text-center"> <?= date("g:i A", strtotime($row["start_time"])); ?> </td>
              <td class="text-center"> <?= date("g:i A", strtotime($row["end_time"])); ?> </td>
              <td class="text-center">
                <?php if ($row["is_booked"] == 1): ?>
                  <span class="badge bg-success">Yes</span>
                <?php else: ?>
                  <span class="badge bg-danger">No</span>
                <?php endif; ?>
              </td>
              <td class="text-center">
                <button class="edit-button btn btn-sm btn-warning"
                  data-id="<?= $row["id"]; ?>"
                  data-date="<?= $row["date"]; ?>"
                  data-starttime="<?= $row["start_time"]; ?>"
                  data-endtime="<?= $row["end_time"]; ?>"
                >
                  Edit
                </button>
                <button class="btn btn-sm btn-danger">Delete</button>
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

<div class="modal fade" id="add_datetime_slot" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable p-4">
    <div class="modal-content">
      <div class="modal-header pb-0 border-0">          
        <h4 class="modal-title w-100 text-center fw-bold m-0 p-0">ADD DATE & TIME SLOT</h4>  
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>      
      <div class="modal-body">
        <form method="POST" novalidate>
          <div class="row mb-2">
            <input type="hidden" name="action" value="add_datetime_slot">
            <div class="col-sm form-floating">
              <input type="date" class="form-control" id="date" name="date" placeholder="Date" required>
              <label for="date" class="form-label ps-4">Date</label>
            </div>
            <div class="col-sm form-floating">
              <input type="number" class="form-control" id="slot_count" name="slot_count" placeholder="Slot Count" value="1" min="1" max="100" required>
              <label for="slot_count" class="form-label ps-4">Slot Count</label>
            </div>
          </div>
          <div class="row mb-2">
            <?php
              $start = strtotime('08:00');
              $end   = strtotime('22:00');
            ?>
            <div class="col-sm form-floating">
              <select id="start_time" name="start_time" class="form-select ps-4" required>
                <option value="" selected disabled>--Select--</option>
                <?php
                   for ($time = $start; $time <= $end; $time += 30 * 60) {
                    $value = date('H:i:s', $time);
                    $label = date('g:i A', $time);
                    echo "<option value='$value'> $label </option>";
                  }
                ?> 
              </select>
              <label for="start_time" class="form-label ps-4">Start Time</label>
            </div>
            <div class="col-sm form-floating">
              <select id="end_time" name="end_time" class="form-select ps-4" required>
                <option value="" selected disabled>--Select--</option>
                <?php
                   for ($time = $start; $time <= $end; $time += 30 * 60) {
                    $value = date('H:i:s', $time);
                    $label = date('g:i A', $time);
                    echo "<option value='$value'> $label </option>";
                  }
                ?> 
              </select>
              <label for="end_time" class="form-label ps-4">End Time</label>
            </div>
          </div>
          <div class="row m-2">
            <div class="d-flex justify-content-center mt-4 mb-2">
              <button type="button" class="btn btn-md btn-danger rounded-3 px-3 me-3" data-bs-dismiss="modal">Cancel</button>
              <input type="submit" class="btn btn-success">
            </div>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>

<div class="modal fade" id="edit_datetime_slot" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable p-4">
    <div class="modal-content">
      <div class="modal-header pb-0 border-0">          
        <h4 class="modal-title w-100 text-center fw-bold m-0 p-0">EDIT DATE & TIME SLOT</h4>  
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>      
      <div class="modal-body">
        <form method="POST" novalidate>
          <div class="row mb-2">
            <input type="hidden" name="action" value="edit_datetime_slot">
            <input type="hidden" id="edit_id" name="edit_id">
            <div class="col-sm form-floating">
              <input type="date" class="form-control" id="edit_date" name="edit_date" placeholder="Date" required>
              <label for="edit_date" class="form-label ps-4">Date</label>
            </div>
            <div class="col-sm form-floating">
              <input type="number" class="form-control" id="edit_slot_count" name="edit_slot_count" placeholder="Slot Count" value="1" min="1" max="100" required>
              <label for="edit_slot_count" class="form-label ps-4">Slot Count</label>
            </div>
          </div>
          <div class="row mb-2">
            <?php
              $start = strtotime('08:00');
              $end   = strtotime('22:00');
            ?>
            <div class="col-sm form-floating">
              <select id="edit_start_time" name="edit_start_time" class="form-select ps-4" required>
                <option value="" selected disabled>--Select--</option>
                <?php
                   for ($time = $start; $time <= $end; $time += 30 * 60) {
                    $value = date('H:i:s', $time);
                    $label = date('g:i A', $time);
                    echo "<option value='$value'> $label </option>";
                  }
                ?> 
              </select>
              <label for="edit_start_time" class="form-label ps-4">Start Time</label>
            </div>
            <div class="col-sm form-floating">
              <select id="edit_end_time" name="edit_end_time" class="form-select ps-4" required>
                <option value="" selected disabled>--Select--</option>
                <?php
                   for ($time = $start; $time <= $end; $time += 30 * 60) {
                    $value = date('H:i:s', $time);
                    $label = date('g:i A', $time);
                    echo "<option  value='$value'> $label </option>";
                  }
                ?> 
              </select>
              <label for="edit_end_time" class="form-label ps-4">End Time</label>
            </div>
          </div>
          <div class="row m-2">
            <div class="d-flex justify-content-center mt-4 mb-2">
              <button type="button" class="btn btn-md btn-danger rounded-3 px-3 me-3" data-bs-dismiss="modal">Cancel</button>
              <input type="submit" class="btn btn-success">
            </div>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>
<script>
  document.querySelectorAll(".edit-button").forEach(btn => {
  btn.addEventListener("click", () => {
    document.getElementById("edit_id").value = btn.dataset.id;
    document.getElementById("edit_date").value = btn.dataset.date;
    
    document.querySelectorAll("#edit_start_time option").forEach(opt => {
      if (opt.value == btn.dataset.starttime) {
        opt.setAttribute("selected", "selected");
      }
    });

    document.querySelectorAll("#edit_end_time option").forEach(opt => {
      if (opt.value == btn.dataset.endtime) {
        opt.setAttribute("selected", "selected");
      }
    });
    
    const modal = new bootstrap.Modal(document.getElementById("edit_datetime_slot"));
    modal.show();
  });
});
</script>
<?php
  showAdminFooter();  
?>