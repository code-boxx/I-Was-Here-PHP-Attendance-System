<?php
// (A) GET COURSE
$edit = isset($_POST["id"]) && $_POST["id"]!="";
if ($edit) {
  $course = $_CORE->autoCall("Courses", "get");
}
// (B) COURSE FORM ?>
<form class="col-md-6 offset-md-3 bg-light border p-4" onsubmit="return cs.save()">
  <h4 class="mb-4"><?=$edit?"EDIT":"ADD"?> COURSE</h4>
  <input type="hidden" id="course_id" value="<?=isset($course)?$course["course_id"]:""?>"/>

  <div class="mb-4">
    <label for="course_code" class="form-label">Course Code</label>
    <input type="text" class="form-control" id="course_code" required value="<?=isset($course)?$course["course_code"]:""?>"/>
  </div>

  <div class="mb-4">
    <label for="course_name" class="form-label">Course Name</label>
    <input type="text" class="form-control" id="course_name" required value="<?=isset($course)?$course["course_name"]:""?>"/>
  </div>

  <div class="mb-4">
    <label for="course_desc" class="form-label">Course Description</label>
    <input type="text" class="form-control" id="course_desc" value="<?=isset($course)?$course["course_desc"]:""?>"/>
  </div>

  <div class="mb-4">
    <label for="course_start" class="form-label">Course Start</label>
    <input type="date" class="form-control" id="course_start" required value="<?=isset($course)?$course["course_start"]:""?>"/>
  </div>

  <div class="mb-4">
    <label for="course_end" class="form-label">Course End</label>
    <input type="date" class="form-control" id="course_end" required value="<?=isset($course)?$course["course_end"]:""?>"/>
  </div>

  <input type="button" class="col btn btn-danger btn-lg" value="Back" onclick="cb.page(1)"/>
  <input type="submit" class="col btn btn-primary btn-lg" value="Save"/>
</form>
