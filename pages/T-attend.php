<?php
// (A) GET CLASS + COURSE
$_CORE->load("Courses");
$class = $_CORE->autoCall("Classes", "get");
$course = $_CORE->Courses->get($class["course_id"]); ?>
<!-- (B) NAVIGATION -->
<div class="d-flex align-items-center mb-3">
  <div class="flex-grow-1">
    <h3 class="mb-0">[<?=$course["course_code"]?>] <?=$course["course_name"]?></h3>
    <small class="fw-bold">
      <?=$class["class_date"]?>
    </small>
  </div>
  <button class="btn btn-danger mi me-1" onclick="cb.page(0)">
    reply
  </button>
  <a class="btn btn-primary mi" target="_blank" href="<?=HOST_BASE?>classqr?c=<?=$class["class_id"]?>">
    qr_code
  </a>
</div>

<!-- (C) ADD USER TO CLASS -->
<form class="d-flex align-items-stretch head border mb-3 p-2" onsubmit="return attend.add()">
  <input type="email" required id="attend-add" required placeholder="Email" class="form-control form-control-sm">
</form>

<!-- (D) ATTENDANCE LIST -->
<div class="mt-4">
  * Blue check "present", red cross "absent".<br>
  * Remember to "save attendance".<br>
  * Enter email above to add student not in the course.
</div>
<div id="attend-list" class="zebra my-4"></div>