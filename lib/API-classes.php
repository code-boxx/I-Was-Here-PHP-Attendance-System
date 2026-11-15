<?php
// (B) API ENDPOINTS
$_CORE->autoAPI([
  "save" => ["Classes", "save", "A"],
  "del" => ["Classes", "del", "A"],
  "import" => ["Classes", "import", "A"],
  "previewImport" => ["Classes", "previewImport", "A"],
  "getSubstitutes" => ["Classes", "getSubstitutes", ["A", "T"]],
  "addSubstitute" => ["Classes", "addSubstitute", ["A", "T"]],
  "delSubstitute" => ["Classes", "delSubstitute", ["A", "T"]],
  "cancelClass" => ["Classes", "cancelClass", ["A", "T"]],
  "rescheduleClass" => ["Classes", "rescheduleClass", ["A", "T"]],
  "getScheduleAudit" => ["Classes", "getScheduleAudit", ["A", "T"]],
  "undoScheduleChange" => ["Classes", "undoScheduleChange", ["A", "T"]]
]);

// (C) INVALID REQUEST
$_CORE->respond(0, "Invalid request", null, null, 400);