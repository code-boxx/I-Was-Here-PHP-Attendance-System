<?php
// (A) GET COURSES
$courses = $_CORE->autoCall("Courses", "getAll");

// (B) DRAW COURSES LIST
if (is_array($courses["data"])) { foreach ($courses["data"] as $id=>$c) { ?>
<div class="d-flex align-items-center border p-2">
  <div class="flex-grow-1">
    <strong>[<?=$c["course_code"]?>] <?=$c["course_name"]?></strong><br>
    <small><?=$c["course_start"]?> TO <?=$c["course_end"]?></small><br>
    <small><?=$c["course_desc"]?></small>
  </div>
  <div>
    <button class="btn btn-danger btn-sm" onclick="course.del(<?=$id?>)">
      <span class="mi">delete</span>
    </button>
    <button class="btn btn-primary btn-sm" onclick="classes.show(<?=$id?>)">
      <span class="mi">class</span>
    </button>
    <button class="btn btn-primary btn-sm" onclick="cuser.show(<?=$id?>)">
      <span class="mi">people</span>
    </button>
    <button class="btn btn-primary btn-sm" onclick="course.addEdit(<?=$id?>)">
      <span class="mi">edit</span>
    </button>
  </div>
</div>
<?php }} else { ?>
<div class="d-flex align-items-center border p-2">No courses found.</div>
<?php }

// (C) PAGINATION
$_CORE->load("Page");
$_CORE->Page->draw($courses["page"], "course.goToPage");
