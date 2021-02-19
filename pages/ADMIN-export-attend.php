<?php
// (A) GET ATTENDANCE
$_CORE->load("Classes");
$attend = $_CORE->Classes->attendGet($_POST['id']);
if (!is_array($attend)) { exit("ERROR - INVALID CLASS"); }

// (B) OUTPUT TO CSV
header('Content-Type: text/csv');
header('Content-Disposition: attachment; filename="attendance.csv"');
foreach ($attend['all'] as $id=>$stu) {
  echo implode(",", [$stu["user_name"], $stu['user_email'], isset($stu['a'])?"Present":"Absent"]);
  echo "\r\n";
}