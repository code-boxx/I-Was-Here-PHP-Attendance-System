<?php
// (A) GET CLASSES FOR STUDENT
$_POST["uid"] = $_USER["user_id"];
$classes = $_CORE->autoCall("Classes", "getByStudent");

// (B) DRAW CLASSES LIST
if (is_array($classes["data"])) { foreach ($classes["data"] as $id=>$c) { ?>
<div class="row p-1"><div class="col">
  <span class="mi">
    <?=($c["sign_date"]?"done":"close")?>
  </span>
  <strong><?=$c["class_date"]?></strong><br>
  [<?=$c["course_code"]?>] <?=$c["course_name"]?><br>
  <small><?=$c["class_desc"]?></small>
</div></div>
<?php }} else { echo "No classes found."; }

// (C) PAGINATION
$_CORE->load("Page");
$_CORE->Page->draw($classes["page"], "classes.goToPage");
