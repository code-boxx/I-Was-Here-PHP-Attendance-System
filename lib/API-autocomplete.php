<?php
// (A) ADMIN & TEACHERS ONLY
if (!isset($_SESS["user"]) || ($_SESS["user"]["user_role"]!="A" && $_SESS["user"]["user_role"]!="T")) {
  $_CORE->respond(0, "No permission or session expired", null, null, 403);
}

switch ($_REQ) {
  // (B) INVALID REQUEST
  default:
    $_CORE->respond(0, "Invalid request", null, null, 400);
    break;

  // (C) SEARCH USER
  case "user":
    $_CORE->autoGETAPI("Users", "autocomplete");
    break;

  // (D) SEARCH COURSE
  case "course":
    $_CORE->autoGETAPI("Courses", "autocomplete");
    break;

  // (E) GET INFO ON COURSE + TEACHER
  case "icourse":
    $_CORE->load("Courses");
    $_CORE->respond(1, "OK", [
      "c" => $_CORE->Courses->get($_POST["id"]),
      "t" => $_CORE->Courses->getTeachers($_POST["id"])
    ]);
    break;
}