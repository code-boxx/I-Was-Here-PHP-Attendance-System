<?php
// (A) GET COURSE
$course = $_CORE->autoCall("Courses", "get"); ?>
<!-- (B) NAVIGATION -->
<div class="d-flex align-items-center mb-3">
  <div class="flex-grow-1">
    <h3>COURSE CLASSES</h3>
    <small>
      [<?=$course["course_code"]?>] <?=$course["course_name"]?>
    </small>
  </div>
  <button class="btn btn-danger me-1 mi" onclick="cb.page(1)">
    reply
  </button>
  <button class="btn btn-primary mi" onclick="classes.addEdit()">
    add
  </button>
</div>

<!-- (C) CLASSES LIST -->
<div id="class-list" class="zebra bg-white border my-4"></div>
