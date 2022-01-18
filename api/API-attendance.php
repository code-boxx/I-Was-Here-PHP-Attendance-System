<?php
// (A) ADMIN & TEACHERS ONLY
if (!isset($_SESS["user"]) || ($_SESS["user"]["user_role"]!="A" && $_SESS["user"]["user_role"]!="T")) {
  $_CORE->respond("E", "No permission or session expired", null, null, 403);
}

switch ($_REQ) {
  // (B) INVALID REQUEST
  default:
    $_CORE->respond(0, "Invalid request", null, null, 400);
    break;

  // (C) SET ATTENDANCE (SINGLE)
  case "attend":
    $_CORE->autoAPI("Classes", "attend");
    break;

  // (D) REMOVE ATTENDANCE (SINGLE)
  case "absent":
    $_CORE->autoAPI("Classes", "absent");
    break;

  // (E) SAVE ATTENDANCE (FOR CLASS)
  case "save":
    $_CORE->autoAPI("Classes", "attendance");
    break;

  // (F) SEARCH USER - FOR AUTOCOMPLETE
  case "search":
    $_CORE->autoGETAPI("Users", "search");
    break;
}
