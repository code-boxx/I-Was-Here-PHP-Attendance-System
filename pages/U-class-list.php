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
  <?php
    $attendanceLabel = $c["a_status"]===null ? "Not recorded" :
      ((int)$c["a_status"]===1 ? "Present" : ((int)$c["a_status"]===2 ? "Excused" : "Absent"));
    $attendanceColor = $c["a_status"]===null ? "secondary" :
      ((int)$c["a_status"]===1 ? "success" : ((int)$c["a_status"]===2 ? "info" : "danger"));
  ?>
  <div class="p-3 badge bg-<?=$attendanceColor?>">
    <?=$attendanceLabel?>
  </div>
  <?php if ($c["class_status"]==="cancelled") { ?>
  <span class="badge bg-danger ms-2">Class cancelled</span>
  <?php } elseif (!empty($c["is_holiday"])) { ?>
  <span class="badge bg-warning text-dark ms-2">Non-instruction day</span>
  <?php } ?>
  <?php if ($c["a_status"]===null && $c["class_status"]==="scheduled" && empty($c["is_holiday"])) { ?>
  <button type="button" class="btn btn-outline-primary btn-sm ms-2"
          onclick="classes.queueOffline(<?=$id?>, this)"
          data-class-date="<?=htmlspecialchars($c["class_date"], ENT_QUOTES, "UTF-8")?>">
    Queue check-in
  </button>
  <?php } ?>
  <?php if ($c["class_status"]==="scheduled" && empty($c["is_holiday"])) { ?>
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
  <?php } ?>
</div>
<?php }} else { echo "No classes found."; }

// (C) PAGINATION
$_CORE->load("Page");
$_CORE->Page->draw("classes.goToPage");