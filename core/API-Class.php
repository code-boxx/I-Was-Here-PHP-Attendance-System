<?php
// (A) TEACHER REQUESTS
$_CORE->load("Classes");
if (isset($_POST['reqT'])) { switch ($_POST['reqT']) {
  // (A0) INVALID
  default:
    $_CORE->respond(0, "Invalid request");
    break;

  // (A1) SAVE CLASS
  case "save":
    $_CORE->autoAPI("Classes", "save");
    break;
  
  // (A2) DELETE CLASS
  case "del":
    $_CORE->autoAPI("Classes", "del");
    break;
  
  // (A3) UPDATE ATTENDANCE
  case "attend":
    $_CORE->autoAPI("Classes", "attendSave");
    break;
}}

// (B) STUDENT REQUESTS
if (isset($_POST['reqS'])) { switch ($_POST['reqS']) {
  // (B0) INVALID
  default:
    $_CORE->respond(0, "Invalid request");
    break;
}}

// (C) OPEN REQUESTS
if (isset($_POST['req'])) { switch ($_POST['req']) {
  // (C0) INVALID
  default:
    $_CORE->respond(0, "Invalid request");
    break;
}}