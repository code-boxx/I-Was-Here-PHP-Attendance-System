<?php
// (A) GET COURSE + CLASS
$class = $_CORE->autoCall("Classes", "get");
$_CORE->load("Courses");
$course = $_CORE->Courses->get($class["course_id"]); ?>
<!-- (B) NAVIGATION -->
<div class="d-flex align-items-center mb-3">
  <div class="flex-grow-1">
    <h3>CLASS ATTENDANCE</h3>
    <small>
      [<?=$course["course_code"]?>] <?=$course["course_name"]?>
    </small><br>
    <small>
      <?=$class["class_date"]?>
    </small>
    <input type="hidden" id="class_course" value="<?=$class["course_id"]?>"/>
  </div>
  <button class="btn btn-danger mi" onclick="cb.page(1)">
    reply
  </button>
</div>

<!-- (C) ADD USER TO CLASS -->
<form class="d-flex align-items-stretch bg-white border mb-3 p-2" onsubmit="return attend.add()">
  <input type="email" required id="attend-add" required placeholder="Email" class="form-control form-control-sm"/>
  <button class="btn btn-primary mi">
    add
  </button>
</form>

<!-- (D) ATTENDANCE LIST -->
<div class="mt-4">
  * Blue check indicates "present", red cross indicates "absent".<br>
  * Remember to hit "save" at the bottom.<br>
  * Manually enter an email above to add a student that is not in the course.
</div>
<div id="attend-list" class="bg-white zebra my-4"></div>
