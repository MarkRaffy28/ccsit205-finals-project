<?php
  session_start();

  include "config.php";
  include "components.php";

  if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST["check_username"])) {
      $username = test_input($_POST["username"]);
      $password = test_input($_POST["password"]);
      
      $stmt_check_usrnm = $conn->prepare("SELECT * FROM users WHERE username = ?");
      $stmt_check_usrnm->bind_param("s", $username);
      $stmt_check_usrnm->execute();
      $result = $stmt_check_usrnm->get_result();
      
      if ($result->num_rows > 0 || $username == "admin") {
        $_SESSION["msg"] = ["danger", "Username already exists."];
        header("Location: " . $_SERVER["PHP_SELF"]);
        exit;
      }
      
      $show_second_form = true;
      $_SESSION["temp_username"] = $username;
      $_SESSION["temp_password"] = password_hash($password, PASSWORD_DEFAULT);
    }
    
    if (isset($_POST["return"])) { 
      unset($show_second_form); 
      unset($_SESSION["temp_username"]);
      unset($_SESSION["temp_password"]);
    }
    
    if (isset($_POST["complete_registration"])) {
      if (isset($_SESSION["temp_username"]) && isset($_SESSION["temp_password"])) {
        $username = $_SESSION["temp_username"];
        $password = $_SESSION["temp_password"];
        $first_name = test_input($_POST["first_name"]);
        $middle_name = test_input($_POST["middle_name"]);
        $last_name = test_input($_POST["last_name"]);
        $extension_name = test_input($_POST["extension_name"]);
        $age = test_input($_POST["age"]);
        $gender = test_input($_POST["gender"]);
        $birth_date = test_input($_POST["birth_date"]);
        $contact_number = test_input($_POST["contact_number"]);
        $email_address = test_input($_POST["email_address"]);
        $address = test_input($_POST["address"]);
      
        $stmt_insert_user = $conn->prepare("INSERT INTO users(username, password, first_name, middle_name, last_name, extension_name, age, gender, birth_date, contact_number, email_address, address) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt_insert_user->bind_param("ssssssisssss", $username, $password, $first_name, $middle_name, $last_name, $extension_name, $age, $gender, $birth_date, $contact_number, $email_address, $address);
        
        if (!$stmt_insert_user->execute()) {
          $_SESSION["msg"] = ["danger", "Insert error. Please try again later."];
        }
        $_SESSION["msg"] = ["success", "User added successfully"];
        header ("Location: " . $_SERVER["PHP_SELF"]);
        exit;
      } else {
        $_SESSION["msg"] = ["danger", "Session Expired. Please try again."];
      }
    }

    // switch ($_POST["action"]) {  
    //   case "edit_datetime_slot":
    //     $edit_id = test_input($_POST["edit_id"]);
    //     $edit_date = test_input($_POST["edit_date"]);
    //     $edit_slot_count = test_input($_POST["edit_slot_count"]);
    //     $edit_start_time = test_input($_POST["edit_start_time"]);
    //     $edit_end_time = test_input($_POST["edit_end_time"]);
        
    //     $stmt_edit_dts = $conn->prepare("UPDATE date_time_slots SET
    //         date = ?,
    //         start_time = ?,
    //         end_time = ?,
    //         slot_count = ?
    //       WHERE id = ?");
    //     $stmt_edit_dts->bind_param("sssii", $edit_date, $edit_start_time, $edit_end_time, $edit_slot_count, $edit_id);
        
    //     if (!$stmt_edit_dts->execute()) {
    //       $_SESSION["msg"] = ["danger", "Update error. Please try again later."];
    //       break;
    //     }
    //     $_SESSION["msg"] = ["success", "Date & time slot updated successfully"];
    //     header ("Location: " . $_SERVER["PHP_SELF"]);
    //     exit;
      
      
    //   case "delete_datetime_slot":
    //     $delete_id = test_input($_POST["delete_id"]);
        
    //     $stmt_delete_dts = $conn->prepare("DELETE FROM date_time_slots WHERE id = ?");
    //     $stmt_delete_dts->bind_param("i", $delete_id);
        
    //     if (!$stmt_delete_dts->execute()) {
    //       $_SESSION["msg"] = ["danger", "Delete error. Please try again later."];
    //       break;
    //     }
    //     $_SESSION["msg"] = ["success", "Date & time slot deleted successfully"];
    //     header ("Location: " . $_SERVER["PHP_SELF"]);
    //     exit;
    // }
  }

  showAdminSidebar("Users")
