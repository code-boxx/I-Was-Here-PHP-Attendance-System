<?php
// (A) STUDENTS ONLY - ATTENDANCE VIA QR CODE
if ($_CORE->Route->act == "attendQR") {
  $_CORE->ucheck("S");
  $_POST["uid"] = $_SESSION["user"]["user_id"];
  $_CORE->autoAPI("Classes", "attendQR");
}

// (B) API ENDPOINTS - ADMIN & TEACHERS ONLY
$_CORE->ucheck(["A", "T"]);
$_CORE->autoAPI([
  "attend" => ["Classes", "attend"],
  "absent" => ["Classes", "absent"],
  "save" => ["Classes", "attendance"]
]);

// (C) INVALID REQUEST
$_CORE->respond(0, "Invalid request", null, null, 400);