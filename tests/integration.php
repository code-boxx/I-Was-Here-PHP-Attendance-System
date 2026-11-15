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
  )===15, "Active session migration was not applied.");
  check((int)$db->fetchCol(
    "SELECT `setting_value` FROM `settings` WHERE `setting_name`='ATTENDANCE_THRESHOLD'"
  )===75, "Attendance warning threshold did not default to 75%.");
  check((int)$db->fetchCol(
    "SELECT `setting_value` FROM `settings` WHERE `setting_name`='ATTENDANCE_LATE_GRACE_MINUTES'"
  )===10, "Late-arrival grace period did not default to 10 minutes.");
  check(
    (int)$db->fetchCol("SELECT `setting_value` FROM `settings` WHERE `setting_name`='SESSION_IDLE_MINUTES'")===30 &&
    (int)$db->fetchCol("SELECT `setting_value` FROM `settings` WHERE `setting_name`='SESSION_ABSOLUTE_HOURS'")===12,
    "Session expiry settings did not use their secure defaults."
  );
  check((int)$db->fetchCol(
    "SELECT COUNT(*) FROM information_schema.tables
     WHERE table_schema=DATABASE() AND table_name='user_sessions'"
  )===1, "Active session table is missing.");
  check((int)$db->fetchCol(
    "SELECT COUNT(*) FROM information_schema.tables
     WHERE table_schema=DATABASE()
     AND table_name IN ('notification_preferences','notification_outbox')"
  )===2, "Notification preference and outbox tables are missing.");
  check((int)$db->fetchCol(
    "SELECT COUNT(*) FROM information_schema.tables
     WHERE table_schema=DATABASE()
     AND table_name IN ('attendance_offline_queue','class_substitutes')"
  )===2, "Offline queue and substitute tables are missing.");
  check((int)$db->fetchCol(
    "SELECT COUNT(*) FROM information_schema.columns
     WHERE table_schema=DATABASE() AND table_name='attendance_offline_queue'
     AND column_name IN ('reviewed_by','review_note')"
  )===2, "Offline attendance review fields are missing.");
  check((int)$db->fetchCol(
    "SELECT COUNT(*) FROM information_schema.columns
     WHERE table_schema=DATABASE() AND table_name='class_substitutes'
     AND column_name='expires_at'"
  )===1, "Substitute access expiration is missing.");
  check((int)$db->fetchCol(
    "SELECT `character_maximum_length` FROM information_schema.columns
     WHERE table_schema=DATABASE() AND table_name='notification_outbox'
     AND column_name='event_key'"
  )===191, "Notification event deduplication key is not migration-safe.");
  check((int)$db->fetchCol(
    "SELECT COUNT(*) FROM information_schema.tables
     WHERE table_schema=DATABASE()
     AND table_name IN ('attendance_sessions','attendance_corrections','attendance_audit')"
  )===3, "Phase 2 attendance tables are missing.");
  check((int)$db->fetchCol(
    "SELECT COUNT(*) FROM information_schema.tables
     WHERE table_schema=DATABASE()
     AND table_name IN ('attendance_holidays','attendance_schedule_audit')"
  )===2, "School calendar or schedule audit tables are missing.");
  check((int)$db->fetchCol(
    "SELECT COUNT(*) FROM information_schema.tables
     WHERE table_schema=DATABASE() AND table_name='course_roster_audit'"
  )===1, "Course roster audit table is missing.");
  check((int)$db->fetchCol(
    "SELECT COUNT(*) FROM information_schema.tables
     WHERE table_schema=DATABASE() AND table_name='course_audit'"
  )===1, "Course audit table is missing.");
  check((int)$db->fetchCol(
    "SELECT COUNT(*) FROM information_schema.columns
     WHERE table_schema=DATABASE() AND table_name='course_audit'
     AND column_name IN ('undone_by','undone_at','undo_reason')"
  )===3, "Course audit undo fields are missing.");
  check((int)$db->fetchCol(
    "SELECT COUNT(*) FROM information_schema.columns
     WHERE table_schema=DATABASE() AND table_name='classes'
     AND column_name IN ('class_room','class_group')"
  )===2, "Class room and group fields are missing.");
  check((int)$db->fetchCol(
    "SELECT COUNT(*) FROM information_schema.columns
     WHERE table_schema=DATABASE() AND table_name='attendance_schedule_audit'
     AND column_name IN ('undone_by','undone_at','undo_reason')"
  )===3, "Schedule audit undo fields are missing.");
  check((int)$db->fetchCol(
    "SELECT COUNT(*) FROM information_schema.columns
     WHERE table_schema=DATABASE() AND table_name='course_roster_audit'
     AND column_name IN ('operation_id','reason','changed_by','changed_at','undone_by','undone_at','undo_reason')"
  )===7, "Course roster audit metadata is incomplete.");
  check((int)$db->fetchCol(
    "SELECT COUNT(*) FROM information_schema.columns
     WHERE table_schema=DATABASE() AND table_name='classes'
     AND column_name IN ('class_status','class_status_reason','class_status_by','class_status_at')"
  )===4, "Class cancellation tracking fields are missing.");

  $suffix = bin2hex(random_bytes(6));
  $email = "integration-$suffix@example.test";
  $teacherEmail = "teacher-$suffix@example.test";
  $otherTeacherEmail = "other-teacher-$suffix@example.test";
  $otherStudentEmail = "other-student-$suffix@example.test";
  $courseCode = "TEST-$suffix";
  $password = "Integration123";

  $_CORE->load("Users");
  check($_CORE->Users->save("Integration Admin", "admin-$suffix@example.test", $password, "A"), "Could not create test admin.");
  $adminId = $db->lastID;
  check($_CORE->Users->save("=1+1", $email, $password, "U"), "Could not create test student.");
  $userId = $db->lastID;
  check($_CORE->Users->save("Integration Teacher", $teacherEmail, $password, "T"), "Could not create test teacher.");
  $teacherId = $db->lastID;
  check($_CORE->Users->save("Other Teacher", $otherTeacherEmail, $password, "T"), "Could not create second test teacher.");
  $otherTeacherId = $db->lastID;
  check($_CORE->Users->save("Other Student", $otherStudentEmail, $password, "U"), "Could not create second test student.");
  $otherStudentId = $db->lastID;
  $_CORE->load("Notifications");
  $_SESSION["user"] = ["user_id" => $userId, "user_level" => "U"];
  $preferences = $_CORE->Notifications->getPreferences();
  check(
    (int)$preferences["attendance_warning"]===0 &&
    (int)$preferences["correction_decision"]===0 &&
    (int)$preferences["class_reminder"]===0,
    "Notifications were not opt-in by default."
  );
  check($_CORE->Notifications->savePreferences("1", "1", "1"), "Could not save notification preferences.");
  check(!$_CORE->Notifications->savePreferences("2", "0", "0"), "Invalid notification preference was accepted.");
  check($_CORE->Notifications->savePreferences("0", "0", "0"), "Could not disable test notification preferences.");
  $importEmail = "import-$suffix@example.test";
  check($_CORE->Users->import("Imported Teacher", $importEmail, $password, "T"), "Valid teacher CSV row was rejected.");
  check(!$_CORE->Users->import("Duplicate Teacher", $importEmail, $password, "T"), "Duplicate user CSV row was accepted.");
  check(!$_CORE->Users->import("Invalid Role", "invalid-role-$suffix@example.test", $password, "Teacher"), "Invalid user level was accepted.");
  check(!$_CORE->Users->import("Invalid Email", "invalid-email-$suffix", $password, "U"), "Invalid email was accepted.");
  $userPreview = $_CORE->Users->previewImport(json_encode([
    ["name" => "Preview User", "email" => "preview-$suffix@example.test", "password" => $password, "level" => "U"],
    ["name" => "Existing User", "email" => $email, "password" => $password, "level" => "U"],
    ["name" => "Preview User", "email" => "preview-$suffix@example.test", "password" => $password, "level" => "U"]
  ], JSON_THROW_ON_ERROR));
  check(
    is_array($userPreview) &&
    array_column($userPreview, "status")===["new", "conflicting", "duplicate"],
    "User CSV preview did not detect new, existing, and duplicate rows."
  );

  $_CORE->load("Courses");
  $_SESSION["user"] = ["user_id" => $adminId, "user_level" => "A"];
  $start = date("Y-m-d", strtotime("-1 day"));
  $end = date("Y-m-d", strtotime("+7 days"));
  check(!$_CORE->Courses->import("BAD-$suffix", "Invalid dates", $end, $start), "Invalid course dates were accepted.");
  check($_CORE->Courses->save($courseCode, "Integration course", $start, $end), "Could not create test course.");
  $destinationCode = "MOVE-$suffix";
  check($_CORE->Courses->save($destinationCode, "Destination course", $start, $end), "Could not create destination course.");
  check(
    $_CORE->Courses->save(
      $courseCode, "Updated integration course", $start, $end, null, $courseCode,
      "Test course metadata update"
    ),
    "Could not update test course."
  );
  $courseAudit = $_CORE->Courses->getCourseAudit($courseCode);
  check(
    is_array($courseAudit) && ($courseAudit[0]["event_type"] ?? null)==="updated",
    "Course metadata update was not audited."
  );
  check(
    $_CORE->Courses->undoCourseChange($courseAudit[0]["event_id"], "Test course metadata undo"),
    "Course metadata update could not be undone."
  );
  check(
    $_CORE->Courses->get($courseCode)["course_name"]==="Integration course",
    "Course metadata undo did not restore the previous values."
  );
  $previewRows = [
    ["code" => $courseCode, "name" => "Integration course", "desc" => "", "start" => $start, "end" => $end],
    ["code" => $destinationCode, "name" => "Changed name", "desc" => "", "start" => $start, "end" => $end],
    ["code" => "NEW-$suffix", "name" => "New course", "desc" => "", "start" => $start, "end" => $end],
    ["code" => "NEW-$suffix", "name" => "New course", "desc" => "", "start" => $start, "end" => $end],
    ["code" => "BAD-DATE-$suffix", "name" => "Invalid date", "desc" => "", "start" => "2026-02-30", "end" => $end]
  ];
  $coursePreview = $_CORE->Courses->previewImport(json_encode($previewRows, JSON_THROW_ON_ERROR));
  check(
    is_array($coursePreview) &&
    array_column($coursePreview, "status")===["unchanged", "changed", "new", "duplicate", "conflicting"],
    "Course CSV preview did not reconcile unchanged, changed, new, duplicate, and invalid rows."
  );
  check($_CORE->Courses->addUser($courseCode, $userId, "Initial test enrollment"), "Could not enroll test student.");
  check($_CORE->Courses->addUser($courseCode, $teacherId, "Initial test teacher assignment"), "Could not enroll test teacher.");
  $bulkEnrollment = $_CORE->Courses->addUsers($courseCode, "$otherStudentEmail\n$otherStudentEmail", "Bulk test enrollment");
  check(
    is_array($bulkEnrollment) && $bulkEnrollment["added"]===1 &&
    $bulkEnrollment["already_enrolled"]===0,
    "Bulk enrollment did not de-duplicate users."
  );
  $enrollmentPreview = $_CORE->Courses->previewUserImport(json_encode([
    ["code" => $courseCode, "uid" => $email],
    ["code" => $courseCode, "uid" => $otherTeacherEmail],
    ["code" => $courseCode, "uid" => $otherTeacherEmail]
  ], JSON_THROW_ON_ERROR));
  check(
    is_array($enrollmentPreview) &&
    array_column($enrollmentPreview, "status")===["unchanged", "new", "duplicate"],
    "Course enrollment CSV preview did not detect unchanged, new, and duplicate rows."
  );
  check(
    !$_CORE->Courses->addUsers($courseCode, "$importEmail\nmissing-$suffix@example.test", "Invalid bulk enrollment test"),
    "Bulk enrollment accepted an unknown user."
  );
  check(
    $db->fetchCol(
      "SELECT 1 FROM `courses_users` WHERE `course_code`=? AND `user_id`=?",
      [$courseCode, $db->fetchCol("SELECT `user_id` FROM `users` WHERE `user_email`=?", [$importEmail])]
    )===null,
    "Invalid bulk enrollment partially added users."
  );
  $studentRoster = $_CORE->Courses->getUsers($courseCode, null, $email, "students", "email_desc");
  check(
    is_array($studentRoster) && count($studentRoster)===1 &&
    (int)array_key_first($studentRoster)===$userId,
    "Course roster search or role filtering returned the wrong students."
  );
  $teacherRoster = $_CORE->Courses->getUsers($courseCode, null, null, "teachers", "name");
  check(
    is_array($teacherRoster) && count($teacherRoster)===1 &&
    (int)array_key_first($teacherRoster)===$teacherId,
    "Course roster teacher filtering returned the wrong users."
  );
  $sortedRoster = $_CORE->Courses->getUsers($courseCode, null, null, "all", "email");
  check(
    is_array($sortedRoster) &&
    array_map("intval", array_keys($sortedRoster))===[$userId, $otherStudentId, $teacherId],
    "Course roster email sorting returned the wrong order."
  );
  $bulkRemoval = $_CORE->Courses->delUsers($courseCode, json_encode([$otherStudentId], JSON_THROW_ON_ERROR), "Test bulk removal");
  check(is_array($bulkRemoval) && $bulkRemoval["removed"]===1, "Bulk removal did not remove the selected enrollment.");
  check($_CORE->Courses->addUser($courseCode, $otherStudentId, "Restore test enrollment"), "Could not restore test student enrollment.");
  check($_CORE->Courses->addUser($destinationCode, $otherStudentId, "Prepare destination enrollment"), "Could not pre-enroll destination student.");
  check(
    !$_CORE->Courses->moveStudents($courseCode, $destinationCode, json_encode([$teacherId], JSON_THROW_ON_ERROR), "Invalid teacher transfer test"),
    "Student transfer accepted a teacher."
  );
  check(
    $db->fetchCol(
      "SELECT 1 FROM `courses_users` WHERE `course_code`=? AND `user_id`=?",
      [$courseCode, $teacherId]
    )!==null,
    "Invalid transfer removed a source-course enrollment."
  );
  $studentMove = $_CORE->Courses->moveStudents(
    $courseCode, $destinationCode, json_encode([$otherStudentId], JSON_THROW_ON_ERROR), "Test student transfer"
  );
  check(
    is_array($studentMove) && $studentMove["moved"]===1 &&
    $studentMove["already_enrolled"]===1 &&
    $db->fetchCol(
      "SELECT 1 FROM `courses_users` WHERE `course_code`=? AND `user_id`=?",
      [$courseCode, $otherStudentId]
    )===null &&
    $db->fetchCol(
      "SELECT 1 FROM `courses_users` WHERE `course_code`=? AND `user_id`=?",
      [$destinationCode, $otherStudentId]
    )!==null,
    "Student transfer did not move the enrollment safely."
  );
  $sourceAudit = $_CORE->Courses->getRosterAudit($courseCode);
  check(is_array($sourceAudit) && count($sourceAudit)>0, "Roster changes were not audited.");
  $transferAudit = null;
  foreach ($sourceAudit as $event) {
    if ($event["event_type"]==="moved_out" && $event["user_id"]==$otherStudentId) {
      $transferAudit = $event;
      break;
    }
  }
  check(is_array($transferAudit), "Transfer audit event was not recorded.");
  check(
    $transferAudit["reason"]==="Test student transfer" &&
    (int)$transferAudit["changed_by"]===$adminId,
    "Roster audit did not retain the reason and actor."
  );
  check(
    !$_CORE->Courses->undoRosterChange($transferAudit["event_id"], ""),
    "Roster undo accepted a missing reason."
  );
  check(
    $_CORE->Courses->undoRosterChange($transferAudit["event_id"], "Reverse test transfer"),
    "Roster transfer undo failed."
  );
  check(
    $db->fetchCol(
      "SELECT 1 FROM `courses_users` WHERE `course_code`=? AND `user_id`=?",
      [$courseCode, $otherStudentId]
    )!==null &&
    $db->fetchCol(
      "SELECT 1 FROM `courses_users` WHERE `course_code`=? AND `user_id`=?",
      [$destinationCode, $otherStudentId]
    )!==null,
    "Undo did not restore the source course while retaining the pre-existing destination enrollment."
  );
  check(
    !$_CORE->Courses->undoRosterChange($transferAudit["event_id"], "Repeated undo"),
    "Roster operation was undone more than once."
  );
  $importedTeacherId = $db->fetchCol(
    "SELECT `user_id` FROM `users` WHERE `user_email`=?", [$importEmail]
  );
  check(
    $_CORE->Courses->addUser($courseCode, $importedTeacherId, "Test undo ordering: add"),
    "Could not create roster history for undo ordering test."
  );
  check(
    is_array($_CORE->Courses->delUser($courseCode, $importedTeacherId, "Test undo ordering: remove")),
    "Could not create later roster removal for undo ordering test."
  );
  $courseAudit = $_CORE->Courses->getRosterAudit($courseCode);
  $addEventId = null;
  $removeEventId = null;
  foreach ($courseAudit as $event) {
    if ($event["user_id"]!=$importedTeacherId) { continue; }
    if ($event["event_type"]==="enrolled" && $addEventId===null) { $addEventId = $event["event_id"]; }
    if ($event["event_type"]==="removed" && $removeEventId===null) { $removeEventId = $event["event_id"]; }
  }
  check(
    $addEventId!==null && $removeEventId!==null &&
    !$_CORE->Courses->undoRosterChange($addEventId, "Try older undo") &&
    $_CORE->Courses->undoRosterChange($removeEventId, "Restore later removal") &&
    $_CORE->Courses->undoRosterChange($addEventId, "Undo original enrollment"),
    "Roster undo did not enforce reverse chronological ordering."
  );

  $_CORE->load("Classes");
  check(
    $_CORE->Classes->save(
      $courseCode, $teacherId, "$start 09:00:00", "Integration session", null, null, "Room 1", "Group A"
    ),
    "Could not create test class."
  );
  $classId = $db->lastID;
  $classPreview = $_CORE->Classes->previewImport(json_encode([
    ["code" => $courseCode, "date" => "$start 09:00:00", "email" => $teacherEmail, "desc" => "Integration session"],
    ["code" => $courseCode, "date" => "$start 09:30:00", "email" => $teacherEmail, "desc" => "Overlapping session"]
  ], JSON_THROW_ON_ERROR));
  check(
    is_array($classPreview) &&
    array_column($classPreview, "status")===["unchanged", "conflicting"],
    "Class CSV preview did not detect existing classes and teacher schedule conflicts."
  );
  check(
    !$_CORE->Classes->save($courseCode, $teacherId, "$start 09:30:00", "Overlapping session"),
    "Teacher timetable conflict was not detected."
  );
  check(
    !$_CORE->Classes->save($courseCode, $otherTeacherId, "$start 09:15:00", "Room conflict", null, null, "Room 1"),
    "Room timetable conflict was not detected."
  );
  check(
    !$_CORE->Classes->save($courseCode, $otherTeacherId, "$start 09:15:00", "Group conflict", null, null, null, "Group A"),
    "Student group timetable conflict was not detected."
  );
  check(
    $_CORE->Classes->save($courseCode, $teacherId, "$start 10:00:00", "Adjacent session"),
    "Adjacent non-overlapping session was rejected."
  );
  $_CORE->Classes->del($db->lastID);
  check(
    $_CORE->Classes->save($courseCode, $teacherId, "$start 00:01:00", "Integration session", $classId, "Use a stable class time for the test."),
    "Could not set a stable attendance test class time."
  );
  $_SESSION["user"] = ["user_id" => $teacherId, "user_level" => "T"];
  $substituteClassDate = "$end 12:00:00";
  check(
    $_CORE->Classes->save($courseCode, $teacherId, $substituteClassDate, "Substitute access test"),
    "Could not create substitute test class."
  );
  $substituteClassId = (int)$db->lastID;
  $substituteExpiry = date("Y-m-d H:i:s", strtotime($substituteClassDate." +12 hours"));
  check(
    $_CORE->Classes->addSubstitute($substituteClassId, $otherTeacherId, $substituteExpiry),
    "Class owner could not assign a time-limited substitute."
  );
  $_SESSION["user"] = ["user_id" => $otherTeacherId, "user_level" => "T"];
  check($_CORE->Classes->canManageClass($substituteClassId), "Active substitute could not manage the assigned class.");
  check(
    !$_CORE->Classes->addSubstitute($substituteClassId, $teacherId, $substituteExpiry),
    "Substitute could assign further substitute access."
  );
  $db->update(
    "class_substitutes", ["expires_at"], "`class_id`=? AND `user_id`=?",
    [date("Y-m-d H:i:s", time()-60), $substituteClassId, $otherTeacherId]
  );
  check(!$_CORE->Classes->canManageClass($substituteClassId), "Expired substitute access remained active.");
  $_SESSION["user"] = ["user_id" => $teacherId, "user_level" => "T"];
  $_CORE->Classes->del($substituteClassId);

  $_CORE->load("Report");
  $_SESSION["user"] = ["user_id" => 1, "user_level" => "A"];
  ob_start();
  $_CORE->Report->attend($courseCode);
  $csv = ob_get_clean();
  check(strpos($csv, $email) !== false, "Report omitted an enrolled student with no attendance.");
  check(strpos($csv, "'=1+1") !== false, "Report did not neutralize a spreadsheet formula.");
  check(strpos($csv, "0%") !== false, "Report omitted the zero attendance percentage.");
  ob_start();
  $_CORE->Report->trends($courseCode, $start, $start);
  $trendsCsv = ob_get_clean();
  check(
    strpos($trendsCsv, "WEEKLY ATTENDANCE TREND")!==false &&
    strpos($trendsCsv, ",0,1,1,0%")!==false,
    "Weekly attendance trend did not count present and unrecorded students correctly: $trendsCsv"
  );
  ob_start();
  $_CORE->Report->followup($courseCode, $start, $start);
  $followupCsv = ob_get_clean();
  check(
    strpos($followupCsv, "Below threshold")!==false &&
    strpos($followupCsv, "'=1+1")!==false &&
    strpos($followupCsv, "0%")!==false,
    "Follow-up export omitted a below-threshold student or CSV formula protection."
  );
  $_SESSION["user"] = ["user_id" => $teacherId, "user_level" => "T"];
  ob_start();
  $_CORE->Report->followup($courseCode, $start, $start);
  $teacherFollowupCsv = ob_get_clean();
  check(
    strpos($teacherFollowupCsv, $email)!==false,
    "Assigned teacher could not export the course follow-up report."
  );
  $_SESSION["user"] = ["user_id" => $otherTeacherId, "user_level" => "T"];
  $unauthorizedReport = false;
  try { $_CORE->Report->trends($courseCode, $start, $start); }
  catch (RuntimeException $ex) { $unauthorizedReport = $ex->getMessage()==="No access permission"; }
  check($unauthorizedReport, "Unassigned teacher viewed course trend data.");
  $_SESSION["user"] = ["user_id" => 1, "user_level" => "A"];
  ob_start();
  $_CORE->Report->attend($courseCode, $start, $start, "print");
  $printReport = ob_get_clean();
  check(
    strpos($printReport, "Attendance report")!==false &&
    strpos($printReport, "<td>Not recorded</td>")!==false &&
    strpos($printReport, $email)!==false,
    "Printable report omitted the date-filtered class or unrecorded student status."
  );
  ob_start();
  $_CORE->Report->attend($courseCode, date("Y-m-d", strtotime("+1 day")), date("Y-m-d", strtotime("+2 days")));
  $filteredCsv = ob_get_clean();
  check(
    strpos($filteredCsv, $email)!==false && strpos($filteredCsv, "N/A")!==false,
    "Date filtering omitted enrolled students with no classes in the selected range."
  );
  $invalidReportDate = false;
  try { $_CORE->Report->attend($courseCode, "2026-02-30", null); }
  catch (InvalidArgumentException $ex) { $invalidReportDate = true; }
  check($invalidReportDate, "Invalid report date was accepted.");

  $_CORE->load("Attend");
  $_SESSION["user"] = ["user_id" => $adminId, "user_level" => "A"];
  check($_CORE->Courses->addUser($courseCode, $otherStudentId, "Dashboard test enrollment"), "Could not enroll the dashboard test student.");
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
  $_SESSION["user"] = ["user_id" => $teacherId, "user_level" => "T"];
  $dashboard = $_CORE->Attend->getDashboard($classId);
  check(
    $dashboard["total"]===2 && $dashboard["present"]===1 &&
    $dashboard["absent"]===0 && $dashboard["unrecorded"]===1 &&
    count($dashboard["not_present_students"])===1 &&
    $dashboard["last_checkin"]["user_name"]==="=1+1",
    "Live dashboard did not return accurate counts, missing roster, and latest QR check-in."
  );
  $db->update(
    "attendance", ["a_date"],
    "`class_id`=? AND `user_id`=?",
    [date("Y-m-d H:i:s", strtotime($start." 00:12:00")), $classId, $userId]
  );
  $lateDashboard = $_CORE->Attend->getDashboard($classId);
  $sessionSummary = $_CORE->Attend->getSessionSummary($classId);
  $projectorStats = $_CORE->Attend->getProjectorStats($classId);
  check(
    $lateDashboard["late"]===1 && $lateDashboard["late_grace_minutes"]===10 &&
    $sessionSummary["late"]===1 && $sessionSummary["present"]===1,
    "Dashboard or session summary did not apply the configurable late-arrival grace period."
  );
  check(
    $projectorStats["present"]===1 && $projectorStats["late"]===1 &&
    $projectorStats["total"]===2 && !array_key_exists("not_present_students", $projectorStats) &&
    !array_key_exists("last_checkin", $projectorStats),
    "Projector endpoint did not restrict its response to aggregate attendance."
  );
  $_SESSION["user"] = ["user_id" => $otherTeacherId, "user_level" => "T"];
  check($_CORE->Attend->getDashboard($classId)===false, "Unassigned teacher viewed another class dashboard.");
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
  $_SESSION["user"] = ["user_id" => $teacherId, "user_level" => "T"];
  $dashboard = $_CORE->Attend->getDashboard($classId);
  check(
    $dashboard["present"]===0 && $dashboard["absent"]===1 &&
    $dashboard["unrecorded"]===1,
    "Live dashboard counts did not update after a correction."
  );
  $audit = $_CORE->Attend->getAudit($classId);
  check(is_array($audit) && count($audit)>=5, "Attendance audit history omitted changes.");
  $_SESSION["user"] = ["user_id" => $otherTeacherId, "user_level" => "T"];
  check($_CORE->Attend->getAudit($classId)===false, "Unassigned teacher viewed class audit history.");

  $_SESSION["user"] = ["user_id" => $teacherId, "user_level" => "T"];
  check($_CORE->Attend->closeSession($classId), "Teacher could not close an attendance session.");
  check(!$_CORE->Attend->attendQR($qr["token"]), "A QR token remained valid after session closure.");
  check($_CORE->Attend->sessionState($classId)["session_status"]=="closed", "Closed session state was not reported.");

  $offlineEventId = "integration-offline-$suffix";
  $offlineTimestamp = date("Y-m-d H:i:s");
  $_SESSION["user"] = ["user_id" => $otherStudentId, "user_level" => "U"];
  $queuedOffline = $_CORE->Attend->queueOfflineAttendance(
    $classId, $offlineEventId, "1", $offlineTimestamp
  );
  check(is_array($queuedOffline) && $queuedOffline["status"]==="pending", "Student offline check-in was not queued.");
  check(
    $_CORE->Attend->queueOfflineAttendance($classId, $offlineEventId, "1", $offlineTimestamp)["status"]==="pending",
    "Retrying the same offline event was not idempotent."
  );
  check(
    !$_CORE->Attend->queueOfflineAttendance($classId, "bad-event", "0", $offlineTimestamp),
    "Offline queue accepted an absent status."
  );
  $_SESSION["user"] = ["user_id" => $otherTeacherId, "user_level" => "T"];
  check($_CORE->Attend->getOfflineQueue($classId)===false, "Unassigned teacher viewed offline check-ins.");
  $_SESSION["user"] = ["user_id" => $teacherId, "user_level" => "T"];
  $offlineRows = $_CORE->Attend->getOfflineQueue($classId);
  check(is_array($offlineRows) && count($offlineRows)===1, "Assigned teacher could not review queued offline check-ins.");
  check(
    !$_CORE->Attend->reviewOfflineQueue($classId, $offlineEventId, "approved", "Unassigned review."),
    "Unassigned teacher approved an offline check-in."
  );
  check(
    $_CORE->Attend->reviewOfflineQueue($classId, $offlineEventId, "approved", "Confirmed against class evidence."),
    "Assigned teacher could not approve an offline check-in."
  );
  check(
    (int)$db->fetchCol(
      "SELECT `a_status` FROM `attendance` WHERE `class_id`=? AND `user_id`=?",
      [$classId, $otherStudentId]
    )===1 &&
    $db->fetchCol(
      "SELECT `status` FROM `attendance_offline_queue` WHERE `event_id`=?",
      [$offlineEventId]
    )==="synced",
    "Approved offline check-in was not recorded and marked reviewed."
  );
  $_SESSION["user"] = ["user_id" => $otherStudentId, "user_level" => "U"];
  $studentOfflineQueue = $_CORE->Attend->getStudentOfflineQueue();
  check(
    is_array($studentOfflineQueue) && count($studentOfflineQueue)===1 &&
    $studentOfflineQueue[0]["status"]==="synced" &&
    $studentOfflineQueue[0]["review_note"]==="Confirmed against class evidence.",
    "Student could not view the review outcome for their own offline event."
  );
  $_SESSION["user"] = ["user_id" => $teacherId, "user_level" => "T"];
  check(
    !$_CORE->Attend->reviewOfflineQueue($classId, $offlineEventId, "approved", "Duplicate review."),
    "An already reviewed offline event was processed again."
  );

  $_SESSION["user"] = ["user_id" => $teacherId, "user_level" => "A"];
  check($_CORE->Settings->save(["ATTENDANCE_THRESHOLD" => "80"]), "Admin could not update attendance threshold.");
  check(!$_CORE->Settings->save(["ATTENDANCE_THRESHOLD" => "101"]), "Out-of-range attendance threshold was accepted.");
  check($_CORE->Settings->save(["ATTENDANCE_LATE_GRACE_MINUTES" => "0"]), "Admin could not disable late-arrival grace.");
  check(!$_CORE->Settings->save(["ATTENDANCE_LATE_GRACE_MINUTES" => "1441"]), "Out-of-range late-arrival grace was accepted.");
  check(!$_CORE->Settings->save(["APP_VER" => "1"]), "Admin settings API accepted a non-editable setting.");
  check($_CORE->Settings->save(["ATTENDANCE_THRESHOLD" => "75"]), "Could not restore test attendance threshold.");
  check($_CORE->Settings->save(["ATTENDANCE_LATE_GRACE_MINUTES" => "10"]), "Could not restore test late-arrival grace.");

  $_CORE->load("Calendar");
  $_CORE->load("Classes");
  $_SESSION["user"] = ["user_id" => 1, "user_level" => "A"];
  check(
    $_CORE->Classes->save($courseCode, $teacherId, "$start 10:00:00", "Excused attendance test"),
    "Could not create excused-status test class."
  );
  $excusedClassId = (int)$db->lastID;
  check(
    $_CORE->Attend->save($excusedClassId, json_encode([
      $userId => ["s" => "2", "n" => "Approved excused absence."],
      $otherStudentId => ["s" => "0", "n" => "Test absence."]
    ], JSON_THROW_ON_ERROR)),
    "Could not save an excused attendance record."
  );
  check(
    $_CORE->Classes->save($courseCode, $teacherId, "$start 12:00:00", "Reschedule audit test"),
    "Could not create class for cancellation and rescheduling tests."
  );
  $rescheduledClassId = (int)$db->lastID;
  check(
    $_CORE->Attend->save($rescheduledClassId, json_encode([
      $userId => ["s" => "1", "n" => "Teacher verified attendance."]
    ], JSON_THROW_ON_ERROR)),
    "Could not save attendance before cancellation."
  );
  check($_CORE->Classes->cancelClass($rescheduledClassId, "Instructor unavailable."), "Class cancellation failed.");
  check(
    $db->fetchCol("SELECT `class_status` FROM `classes` WHERE `class_id`=?", [$rescheduledClassId])==="cancelled",
    "Cancelled class state was not saved."
  );
  $_SESSION["user"] = ["user_id" => $userId, "user_level" => "U"];
  $cancelledSummary = $_CORE->Attend->getStudentSummary();
  check(
    (int)$cancelledSummary[$courseCode]["total_classes"]===2,
    "Cancelled class was included in student attendance totals."
  );
  $_SESSION["user"] = ["user_id" => 1, "user_level" => "A"];
  check(
    !$_CORE->Attend->openSession($rescheduledClassId),
    "Attendance session opened for a cancelled class."
  );
  check(
    $_CORE->Classes->rescheduleClass($rescheduledClassId, "{$start}T16:00", "Room became available."),
    "Cancelled class could not be rescheduled."
  );
  check(
    $db->fetchCol("SELECT `class_status` FROM `classes` WHERE `class_id`=?", [$rescheduledClassId])==="scheduled" &&
    (int)$db->fetchCol(
      "SELECT `a_status` FROM `attendance` WHERE `class_id`=? AND `user_id`=?",
      [$rescheduledClassId, $userId]
    )===1,
    "Rescheduling did not reactivate the class while preserving its attendance record."
  );
  check(
    count($_CORE->Classes->getScheduleAudit($rescheduledClassId))===2,
    "Cancellation and rescheduling were not both recorded in the schedule audit."
  );
  $scheduleAudit = $_CORE->Classes->getScheduleAudit($rescheduledClassId);
  check(
    $_CORE->Classes->undoScheduleChange($scheduleAudit[0]["event_id"], "Restore original schedule"),
    "Rescheduled class could not be restored from schedule audit."
  );
  check(
    $db->fetchCol("SELECT `class_status` FROM `classes` WHERE `class_id`=?", [$rescheduledClassId])==="scheduled" &&
    $db->fetchCol("SELECT `class_date` FROM `classes` WHERE `class_id`=?", [$rescheduledClassId])==="$start 12:00:00",
    "Schedule undo did not restore the original class state."
  );
  check(
    $_CORE->Classes->save($courseCode, $teacherId, "$start 14:00:00", "Holiday test class"),
    "Could not create holiday test class."
  );
  $holidayClassId = (int)$db->lastID;
  check(
    $_CORE->Attend->save($holidayClassId, json_encode([
      $userId => ["s" => "1", "n" => "Verified before holiday."]
    ], JSON_THROW_ON_ERROR)),
    "Could not record attendance before setting holiday."
  );
  check(
    $_CORE->Calendar->saveHoliday($start, "Integration non-instruction day", "Test school closure."),
    "Could not add a school non-instruction day."
  );
  check(
    !$_CORE->Classes->save($courseCode, $teacherId, "$start 18:00:00", "Must reject holiday"),
    "Class creation on a school holiday was accepted."
  );
  check(
    !$_CORE->Attend->save($classId, json_encode([
      $userId => ["s" => "1", "n" => "Should be blocked."]
    ], JSON_THROW_ON_ERROR)),
    "Attendance was editable on a school non-instruction day."
  );
  $_SESSION["user"] = ["user_id" => $userId, "user_level" => "U"];
  $holidaySummary = $_CORE->Attend->getStudentSummary();
  check(
    (int)$holidaySummary[$courseCode]["total_classes"]===0 &&
    $holidaySummary[$courseCode]["attendance_percentage"]===null,
    "Holiday classes were counted in the attendance denominator."
  );
  $_SESSION["user"] = ["user_id" => 1, "user_level" => "A"];
  check(
    $_CORE->Calendar->deleteHoliday($start, "Restore integration attendance test."),
    "Could not remove the integration non-instruction day."
  );
  check(
    count($_CORE->Calendar->getAudit($start))===2,
    "School calendar add/remove actions were not recorded in the audit history."
  );

  $_SESSION["user"] = ["user_id" => $userId, "user_level" => "U"];
  $summary = $_CORE->Attend->getStudentSummary();
  check(
    (int)$summary[$courseCode]["total_classes"]===4 &&
    (int)$summary[$courseCode]["counted_classes"]===3 &&
    (int)$summary[$courseCode]["excused_classes"]===1 &&
    (int)$summary[$courseCode]["present_classes"]===2 &&
    $summary[$courseCode]["attendance_percentage"]===67 &&
    $summary[$courseCode]["attendance_threshold"]===75 &&
    $summary[$courseCode]["below_threshold"]===true,
    "Student course summary or low-attendance warning did not reflect policy."
  );
  $_SESSION["user"] = ["user_id" => $otherStudentId, "user_level" => "U"];
  $otherSummary = $_CORE->Attend->getStudentSummary();
  check(
    (int)$otherSummary[$courseCode]["total_classes"]===4 &&
    (int)$otherSummary[$courseCode]["present_classes"]===1 &&
    (int)$otherSummary[$courseCode]["absent_classes"]===1 &&
    $otherSummary[$courseCode]["below_threshold"]===true,
    "Unrecorded attendance was not considered in the policy warning."
  );
  $_SESSION["user"] = ["user_id" => $userId, "user_level" => "U"];
  check(is_array($_CORE->Classes->getByStudent($userId)), "Student class portal query failed.");
  $_CORE->Classes->del($classId);
  foreach (["attendance_sessions", "attendance_corrections", "attendance_audit", "attendance_schedule_audit"] as $table) {
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
  echo "Integration tests passed: Phase 3 migrations, attendance trends and follow-up reports, bulk enrollment, schedule conflicts, notification preferences, QR sessions, correction workflow, authorization, login throttling, and session expiry.\n";
} catch (Throwable $ex) {
  if ($db->pdo->inTransaction()) { $db->end(false); }
  fwrite(STDERR, "Integration test failed: " . $ex->getMessage() . "\n");
  exit(1);
}
