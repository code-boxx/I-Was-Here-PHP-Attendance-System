<?php
// (A) GET COURSE
$course = $_CORE->autoCall("Courses", "get"); ?>
<!-- (B) NAVIGATION -->
<div class="d-flex align-items-center mb-3">
  <div class="flex-grow-1">
    <h3>COURSE COHORT</h3>
    <small>
      [<?=$course["course_code"]?>] <?=$course["course_name"]?>
    </small>
  </div>
  <label class="btn btn-primary me-1 mi" for="course-user-import">
    upload
  </label>
  <input type="file" class="d-none" accept=".csv" id="course-user-import" onchange="cuserin.init()"/>
  <button class="btn btn-danger mi" onclick="cb.page(1)">
    reply
  </button>
</div>

<!-- (B) ADD SINGLE USER TO COURSE -->
<form class="d-flex align-items-stretch bg-white border mb-3 p-3" onsubmit="return cuser.add()">
  <input type="email" required id="course-user-add" placeholder="Student/Teacher Email" class="form-control form-control-sm"/>
  <button class="btn btn-primary mi">
    add
  </button>
</form>

<!-- (C) USERS LIST -->
<div id="course-user-list" class="bg-white zebra my-4"></div>
