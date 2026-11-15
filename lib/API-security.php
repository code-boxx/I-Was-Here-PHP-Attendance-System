<?php
$_CORE->ucheck("A");
$_CORE->autoAPI([
  "getEvents" => ["Security", "getEvents"]
]);
$_CORE->respond(0, "Invalid request", null, null, 400);
