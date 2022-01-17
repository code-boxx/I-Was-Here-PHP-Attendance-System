<?php
// (A) GET COURSE
$course = $_CORE->autoCall("Courses", "get"); ?>
<!-- (B) NAVIGATION -->
<nav class="bg-white border mb-3">
  <!-- (B1) PAGE HEADER -->
  <div class="d-flex align-items-center p-3 pb-0">
    <div class="flex-grow-1">
      <h3>COURSE COHORT</h3>
      <small>
        [<?=$course["course_code"]?>] <?=$course["course_name"]?>
      </small>
    </div>
    <label class="btn btn-primary me-1" for="course-user-import">
      <span class="mi">upload</span>
    </label>
    <input type="file" class="d-none" accept=".csv" id="course-user-import" onchange="cuserin.init()"/>
    <button class="btn btn-danger" onclick="cb.page(1)">
      <span class="mi">reply</span>
    </button>
  </div>

  <!-- (B2) ADD SINGLE USER TO COURSE -->
  <form class="d-flex align-items-stretch p-3" onsubmit="return cuser.add()">
    <input type="email" required id="course-user-add" placeholder="Student/Teacher Email" class="form-control form-control-sm"/>
    <button class="btn btn-primary">
      <span class="mi">add</span>
    </button>
  </div>
</nav>

<!-- (C) USERS LIST -->
<div id="course-user-list" class="bg-white border zebra my-4"></div>
