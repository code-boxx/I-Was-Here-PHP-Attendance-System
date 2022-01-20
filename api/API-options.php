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

  // (C) SAVE SETTINGS
  case "save":
    $_POST["options"] = json_decode($_POST["options"], 1);
    $_CORE->autoAPI("Options", "save");
    break;
}
