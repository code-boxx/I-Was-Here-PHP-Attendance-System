<?php
if (PHP_SAPI!=="cli") {
  http_response_code(404);
  exit("Not found.\n");
}

require dirname(__DIR__) . "/lib/CORE-Config.php";
require PATH_LIB . "LIB-Core.php";
$db = null;
$dryRun = in_array("--dry-run", $argv ?? [], true);
$discovered = 0;

function queueNotification ($userId, $category, $eventKey, $subject, $body) {
  global $db, $dryRun, $discovered;
  $discovered++;
  $preferenceColumn = [
    "attendance_warning" => "attendance_warning",
    "correction_decision" => "correction_decision",
    "class_reminder" => "class_reminder"
  ][$category] ?? null;
  if ($preferenceColumn===null) {
    throw new InvalidArgumentException("Unknown notification category.");
  }
  $allowedLevels = $category==="class_reminder" ? "('U','T')" : "('U')";
  if ($dryRun) { return; }
  $db->query(
    "INSERT IGNORE INTO `notification_outbox`
      (`user_id`,`category`,`event_key`,`recipient_email`,`subject`,`body`,`available_at`)
     SELECT u.`user_id`, ?, ?, u.`user_email`, ?, ?, NOW()
     FROM `users` u
     JOIN `notification_preferences` p ON (p.`user_id`=u.`user_id`)
     WHERE u.`user_id`=? AND u.`user_level` IN $allowedLevels AND p.`$preferenceColumn`=1",
    [$category, $eventKey, $subject, $body, $userId]
  );
}

function htmlText ($value) {
  return htmlspecialchars((string)$value, ENT_QUOTES | ENT_SUBSTITUTE, "UTF-8");
}

