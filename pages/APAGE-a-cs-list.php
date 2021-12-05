<?php
// (A) GET COURSES
$courses = $_CORE->autoCall("Courses", "getAll");

// (B) DRAW COURSES LIST
if (is_array($courses["data"])) { foreach ($courses["data"] as $id=>$c) { ?>
<div class="row p-1">
  <div class="col-7">
    <strong>[<?=$c["course_code"]?>] <?=$c["course_name"]?></strong><br>
    <small><?=$c["course_start"]?> TO <?=$c["course_end"]?></small><br>
    <small><?=$c["course_desc"]?></small>
  </div>
  <div class="col text-end">
    <button class="btn btn-danger btn-sm" onclick="cs.del(<?=$id?>)">
      <span class="mi">delete</span>
    </button>
    <button class="btn btn-primary btn-sm" onclick="cc.show(<?=$id?>)">
      <span class="mi">class</span>
    </button>
    <button class="btn btn-primary btn-sm" onclick="cu.show(<?=$id?>)">
      <span class="mi">people</span>
    </button>
    <button class="btn btn-primary btn-sm" onclick="cs.addEdit(<?=$id?>)">
      <span class="mi">edit</span>
    </button>
  </div>
</div>
<?php }} else { echo "No courses found."; }

// (C) PAGINATION
$_CORE->load("Page");
$_CORE->Page->draw($courses["page"], "cs.goToPage");
