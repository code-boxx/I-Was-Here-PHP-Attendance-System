<?php
// (A) GET CLASSES FOR STUDENT
$_POST["uid"] = $_SESSION["user"]["user_id"];
$classes = $_CORE->autoCall("Classes", "getByStudent");

// (B) DRAW CLASSES LIST
if (is_array($classes)) { foreach ($classes as $id=>$c) { ?>
<div class="d-flex align-items-center border p-2">
  <div class="flex-grow-1">
    <strong><?=$c["cd"]?></strong><br>
    [<?=$c["course_code"]?>] <?=$c["course_name"]?><br>
    <small><?=$c["class_desc"]?></small>
  </div>
  <div class="p-3 badge bg-<?=($c["a_status"]==1?"success":"danger")?>">
    <i class="icon icon-<?=($c["a_status"]==1?"checkmark":"cross")?>"></i>
  </div>
</div>
<?php }} else { echo "No classes found."; }

// (C) PAGINATION
$_CORE->load("Page");
$_CORE->Page->draw("classes.goToPage");