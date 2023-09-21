<?php
// (A) GET COURSE
$edit = isset($_POST["code"]) && $_POST["code"]!="";
if ($edit) { $course = $_CORE->autoCall("Courses", "get"); }

// (B) COURSE FORM ?>
<h3 class="mb-3"><?=$edit?"EDIT":"ADD"?> COURSE</h3>
<form onsubmit="return course.save()">
  <div class="fw-bold text-danger mb-2">BASIC COURSE INFORMATION</div>
  <div class="bg-white border p-4 mb-4">
    <div class="form-floating mb-4">
      <input type="text" class="form-control" id="course_code" required value="<?=isset($course)?$course["course_code"]:""?>">
      <input type="hidden" id="course_ocode" value="<?=isset($course)?$course["course_code"]:""?>">
      <label>Course Code</label>
    </div>

    <div class="form-floating mb-4">
      <input type="text" class="form-control" id="course_name" required value="<?=isset($course)?$course["course_name"]:""?>">
      <label>Course Name</label>
    </div>

    <div class="form-floating">
      <input type="text" class="form-control" id="course_desc" value="<?=isset($course)?$course["course_desc"]:""?>">
      <label>Course Description (if any)</label>
    </div>
  </div>

  <div class="fw-bold text-danger mb-2">START-END DATE</div>
  <div class="bg-white border p-4 mb-4">
    <div class="form-floating mb-4">
      <input type="date" class="form-control" id="course_start" required value="<?=isset($course)?$course["course_start"]:""?>">
      <label>Start Date</label>
    </div>

    <div class="form-floating">
      <input type="date" class="form-control" id="course_end" required value="<?=isset($course)?$course["course_end"]:""?>">
      <label>End Date</label>
    </div>
  </div>

  <button type="button" class="my-1 btn btn-danger d-flex-inline" onclick="cb.page(1)">
    <i class="ico-sm icon-undo2"></i> Back
  </button>
  <button type="submit" class="my-1 btn btn-primary d-flex-inline">
    <i class="ico-sm icon-checkmark"></i> Save
  </button>
</form>