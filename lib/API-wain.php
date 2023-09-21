<?php
// (A) API ENDPOINTS
$_CORE->autoAPI([
  "regA" => ["WAIN", "regA", true],
  "regB" => ["WAIN", "regB", true],
  "unreg" => ["WAIN", "unreg", true],
  "loginA" => ["WAIN", "loginA"],
  "loginB" => ["WAIN", "loginB"]
]);

// (B) INVALID REQUEST
$_CORE->respond(0, "Invalid request", null, null, 400);