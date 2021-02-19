<?php
// (A) GET COURSE
$edit = isset($_POST['code']) && $_POST['code']!="";
if ($edit) {
  $_CORE->load("Course");
  $course = $_CORE->Course->get($_POST['code']);
  if (!is_array($course)) { exit("ERROR - COURSE NOT FOUND!"); }
}

// (B) COURSE FORM ?>
<h1><?=$edit?"EDIT":"ADD"?> COURSE</h1>
<form class="standard" onsubmit="return cor.save()">
  <input type="hidden" id="old_course_code" value="<?=$edit?$course['course_code']:""?>"/>
  <label for="course_role">Course Code</label>
  <input type="text" id="course_code" required value="<?=$edit?$course['course_code']:""?>"/>
  <label for="course_name">Course Name</label>
  <input type="text" id="course_name" required value="<?=$edit?$course['course_name']:""?>"/>
  <label for="course_desc">Course Description</label>
  <input type="text" id="course_desc" value="<?=$edit?$course['course_desc']:""?>"/>
  <input type="submit" value="Save"/>
  <input type="button" value="Back" onclick="common.page('A')"/>
</form>