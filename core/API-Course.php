<?php
// (A) TEACHER REQUESTS
$_CORE->load("Course");
if (isset($_POST['reqT'])) { switch ($_POST['reqT']) {
  // (A0) INVALID
  default:
    $_CORE->respond(0, "Invalid request");
    break;

  // (A1) SAVE COURSE
  case "save":
    $_CORE->autoAPI("Course", "save");
    break;

  // (A2) DELETE COURSE
  case "del":
    $_CORE->autoAPI("Course", "del");
    break;
  
  // (A3) ADD STUDENT TO COURSE
  case "stuAdd":
    $_CORE->autoAPI("Course", "stuAdd");
    break;
  
  // (A4) DELETE STUDENT FROM COURSE
  case "stuDel":
    $_CORE->autoAPI("Course", "stuDel");
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