<?php
// (A) ADMIN ONLY
$_CORE->ucheck("A");

// (B) API ENDPOINTS
$_CORE->autoAPI([
  "save" => ["Classes", "save"],
  "del" => ["Classes", "del"],
  "import" => ["Classes", "import"]
]);

// (C) INVALID REQUEST
$_CORE->respond(0, "Invalid request", null, null, 400);