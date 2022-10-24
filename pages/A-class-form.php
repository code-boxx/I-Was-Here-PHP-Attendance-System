<?php
// (A) GET CLASS
$edit = isset($_POST["id"]) && $_POST["id"]!="";
if ($edit) {
  $class = $_CORE->autoCall("Classes", "get");
  $_CORE->load("Courses");
  $course = $_CORE->Courses->get($class["course_id"]);
  $teachers = $_CORE->Courses->getTeachers($class["course_id"]);
}

// (B) CLASS FORM ?>
<h3 class="mb-3"><?=$edit?"EDIT":"ADD"?> CLASS</h3>
<form onsubmit="return classes.save()">
  <input type="hidden" id="class_id" value="<?=isset($class)?$class["class_id"]:""?>">

  <div class="fw-bold text-danger">COURSE</div>
  <div class="bg-white border p-4 mb-3">
    <div class="d-flex">
      <div class="input-group-prepend">
        <span class="input-group-text mi">school</span>
      </div>
      <input type="text" class="form-control" id="class_course"
             <?=isset($class)?" readonly onclick='classes.toggle(false)'":""?>
             value="<?=isset($class)?"[{$class["course_code"]}] {$class["course_name"]}":""?>">
      <input type="hidden" id="class_course_id" value="<?=isset($class)?$class["course_id"]:""?>">
    </div>
    <small id="class_course_note" class="text-danger<?=isset($class)?"":" d-none"?>">
      * Click to change course.
    </small>
  </div>

  <div class="fw-bold text-danger">CLASS</div>
  <div class="bg-white border p-4 mb-3">
    <div class="input-group mb-3">
      <div class="input-group-prepend">
        <span class="input-group-text mi">today</span>
      </div>
      <input type="datetime-local" class="form-control" id="class_date" required<?=isset($class)?"":" disabled"?>
             value="<?=isset($class)?str_replace(" ", "T", $class["class_date"]):""?>"
             min="<?=isset($class)?$course["course_start"]." 00:00:00":""?>"
             max="<?=isset($class)?$course["course_end"]." 23:59:59":""?>">
    </div>

    <div class="input-group mb-3">
      <div class="input-group-prepend">
        <span class="input-group-text mi">person</span>
      </div>
      <select class="form-select" id="class_teacher"<?=isset($class)?"":" disabled"?>><?php
        if (isset($teachers) && is_array($teachers)) { foreach ($teachers as $id=>$t) {
          printf("<option %svalue='%u'>%s (%s)</option>",
            $id==$class["user_id"] ? "selected " : "",
            $id, $t["user_name"], $t["user_email"]
          );
        }} else { echo "<option value=''>No teachers assigned!</option>"; }
      ?></select>
    </div>

    <div class="input-group">
      <div class="input-group-prepend">
        <span class="input-group-text mi">description</span>
      </div>
      <input type="text" class="form-control" id="class_desc"<?=isset($class)?"":" disabled"?>
             value="<?=isset($class)?$class["class_desc"]:""?>" placeholder="Description, if any.">
    </div>
  </div>

  <input type="button" class="col btn btn-danger" value="Back" onclick="cb.page(0)">
  <input type="submit" class="col btn btn-primary" value="Save">
</form>