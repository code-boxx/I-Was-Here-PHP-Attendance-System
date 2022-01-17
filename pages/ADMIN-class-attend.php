<?php
// (A) GET COURSE + CLASS
$_CORE->load("Courses");
$course = $_CORE->Courses->get($_POST["cid"]);
$class = $_CORE->autoCall("Classes", "get"); ?>
<!-- (B) NAVIGATION -->
<nav class="bg-white border mb-3">
  <!-- (B1) PAGE HEADER -->
  <div class="d-flex align-items-center p-3 pb-0">
    <div class="flex-grow-1">
      <h4>Class Attendance</h4>
      <small>
        [<?=$course["course_code"]?>] <?=$course["course_name"]?>
      </small><br>
      <small>
        <?=$class["class_date"]?>
      </small>
    </div>
    <button class="btn btn-danger" onclick="cb.page(2)">
      <span class="mi">reply</span>
    </button>
  </div>

  <!-- (B2) ADD USER TO CLASS -->
  <form class="d-flex align-items-stretch p-3" onsubmit="return attend.add()">
    <input type="email" required id="attend-add" required placeholder="Email" class="form-control form-control-sm"/>
    <button class="btn btn-primary">
      <span class="mi">add</span>
    </button>
  </form></div>
</nav>

<!-- (C) ATTENDANCE LIST -->
<div class="mt-4">
  * Blue check indicates "present", red cross indicates "absent".<br>
  * Remember to hit "save" at the bottom.<br>
  * Manually enter an email above to add a student that is not in the course.
</div>
<div id="attend-list" class="bg-white border zebra my-4"></div>
