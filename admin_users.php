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
      
        $stmt_add_user = $conn->prepare("INSERT INTO users(username, password, first_name, middle_name, last_name, extension_name, age, gender, birth_date, contact_number, email_address, address) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt_add_user->bind_param("ssssssisssss", $username, $password, $first_name, $middle_name, $last_name, $extension_name, $age, $gender, $birth_date, $contact_number, $email_address, $address);
        
        if (!$stmt_add_user->execute()) {
          $_SESSION["msg"] = ["danger", "Insert error. Please try again later."];
        }
        $_SESSION["msg"] = ["success", "User added successfully"];
        header ("Location: " . $_SERVER["PHP_SELF"]);
        exit;
      } else {
        $_SESSION["msg"] = ["danger", "Session Expired. Please try again."];
      }
    }
    
    if (isset( $_POST["edit_user"])) {
      $edit_id = test_input($_POST["edit_id"]);
      $edit_username = test_input($_POST["edit_username"]);
      $original_username = test_input($_POST["original_username"]);
      
      if ($edit_username != $original_username) {
        $stmt_check_usrnm = $conn->prepare("SELECT * FROM users WHERE username = ?");
        $stmt_check_usrnm->bind_param("s", $edit_username);
        $stmt_check_usrnm->execute();
        $result = $stmt_check_usrnm->get_result();
        $row = $result->fetch_assoc();
        
        if ($result->num_rows > 0 || $edit_username == "admin") {
          $_SESSION["msg"] = ["danger", "Username already exists."];
          header("Location: " . $_SERVER["PHP_SELF"]);
          exit;
        }
      }
      
      $edit_first_name = test_input($_POST["edit_first_name"]);
      $edit_middle_name = test_input($_POST["edit_middle_name"]);
      $edit_last_name = test_input($_POST["edit_last_name"]);
      $edit_extension_name = test_input($_POST["edit_extension_name"]);
      $edit_age = test_input($_POST["edit_age"]);
      $edit_gender = test_input($_POST["edit_gender"]);
      $edit_birth_date = test_input($_POST["edit_birth_date"]);
      $edit_contact_number = test_input($_POST["edit_contact_number"]);
      $edit_email_address = test_input($_POST["edit_email_address"]);
      $edit_address = test_input($_POST["edit_address"]);
      
      $stmt_edit_user = $conn->prepare("UPDATE users SET 
          username = ?,
          first_name = ?,
          middle_name = ?,
          last_name = ?,
          extension_name = ?,
          age = ?,
          gender = ?,
          birth_date = ?,
          contact_number = ?,
          email_address = ?,
          address = ?
        WHERE id = ?");
      $stmt_edit_user->bind_param("sssssisssssi", $edit_username, $edit_first_name, $edit_middle_name, $edit_last_name, $edit_extension_name, $edit_age, $edit_gender, $edit_birth_date, $edit_contact_number, $edit_email_address, $edit_address, $edit_id);
      if (!$stmt_edit_user->execute()) {
        $_SESSION["msg"] = ["danger", "Update error. Please try again later."];
      }
      $_SESSION["msg"] = ["success", "User updated successfully"];
      header ("Location: " . $_SERVER["PHP_SELF"]);
      exit;
    }
    
    if (isset($_POST["delete_user"])) {
      $delete_id = test_input($_POST["delete_id"]);
      
      $stmt_delete_user = $conn->prepare("DELETE FROM users WHERE id = ?");
      $stmt_delete_user->bind_param("i", $delete_id);
      
      if (!$stmt_delete_user->execute()) {
        $_SESSION["msg"] = ["danger", "Delete error. Please try again later."];
      }
      $_SESSION["msg"] = ["success", "User deleted successfully"];
      header ("Location: " . $_SERVER["PHP_SELF"]);
      exit;
    }
  }
  
  showAdminSidebar("Users")
?>

