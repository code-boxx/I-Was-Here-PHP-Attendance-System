<?php
// (A) GET STUDENTS & ATTENDANCE
$students = $_CORE->autoCall("Attend", "getStudents");

// (B) DRAW STUDENTS LIST
if (is_array($students)) { foreach ($students as $id=>$s) { ?>
<div class="d-flex align-items-stretch border p-2">
  <div class="flex-grow-1">
    <strong><?=$s["user_name"]?> | <?=$s["user_email"]?></strong>
    <input type="hidden" class="att-i" value="<?=$id?>">
    <input id="att-n<?=$id?>" type="text" class="form-control mt-2" placeholder="Notes, if any."
           value="<?=$s["a_notes"]?$s["a_notes"]:""?>">
  </div>
  <button id="att-s<?=$id?>" value="<?=$id?>" onclick="attend.toggle(<?=$id?>)"
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