<?php
if (getenv("IWH_ALLOW_DEV_SEED")!=="1") {
  fwrite(STDERR, "Refusing to seed: set IWH_ALLOW_DEV_SEED=1 explicitly.\n");
  exit(1);
}

require dirname(__DIR__).DIRECTORY_SEPARATOR."lib".DIRECTORY_SEPARATOR."CORE-Go.php";
$adminPassword = getenv("IWH_DEV_ADMIN_PASSWORD") ?: "dev-admin-change-me";
$studentPassword = getenv("IWH_DEV_STUDENT_PASSWORD") ?: "dev-student-change-me";

$_CORE->DB->start();
try {
  $admin = $_CORE->DB->fetchCol("SELECT `user_id` FROM `users` WHERE `user_email`=?",
    ["admin@example.test"]);
  if ($admin===null) {
    $_CORE->DB->insert("users",
      ["user_level", "user_name", "user_email", "user_password", "session_version"],
      ["A", "Development Admin", "admin@example.test",
       password_hash($adminPassword, PASSWORD_DEFAULT), 0]);
    $admin = $_CORE->DB->lastID;
  }
  $student = $_CORE->DB->fetchCol("SELECT `user_id` FROM `users` WHERE `user_email`=?",
    ["student@example.test"]);
  if ($student===null) {
    $_CORE->DB->insert("users",
      ["user_level", "user_name", "user_email", "user_password", "session_version"],
      ["U", "Development Student", "student@example.test",
       password_hash($studentPassword, PASSWORD_DEFAULT), 0]);
    $student = $_CORE->DB->lastID;
  }
  $course = $_CORE->DB->fetchCol("SELECT `course_code` FROM `courses` WHERE `course_code`=?",
    ["DEMO-101"]);
  if ($course===null) {
    $_CORE->DB->insert("courses",
      ["course_code", "course_name", "course_desc", "course_start", "course_end"],
      ["DEMO-101", "Development Attendance", "Safe local demo course",
       date("Y-m-d"), date("Y-m-d", strtotime("+90 days"))]);
  }
  $_CORE->DB->query(
    "INSERT IGNORE INTO `courses_users` (`course_code`,`user_id`) VALUES (?,?)",
    ["DEMO-101", $student]
  );
  $class = $_CORE->DB->fetchCol(
    "SELECT `class_id` FROM `classes` WHERE `course_code`=? AND `user_id`=?",
    ["DEMO-101", $admin]
  );
  if ($class===null) {
    $_CORE->DB->insert("classes",
      ["course_code", "user_id", "class_date", "class_desc", "class_hash", "class_status"],
      ["DEMO-101", $admin, date("Y-m-d H:00:00", strtotime("+1 day")),
       "Development demo class", md5("DEMO-101-demo"), "scheduled"]);
  }
  $_CORE->DB->end();
  echo "Development seed data is ready.\n";
} catch (Throwable $ex) {
  if ($_CORE->DB->pdo->inTransaction()) { $_CORE->DB->end(false); }
  throw $ex;
}
