<?php
  session_start();

  include "components.php";

  showAdminSidebar("Date & Time Slots")
?>

<section class="m-4">
  <div class="d-flex justify-content-between">
    <h4 class="w-1oo fw-semibold">Date & Time Slots</h4>
    <button class="btn btn-success px-3">Add</button>
  </div>
</section>

<?php
  showAdminFooter();  
?>