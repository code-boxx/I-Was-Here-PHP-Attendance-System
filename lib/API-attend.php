<?php
// (A) API ENDPOINTS
$_CORE->autoAPI([
  "save" => ["Attend", "save", ["A", "T"]],
  "attendQR" => ["Attend", "attendQR", "U"],
  "openSession" => ["Attend", "openSession", ["A", "T"]],
  "closeSession" => ["Attend", "closeSession", ["A", "T"]],
  "sessionState" => ["Attend", "sessionState", ["A", "T"]],
  "qrToken" => ["Attend", "qrToken", ["A", "T"]],
  "studentSummary" => ["Attend", "getStudentSummary", "U"],
  "requestCorrection" => ["Attend", "requestCorrection", "U"],
  "getCorrections" => ["Attend", "getCorrections"],
  "decideCorrection" => ["Attend", "decideCorrection", ["A", "T"]],
  "getAudit" => ["Attend", "getAudit", ["A", "T"]]
]);

// (B) INVALID REQUEST
$_CORE->respond(0, "Invalid request", null, null, 400);