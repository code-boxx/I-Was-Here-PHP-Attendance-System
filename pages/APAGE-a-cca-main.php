<?php
// (A) GET COURSE + CLASS
$_CORE->load("Courses");
$course = $_CORE->Courses->get($_POST["cid"]);
$class = $_CORE->autoCall("Classes", "get");
?>

<!-- (B) NAVIGATION -->
<!-- (B1) PAGE HEADER -->
<nav class="navbar cb-grey">
<div class="container-fluid">
  <div>
    <h4>Class Attendance</h4>
    <small>
      [<?=$course["course_code"]?>] <?=$course["course_name"]?>
    </small><br>
    <small>
      <?=$class["class_date"]?>
    </small>
  </div>
  <div class="d-flex">
    <button class="btn btn-danger" onclick="cb.page(2)">
      <span class="mi">reply</span>
    </button>
  </div>
</div>
</nav>

<!-- (B2) ADD USER TO CLASS -->
<div class="searchBar"><form class="d-flex" onsubmit="return cca.add()">
  <input type="email" required id="cca-add" required placeholder="Email" class="form-control form-control-sm"/>
  <button class="btn btn-primary">
    <span class="mi">add</span>
  </button>
</form></div>

<!-- (C) ATTENDANCE LIST -->
<div class="mt-4">
  * Blue check indicates "present", red cross indicates "absent".<br>
  * Remember to hit "save" at the bottom.<br>
  * Manually enter an email above to add a student that is not in the course.
</div>
<div id="cca-list" class="container zebra my-4"></div>
