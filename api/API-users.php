<?php
// (A) ADMIN ONLY
if (!isset($_SESS["user"]) || $_SESS["user"]["user_role"]!="A") {
  $_CORE->respond("E", "No permission or session expired", null, null, 403);
}

switch ($_REQ) {
  // (B) INVALID REQUEST
  default:
    $_CORE->respond(0, "Invalid request", null, null, 400);
    break;

  // (C) GET USER
  case "get":
    $_CORE->autoGETAPI("Users", "get");
    break;

  // (D) GET OR SEARCH USERS
  case "getAll":
    $_CORE->autoGETAPI("Users", "getAll");
    break;

  // (E) SAVE USER
  case "save":
    $_CORE->autoAPI("Users", "save");
    break;

  // (F) IMPORT USER (ALWAYS OVERRIDE)
  case "saveO":
    $_CORE->autoAPI("Users", "saveO");
    break;

  // (G) "DELETE" USER
  case "del":
    $_CORE->autoAPI("Users", "del");
    break;
}
