<?php
// (A) ADMIN ONLY
$_CORE->ucheck("A");

// (B) API ENDPOINTS
$_CORE->autoAPI([
  "user" => ["Autocomplete", "user"],
  "userEmail" => ["Autocomplete", "userEmail"],
  "course" => ["Autocomplete", "course"],
  "icourse" => ["Autocomplete", "icourse"]
]);

// (C) INVALID REQUEST
$_CORE->respond(0, "Invalid request", null, null, 400);