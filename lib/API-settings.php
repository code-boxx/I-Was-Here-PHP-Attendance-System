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

  // (C) SAVE SETTINGS
  case "save":
    $_POST["settings"] = json_decode($_POST["settings"], 1);
    $_CORE->autoAPI("Settings", "save");
    break;
}