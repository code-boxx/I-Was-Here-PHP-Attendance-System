<?php
$_PMETA = ["load" => [
  ["l", HOST_ASSETS."CB-selector.css"],
  ["s", HOST_ASSETS."CB-selector.js", "defer"],
  ["s", HOST_ASSETS."A-reports.js", "defer"]
]];
require PATH_PAGES . "TEMPLATE-top.php"; ?>
<div class="d-flex flex-wrap">
  <!-- (A) ATTENDANCE -->
  <form class="m-1 p-4 bg-white border" id="report-attend" method="post" target="_blank" action="<?=HOST_BASE?>report/attend">
    <h5 class="mb-3">ATTENDANCE REPORT</h5>
    <input type="text" class="form-control" id="attend-course" placeholder="Course Name/Code">
    <input type="hidden" id="attend-id" name="id">
  </form>
</div>
<?php require PATH_PAGES . "TEMPLATE-bottom.php"; ?>