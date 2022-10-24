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
<!-- (A) HEADER -->
<h3 class="mb-3">MANAGE COURSES</h3>

<!-- (B) ACTION BAR -->
<form class="d-flex align-items-stretch head border mb-3 p-2" onsubmit="return course.search()">
  <!-- (B1) SEARCH -->
  <input type="text" id="course-search" placeholder="Search" class="form-control form-control-sm">
  <button class="btn btn-primary mi mx-1">
    search
  </button>

  <!-- (B2) ADD -->
  <div class="dropdown">
    <button class="btn btn-primary mi" type="button" data-bs-toggle="dropdown">
      add
    </button>
    <ul class="dropdown-menu dropdown-menu-dark">
      <li class="dropdown-item" onclick="course.addEdit()">
        <i class="mi mi-smil">add</i> Add Single
      </li>
      <li class="dropdown-item" onclick="cimport.init()">
        <i class="mi mi-smil">upload</i> Import CSV
      </li>
    </ul>
  </div>
</form>

<!-- (C) COURSES LIST -->
<div id="course-list" class="zebra my-4"></div>
<?php require PATH_PAGES . "TEMPLATE-bottom.php"; ?>