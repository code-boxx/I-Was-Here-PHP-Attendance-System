<?php
// (A) GET COURSES
$courses = $_CORE->autoCall("Courses", "getAll");

// (B) DRAW COURSES LIST
if (is_array($courses)) { foreach ($courses as $code=>$c) { ?>
<div class="d-flex align-items-center border p-2">
  <div class="flex-grow-1">
    <strong>[<?=$c["course_code"]?>] <?=$c["course_name"]?></strong><br>
    <small><?=$c["sd"]?> TO <?=$c["ed"]?></small><br>
    <small><?=$c["course_desc"]?></small>
  </div>
  <div class="dropdown">
    <button type="button" class="btn btn-primary p-3 ico-sm icon-arrow-right" type="button" data-bs-toggle="dropdown"></button>
    <ul class="dropdown-menu dropdown-menu-dark">
      <li class="dropdown-item" onclick="course.addEdit('<?=$code?>')">
        <i class="text-secondary ico-sm icon-pencil"></i> Edit
      </li>
      <li class="dropdown-item" onclick="cuser.show('<?=$code?>')">
        <i class="text-secondary ico-sm icon-users"></i> Cohort
      </li>
      <li class="dropdown-item text-warning" onclick="course.del('<?=$code?>')">
        <i class="ico-sm icon-bin2"></i> Delete
      </li>
    </ul>
  </div>
</div>
<?php }} else { echo "No courses found."; }

// (C) PAGINATION
$_CORE->load("Page");
$_CORE->Page->draw("course.goToPage");