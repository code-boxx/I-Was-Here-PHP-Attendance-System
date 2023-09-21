<?php
// (A) API ENDPOINTS
$_CORE->autoAPI([
  "add" => ["NFCIN", "add", "A"],
  "del" => ["NFCIN", "del", "A"],
  "login" => ["NFCIN", "login"]
]);

// (B) INVALID REQUEST
$_CORE->respond(0, "Invalid request", null, null, 400);