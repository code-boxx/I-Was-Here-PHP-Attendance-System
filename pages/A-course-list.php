<?php
// (A) GET COURSES
$courses = $_CORE->autoCall("Courses", "getAll");

// (B) DRAW COURSES LIST
if (is_array($courses)) { foreach ($courses as $id=>$c) { ?>
<div class="d-flex align-items-center border p-2">
  <div class="flex-grow-1">
    <strong>[<?=$c["course_code"]?>] <?=$c["course_name"]?></strong><br>
    <small><?=$c["course_start"]?> TO <?=$c["course_end"]?></small><br>
    <small><?=$c["course_desc"]?></small>
  </div>
  <div>
    <button class="btn btn-danger btn-sm mi" onclick="course.del(<?=$id?>)">
      delete
    </button>
    <button class="btn btn-primary btn-sm mi" onclick="cuser.show(<?=$id?>)">
      people
    </button>
    <button class="btn btn-primary btn-sm mi" onclick="course.addEdit(<?=$id?>)">
      edit
    </button>
  </div>
</div>
<?php }} else { echo "No courses found."; }

// (C) PAGINATION
$_CORE->load("Page");
$_CORE->Page->draw("course.goToPage");