<?php
// (A) MUST BE SIGNED IN
if ($_USER===false || $_USER["user_role"]!="A") {
  $_CORE->respond("E", "No permission or session expired", null, null, 403);
}

switch ($_REQ) {
  // (B) INVALID REQUEST
  default:
    $_CORE->respond(0, "Invalid request", null, null, 400);
    break;

  // (C) SAVE COURSE
  case "save":
    $_CORE->autoAPI("Courses", "save");
    break;

  // (D) DELETE COURSE
  case "del":
    $_CORE->autoAPI("Courses", "del");
    break;

  // (E) ADD USER TO COURSE
  case "addUser":
    $_CORE->autoAPI("Courses", "addUser");
    break;

  // (F) REMOVE USER FROM COURSE
  case "delUser":
    $_CORE->autoAPI("Courses", "delUser");
    break;
}