<section class="m-4">
  <?php showAlert(); ?>
  <div class="d-flex align-items-center">
    <h4 class="w-1oo fw-semibold m-0 p-0">Users</h4>
    <div class="d-inline-block bg-secondary ms-auto px-2 py-1 rounded">
			<div class="form-check form-switch">
				<input class="form-check-input" type="checkbox" role="switch" id="users_switch">
				<label class="form-check-label text-white" for="users_switch">View Full Table</label>
			</div>
		</div>
    <button class="btn btn-success ms-3 px-3" data-bs-toggle="modal" data-bs-target="#add_user" type="button">Add</button>
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
            <th class="text-center hidden-column hidden">Birth Date</th>
            <th class="text-center">Contact Number</th>
            <th class="text-center hidden-column hidden">E-mail Address</th>
            <th class="text-center">Address</th>
            <th class="text-center">Book</th>
            <th class="text-center">Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php
            $stmt_fetch_datetime = $conn->prepare("SELECT * FROM users");
            $stmt_fetch_datetime->execute();
            $result = $stmt_fetch_datetime->get_result();
            
            if ($result->num_rows === 0) {
              echo '<tr><td colspan="11" class="text-center text-muted fw-semibold mt-4">No users available.</td></tr>';
            }
            while($row = $result->fetch_assoc()):
          ?>
            <tr class="text-nowrap">
              <th class="text-center"> <?= $row["id"]; ?> </th>
              <td class="text-center"> <?= $row["username"]; ?> </td>
              <td class="text-center"> <?= $row["first_name"] . " " . $row["middle_name"] . " " . $row["last_name"] . " " . $row["extension_name"] ?> </td>
              <td class="text-center"> <?= $row["age"]; ?> </td>
              <td class="text-center"> <?= $row["gender"]; ?> </td>
              <td class="text-center hidden-column hidden"> <?= date("F j, Y", strtotime($row["birth_date"])); ?> </td>
              <td class="text-center"> <?= $row["contact_number"]; ?> </td>
              <td class="text-center hidden-column hidden"> <?= $row["email_address"]; ?> </td>
              <td class="text-center"> <?= $row["address"]; ?> </td>
              <td class="text-center">
                <a href="admin_appointments_book.php?user_id=<?= $row['id']; ?>" class="btn btn-sm btn-success px-2">Book</a>
              </td>
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
                  data-address="<?= $row["address"]; ?>"
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
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
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
              <div class="d-flex justify-content-center mb-2 mt-4">
                <button type="button" class="btn btn-md btn-danger rounded-3 px-3 me-3" data-bs-dismiss="modal">Cancel</button>
                <input type="submit" name="check_username" value="Next" class="btn btn-success">
              </div>
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
                <label for="contact_number" class="form-label  ps-4">Contact Number (e.g. 09...)</label>
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
              <input type="submit" name="complete_registration" class="btn btn-success mt-4">
            </div>         
          <?php endif; ?>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>

<div class="modal fade" id="edit_user" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable p-4">
    <div class="modal-content">
      <div class="modal-header border-0">
        <h4 class="modal-title w-100 text-center fw-bold m-0 p-0">ADD USER</h4>  
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      
      <div class="modal-body">
        <form method="POST" novalidate>
          <input type="hidden" id="edit_id" name="edit_id">
          <input type="hidden" id="original_username" name="original_username">
          <div class="row mb-2">
            <div class="col-sm form-floating">
              <input type="text" class="form-control" id="edit_username" name="edit_username" placeholder="Username" pattern="[A-Za-z0-9._]+" required>
              <label for="edit_username" class="form-label ps-4">Username</label>
            </div>
          </div>
          <div class="row mb-2 gx-3 gy-2">
            <div class="col-sm form-floating">
              <input type="text" class="form-control" id="edit_first_name" name="edit_first_name" placeholder="First Name" required>
              <label for="edit_first_name" class="form-label ps-4">First Name</label>
            </div>
            <div class="col-sm form-floating">
              <input type="text" class="form-control" id="edit_middle_name" name="edit_middle_name" placeholder="Middle Name" required>
              <label for="edit_middle_name" class="form-label ps-4">Middle Name</label>
            </div>
          </div>
          <div class="row mb-2 gx-3 gy-2">
            <div class="col-sm form-floating">
              <input type="text" class="form-control" id="edit_last_name" name="edit_last_name" placeholder="Last Name" required>
              <label for="edit_last_name" class="form-label ps-4">Last Name</label>
            </div>
            <div class="col-sm form-floating">
              <input type="text" class="form-control" id="edit_extension_name" name="edit_extension_name" placeholder="Extension Name">
              <label for="edit_extension_name" class="form-label ps-4">Extension Name</label>
            </div>
          </div>
          <div class="row mb-2 gx-3 gy-2">
            <div class="col-sm form-floating">
              <input type="number" class="form-control" id="edit_age" name="edit_age" placeholder="Age" required>
              <label for="edit_age" class="form-label ps-4">Age</label>
            </div>
            <div class="col-sm form-floating">
              <select id="edit_gender" name="edit_gender" class="form-select ps-4" required>
                <option value="" selected disabled>Select</option>
                <option value="Male">Male</option>
                <option value="Female">Female</option>
                <option value="Other">Other</option>
              </select>
              <label for="edit_gender" class="form-label ps-4">Select Gender</label>
            </div>
          </div>
          <div class="row mb-2 gx-3 gy-2">
            <div class="col-sm form-floating">
              <input type="date" class="form-control" id="edit_birth_date" name="edit_birth_date" placeholder="Birth Date" required>
              <label for="edit_birth_date" class="form-label ps-4">Birth Date</label>
            </div>
            <div class="col-sm form-floating">
              <input type="tel" class="form-control" id="edit_contact_number" name="edit_contact_number" placeholder="Contact Number (e.g. 09...="")" required pattern="\d{11}" minlength="11" maxlength="11">
              <label for="edit_contact_number" class="form-label  ps-4">Contact Number (e.g. 09...)</label>
            </div>
          </div>
          <div class="row mb-2 gx-3 gy-2">
            <div class="col-sm form-floating">
              <input type="email" class="form-control" id="edit_email_address" name="edit_email_address" placeholder="E-mail Address" required>
              <label for="edit_email_address" class="form-label ps-4">E-mail Address</label>
            </div>
            <div class="col-sm form-floating">
              <textarea class="form-control" id="edit_address" name="edit_address" placeholder="Address" required></textarea>
              <label for="edit_address" class="form-label ps-4">Address</label>
            </div>
          </div>
          <div class="row m-2">
            <div class="d-flex justify-content-center mt-4 mb-2">
              <button type="button" class="btn btn-md btn-danger rounded-3 px-3 me-3" data-bs-dismiss="modal">Cancel</button>
              <input type="submit" name="edit_user" value="Update" class="btn btn-success">
            </div>         
          </div>
        </form>
      </div>
    </div>
  </div>
