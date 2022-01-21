<?php
// (A) GET CLASSES FOR TEACHER
$_POST["uid"] = $_SESS["user"]["user_id"];
$classes = $_CORE->autoCall("Classes", "getByTeacher");

// (B) DRAW CLASSES LIST
if (is_array($classes["data"])) { foreach ($classes["data"] as $id=>$c) { ?>
<div class="d-flex align-items-center border p-2">
  <div class="flex-grow-1">
    <strong><?=$c["class_date"]?></strong><br>
    [<?=$c["course_code"]?>] <?=$c["course_name"]?><br>
    <small><?=$c["class_desc"]?></small>
  </div>
  <button class="btn btn-primary btn-sm mi" onclick="attend.show(<?=$id?>)">
    checklist
  </button>
</div>
<?php }} else { ?>
<div class="d-flex align-items-center border p-2">No classes found.</div>
<?php }

// (C) PAGINATION
$_CORE->load("Page");
$_CORE->Page->draw($classes["page"], "classes.goToPage");
