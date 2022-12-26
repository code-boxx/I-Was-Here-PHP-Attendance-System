<?php
// (A) GET COURSES
$courses = $_CORE->autoCall("Courses", "getAll");

// (B) DRAW COURSES LIST
if (is_array($courses)) { foreach ($courses as $id=>$c) { ?>
<div class="d-flex align-items-center border p-2">
  <div class="flex-grow-1">
    <strong>[<?=$c["course_code"]?>] <?=$c["course_name"]?></strong><br>
    <small><?=$c["sd"]?> TO <?=$c["ed"]?></small><br>
    <small><?=$c["course_desc"]?></small>
  </div>
  <div class="dropdown">
    <button class="btn btn-primary btn-sm mi" type="button" data-bs-toggle="dropdown">
      more_vert
    </button>
    <ul class="dropdown-menu dropdown-menu-dark">
      <li class="dropdown-item" onclick="course.addEdit(<?=$id?>)">
        <i class="mi mi-smol">edit</i> Edit
      </li>
      <li class="dropdown-item" onclick="cuser.show(<?=$id?>)">
        <i class="mi mi-smol">people</i> Cohort
      </li>
      <li class="dropdown-item text-warning" onclick="course.del(<?=$id?>)">
        <i class="mi mi-smol">delete</i> Delete
      </li>
    </ul>
  </div>
</div>
<?php }} else { echo "No courses found."; }

// (C) PAGINATION
$_CORE->load("Page");
$_CORE->Page->draw("course.goToPage");