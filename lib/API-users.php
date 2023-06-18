<?php
// (A) ADMIN ONLY
$_CORE->ucheck("A");

// (B) API ENDPOINTS
$_CORE->autoAPI([
  "get" => ["Users", "get"],
  "getAll" => ["Users", "getAll"],
  "save" => ["Users", "save"],
  "import" => ["Users", "import"],
  "del" => ["Users", "del"]
]);

// (C) INVALID REQUEST
$_CORE->respond(0, "Invalid request", null, null, 400);