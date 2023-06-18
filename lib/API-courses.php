<?php
// (A) ADMIN ONLY
$_CORE->ucheck("A");

// (B) API ENDPOINTS
$_CORE->autoAPI([
  "save" => ["Courses", "save"],
  "import" => ["Courses", "import"],
  "del" => ["Courses", "del"],
  "addUser" => ["Courses", "addUser"],
  "delUser" => ["Courses", "delUser"]
]);

// (C) INVALID REQUEST
$_CORE->respond(0, "Invalid request", null, null, 400);