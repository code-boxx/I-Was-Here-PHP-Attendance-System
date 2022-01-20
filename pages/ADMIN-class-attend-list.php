<?php
// (A) GET STUDENTS & ATTENDANCE
$students = $_CORE->autoCall("Classes", "getStudents");

// (B) DRAW CLASSES LIST
if (is_array($students)) {
  foreach ($students as $id=>$s) { ?>
  <div class="d-flex align-items-center border p-2">
    <button id="att<?=$id?>" class="me-2 btn btn-<?=isset($s["a"])?"primary":"danger"?> btn-sm" value="<?=$id?>" onclick="attend.toggle(<?=$id?>)">
      <span class="mi"><?=isset($s["a"])?"done":"close"?></span>
    </button>
    <?=$s["user_name"]?> | <?=$s["user_email"]?>
  </div>
  <?php } ?>
  <div class="d-flex align-items-center p-2">
    <button class="btn btn-primary" onclick="attend.save()">
      Save Attendance
    </button>
  </div>
<?php } else { ?>
<div class="d-flex align-items-center border p-2">No students found.</div>
<?php }