try {
  $_CORE->load("DB");
  $_CORE->load("Settings");
  $_CORE->load("Mail");
  $db = $_CORE->DB;

  $thresholdValue = $db->fetchCol(
    "SELECT `setting_value` FROM `settings` WHERE `setting_name`='ATTENDANCE_THRESHOLD'"
  );
  if ($thresholdValue===null || !ctype_digit((string)$thresholdValue) ||
      (int)$thresholdValue<1 || (int)$thresholdValue>100) {
    throw new RuntimeException("Attendance threshold is not configured correctly.");
  }
  $threshold = (int)$thresholdValue;
  $dayKey = date("Y-m-d");

  $db->query(
    "SELECT cu.`user_id`, cu.`course_code`, co.`course_name`, u.`user_name`,
            COUNT(CASE WHEN a.`a_status`=2 THEN NULL ELSE cl.`class_id` END) AS `total`,
            COALESCE(SUM(a.`a_status`=1),0) AS `present`
     FROM `courses_users` cu
     JOIN `users` u ON (u.`user_id`=cu.`user_id` AND u.`user_level`='U')
     JOIN `courses` co ON (co.`course_code`=cu.`course_code`)
     JOIN `classes` cl ON (cl.`course_code`=co.`course_code` AND cl.`class_date`<=NOW()
                           AND cl.`class_status`='scheduled')
     LEFT JOIN `attendance` a ON (a.`class_id`=cl.`class_id` AND a.`user_id`=u.`user_id`)
     LEFT JOIN `attendance_holidays` h ON (h.`holiday_date`=DATE(cl.`class_date`))
     JOIN `notification_preferences` p ON (p.`user_id`=u.`user_id` AND p.`attendance_warning`=1)
     WHERE h.`holiday_date` IS NULL
     GROUP BY cu.`user_id`,cu.`course_code`,co.`course_name`,u.`user_name`"
  );
  $warningCandidates = $db->stmt->fetchAll();
  foreach ($warningCandidates as $row) {
    $total = (int)$row["total"];
    $present = (int)$row["present"];
    if ($total===0 || round(100*$present/$total)>=$threshold) { continue; }
    $percent = (int)round(100*$present/$total);
    $key = "attendance-warning:".hash("sha256", $row["course_code"]).":".
      $row["user_id"].":".$threshold.":".$dayKey;
    $subject = "Attendance warning for ".$row["course_code"];
    $body = "<p>Hello ".htmlText($row["user_name"]).",</p><p>Your attendance for <strong>".
      htmlText($row["course_name"])."</strong> is ".$percent."%, below the current school threshold of ".
      $threshold."% (".$present." present of ".$total." completed classes).</p>".
      "<p>Please contact your teacher if you believe a record needs correction.</p>";
    queueNotification($row["user_id"], "attendance_warning", $key, $subject, $body);
  }

  $db->query(
    "SELECT DISTINCT u.`user_id`, u.`user_name`, u.`user_email`, cl.`class_id`,
            cl.`class_date`, cl.`class_desc`, co.`course_code`, co.`course_name`
     FROM `classes` cl
     JOIN `courses` co ON (co.`course_code`=cl.`course_code`)
     JOIN `users` u ON (
       (u.`user_level`='U' AND EXISTS (
         SELECT 1 FROM `courses_users` cu
         WHERE cu.`course_code`=co.`course_code` AND cu.`user_id`=u.`user_id`
       )) OR (u.`user_level`='T' AND u.`user_id`=cl.`user_id`)
     )
     JOIN `notification_preferences` p ON (p.`user_id`=u.`user_id` AND p.`class_reminder`=1)
     LEFT JOIN `attendance_holidays` h ON (h.`holiday_date`=DATE(cl.`class_date`))
     WHERE cl.`class_status`='scheduled' AND h.`holiday_date` IS NULL
     AND cl.`class_date`>NOW() AND cl.`class_date`<=DATE_ADD(NOW(), INTERVAL 24 HOUR)"
  );
  $reminderCandidates = $db->stmt->fetchAll();
  foreach ($reminderCandidates as $row) {
    $key = "class-reminder:".$row["class_id"].":".$row["user_id"].":".
      date("YmdHi", strtotime($row["class_date"]));
    $when = date("Y-m-d H:i", strtotime($row["class_date"]));
    $subject = "Upcoming class: ".$row["course_code"];
    $body = "<p>Hello ".htmlText($row["user_name"]).",</p><p><strong>".
      htmlText($row["course_name"])."</strong> is scheduled for ".$when.".</p>";
    if ($row["class_desc"]!=="") {
      $body .= "<p>".htmlText($row["class_desc"])."</p>";
    }
    queueNotification($row["user_id"], "class_reminder", $key, $subject, $body);
  }

  $db->query(
    "SELECT r.`request_id`, r.`user_id`, r.`request_status`, r.`decision_note`,
            r.`decided_at`, u.`user_name`, co.`course_code`, co.`course_name`
     FROM `attendance_corrections` r
     JOIN `users` u ON (u.`user_id`=r.`user_id` AND u.`user_level`='U')
     JOIN `classes` cl ON (cl.`class_id`=r.`class_id`)
     JOIN `courses` co ON (co.`course_code`=cl.`course_code`)
     JOIN `notification_preferences` p ON (p.`user_id`=u.`user_id` AND p.`correction_decision`=1)
     WHERE r.`request_status` IN ('approved','rejected')
     AND r.`decided_at`>=DATE_SUB(NOW(), INTERVAL 7 DAY)"
  );
  $correctionCandidates = $db->stmt->fetchAll();
  foreach ($correctionCandidates as $row) {
    $decision = $row["request_status"];
    $key = "correction-decision:".$row["request_id"];
    $subject = "Attendance correction request ".$decision;
    $body = "<p>Hello ".htmlText($row["user_name"]).",</p><p>Your attendance correction request for <strong>".
      htmlText($row["course_code"])." — ".htmlText($row["course_name"])."</strong> was ".
      $decision.".</p><p>Teacher note: ".htmlText($row["decision_note"] ?? "")."</p>";
    queueNotification($row["user_id"], "correction_decision", $key, $subject, $body);
  }

  if ($dryRun) {
    $counts = $db->fetchAll(
      "SELECT `delivery_status`, COUNT(*) AS `total`
       FROM `notification_outbox` GROUP BY `delivery_status`"
    ) ?? [];
    echo "Notification dry run complete. Eligible events: $discovered; queue status totals: ";
    echo json_encode($counts, JSON_INVALID_UTF8_SUBSTITUTE)."\n";
    exit(0);
  }

  $db->query(
    "UPDATE `notification_outbox`
     SET `delivery_status`=IF(`attempts`>=5,'failed','pending'), `claimed_at`=NULL
     WHERE `delivery_status`='sending' AND `claimed_at`<DATE_SUB(NOW(), INTERVAL 15 MINUTE)"
  );
  $db->start();
  $db->query(
    "SELECT * FROM `notification_outbox`
     WHERE `delivery_status`='pending' AND `available_at`<=NOW() AND `attempts`<5
     ORDER BY `notification_id` LIMIT 50 FOR UPDATE"
  );
  $batch = $db->stmt->fetchAll();
  foreach ($batch as $message) {
    $db->update(
      "notification_outbox",
      ["delivery_status", "claimed_at", "attempts"],
      "`notification_id`=?",
      ["sending", date("Y-m-d H:i:s"), (int)$message["attempts"]+1, $message["notification_id"]]
    );
  }
  $db->end();

  $sent = 0; $failed = 0;
  foreach ($batch as $message) {
    $preferenceColumn = [
      "attendance_warning" => "attendance_warning",
      "correction_decision" => "correction_decision",
      "class_reminder" => "class_reminder"
    ][$message["category"]] ?? null;
    if ($preferenceColumn===null) {
      throw new RuntimeException("Notification queue contains an unknown category.");
    }
    $currentPreference = $db->fetchCol(
      "SELECT `".$preferenceColumn."` FROM `notification_preferences`
       WHERE `user_id`=?",
      [$message["user_id"]]
    );
    if ((int)$currentPreference!==1) {
      $db->update(
        "notification_outbox", ["delivery_status", "last_error"],
        "`notification_id`=?", ["cancelled", "Preference disabled before delivery.", $message["notification_id"]]
      );
      continue;
    }
    $subject = preg_replace('/[\r\n]+/', " ", $message["subject"]);
    if ($_CORE->Mail->send([
      "to" => $message["recipient_email"], "subject" => $subject, "body" => $message["body"]
    ])) {
      $db->update(
        "notification_outbox", ["delivery_status", "sent_at", "claimed_at", "last_error"],
        "`notification_id`=?", ["sent", date("Y-m-d H:i:s"), null, null, $message["notification_id"]]
      );
      $sent++;
    } else {
      $error = $_CORE->Mail->error ?: "Email delivery failed.";
      $status = (int)$message["attempts"]+1>=5 ? "failed" : "pending";
      $db->update(
        "notification_outbox",
        ["delivery_status", "available_at", "claimed_at", "last_error"],
        "`notification_id`=?",
        [$status, date("Y-m-d H:i:s", time()+900), null, substr($error, 0, 500), $message["notification_id"]]
      );
      error_log("Notification delivery failed for queue item ".$message["notification_id"].": ".$error);
      $failed++;
    }
  }
  echo "Queued and processed notifications. Sent: $sent; failed or retried: $failed.\n";
} catch (Throwable $ex) {
  error_log("Notification worker failed: ".$ex->getMessage());
  fwrite(STDERR, "Notification worker failed: ".$ex->getMessage()."\n");
  exit(1);
}
