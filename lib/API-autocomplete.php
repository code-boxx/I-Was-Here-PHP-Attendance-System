<?php
// (A) ADMIN & TEACHERS ONLY
$_CORE->ucheck(["A", "T"]);

// (B) API ENDPOINTS
$_CORE->autoAPI([
  "user" => ["Users", "autocomplete"],
  "course" => ["Courses", "autocomplete"]
]);
if ($_CORE->Route->act == "icourse") {
  $_CORE->load("Courses");
  $_CORE->respond(1, "OK", [
    "c" => $_CORE->Courses->get($_POST["id"]),
    "t" => $_CORE->Courses->getTeachers($_POST["id"])
  ]);
}

// (C) INVALID REQUEST
$_CORE->respond(0, "Invalid request", null, null, 400);