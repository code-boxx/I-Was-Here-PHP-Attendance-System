<?php
// (A) GET CLASS + COURSE
$_CORE->load("Courses");
$class = $_CORE->autoCall("Classes", "get");
$course = $_CORE->Courses->get($class["course_code"]); ?>
<!-- (B) NAVIGATION -->
<div class="d-flex align-items-center mb-3">
  <div class="flex-grow-1">
    <h3 class="mb-0">[<?=$course["course_code"]?>] <?=$course["course_name"]?></h3>
    <small class="fw-bold"><?=$class["cd"]?></small>
  </div>
  <button type="button" class="btn btn-danger p-3 ico-sm icon-undo2"
          aria-label="Back to classes" onclick="cb.page(1)"></button>
</div>

<!-- (C) NOTES -->
<div class="d-flex align-items-stretch bg-white border mb-1 p-2">
  * Blue check is "present", red cross is "absent".<br>
  * Remember to "save attendance" below.
</div>

<!-- (D) ATTENDANCE LIST -->
<div id="attend-list" class="zebra my-4"></div>
<button type="button" class="btn btn-outline-secondary" onclick="attend.audit()">
  View attendance change history
</button>
<div id="attendance-audit" class="my-3" aria-live="polite"></div>