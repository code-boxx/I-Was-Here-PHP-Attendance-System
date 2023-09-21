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
  <button type="button" class="btn btn-danger p-3 ico-sm icon-undo2" onclick="cb.page(1)"></button>
</div>

<!-- (C) ADD USER TO COURSE -->
<form class="d-flex align-items-stretch head border mb-3 p-2" onsubmit="return cuser.add()">
  <input type="email" required id="course-user-add" placeholder="User Name/Email" class="form-control form-control-sm">
  <button type="submit" class="btn btn-primary p-3 mx-1 ico-sm icon-plus"></button>
  <button type="button" class="btn btn-primary p-3 ico-sm icon-upload3" onclick="cuser.import()"></button>
</form>

<!-- (D) USERS LIST -->
<div id="course-user-list" class="zebra my-4"></div>