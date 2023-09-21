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
             min="<?=isset($class)?$course["course_start"]." 00:00:00":""?>"
             max="<?=isset($class)?$course["course_end"]." 23:59:59":""?>">
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
  </div>

  <button type="button" class="my-1 btn btn-danger d-flex-inline" onclick="cb.page(1)">
    <i class="ico-sm icon-undo2"></i> Back
  </button>
  <button type="submit" class="my-1 btn btn-primary d-flex-inline">
    <i class="ico-sm icon-checkmark"></i> Save
  </button>
</form>