</div>

<div class="modal fade p-4" id="delete_user" tabindex="-1">
  <div class="modal-dialog modal-dialog-scrollable modal-dialog-centered">
    <div class="modal-content rounded-4 shadow">
      <div class="modal-header border-0">
        <h5 class="modal-title w-100 text-center fw-bold">DELETE?</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>     
      <div class="modal-body text-center">
        <i class="fa-solid fa-triangle-exclamation fa-4x text-danger mb-3"></i>
        <p class="mb-0 px-2">Are you sure you want to delete this user? This action cannot be undone.</p>
      </div>      
      <div class="modal-footer border-0 d-flex justify-content-center">
        <button type="button" class="btn btn-secondary rounded-3 px-4" data-bs-dismiss="modal">Close</button>
        <form method="POST">
          <input type="hidden" name="delete_id" id="delete_id">
          <input type="submit" name="delete_user" value="Yes, Delete" class="btn bg-danger text-light rounded-3 px-4">
        </form>
      </div>
    </div>
  </div>
</div>

<script>
  const switchInput = document.getElementById('users_switch');
  const hiddenColumns = document.querySelectorAll('.hidden-column');
  
  switchInput.addEventListener('change', () => {
    hiddenColumns.forEach(column => {
    column.classList.toggle('hidden', !switchInput.checked);
    });
  });
  
  document.querySelectorAll(".edit-button").forEach(btn => {
    btn.addEventListener("click", () => {
      document.getElementById("edit_id").value = btn.dataset.id;
      document.getElementById("original_username").value = btn.dataset.username;
      document.getElementById("edit_username").value = btn.dataset.username;
      document.getElementById("edit_first_name").value = btn.dataset.firstname;
      document.getElementById("edit_middle_name").value = btn.dataset.middlename ;
      document.getElementById("edit_last_name").value = btn.dataset.lastname;
      document.getElementById("edit_extension_name").value = btn.dataset.extensionname;
      document.getElementById("edit_age").value = btn.dataset.age;
      document.querySelectorAll("#edit_gender option").forEach(opt => {
        if (opt.value == btn.dataset.gender) {
          opt.setAttribute("selected", "selected");
        }
      });
      document.getElementById("edit_birth_date").value = btn.dataset.birthdate;
      document.getElementById("edit_contact_number").value = btn.dataset.contactnumber;
      document.getElementById("edit_email_address").value = btn.dataset.emailaddress;
      document.getElementById("edit_address").value = btn.dataset.address;
      
      
      const modal = new bootstrap.Modal(document.getElementById("edit_user"));
      modal.show();
    });
  });
  
  document.querySelectorAll(".delete-button").forEach(btn => {
    btn.addEventListener("click", ()=> {
      document.getElementById("delete_id").value = btn.dataset.id;
      
      const modal = new bootstrap.Modal(document.getElementById("delete_user"));
      modal.show();
    });
  });
</script>
<?php
  showAdminFooter();  
?>