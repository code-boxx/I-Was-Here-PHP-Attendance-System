<?php
// (A) GET CLASS
$edit = isset($_POST["id"]) && $_POST["id"]!="";
if ($edit) {
  $class = $_CORE->autoCall("Classes", "get");
}

// (B) GET COURSE & TEACHERS
$_CORE->load("Courses");
$course = $_CORE->Courses->get($_POST["cid"]);
$teachers = $_CORE->Courses->getTeachers($_POST["cid"]);
$error = $teachers===false || !is_array($teachers);

// (C) CLASS FORM ?>
<form class="col-md-6 offset-md-3 bg-light border p-4" onsubmit="return cc.save()">
  <h4 class="mb-4"><?=$edit?"EDIT":"ADD"?> CLASS</h4>
  <input type="hidden" id="class_id" value="<?=isset($class)?$class["class_id"]:""?>"/>

  <div class="mb-4">
    <label for="class_course" class="form-label">Course</label>
    <input type="text" class="form-control" id="class_course" value="[<?=$course["course_code"]?>] <?=$course["course_name"]?>" readonly/>
  </div>

  <div class="mb-4">
    <label for="class_teacher" class="form-label">Teacher-In-Charge</label>
    <select class="form-control" id="class_teacher"><?php
      if ($error) { echo "<option>ERROR - NO TEACHERS</option>"; }
      else { foreach ($teachers as $id=>$t) {
        printf("<option %svalue='%u'>%s</option>",
          $id==$class["user_id"] ? "selected " : "",
          $id, $t["user_name"]
        );
      }}
    ?></select>
  </div>

  <div class="mb-4">
    <label for="class_desc" class="form-label">Class Description (If Any)</label>
    <input type="text" class="form-control" id="class_desc" value="<?=isset($class)?$class["class_desc"]:""?>"/>
  </div>

  <div class="mb-4">
    <label for="class_date" class="form-label">Class Date Time</label>
    <input type="datetime-local" class="form-control" id="class_date" required
           min="<?=$course["course_start"]?>T00:00"
           max="<?=$course["course_end"]?>T23:59"
           value="<?=isset($class)?str_replace(" ", "T", $class["class_date"]):""?>"/>
  </div>

  <input type="button" class="col btn btn-danger btn-lg" value="Back" onclick="cb.page(2)"/>
  <input type="submit" class="col btn btn-primary btn-lg" value="Save"<?=$error?" disabled":""?>/>
</form>
