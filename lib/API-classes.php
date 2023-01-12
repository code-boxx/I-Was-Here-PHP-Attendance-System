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

  // (C) SAVE CLASS
  case "save":
    $_CORE->autoAPI("Classes", "save");
    break;

  // (D) DELETE CLASS
  case "del":
    $_CORE->autoAPI("Classes", "del");
    break;

  // (E) IMPORT CLASS
  case "import":
    $_CORE->autoAPI("Classes", "import");
    break;
}