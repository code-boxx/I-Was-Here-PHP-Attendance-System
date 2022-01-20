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
<h3 class="mb-3"><?=$edit?"EDIT":"ADD"?> CLASS</h3>
<form onsubmit="return classes.save()">
  <input type="hidden" id="class_id" value="<?=isset($class)?$class["class_id"]:""?>"/>

  <div class="bg-white border p-4 mb-3">
    <div class="input-group mb-3">
      <div class="input-group-prepend">
        <span class="input-group-text mi">school</span>
      </div>
      <input type="text" class="form-control" id="class_course" value="[<?=$course["course_code"]?>] <?=$course["course_name"]?>" readonly/>
    </div>

    <div class="input-group mb-3">
      <div class="input-group-prepend">
        <span class="input-group-text mi">person</span>
      </div>
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

    <div class="input-group mb-3">
      <div class="input-group-prepend">
        <span class="input-group-text mi">description</span>
      </div>
      <input type="text" class="form-control" id="class_desc" value="<?=isset($class)?$class["class_desc"]:""?>" placeholder="Description, if any."/>
    </div>

    <div class="input-group">
      <div class="input-group-prepend">
        <span class="input-group-text mi">today</span>
      </div>
      <input type="datetime-local" class="form-control" id="class_date" required
      min="<?=$course["course_start"]?>T00:00"
      max="<?=$course["course_end"]?>T23:59"
      value="<?=isset($class)?str_replace(" ", "T", $class["class_date"]):""?>"/>
    </div>
  </div>

  <input type="button" class="col btn btn-danger btn-lg" value="Back" onclick="cb.page(2)"/>
  <input type="submit" class="col btn btn-primary btn-lg" value="Save"<?=$error?" disabled":""?>/>
</form>
