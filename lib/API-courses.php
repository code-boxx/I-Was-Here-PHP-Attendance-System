<?php
// (A) ADMIN ONLY
$_CORE->ucheck("A");

// (B) API ENDPOINTS
$_CORE->autoAPI([
  "save" => ["Courses", "save"],
  "import" => ["Courses", "import"],
  "previewImport" => ["Courses", "previewImport"],
  "previewUserImport" => ["Courses", "previewUserImport"],
  "del" => ["Courses", "del"],
  "addUser" => ["Courses", "addUser"],
  "delUser" => ["Courses", "delUser"],
  "addUsers" => ["Courses", "addUsers"],
  "delUsers" => ["Courses", "delUsers"],
  "moveStudents" => ["Courses", "moveStudents"],
  "getCourseAudit" => ["Courses", "getCourseAudit"],
  "undoCourseChange" => ["Courses", "undoCourseChange"],
  "getRosterAudit" => ["Courses", "getRosterAudit"],
  "undoRosterChange" => ["Courses", "undoRosterChange"]
]);

// (C) INVALID REQUEST
$_CORE->respond(0, "Invalid request", null, null, 400);