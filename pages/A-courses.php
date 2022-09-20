<?php
$_PMETA = ["load" => [
  ["l", HOST_ASSETS."CB-selector.css"],
  ["s", HOST_ASSETS."CB-selector.js", "defer"],
  ["s", HOST_ASSETS."A-course.js", "defer"],
  ["s", HOST_ASSETS."A-course-import.js", "defer"],
  ["s", HOST_ASSETS."A-course-user.js", "defer"],
  ["s", HOST_ASSETS."A-course-user-import.js", "defer"]
]];
require PATH_PAGES . "TEMPLATE-top.php"; ?>
<!-- (A) NAVIGATION -->
<div class="d-flex align-items-center mb-3">
  <h3 class="flex-grow-1">MANAGE COURSES</h3>
  <button class="btn btn-primary mx-1 mi" onclick="cimport.init()">
    upload
  </button>
  <button class="btn btn-primary mi" onclick="course.addEdit()">
    add
  </button>
</div>

<!-- (B) SEARCH BAR -->
<form class="d-flex align-items-stretch head border mb-3 p-2" onsubmit="return course.search()">
  <input type="text" id="course-search" placeholder="Search" class="form-control form-control-sm">
  <button class="btn btn-primary mi mx-1">
    search
  </button>
</form>

<!-- (C) COURSES LIST -->
<div id="course-list" class="zebra my-4"></div>
<?php require PATH_PAGES . "TEMPLATE-bottom.php"; ?>