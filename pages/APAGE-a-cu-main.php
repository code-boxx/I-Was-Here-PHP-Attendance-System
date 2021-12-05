<?php
// (A) GET COURSE
$course = $_CORE->autoCall("Courses", "get");
?>

<!-- (B) NAVIGATION -->
<!-- (B1) PAGE HEADER -->
<nav class="navbar cb-grey">
<div class="container-fluid">
  <div>
    <h4>Course Cohort</h4>
    <small>
      [<?=$course["course_code"]?>] <?=$course["course_name"]?>
    </small>
  </div>
  <div class="d-flex">
    <label class="btn btn-primary" for="cu-import">
      <span class="mi">upload</span>
    </label>
    <input type="file" class="d-none" accept=".csv" id="cu-import" onchange="cuin.init()"/>
    <button class="btn btn-danger" onclick="cb.page(1)">
      <span class="mi">reply</span>
    </button>
  </idv>
</div>
</nav>

<!-- (B2) ADD SINGLE USER TO COURSE -->
<div class="searchBar"><form class="d-flex" onsubmit="return cu.add()">
  <input type="email" required id="cu-add" placeholder="Student/Teacher Email" class="form-control form-control-sm"/>
  <button class="btn btn-primary">
    <span class="mi">add</span>
  </button>
</form></div>

<!-- (C) USERS LIST -->
<div id="cu-list" class="container zebra my-4"></div>
