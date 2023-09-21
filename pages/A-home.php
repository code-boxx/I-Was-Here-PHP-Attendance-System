<?php
// (A) PAGE META
$_PMETA = ["load" => [
  ["s", HOST_ASSETS."CB-autocomplete.js", "defer"],
  ["s", HOST_ASSETS."A-reports.js", "defer"]
]];

// (B) HTML
require PATH_PAGES . "TEMPLATE-top.php"; ?>
<div class="d-flex flex-wrap">
  <!-- (B1) ATTENDANCE -->
  <form class="m-1 p-4 bg-white border" id="report-attend" method="post" target="_blank" action="<?=HOST_BASE?>report/attend">
    <div class="fw-bold text-danger mb-2">ATTENDANCE REPORT</div>
    <div class="form-floating">
      <input type="text" class="form-control" id="attend-course">
      <label>Course Code/Name</label>
    </div>
    <input type="hidden" id="attend-code" name="code">
  </form>
</div>
<?php require PATH_PAGES . "TEMPLATE-bottom.php"; ?>