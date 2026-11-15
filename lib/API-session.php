<?php
// (A) API ENDPOINTS
$_CORE->autoAPI([
  "login" => ["Users", "login"],
  "mfaVerify" => ["Users", "mfaVerify"],
  "mfaStart" => ["Users", "mfaStart"],
  "mfaEnable" => ["Users", "mfaEnable"],
  "mfaDisable" => ["Users", "mfaDisable"],
  "logout" => ["Users", "logout"],
  "getActiveSessions" => ["Session", "getActiveSessions"],
  "revokeSession" => ["Session", "revokeSession"],
  "update" => ["Users", "update"],
  "forgotA" => ["Forgot", "request"],
  "forgotB" => ["Forgot", "reset"]
]);

// (B) INVALID REQUEST
$_CORE->respond(0, "Invalid request", null, null, 400);