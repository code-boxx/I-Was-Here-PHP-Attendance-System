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

  // (C) SAVE USER
  case "save":
    $_CORE->autoAPI("Users", "save");
    break;

  // (D) "FORCE SAVE" USER
  case "saveO":
    $_CORE->autoAPI("Users", "saveO");
    break;

  // (E) DELETE USER
  case "del":
    $_CORE->autoAPI("Users", "del");
    break;
}
