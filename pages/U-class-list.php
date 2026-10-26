<?php
// (A) GET CLASSES FOR STUDENT
$_POST["uid"] = $_SESSION["user"]["user_id"];
$classes = $_CORE->autoCall("Classes", "getByStudent");

// (B) DRAW CLASSES LIST
if (is_array($classes)) { foreach ($classes as $id=>$c) { ?>
<div class="d-flex align-items-center border p-2">
  <div class="flex-grow-1">
    <strong><?=htmlspecialchars($c["cd"], ENT_QUOTES, "UTF-8")?></strong><br>
    [<?=htmlspecialchars($c["course_code"], ENT_QUOTES, "UTF-8")?>]
    <?=htmlspecialchars($c["course_name"], ENT_QUOTES, "UTF-8")?><br>
    <small><?=htmlspecialchars($c["class_desc"] ?? "", ENT_QUOTES, "UTF-8")?></small>
  </div>
  <div class="p-3 badge bg-<?=($c["a_status"]===null?"secondary":($c["a_status"]==1?"success":"danger"))?>">
    <?=($c["a_status"]===null?"Not recorded":($c["a_status"]==1?"Present":"Absent"))?>
  </div>
  <div class="ms-2">
    <?php if (($c["correction_status"] ?? null)=="pending") { ?>
    <span class="badge bg-info"><?=htmlspecialchars(ucfirst($c["correction_status"]), ENT_QUOTES, "UTF-8")?> request</span>
    <?php } else { ?>
    <?php if (isset($c["correction_status"])) { ?>
    <span class="badge bg-secondary me-1"><?=htmlspecialchars(ucfirst($c["correction_status"]), ENT_QUOTES, "UTF-8")?></span>
    <?php } ?>
    <button class="btn btn-outline-primary btn-sm"
            type="button" onclick="studentAttendance.request(<?=$id?>)">Request correction</button>
    <?php } ?>
  </div>
</div>
<?php }} else { echo "No classes found."; }

// (C) PAGINATION
$_CORE->load("Page");
$_CORE->Page->draw("classes.goToPage");