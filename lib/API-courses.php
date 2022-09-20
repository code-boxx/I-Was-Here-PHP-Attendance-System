<?php
// (A) ADMIN ONLY
if (!isset($_SESS["user"]) || $_SESS["user"]["user_role"]!="A") {
  $_CORE->respond(0, "No permission or session expired", null, null, 403);
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

  // (D) IMPORT COURSE (OVERRIDES OLD ENTRY)
  case "import":
    $_CORE->autoAPI("Courses", "import");
    break;

  // (E) DELETE COURSE
  case "del":
    $_CORE->autoAPI("Courses", "del");
    break;

  // (F) ADD USER TO COURSE
  case "addUser":
    $_CORE->autoAPI("Courses", "addUser");
    break;

  // (G) REMOVE USER FROM COURSE
  case "delUser":
    $_CORE->autoAPI("Courses", "delUser");
    break;
}