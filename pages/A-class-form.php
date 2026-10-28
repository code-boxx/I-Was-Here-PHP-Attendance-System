<?php
// (A) GET CLASS
$edit = isset($_POST["id"]) && $_POST["id"]!="";
if ($edit) {
  $class = $_CORE->autoCall("Classes", "get");
  $_CORE->load("Courses");
  $course = $_CORE->Courses->get($class["course_code"]);
  $teachers = $_CORE->Courses->getTeachers($class["course_code"]);
}

// (B) CLASS FORM ?>
<h3 class="mb-3"><?=$edit?"EDIT":"ADD"?> CLASS</h3>
<form onsubmit="return classes.save()">
  <div class="fw-bold text-danger mb-0">COURSE</div>
  <div class="text-secondary mb-2">Enter course code/name, and select from the autocomplete.</div>
  <div class="bg-white border p-4 mb-4">
    <div class="form-floating">
      <input type="text" class="form-control" id="class_course"
             <?=isset($class)?" readonly onclick='classes.toggle(false)'":""?>
             value="<?=isset($class)?"[{$class["course_code"]}] {$class["course_name"]}":""?>">
      <input type="hidden" id="class_course_code" value="<?=isset($class)?$class["course_code"]:""?>">
      <label>Course</label>
    </div>
    <small id="class_course_note" class="text-danger<?=isset($class)?"":" d-none"?>">
      * Click to change course.
    </small>
  </div>

  <div class="fw-bold text-danger mb-2">CLASS</div>
  <div class="bg-white border p-4 mb-4">
    <input type="hidden" id="class_id" value="<?=isset($class)?$class["class_id"]:""?>">

    <div class="form-floating mb-4">
      <input type="datetime-local" class="form-control" id="class_date" required<?=isset($class)?"":" disabled"?>
             value="<?=isset($class)?str_replace(" ", "T", $class["class_date"]):""?>"
             min="<?=isset($class)?$course["course_start"]."T00:00":""?>"
             max="<?=isset($class)?$course["course_end"]."T23:59":""?>">
      <label>Date Time</label>
    </div>

    <div class="form-floating mb-4">
      <select class="form-select" id="class_teacher"<?=isset($class)?"":" disabled"?>><?php
        if (isset($teachers) && is_array($teachers)) { foreach ($teachers as $id=>$t) {
          printf("<option %svalue='%u'>%s (%s)</option>",
            $id==$class["user_id"] ? "selected " : "",
            $id, $t["user_name"], $t["user_email"]
          );
        }} else { echo "<option value=''>No teachers assigned!</option>"; }
      ?></select>
      <label>Teacher-In-Charge</label>
    </div>

    <div class="form-floating">
      <input type="text" class="form-control" id="class_desc"<?=isset($class)?"":" disabled"?>
             value="<?=isset($class)?$class["class_desc"]:""?>">
      <label>Description, if any.</label>
    </div>
    <?php if (isset($class)) { ?>
    <div class="form-floating mt-4">
      <input type="text" class="form-control" id="class-change-reason" maxlength="500">
      <label for="class-change-reason">Reason if changing the scheduled date or time</label>
    </div>
    <?php } ?>
  </div>

  <?php if (isset($class) && is_array($class)) { $_CORE->load("Classes"); $subs = $_CORE->Classes->getSubstitutes($class["class_id"]); if (!is_array($subs)) { $subs = []; } ?>
  <div class="fw-bold text-danger mb-2">SUBSTITUTE TEACHERS</div>
  <div class="bg-white border p-4 mb-4">
    <div class="mb-3">
      <label class="form-label" for="class_substitute_expiry">Access expires at</label>
      <input id="class_substitute_expiry" type="datetime-local" class="form-control mb-3"
             value="<?=htmlspecialchars(date("Y-m-d\TH:i", strtotime($class["class_date"]." +2 hours")), ENT_QUOTES, "UTF-8")?>" required>
      <label class="form-label" for="class_substitute_user">Add substitute teacher</label>
      <div class="input-group">
        <select id="class_substitute_user" class="form-select">
          <option value="">Select a teacher</option>
          <?php if (is_array($teachers)) { foreach ($teachers as $id=>$t) { if ((int)$id === (int)$class["user_id"]) continue; ?>
            <option value="<?=$id?>"><?=htmlspecialchars($t["user_name"], ENT_QUOTES, "UTF-8")?> (<?=htmlspecialchars($t["user_email"], ENT_QUOTES, "UTF-8")?>)</option>
          <?php }} ?>
        </select>
        <button type="button" class="btn btn-primary" onclick="classes.addSubstitute()">Add</button>
      </div>
    </div>
    <div id="class-substitute-list" class="d-grid gap-2">
      <?php if (count($subs)>0) { foreach ($subs as $sub) { ?>
        <div class="d-flex justify-content-between align-items-center border p-2">
          <span><?=htmlspecialchars($sub["user_name"], ENT_QUOTES, "UTF-8")?> (<?=htmlspecialchars($sub["user_email"], ENT_QUOTES, "UTF-8")?>) — access expires <?=htmlspecialchars($sub["expires_at"], ENT_QUOTES, "UTF-8")?></span>
          <button type="button" class="btn btn-sm btn-outline-danger" onclick="classes.removeSubstitute(<?=$sub["user_id"]?>)">Remove</button>
        </div>
      <?php } } else { ?>
        <small class="text-secondary">No substitute teachers assigned.</small>
      <?php } ?>
    </div>
  </div>
  <?php } ?>

  <button type="button" class="my-1 btn btn-danger d-flex-inline" onclick="cb.page(1)">
    <i class="ico-sm icon-undo2"></i> Back
  </button>
  <button type="submit" class="my-1 btn btn-primary d-flex-inline">
    <i class="ico-sm icon-checkmark"></i> Save
  </button>
</form>