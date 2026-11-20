<?php
$_CORE->ucheck("A");
$_CORE->autoAPI([
  "getHolidays" => ["Calendar", "getHolidays"],
  "saveHoliday" => ["Calendar", "saveHoliday"],
  "deleteHoliday" => ["Calendar", "deleteHoliday"],
  "getAudit" => ["Calendar", "getAudit"]
]);
$_CORE->respond(0, "Invalid request", null, null, 400);
