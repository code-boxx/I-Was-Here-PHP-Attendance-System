<?php
$_PMETA = ["load" => [
  ["l", HOST_ASSETS."CB-selector.css"],
  ["s", HOST_ASSETS."CB-selector.js", "defer"],
  ["s", HOST_ASSETS."ADMIN-course.js", "defer"],
  ["s", HOST_ASSETS."ADMIN-course-user.js", "defer"],
  ["s", HOST_ASSETS."ADMIN-course-user-import.js", "defer"],
  ["s", HOST_ASSETS."ADMIN-course-class.js", "defer"],
  ["s", HOST_ASSETS."ADMIN-course-attend.js", "defer"]
]];
require PATH_PAGES . "TEMPLATE-top.php"; ?>
<!-- (A) NAVIGATION -->
<div class="d-flex align-items-center mb-3">
  <h3 class="flex-grow-1">MANAGE COURSES</h3>
  <button class="btn btn-primary" onclick="course.addEdit()">
    <span class="mi">add</span>
  </button>
</div>

<!-- (B) SEARCH BAR -->
<form class="d-flex align-items-stretch bg-white border mb-3 p-2" onsubmit="return course.search()">
  <input type="text" id="course-search" placeholder="Search" class="form-control form-control-sm"/>
  <button class="btn btn-primary">
    <span class="mi">search</span>
  </button>
</form>

<!-- (C) COURSES LIST -->
<div id="course-list" class="bg-white zebra my-4"></div>
<?php require PATH_PAGES . "TEMPLATE-bottom.php"; ?>
