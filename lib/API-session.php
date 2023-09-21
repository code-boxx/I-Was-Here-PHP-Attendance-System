<?php
// (A) API ENDPOINTS
$_CORE->autoAPI([
  "login" => ["Users", "login"],
  "logout" => ["Users", "logout"],
  "update" => ["Users", "update"],
  "forgotA" => ["Forgot", "request"],
  "forgotB" => ["Forgot", "reset"]
]);

// (B) INVALID REQUEST
$_CORE->respond(0, "Invalid request", null, null, 400);