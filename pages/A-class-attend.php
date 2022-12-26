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
      <?=$class["cd"]?>
    </small>
  </div>
  <button class="btn btn-danger mi me-1" onclick="cb.page(0)">
    reply
  </button>
</div>

<!-- (C) ADD USER TO CLASS -->
<form class="d-flex align-items-stretch head border mb-3 p-2" onsubmit="return attend.add()">
  <input type="email" required id="attend-add" required placeholder="Email" class="form-control form-control-sm">
  <input type="submit" class="btn btn-primary btn-sm ms-1 mi" value="add">
</form>

<!-- (D) ATTENDANCE LIST -->
<div class="mt-4">
  * Enter email above to add student not in the course.<br>
  * Blue check "present", red cross "absent".<br>
  * Remember to "save attendance" below.
</div>
<div id="attend-list" class="zebra my-4"></div>