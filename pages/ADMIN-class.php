<?php
// (A) GET COURSE
$course = $_CORE->autoCall("Courses", "get"); ?>
<!-- (B) NAVIGATION -->
<nav class="bg-white border mb-3">
<div class="d-flex align-items-center p-3">
  <div class="flex-grow-1">
    <h4>COURSE CLASSES</h4>
    <small>
      [<?=$course["course_code"]?>] <?=$course["course_name"]?>
    </small>
  </div>
  <button class="btn btn-danger me-1" onclick="cb.page(1)">
    <span class="mi">reply</span>
  </button>
  <button class="btn btn-primary" onclick="classes.addEdit()">
    <span class="mi">add</span>
  </button>
</div>
</nav>

<!-- (C) CLASSES LIST -->
<div id="class-list" class="bg-white border zebra my-4"></div>