?>

<section class="m-4">
  <?php showAlert(); ?>
  <div class="d-flex justify-content-between">
    <h4 class="w-1oo fw-semibold">Users</h4>
    <button class="btn btn-success px-3" data-bs-toggle="modal" data-bs-target="#add_user" type="button">Add</button>
  </div>

  <div class="container-fluid py-4">
    <div class="table-responsive">
      <table class="table table-hover align-middle">
        <thead class="border-primary border-bottom-4">
          <tr class="align-middle">
            <th class="text-center">ID</th>
            <th class="text-center">Username</th>
            <th class="text-center">Name</th>
            <th class="text-center">Age</th>
            <th class="text-center">Gender</th>
            <th class="text-center">Birth Date</th>
            <th class="text-center">Contact Number</th>
            <th class="text-center">E-mail Address</th>
            <th class="text-center">Address</th>
          </tr>
        </thead>
        <tbody>
          <?php
            $stmt_fetch_datetime = $conn->prepare("SELECT * FROM users");
            $stmt_fetch_datetime->execute();
            $result = $stmt_fetch_datetime->get_result();

            while($row = $result->fetch_assoc()):
          ?>
            <tr class="text-nowrap">
              <th class="text-center"> <?= $row["id"]; ?> </th>
              <td class="text-center"> <?= $row["username"]; ?> </td>
              <td class="text-center"> <?= $row["first_name"] . " " . $row["middle_name"] . " " . $row["last_name"] . " " . $row["extension_name"] ?> </td>
              <td class="text-center"> <?= $row["age"]; ?> </td>
              <td class="text-center"> <?= $row["gender"]; ?> </td>
              <td class="text-center"> <?= date("F j, Y", strtotime($row["birth_date"])); ?> </td>
              <td class="text-center"> <?= $row["contact_number"]; ?> </td>
              <td class="text-center"> <?= $row["email_address"]; ?> </td>
              <td class="text-center"> <?= $row["address"]; ?> </td>
              <td class="text-center">
                <button class="edit-button btn btn-sm btn-warning"
                  data-id="<?= $row["id"]; ?>"
                  data-username="<?= $row["username"]; ?>"
                  data-firstname="<?= $row["first_name"]; ?>"
                  data-middlename="<?= $row["middle_name"]; ?>"
                  data-lastname="<?= $row["last_name"]; ?>"
                  data-extensionname="<?= $row["extension_name"]; ?>"
                  data-age="<?= $row["age"]; ?>"
                  data-gender="<?= $row["gender"]; ?>"
                  data-birthdate="<?= $row["birth_date"]; ?>"
                  data-contactnumber="<?= $row["contact_number"]; ?>"
                  data-emailaddress="<?= $row["email_address"]; ?>"
                >
                  Edit
                </button>
                <button class="delete-button btn btn-sm btn-danger" data-id="<?= $row["id"]; ?>">Delete</button>
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

