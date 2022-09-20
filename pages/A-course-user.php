<?php
// (A) GET COURSE
$course = $_CORE->autoCall("Courses", "get"); ?>
<!-- (B) NAVIGATION -->
<div class="d-flex align-items-center mb-3">
  <div class="flex-grow-1">
    <h3 class="mb-0">COURSE COHORT</h3>
    <small class="fw-bold">
      [<?=$course["course_code"]?>] <?=$course["course_name"]?>
    </small>
  </div>
  <button class="btn btn-danger mi me-1" onclick="cb.page(0)">
    reply
  </button>
  <button class="btn btn-primary mi" onclick="uimport.init()">
    upload
  </button>
</div>

<!-- (B) ADD SINGLE USER TO COURSE -->
<form class="d-flex align-items-stretch head border mb-3 p-2" onsubmit="return cuser.add()">
  <input type="email" required id="course-user-add" placeholder="User Name/Email" class="form-control form-control-sm">
  <button class="btn btn-primary mi mx-1">
    add
  </button>
</form>

<!-- (C) USERS LIST -->
<div id="course-user-list" class="zebra my-4"></div>