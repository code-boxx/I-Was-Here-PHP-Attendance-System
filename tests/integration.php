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
  check(!$_CORE->Attend->save($classId, json_encode([$otherStudentId => ["s" => "1", "n" => ""]], JSON_THROW_ON_ERROR)), "Teacher marked a student not enrolled in the class.");

  $_SESSION["user"] = ["user_id" => $otherTeacherId, "user_level" => "T"];
  check(!$_CORE->Attend->save($classId, json_encode([$userId => ["s" => "1", "n" => ""]], JSON_THROW_ON_ERROR)), "Teacher changed attendance for another teacher's class.");
  check($_CORE->Classes->get($classId)===null, "Teacher viewed another teacher's class details.");

  $classHash = $db->fetchCol("SELECT `class_hash` FROM `classes` WHERE `class_id`=?", [$classId]);
  $_SESSION["user"] = ["user_id" => $userId, "user_level" => "U"];
  check($_CORE->Attend->attendQR($classId, $classHash), "Enrolled student QR check-in failed.");
  check(!$_CORE->Attend->attendQR($classId, $classHash), "Duplicate QR check-in was accepted.");
  $_SESSION["user"] = ["user_id" => $otherStudentId, "user_level" => "U"];
  check(!$_CORE->Attend->attendQR($classId, $classHash), "Student outside the course checked in by QR.");

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
  echo "Integration tests passed: attendance authorization, QR enrollment and duplicate protection, session revocation, reports, login throttling, and expiry.\n";
} catch (Throwable $ex) {
  if ($db->pdo->inTransaction()) { $db->end(false); }
  fwrite(STDERR, "Integration test failed: " . $ex->getMessage() . "\n");
  exit(1);
}
