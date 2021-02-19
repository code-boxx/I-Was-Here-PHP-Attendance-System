<?php
// (A) GET COURSES, TEACHERS, CLASS
$_CORE->load("Course");
$_CORE->load("User");
$courses = $_CORE->Course->getAll("", false);
$teachers = $_CORE->User->getAll("", "T", false);
$edit = is_numeric($_POST['id']);
if ($edit) {
  $_CORE->load("Classes");
  $class = $_CORE->Classes->get($_POST['id']);
  if (!is_array($class)) { exit("ERROR - INVALID CLASS"); }
}
$date = $edit ? substr($class['class_date'], 0, 10) : date("Y-m-d") ;
$time = $edit ? date("h:i A", strtotime($class['class_date'])) : date("h:i A") ;

// (B) CLASS FORM ?>
<h1><?=$edit?"EDIT":"ADD"?> CLASS</h1>
<form class="standard" onsubmit="return cla.save()">
  <input type="hidden" id="class_id" value="<?=$edit?$class['class_id']:""?>"/>
  <label for="course_code">Course Code</label>
  <select id="course_code"><?php
    foreach ($courses as $code=>$c) {
      printf("<option value='%s'%s>%s</option>", 
        $c['course_code'], $c['course_code']==$class['course_code']?" selected='selected'":"", $c['course_name']
      );
    }
  ?></select>
  <label for="class_date">Date</label>
  <input type="text" id="class_date" required value="<?=$date?>"/>
  <label for="class_time">Time</label>
  <input type="text" id="class_time" required value="<?=$time?>"/>
  <label for="class_teacher">Teacher-in-charge</label>
  <select id="course_teacher"><?php
    foreach ($teachers as $id=>$t) {
      printf("<option value='%u'%s>%s</option>",
        $id, $id==$class['user_id']?" selected='selected'":"", $t['user_name']
      );
    }
  ?></select>
  <label for="class_desc">Description</label>
  <input type="text" id="class_desc" value="<?=$edit?$class['class_desc']:""?>"/>
  <input type="submit" value="Save">
  <input type="button" value="Back" onclick="common.page('A')"/>
</form>