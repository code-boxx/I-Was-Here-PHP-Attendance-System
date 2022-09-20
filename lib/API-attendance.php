<?php
// (A) CHECKS
// (A1) ADMIN & TEACHERS ONLY
function checkAT () {
  global $_SESS; global $_CORE;
  if (!isset($_SESS["user"]) || ($_SESS["user"]["user_role"]!="A" && $_SESS["user"]["user_role"]!="T")) {
    $_CORE->respond(0, "No permission or session expired", null, null, 403);
  }
}

// (A2) STUDENT ONLY
function checkS () {
  global $_SESS; global $_CORE;
  if (!isset($_SESS["user"]) || $_SESS["user"]["user_role"]!="S") {
    $_CORE->respond(0, "No permission or session expired", null, null, 403);
  }
}

switch ($_REQ) {
  // (B) INVALID REQUEST
  default:
    $_CORE->respond(0, "Invalid request", null, null, 400);
    break;

  // (C) SET ATTENDANCE (SINGLE)
  case "attend":
    checkAT();
    $_CORE->autoAPI("Classes", "attend");
    break;

  // (D) REMOVE ATTENDANCE (SINGLE)
  case "absent":
    checkAT();
    $_CORE->autoAPI("Classes", "absent");
    break;

  // (E) SAVE ATTENDANCE (FOR CLASS)
  case "save":
    checkAT();
    $_CORE->autoAPI("Classes", "attendance");
    break;

  // (F) ATTENDANCE VIA QR CODE
  case "attendQR":
    checkS();
    $_POST["uid"] = $_SESS["user"]["user_id"];
    $_CORE->autoAPI("Classes", "attendQR");
    break;
}