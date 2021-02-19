<?php  
// (A) GET COURSE
$_CORE->load("Course");
$course = $_CORE->Course->get($_POST['code']);
if (!is_array($course)) { exit("ERROR - INVALID COURSE"); }

// (B) STUDENTS LIST ?>
<h1>STUDENTS FOR <?=strtoupper($course['course_code'])?></h1>
<form class="bar" onsubmit="return stu.add();">
  <input type="button" value="Back" onclick="common.page('A')"/>
  <input type="email" id="stuadd" placeholder="Email" required/>
  <input type="submit" value="Add"/>
  <input type="button" value="Import" onclick="simp.show()"/>
</form>

<div id="stu-list"></div>