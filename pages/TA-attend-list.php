<?php
// (A) GET STUDENTS & ATTENDANCE
$students = $_CORE->autoCall("Attend", "getStudents");

// (B) DRAW STUDENTS LIST
if (is_array($students)) { foreach ($students as $id=>$s) {
  $studentName = htmlspecialchars($s["user_name"], ENT_QUOTES, "UTF-8");
  $studentEmail = htmlspecialchars($s["user_email"], ENT_QUOTES, "UTF-8");
  $attendanceNotes = htmlspecialchars($s["a_notes"] ?? "", ENT_QUOTES, "UTF-8");
?>
<div class="d-flex align-items-stretch border p-2">
  <div class="flex-grow-1">
    <strong><?=$studentName?> | <?=$studentEmail?></strong>
    <input type="hidden" class="att-i" value="<?=$id?>">
    <input id="att-n<?=$id?>" type="text" class="form-control mt-2"
           aria-label="Attendance notes for <?=$studentName?>" placeholder="Notes, if any."
           value="<?=$attendanceNotes?>">
  </div>
  <button id="att-s<?=$id?>" value="<?=$id?>" data-student="<?=$studentName?>"
          aria-pressed="<?=$s["a_status"]==1?"true":"false"?>"
          aria-label="<?=$s["a_status"]==1?"Present; mark absent":"Absent; mark present"?> for <?=$studentName?>"
          onclick="attend.toggle(<?=$id?>)"
          class="mx-2 btn btn-<?=$s["a_status"]==1?"primary":"danger"?> 
                 icon icon-<?=$s["a_status"]==1?"checkmark":"cross"?>">
  </button>
</div>
<?php } ?>
<div class="d-flex align-items-center mt-4">
  <button type="button" class="my-1 btn btn-primary d-flex-inline" onclick="attend.save()">
    <i class="ico-sm icon-checkmark"></i> Save
  </button>
</div>
<?php } else { echo "No students found."; }