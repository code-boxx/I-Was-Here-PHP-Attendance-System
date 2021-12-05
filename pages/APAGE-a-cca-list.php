<?php
// (A) GET STUDENTS & ATTENDANCE
$students = $_CORE->autoCall("Classes", "getStudents");

// (B) DRAW CLASSES LIST
if (is_array($students)) {
  foreach ($students as $id=>$s) { ?>
  <div class="row p-1"><div class="col">
    <button id="att<?=$id?>" class="btn btn-<?=$s["a"]?"primary":"danger"?> btn-sm" value="<?=$id?>" onclick="cca.toggle(<?=$id?>)">
      <span class="mi"><?=$s["a"]?"done":"close"?></span>
    </button>
    <?=$s["user_name"]?> | <?=$s["user_email"]?>
  </div></div>
  <?php } ?>
  <div class="row p-1"><div class="col">
    <button class="btn btn-primary" onclick="cca.save()">
      Save Attendance
    </button>
  </div></div>
<?php } else { echo "No students found."; }
