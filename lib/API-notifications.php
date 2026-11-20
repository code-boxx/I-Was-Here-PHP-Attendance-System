<?php
$_CORE->ucheck(["A", "T", "U"]);
$_CORE->autoAPI([
  "getPreferences" => ["Notifications", "getPreferences"],
  "savePreferences" => ["Notifications", "savePreferences"]
]);
$_CORE->respond(0, "Invalid request", null, null, 400);
