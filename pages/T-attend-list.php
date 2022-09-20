<?php
// (A) GET STUDENTS & ATTENDANCE
$students = $_CORE->autoCall("Classes", "getStudents");

// (B) DRAW CLASSES LIST
if (is_array($students)) { foreach ($students as $id=>$s) { ?>
<div class="d-flex align-items-center border p-2">
  <div class="flex-grow-1">
    <strong><?=$s["user_name"]?></strong><br>
    <?=$s["user_email"]?>
  </div>
  <button id="att<?=$id?>" class="mi me-2 btn btn-<?=isset($s["a"])?"primary":"danger"?> btn-sm" 
          value="<?=$id?>" onclick="attend.toggle(<?=$id?>)">
    <?=isset($s["a"])?"done":"close"?>
  </button>
</div>
<?php } ?>
<div class="d-flex align-items-center p-2">
  <button class="btn btn-primary" onclick="attend.save()">
    Save Attendance
  </button>
</div>
<?php } else { echo "No students found."; }