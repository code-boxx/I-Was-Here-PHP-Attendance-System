<?php
if (PHP_SAPI !== "cli") {
  exit("Run this integration test from the command line.\n");
}

require dirname(__DIR__) . "/lib/CORE-Config.php";
require PATH_LIB . "LIB-Core.php";
$_COOKIE = [];
$_SESSION = [];
$_SERVER["REMOTE_ADDR"] = "203.0.113.17";
$_SERVER["SERVER_SOFTWARE"] = "PHP CLI";

$_CORE->load("DB");
$_CORE->load("Settings");
$_CORE->load("Install");
$_CORE->Install->C();
$_CORE->load("Session");
$db = $_CORE->DB;
$db->start();

function check ($condition, $message) {
  if (!$condition) { throw new RuntimeException($message); }
}

try {
  check((int)$db->fetchCol(
    "SELECT `setting_value` FROM `settings` WHERE `setting_name`='APP_VER'"
  )===4, "Phase 2 database migration was not applied.");
  check((int)$db->fetchCol(
    "SELECT COUNT(*) FROM information_schema.tables
     WHERE table_schema=DATABASE()
     AND table_name IN ('attendance_sessions','attendance_corrections','attendance_audit')"
  )===3, "Phase 2 attendance tables are missing.");

  $suffix = bin2hex(random_bytes(6));
  $email = "integration-$suffix@example.test";
  $teacherEmail = "teacher-$suffix@example.test";
  $otherTeacherEmail = "other-teacher-$suffix@example.test";
  $otherStudentEmail = "other-student-$suffix@example.test";
  $courseCode = "TEST-$suffix";
  $password = "Integration123";

  $_CORE->load("Users");
  check($_CORE->Users->save("=1+1", $email, $password, "U"), "Could not create test student.");
  $userId = $db->lastID;
  check($_CORE->Users->save("Integration Teacher", $teacherEmail, $password, "T"), "Could not create test teacher.");
  $teacherId = $db->lastID;
  check($_CORE->Users->save("Other Teacher", $otherTeacherEmail, $password, "T"), "Could not create second test teacher.");
  $otherTeacherId = $db->lastID;
  check($_CORE->Users->save("Other Student", $otherStudentEmail, $password, "U"), "Could not create second test student.");
  $otherStudentId = $db->lastID;
  $importEmail = "import-$suffix@example.test";
  check($_CORE->Users->import("Imported Teacher", $importEmail, $password, "T"), "Valid teacher CSV row was rejected.");
  check(!$_CORE->Users->import("Duplicate Teacher", $importEmail, $password, "T"), "Duplicate user CSV row was accepted.");
  check(!$_CORE->Users->import("Invalid Role", "invalid-role-$suffix@example.test", $password, "Teacher"), "Invalid user level was accepted.");
  check(!$_CORE->Users->import("Invalid Email", "invalid-email-$suffix", $password, "U"), "Invalid email was accepted.");

  $_CORE->load("Courses");
  $start = date("Y-m-d");
  $end = date("Y-m-d", strtotime("+7 days"));
  check(!$_CORE->Courses->import("BAD-$suffix", "Invalid dates", $end, $start), "Invalid course dates were accepted.");
  check($_CORE->Courses->save($courseCode, "Integration course", $start, $end), "Could not create test course.");
  check($_CORE->Courses->addUser($courseCode, $userId), "Could not enroll test student.");
  check($_CORE->Courses->addUser($courseCode, $teacherId), "Could not enroll test teacher.");

  $_CORE->load("Classes");
  check($_CORE->Classes->save($courseCode, $teacherId, "$start 09:00:00", "Integration session"), "Could not create test class.");
  $classId = $db->lastID;

  $_CORE->load("Report");
  ob_start();
  $_CORE->Report->attend($courseCode);
  $csv = ob_get_clean();
  check(strpos($csv, $email) !== false, "Report omitted an enrolled student with no attendance.");
  check(strpos($csv, "'=1+1") !== false, "Report did not neutralize a spreadsheet formula.");
  check(strpos($csv, "0%") !== false, "Report omitted the zero attendance percentage.");

  $_CORE->load("Attend");
  $_SESSION["user"] = ["user_id" => $teacherId, "user_level" => "T"];
  check($_CORE->Attend->save($classId, json_encode([$userId => ["s" => "0", "n" => ""]], JSON_THROW_ON_ERROR)), "Authorized teacher could not save attendance.");
  check(!$_CORE->Attend->save($classId, json_encode([$userId => ["s" => "1", "n" => ""]], JSON_THROW_ON_ERROR)), "Manual attendance correction without a reason was accepted.");
  check((int)$db->fetchCol("SELECT `a_status` FROM `attendance` WHERE `class_id`=? AND `user_id`=?", [$classId, $userId])===0, "Rejected manual correction changed attendance.");
  check($_CORE->Attend->save($classId, json_encode([$userId => ["s" => "1", "n" => "Teacher verified arrival."]], JSON_THROW_ON_ERROR)), "Manual correction with a reason failed.");
  check($_CORE->Attend->save($classId, json_encode([$userId => ["s" => "0", "n" => "Teacher corrected the record."]], JSON_THROW_ON_ERROR)), "Manual correction could not restore an absent status.");
  check(!$_CORE->Attend->save($classId, json_encode([$otherStudentId => ["s" => "1", "n" => ""]], JSON_THROW_ON_ERROR)), "Teacher marked a student not enrolled in the class.");

  $_SESSION["user"] = ["user_id" => $otherTeacherId, "user_level" => "T"];
  check(!$_CORE->Attend->save($classId, json_encode([$userId => ["s" => "1", "n" => ""]], JSON_THROW_ON_ERROR)), "Teacher changed attendance for another teacher's class.");
  check($_CORE->Classes->get($classId)===null, "Teacher viewed another teacher's class details.");

  $_SESSION["user"] = ["user_id" => $teacherId, "user_level" => "T"];
  $sessionId = $_CORE->Attend->openSession($classId);
  check(is_int($sessionId), "Authorized teacher could not open an attendance session.");
  check($_CORE->Attend->sessionState($classId)["session_status"]=="open", "Open session state was not reported.");
  check(!$_CORE->Attend->openSession($classId), "A second active attendance session was allowed.");
  $qr = $_CORE->Attend->qrToken($classId);
  check(is_array($qr) && isset($qr["token"], $qr["expires"]), "Could not issue a rotating QR token.");
  $expiredPayload = rtrim(strtr(base64_encode(json_encode([
    "s" => $sessionId, "c" => (int)$classId, "o" => $courseCode, "e" => time()-1
  ], JSON_THROW_ON_ERROR)), "+/", "-_"), "=");
  $expiredQr = $expiredPayload . "." . hash_hmac("sha256", $expiredPayload, JWT_SECRET);
  $wrongClassPayload = rtrim(strtr(base64_encode(json_encode([
    "s" => $sessionId, "c" => (int)$classId + 1, "o" => $courseCode, "e" => time()+20
  ], JSON_THROW_ON_ERROR)), "+/", "-_"), "=");
  $wrongClassQr = $wrongClassPayload . "." . hash_hmac("sha256", $wrongClassPayload, JWT_SECRET);
  $tamperedQr = $qr["token"] . "x";
  $_SESSION["user"] = ["user_id" => $otherTeacherId, "user_level" => "T"];
  check(!$_CORE->Attend->qrToken($classId), "Another teacher received a class QR token.");
  $_SESSION["user"] = ["user_id" => $userId, "user_level" => "U"];
  check(!$_CORE->Attend->attendQR($expiredQr), "Expired QR token was accepted.");
  check(!$_CORE->Attend->attendQR($wrongClassQr), "QR token was accepted for a different class.");
  check(!$_CORE->Attend->attendQR($tamperedQr), "Tampered QR token was accepted.");
  check($_CORE->Attend->attendQR($qr["token"]), "Enrolled student QR check-in failed.");
  check(!$_CORE->Attend->attendQR($qr["token"]), "Duplicate QR check-in was accepted.");
  $_SESSION["user"] = ["user_id" => $otherStudentId, "user_level" => "U"];
  $otherRequests = $_CORE->Attend->getCorrections();
  check($otherRequests===null || count($otherRequests)==0, "Student could view another student's correction requests.");
  check(!$_CORE->Attend->attendQR($qr["token"]), "Student outside the course checked in by QR.");
  check(!$_CORE->Attend->requestCorrection($classId, "1", "Unenrolled request."), "Unenrolled student submitted a correction.");
  $_SESSION["user"] = ["user_id" => $userId, "user_level" => "U"];
  check($_CORE->Attend->requestCorrection($classId, "0", "I was recorded present by mistake."), "Enrolled student correction request failed.");
  check(!$_CORE->Attend->requestCorrection($classId, "1", "Second pending request."), "Duplicate pending correction request was accepted.");
  $studentRequests = $_CORE->Attend->getCorrections();
  check(count($studentRequests)==1 && $studentRequests[0]["request_status"]=="pending", "Student correction history was not private and complete.");

  $_SESSION["user"] = ["user_id" => $otherTeacherId, "user_level" => "T"];
  $otherTeacherRequests = $_CORE->Attend->getCorrections();
  check($otherTeacherRequests===null || count($otherTeacherRequests)==0, "Teacher could view requests from another teacher's class.");
  check(!$_CORE->Attend->decideCorrection($studentRequests[0]["request_id"], "approved", "Unauthorized teacher."), "Unassigned teacher decided a correction.");
  $_SESSION["user"] = ["user_id" => $teacherId, "user_level" => "T"];
  check($_CORE->Attend->decideCorrection($studentRequests[0]["request_id"], "approved", "Verified against class records."), "Assigned teacher could not approve a correction.");
  check((int)$db->fetchCol("SELECT `a_status` FROM `attendance` WHERE `class_id`=? AND `user_id`=?", [$classId, $userId])===0, "Approved correction did not update attendance.");
  $audit = $_CORE->Attend->getAudit($classId);
  check(is_array($audit) && count($audit)>=5, "Attendance audit history omitted changes.");
  $_SESSION["user"] = ["user_id" => $otherTeacherId, "user_level" => "T"];
  check($_CORE->Attend->getAudit($classId)===false, "Unassigned teacher viewed class audit history.");

  $_SESSION["user"] = ["user_id" => $teacherId, "user_level" => "T"];
  check($_CORE->Attend->closeSession($classId), "Teacher could not close an attendance session.");
  check(!$_CORE->Attend->attendQR($qr["token"]), "A QR token remained valid after session closure.");
  check($_CORE->Attend->sessionState($classId)["session_status"]=="closed", "Closed session state was not reported.");
  $_SESSION["user"] = ["user_id" => $userId, "user_level" => "U"];
  $summary = $_CORE->Attend->getStudentSummary();
  check((int)$summary[$courseCode]["total_classes"]==1 && (int)$summary[$courseCode]["present_classes"]==0, "Student course summary did not reflect corrected attendance.");
  check(is_array($_CORE->Classes->getByStudent($userId)), "Student class portal query failed.");
  $_CORE->Classes->del($classId);
  foreach (["attendance_sessions", "attendance_corrections", "attendance_audit"] as $table) {
    check((int)$db->fetchCol("SELECT COUNT(*) FROM `$table` WHERE `class_id`=?", [$classId])===0, "Deleting a class left orphaned $table data.");
  }
  $_CORE->Courses->del($courseCode);

  $_SESSION = [];
  require PATH_LIB . "jwt/autoload.php";
  $teacherVersion = (int)$db->fetchCol("SELECT `session_version` FROM `users` WHERE `user_id`=?", [$teacherId]);
  $now = time();
  $sessionToken = Firebase\JWT\JWT::encode([
    "iat" => $now, "nbf" => $now, "ver" => $teacherVersion,
    "iss" => JWT_ISSUER, "aud" => HOST_NAME,
    "data" => ["user" => ["user_id" => $teacherId]]
  ], JWT_SECRET, JWT_ALGO);
  $_COOKIE["cbsess"] = $sessionToken;
  new Session($_CORE);
  check(isset($_SESSION["user"]), "Current session token was rejected.");
  check($_CORE->Users->save("Updated Teacher", $teacherEmail, $password, "T", $teacherId), "Could not update test user.");
  $_COOKIE["cbsess"] = $sessionToken;
  $revoked = false;
  try { new Session($_CORE); }
  catch (Exception $ex) { $revoked = true; }
  check($revoked, "Updating an account did not revoke existing session tokens.");

  $_SESSION = [];
  unset($_COOKIE["cbsess"]);
  $_SERVER["REMOTE_ADDR"] = "203.0.113.17";
  for ($i=0; $i<5; $i++) {
    check(!$_CORE->Users->login($email, "WrongPassword1"), "Incorrect password unexpectedly succeeded.");
  }
  $key = $_CORE->Users->loginKey($email);
  check($_CORE->Users->loginLocked($key), "Repeated failures did not lock sign-in.");
  check(!$_CORE->Users->login($email, $password), "Locked sign-in accepted the correct password.");

  $_SERVER["REMOTE_ADDR"] = "203.0.113.18";
  check($_CORE->Users->login($email, $password), "Valid sign-in failed from an unblocked address.");
  $_CORE->Session->destroy();

  $now = time();
  $_COOKIE["cbsess"] = Firebase\JWT\JWT::encode([
    "iat" => $now - 120, "nbf" => $now - 120, "exp" => $now - 60,
    "ver" => 0, "iss" => JWT_ISSUER, "aud" => HOST_NAME, "data" => []
  ], JWT_SECRET, JWT_ALGO);
  $expiredRejected = false;
  try { new Session($_CORE); }
  catch (Exception $ex) { $expiredRejected = true; }
  check($expiredRejected, "Expired session token was accepted.");

  $db->end(false);
  echo "Integration tests passed: Phase 2 migration, QR sessions and expiry, correction workflow, audit history, student summaries, attendance authorization, session revocation, reports, login throttling, and session expiry.\n";
} catch (Throwable $ex) {
  if ($db->pdo->inTransaction()) { $db->end(false); }
  fwrite(STDERR, "Integration test failed: " . $ex->getMessage() . "\n");
  exit(1);
}