<div class="modal fade" id="add_user" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable p-4">
    <div class="modal-content">
      <div class="modal-header border-0">
        <h4 class="modal-title w-100 text-center fw-bold m-0 p-0">ADD USER</h4>  
      </div>
      
      <div class="modal-body">
        <form method="POST" novalidate>
          <?php if(empty($show_second_form)): ?>
            <div class="row mb-2">
              <div class="col-sm form-floating">
                <input type="text" class="form-control" id="username" name="username" placeholder="Username" pattern="[A-Za-z0-9._]+" required>
                <label for="username" class="form-label ps-4">Username</label>
              </div>
            </div>
            <div class="row mb-2">
              <div class="col-sm form-floating">
                <input type="password" class="form-control" id="password" name="password" placeholder="Password" pattern="[A-Za-z0-9@$!%*?&._]+" required>
                <label for="password" class="form-label ps-4">Password</label>
                <i class="bi bi-eye fs-4 eye"></i>
              </div>
            </div>
            <div class="row m-2">
            <div class="d-flex justify-content-center mb-2">
              <input type="submit" name="check_username" value="Next" class="btn btn-success mt-4">
            </div>                
            
          <?php elseif (isset($show_second_form)): ?>
            <script>
              document.addEventListener("DOMContentLoaded", ()=> {
                const modal = new bootstrap.Modal(document.getElementById("add_user"));
                modal.show();
              })
            </script>
            <div class="row mb-2 gx-3 gy-2">
              <div class="col-sm form-floating">
                <input type="text" class="form-control" id="first_name" name="first_name" placeholder="First Name" required>
                <label for="first_name" class="form-label ps-4">First Name</label>
              </div>
              <div class="col-sm form-floating">
                <input type="text" class="form-control" id="middle_name" name="middle_name" placeholder="Middle Name" required>
                <label for="middle_name" class="form-label ps-4">Middle Name</label>
              </div>
            </div>
            
            <div class="row mb-2 gx-3 gy-2">
              <div class="col-sm form-floating">
                <input type="text" class="form-control" id="last_name" name="last_name" placeholder="Last Name" required>
                <label for="last_name" class="form-label ps-4">Last Name</label>
              </div>
              <div class="col-sm form-floating">
                <input type="text" class="form-control" id="extension_name" name="extension_name" placeholder="Extension Name">
                <label for="extension_name" class="form-label ps-4">Extension Name</label>
              </div>
            </div>
            
            <div class="row mb-2 gx-3 gy-2">
              <div class="col-sm form-floating">
                <input type="number" class="form-control" id="age" name="age" placeholder="Age" required>
                <label for="age" class="form-label ps-4">Age</label>
              </div>
              <div class="col-sm form-floating">
                <select id="gender" name="gender" class="form-select ps-4" required>
                  <option value="" selected disabled>Select</option>
                  <option value="Male">Male</option>
                  <option value="Female">Female</option>
                  <option value="Other">Other</option>
                </select>
                <label for="gender" class="form-label ps-4">Select Gender</label>
              </div>
            </div>
            
            <div class="row mb-2 gx-3 gy-2">
              <div class="col-sm form-floating">
                <input type="date" class="form-control" id="birth_date" name="birth_date" placeholder="Birth Date" required>
                <label for="birth_date" class="form-label ps-4">Birth Date</label>
              </div>
              <div class="col-sm form-floating">
                <input type="tel" class="form-control" id="contact_number" name="contact_number" placeholder="Contact Number (e.g. 09...="")" required pattern="\d{11}" minlength="11" maxlength="11">
                <label for="contact_nunber" class="form-label  ps-4">Contact Number (e.g. 09...)</label>
              </div>
            </div>
            
            <div class="row mb-2 gx-3 gy-2">
              <div class="col-sm form-floating">
                <input type="email" class="form-control" id="email_address" name="email_address" placeholder="E-mail Address" required>
                <label for="email_address" class="form-label ps-4">E-mail Address</label>
              </div>
              <div class="col-sm form-floating">
                <textarea class="form-control" id="address" name="address" placeholder="Address" required></textarea>
                <label for="address" class="form-label ps-4">Address</label>
              </div>
            </div>
            
            <div class="row m-2">
            <div class="d-flex justify-content-center mb-2">
              <input type="submit" name="return" value="Return" class="btn btn-danger mt-4 me-3" formnovalidate>
              <input type="submit" name="complete_registration" value="Complete Registration" class="btn btn-success mt-4">
            </div>         
          <?php endif; ?>
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


<div class="modal fade p-4" id="delete_datetime_slot" tabindex="-1">
  <div class="modal-dialog modal-dialog-scrollable modal-dialog-centered">
    <div class="modal-content rounded-4 shadow">
      <div class="modal-header border-0">
        <h5 class="modal-title w-100 text-center fw-bold">DELETE?</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>     
      <div class="modal-body text-center">
        <i class="fa-solid fa-triangle-exclamation fa-4x text-danger mb-3"></i>
        <p class="mb-0 px-2">Are you sure you want to delete your account? This action cannot be undone.</p>
      </div>      
      <div class="modal-footer border-0 d-flex justify-content-center">
        <button type="button" class="btn btn-secondary rounded-3 px-4" data-bs-dismiss="modal">Close</button>
        <form method="POST">
          <input type="hidden" name="action" value="delete_datetime_slot">
          <input type="hidden" name="delete_id" id="delete_id">
          <input type="submit" value="Yes, Delete" class="btn bg-danger text-light rounded-3 px-4">
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
    document.getElementById("edit_slot_count").value = btn.dataset.slotcount;
    
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

document.querySelectorAll(".delete-button").forEach(btn => {
  btn.addEventListener("click", ()=> {
    document.getElementById("delete_id").value = btn.dataset.id;
    
    const modal = new bootstrap.Modal(document.getElementById("delete_datetime_slot"));
    modal.show();
  });
});
</script>
<?php
  showAdminFooter();  
?>