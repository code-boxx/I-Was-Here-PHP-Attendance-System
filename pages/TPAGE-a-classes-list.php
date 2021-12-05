<?php
// (A) GET CLASSES FOR TEACHER
$_POST["uid"] = $_USER["user_id"];
$classes = $_CORE->autoCall("Classes", "getByTeacher");

// (B) DRAW CLASSES LIST
if (is_array($classes["data"])) { foreach ($classes["data"] as $id=>$c) { ?>
<div class="row p-1">
  <div class="col-7">
    <strong><?=$c["class_date"]?></strong><br>
    [<?=$c["course_code"]?>] <?=$c["course_name"]?><br>
    <small><?=$c["class_desc"]?></small>
  </div>
  <div class="col text-end">
    <button class="btn btn-primary btn-sm" onclick="att.show(<?=$id?>)">
      <span class="mi">checklist</span>
    </button>
  </div>
</div>
<?php }} else { echo "No classes found."; }

// (C) PAGINATION
$_CORE->load("Page");
$_CORE->Page->draw($classes["page"], "classes.goToPage");
