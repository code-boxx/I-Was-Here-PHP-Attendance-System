<?php
class Classes extends Core {
  // (A) ADD OR UPDATE CLASS
  //  $code : course code
  //  $uid : user id (teacher in charge)
  //  $date : class date
  //  $desc : class description
  //  $id : class id (for edit only)
  function save ($code, $uid, $date, $desc=null, $id=null, $reason=null, $room=null, $group=null) {
    if (is_string($date) && preg_match('/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}$/D', $date)) {
      $date .= ":00";
    }
    $parsedDate = is_string($date) ? DateTimeImmutable::createFromFormat("!Y-m-d H:i:s", $date) : false;
    if (!$parsedDate || $parsedDate->format("Y-m-d H:i:s")!==$date) {
      $this->error = "Class date and time are invalid.";
      return false;
    }
    $course = $this->DB->fetch(
      "SELECT `course_start`, `course_end` FROM `courses` WHERE `course_code`=?",
      [$code]
    );
    if (
      !is_array($course) || $date<($course["course_start"]." 00:00:00") ||
      $date>($course["course_end"]." 23:59:59")
    ) {
      $this->error = "Class date must fall within the course dates.";
      return false;
    }
    if ($this->isHolidayDate(substr($date, 0, 10))) {
      $this->error = "Classes cannot be scheduled on a school holiday or non-instruction day.";
      return false;
    }
    $room = $this->validLocation($room);
    $group = $this->validLocation($group);
    if ($room===false || $group===false) { return false; }
    $teacher = $this->DB->fetchCol(
      "SELECT 1 FROM `courses_users` cu
       JOIN `users` u ON (u.`user_id`=cu.`user_id`)
       WHERE cu.`course_code`=? AND cu.`user_id`=? AND u.`user_level` IN ('A','T')",
      [$code, $uid]
    );
    if ($teacher===null) {
      $this->error = "Select a teacher assigned to this course.";
      return false;
    }
    // (A1) DATA SETUP
    $fields = ["course_code", "user_id", "class_date", "class_desc", "class_room", "class_group"];
    $data = [$code, $uid, $date, $desc, $room ?: null, $group ?: null];

    $this->DB->start();
    try {
      $this->DB->fetchCol(
        "SELECT `user_id` FROM `users` WHERE `user_id`=? FOR UPDATE",
        [$uid]
      );
      $sql = "SELECT `class_id`, DATE_FORMAT(`class_date`, '".DT_LONG."') AS `conflict_date`
              FROM `classes`
              WHERE `user_id`=? AND `class_date`<DATE_ADD(?, INTERVAL 1 HOUR)
              AND DATE_ADD(`class_date`, INTERVAL 1 HOUR)>?
              AND `class_status`='scheduled'";
      $params = [$uid, $date, $date];
      $resourceConditions = [];
      $resourceParams = [];
      if ($room) {
        $resourceConditions[] = "`class_room`=?";
        $resourceParams[] = $room;
      }
      if ($group) {
        $resourceConditions[] = "`class_group`=?";
        $resourceParams[] = $group;
      }
      if (count($resourceConditions)>0) {
        $sql .= " AND (".implode(" OR ", $resourceConditions).")";
        $params = array_merge($params, $resourceParams);
      }
      if ($id!==null && $id!=="") {
        $sql .= " AND `class_id`!=?";
        $params[] = $id;
      }
      $conflict = $this->DB->fetch($sql." LIMIT 1", $params);
      if (is_array($conflict)) {
        $this->DB->end(false);
        $this->error = "Teacher, room, or student group already has a scheduled class overlapping this 60-minute session (class ".$conflict["class_id"]." at ".$conflict["conflict_date"].").";
        return false;
      }

      // (A2) ADD/UPDATE CLASS
      if ($id==null) {
        $fields[] = "class_hash";
        $data[] = $this->Core->random(12);
        $this->DB->insert("classes", $fields, $data);
      } else {
        $previous = $this->DB->fetch(
          "SELECT `class_date`, `class_status` FROM `classes` WHERE `class_id`=? FOR UPDATE",
          [$id]
        );
        if (!is_array($previous)) {
          $this->DB->end(false);
          $this->error = "Class was not found.";
          return false;
        }
        if ($previous["class_date"]!==$date) {
          $reason = $this->validScheduleReason($reason);
          if ($reason===false) {
            $this->DB->end(false);
            $this->error = "Provide a reason when changing a class date.";
            return false;
          }
          $this->DB->insert(
            "attendance_schedule_audit",
            ["class_id", "event_type", "old_value", "new_value", "reason", "changed_by", "changed_at"],
            [$id, "class_rescheduled", $previous["class_date"], $date, $reason,
             $_SESSION["user"]["user_id"], date("Y-m-d H:i:s")]
          );
          $changedAt = date("Y-m-d H:i:s");
          $this->DB->update(
            "attendance_sessions",
            ["session_status", "closed_at"],
            "`class_id`=? AND `session_status`='open'",
            ["closed", $changedAt, $id]
          );
          $this->DB->query(
            "UPDATE `notification_outbox` SET `delivery_status`='cancelled'
             WHERE `event_key` LIKE ? AND `delivery_status`='pending'",
            ["class-reminder:".$id.":%"]
          );
          if ($previous["class_status"]==="scheduled") {
            $this->queueClassChangeNotification($id, "class_rescheduled", $date, $changedAt);
          }
        }
        $data[] = $id;
        $this->DB->update("classes", $fields, "`class_id`=?", $data);
      }
      $this->DB->end();
    } catch (Throwable $ex) {
      if ($this->DB->pdo->inTransaction()) { $this->DB->end(false); }
      throw $ex;
    }
    return true;
  }

  private function validScheduleReason ($reason) {
    $reason = trim((string)$reason);
    $length = preg_match_all('/./us', $reason, $characters);
    return $length!==false && $length>0 && $length<=500 ? $reason : false;
  }

  private function validLocation ($value) {
    $value = trim((string)$value);
    if ($value==="") { return ""; }
    $length = preg_match_all('/./us', $value, $characters);
    if ($length===false || $length>100) {
      $this->error = "Room and student group names must be 100 characters or fewer.";
      return false;
    }
    return $value;
  }

  private function isHolidayDate ($date) {
    return $this->DB->fetchCol(
      "SELECT 1 FROM `attendance_holidays` WHERE `holiday_date`=?",
      [$date]
    ) !== null;
  }

  private function canManageSchedule ($classId) {
    if (!isset($_SESSION["user"])) { return false; }
    if (($_SESSION["user"]["user_level"] ?? "")==="A") {
      return $this->canManageClass($classId);
    }
    return ($_SESSION["user"]["user_level"] ?? "")==="T" &&
      $this->DB->fetchCol(
        "SELECT 1 FROM `classes` WHERE `class_id`=? AND `user_id`=?",
        [$classId, $_SESSION["user"]["user_id"]]
      ) !== null;
  }

  private function queueClassChangeNotification ($classId, $event, $date, $changedAt) {
    $class = $this->DB->fetch(
      "SELECT cl.`class_id`, co.`course_code`, co.`course_name`
       FROM `classes` cl JOIN `courses` co USING (`course_code`)
       WHERE cl.`class_id`=?",
      [$classId]
    );
    if (!is_array($class)) { return; }
    $subject = $event==="class_cancelled"
      ? "Class cancelled: ".$class["course_code"]
      : "Class rescheduled: ".$class["course_code"];
    $body = $event==="class_cancelled"
      ? "<p>The class <strong>".htmlspecialchars($class["course_name"], ENT_QUOTES | ENT_SUBSTITUTE, "UTF-8")."</strong> scheduled for ".htmlspecialchars($date, ENT_QUOTES | ENT_SUBSTITUTE, "UTF-8")." has been cancelled.</p>"
      : "<p>The class <strong>".htmlspecialchars($class["course_name"], ENT_QUOTES | ENT_SUBSTITUTE, "UTF-8")."</strong> has been rescheduled to ".htmlspecialchars($date, ENT_QUOTES | ENT_SUBSTITUTE, "UTF-8").".</p>";
    $this->DB->query(
      "INSERT IGNORE INTO `notification_outbox`
         (`user_id`,`category`,`event_key`,`recipient_email`,`subject`,`body`,`available_at`)
       SELECT u.`user_id`, 'class_reminder',
              CONCAT('class-change:', ?, ':', ?, ':', ?, ':', u.`user_id`),
              u.`user_email`, ?, ?, ?
       FROM `courses_users` cu
       JOIN `users` u ON (u.`user_id`=cu.`user_id` AND u.`user_level`='U')
       JOIN `notification_preferences` p ON (p.`user_id`=u.`user_id` AND p.`class_reminder`=1)
       WHERE cu.`course_code`=?",
      [$event, $classId, str_replace([" ", ":"], ["-", ""], $changedAt),
       $subject, $body, $changedAt, $class["course_code"]]
    );
  }

  function cancelClass ($classId, $reason) {
    if (!$this->canManageSchedule($classId)) {
      $this->error = "No access permission";
      return false;
    }
    $reason = $this->validScheduleReason($reason);
    if ($reason===false) {
      $this->error = "A cancellation reason of 1 to 500 characters is required.";
      return false;
    }
    $actor = (int)$_SESSION["user"]["user_id"];
    $now = date("Y-m-d H:i:s");
    $this->DB->start();
    try {
      $class = $this->DB->fetch(
        "SELECT `class_date`, `class_status` FROM `classes`
         WHERE `class_id`=? FOR UPDATE",
        [$classId]
      );
      if (!is_array($class)) {
        $this->DB->end(false);
        $this->error = "Class was not found.";
        return false;
      }
      if ($class["class_status"]==="cancelled") {
        $this->DB->end(false);
        $this->error = "Class is already cancelled.";
        return false;
      }
      $this->DB->update(
        "classes",
        ["class_status", "class_status_reason", "class_status_by", "class_status_at"],
        "`class_id`=?",
        ["cancelled", $reason, $actor, $now, $classId]
      );
      $this->DB->update(
        "attendance_sessions",
        ["session_status", "closed_at"],
        "`class_id`=? AND `session_status`='open'",
        ["closed", $now, $classId]
      );
      $this->DB->insert(
        "attendance_schedule_audit",
        ["class_id", "event_type", "old_value", "new_value", "reason", "changed_by", "changed_at"],
        [$classId, "class_cancelled", $class["class_date"], null, $reason, $actor, $now]
      );
      $this->DB->query(
        "UPDATE `notification_outbox` SET `delivery_status`='cancelled'
         WHERE `event_key` LIKE ? AND `delivery_status`='pending'",
        ["class-reminder:".$classId.":%"]
      );
      $this->queueClassChangeNotification($classId, "class_cancelled", $class["class_date"], $now);
      $this->DB->end();
      return true;
    } catch (Throwable $ex) {
      if ($this->DB->pdo->inTransaction()) { $this->DB->end(false); }
      throw $ex;
    }
  }

  function rescheduleClass ($classId, $date, $reason) {
    if (!$this->canManageSchedule($classId)) {
      $this->error = "No access permission";
      return false;
    }
    if (is_string($date) && preg_match('/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}$/D', $date)) {
      $date = str_replace("T", " ", $date).":00";
    }
    $parsedDate = is_string($date) ? DateTimeImmutable::createFromFormat("!Y-m-d H:i:s", $date) : false;
    $reason = $this->validScheduleReason($reason);
    if (!$parsedDate || $parsedDate->format("Y-m-d H:i:s")!==$date || $reason===false) {
      $this->error = "A valid new date and a rescheduling reason are required.";
      return false;
    }
    if ($this->isHolidayDate(substr($date, 0, 10))) {
      $this->error = "Classes cannot be rescheduled to a school holiday or non-instruction day.";
      return false;
    }
    $actor = (int)$_SESSION["user"]["user_id"];
    $now = date("Y-m-d H:i:s");
    $this->DB->start();
    try {
      $class = $this->DB->fetch(
        "SELECT cl.`class_date`, cl.`class_status`, cl.`user_id`, cl.`class_room`,
                cl.`class_group`, co.`course_start`, co.`course_end`
         FROM `classes` cl JOIN `courses` co USING (`course_code`)
         WHERE cl.`class_id`=? FOR UPDATE",
        [$classId]
      );
      if (!is_array($class)) {
        $this->DB->end(false);
        $this->error = "Class was not found.";
        return false;
      }
      if (
        $date<($class["course_start"]." 00:00:00") ||
        $date>($class["course_end"]." 23:59:59")
      ) {
        $this->DB->end(false);
        $this->error = "Rescheduled class must fall within the course dates.";
        return false;
      }
      if ($class["class_date"]===$date && $class["class_status"]==="scheduled") {
        $this->DB->end(false);
        $this->error = "Choose a different date or time.";
        return false;
      }
      $conflictSql = "SELECT `class_id` FROM `classes`
        WHERE `class_id`!=? AND `class_status`='scheduled'
        AND `class_date`<DATE_ADD(?, INTERVAL 1 HOUR)
        AND DATE_ADD(`class_date`, INTERVAL 1 HOUR)>?
        AND (`user_id`=?";
      $conflictParams = [$classId, $date, $date, $class["user_id"]];
      if (!empty($class["class_room"])) {
        $conflictSql .= " OR `class_room`=?";
        $conflictParams[] = $class["class_room"];
      }
      if (!empty($class["class_group"])) {
        $conflictSql .= " OR `class_group`=?";
        $conflictParams[] = $class["class_group"];
      }
      $conflict = $this->DB->fetch($conflictSql.") LIMIT 1", $conflictParams);
      if (is_array($conflict)) {
        $this->DB->end(false);
        $this->error = "The teacher, room, or student group already has a class overlapping this 60-minute session.";
        return false;
      }
      $this->DB->update(
        "classes",
        ["class_date", "class_status", "class_status_reason", "class_status_by", "class_status_at"],
        "`class_id`=?",
        [$date, "scheduled", null, $actor, $now, $classId]
      );
      $this->DB->update(
        "attendance_sessions",
        ["session_status", "closed_at"],
        "`class_id`=? AND `session_status`='open'",
        ["closed", $now, $classId]
      );
      $this->DB->insert(
        "attendance_schedule_audit",
        ["class_id", "event_type", "old_value", "new_value", "reason", "changed_by", "changed_at"],
        [$classId, "class_rescheduled", $class["class_date"], $date, $reason, $actor, $now]
      );
      $this->DB->query(
        "UPDATE `notification_outbox` SET `delivery_status`='cancelled'
         WHERE `event_key` LIKE ? AND `delivery_status`='pending'",
        ["class-reminder:".$classId.":%"]
      );
      $this->queueClassChangeNotification($classId, "class_rescheduled", $date, $now);
      $this->DB->end();
      return true;
    } catch (Throwable $ex) {
      if ($this->DB->pdo->inTransaction()) { $this->DB->end(false); }
      throw $ex;
    }
  }

  function getScheduleAudit ($classId) {
    if (!$this->canManageClass($classId)) {
      $this->error = "No access permission";
      return false;
    }
    return $this->DB->fetchAll(
      "SELECT a.*, COALESCE(u.`user_name`, CONCAT('Deleted account #', a.`changed_by`)) AS `actor_name`,
              COALESCE(uu.`user_name`, CONCAT('Deleted account #', a.`undone_by`)) AS `undo_actor_name`
       FROM `attendance_schedule_audit` a
       LEFT JOIN `users` u ON (u.`user_id`=a.`changed_by`)
       LEFT JOIN `users` uu ON (uu.`user_id`=a.`undone_by`)
       WHERE a.`class_id`=? ORDER BY a.`changed_at` DESC",
      [$classId]
    ) ?? [];
  }

  function undoScheduleChange ($eventId, $reason) {
    if (!isset($_SESSION["user"]["user_id"])) {
      $this->error = "Sign in before undoing a schedule change.";
      return false;
    }
    $reason = $this->validScheduleReason($reason);
    if ($reason===false || (!is_int($eventId) && !(is_string($eventId) && ctype_digit($eventId)))) {
      $this->error = "Select a valid schedule change and provide a reason.";
      return false;
    }
    $this->DB->start();
    try {
      $event = $this->DB->fetch(
        "SELECT * FROM `attendance_schedule_audit` WHERE `event_id`=? FOR UPDATE",
        [(int)$eventId]
      );
      if (!is_array($event) || $event["undone_at"]!==null ||
          !in_array($event["event_type"], ["class_rescheduled", "class_cancelled"], true) ||
          !$this->canManageSchedule($event["class_id"])) {
        $this->DB->end(false);
        $this->error = "This schedule change is missing, unsupported, or already undone.";
        return false;
      }
      $class = $this->DB->fetch(
        "SELECT cl.*, co.`course_start`, co.`course_end`
         FROM `classes` cl JOIN `courses` co USING (`course_code`)
         WHERE cl.`class_id`=? FOR UPDATE",
        [$event["class_id"]]
      );
      $restoreDate = $event["old_value"];
      if (!is_array($class) || !$restoreDate ||
          ($event["event_type"]==="class_rescheduled" && $class["class_date"]!==$event["new_value"]) ||
          ($event["event_type"]==="class_cancelled" && $class["class_status"]!=="cancelled")) {
        $this->DB->end(false);
        $this->error = "The class changed after this audit entry. Undo the later change first.";
        return false;
      }
      $conflictSql = "SELECT `class_id` FROM `classes`
        WHERE `class_id`!=? AND `class_status`='scheduled'
        AND `class_date`<DATE_ADD(?, INTERVAL 1 HOUR)
        AND DATE_ADD(`class_date`, INTERVAL 1 HOUR)>?
        AND (`user_id`=?";
      $conflictParams = [$event["class_id"], $restoreDate, $restoreDate, $class["user_id"]];
      if (!empty($class["class_room"])) {
        $conflictSql .= " OR `class_room`=?";
        $conflictParams[] = $class["class_room"];
      }
      if (!empty($class["class_group"])) {
        $conflictSql .= " OR `class_group`=?";
        $conflictParams[] = $class["class_group"];
      }
      if (is_array($this->DB->fetch($conflictSql.") LIMIT 1", $conflictParams))) {
        $this->DB->end(false);
        $this->error = "The original schedule now conflicts with another class.";
        return false;
      }
      $now = date("Y-m-d H:i:s");
      $this->DB->update(
        "classes",
        ["class_date", "class_status", "class_status_reason", "class_status_by", "class_status_at"],
        "`class_id`=?",
        [$restoreDate, "scheduled", null, $_SESSION["user"]["user_id"], $now, $event["class_id"]]
      );
      $this->DB->update(
        "attendance_schedule_audit",
        ["undone_by", "undone_at", "undo_reason"],
        "`event_id`=?",
        [$_SESSION["user"]["user_id"], $now, $reason, (int)$eventId]
      );
      $this->DB->end();
    } catch (Throwable $ex) {
      if ($this->DB->pdo->inTransaction()) { $this->DB->end(false); }
      throw $ex;
    }
    return true;
  }

  // (B) IMPORT CLASS
  //  $code : course code
  //  $date : class date & time
  //  $email : teacher's email
  //  $desc : description, optional
  function import ($code, $date, $email, $desc, $room=null, $group=null) {
    if (is_string($date) && preg_match('/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}$/D', $date)) {
      $date .= ":00";
    }
    // (B1) CHECK - COURSE CODE
    $this->Core->load("Courses");
    $course = $this->Courses->get($code);
    if (!is_array($course)) {
      $this->error = "Invalid course - $code";
      return false;
    }

    // (B2) CHECK - CLASS DATE
    $udate = strtotime($date);
    if ($udate<strtotime("{$course["course_start"]} 00:00:00") || $udate>strtotime("{$course["course_end"]} 23:59:59")) {
      $this->error = "Class date does not fall within course period";
      return false;
    }

    // (B3) CHECK TEACHER
    $teacher = $this->DB->fetch(
      "SELECT * FROM `courses_users` c
      LEFT JOIN `users` u USING (`user_id`)
      WHERE c.`course_code`=? AND u.`user_email`=?",
      [$course["course_code"], $email]
    );
    if (!is_array($teacher)) {
      $this->error = "$email is not a teacher of this course";
      return false;
    }
    if ($teacher["user_level"]!="T" && $teacher["user_level"]!="A") {
      $this->error = "$email is not a teacher of this course";
      return false;
    }

    // (B4) IMPORT CLASS
    return $this->save($course["course_code"], $teacher["user_id"], $date, $desc, null, null, $room, $group);
  }

  function previewImport ($rows) {
    if (!is_string($rows)) {
      $this->error = "Import preview rows are invalid.";
      return false;
    }
    try { $rows = json_decode($rows, true, 512, JSON_THROW_ON_ERROR); }
    catch (JsonException $ex) {
      $this->error = "Import preview rows are invalid JSON.";
      return false;
    }
    if (!is_array($rows) || count($rows)==0 || count($rows)>500) {
      $this->error = "Preview between 1 and 500 class rows.";
      return false;
    }

    $this->Core->load("Courses");
    $result = [];
    $seen = [];
    foreach ($rows as $row) {
      if (!is_array($row)) {
        $result[] = ["status" => "conflicting", "message" => "Invalid row data."];
        continue;
      }
      $code = trim((string)($row["code"] ?? ""));
      $date = trim((string)($row["date"] ?? ""));
      $email = strtolower(trim((string)($row["email"] ?? "")));
      $desc = trim((string)($row["desc"] ?? ""));
      $room = trim((string)($row["room"] ?? ""));
      $group = trim((string)($row["group"] ?? ""));
      if (preg_match('/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}$/D', $date)) { $date .= ":00"; }
      $parsed = DateTimeImmutable::createFromFormat("!Y-m-d H:i:s", $date);
      if ($code==="" || !filter_var($email, FILTER_VALIDATE_EMAIL)
          || !$parsed || $parsed->format("Y-m-d H:i:s")!==$date) {
        $result[] = ["status" => "conflicting", "message" => "Course code, teacher email, and valid class date/time are required."];
        continue;
      }

      $course = $this->Courses->get($code);
      if (!is_array($course) || $date<($course["course_start"]." 00:00:00")
          || $date>($course["course_end"]." 23:59:59")) {
        $result[] = ["status" => "conflicting", "message" => "Course is missing or class date falls outside the course period."];
        continue;
      }
      if ($this->isHolidayDate(substr($date, 0, 10))) {
        $result[] = ["status" => "conflicting", "message" => "Classes cannot be scheduled on a school holiday or non-instruction day."];
        continue;
      }
      $teacher = $this->DB->fetch(
        "SELECT u.`user_id`
         FROM `courses_users` cu JOIN `users` u USING (`user_id`)
         WHERE cu.`course_code`=? AND u.`user_email`=? AND u.`user_level` IN ('A','T')",
        [$course["course_code"], $email]
      );
      if (!is_array($teacher)) {
        $result[] = ["status" => "conflicting", "message" => "Teacher is not assigned to this course."];
        continue;
      }

      $key = strtolower($course["course_code"]).":".$teacher["user_id"].":".$date;
      if (isset($seen[$key])) {
        $same = $seen[$key]===$desc;
        $result[] = [
          "status" => $same ? "duplicate" : "conflicting",
          "message" => $same
            ? "Duplicate class row; it will not be imported."
            : "This class schedule has conflicting descriptions in another row."
        ];
        continue;
      }
      $seen[$key] = $desc;

      $existing = $this->DB->fetch(
        "SELECT `class_desc`,`class_room`,`class_group` FROM `classes`
         WHERE `course_code`=? AND `user_id`=? AND `class_date`=?
         AND `class_status`='scheduled' LIMIT 1",
        [$course["course_code"], $teacher["user_id"], $date]
      );
      if (is_array($existing)) {
        $same = (string)($existing["class_desc"] ?? "")===$desc
          && (string)($existing["class_room"] ?? "")===$room
          && (string)($existing["class_group"] ?? "")===$group;
        $result[] = $same
          ? ["status" => "unchanged", "message" => "This scheduled class already exists."]
          : ["status" => "conflicting", "message" => "A class at this time already exists with a different description."];
        continue;
      }

      $conflictSql = "SELECT 1 FROM `classes`
         WHERE `class_date`<DATE_ADD(?, INTERVAL 1 HOUR)
         AND DATE_ADD(`class_date`, INTERVAL 1 HOUR)>?
         AND `class_status`='scheduled'
         AND (`user_id`=?";
      $conflictParams = [$date, $date, $teacher["user_id"]];
      if ($room!=="") {
        $conflictSql .= " OR `class_room`=?";
        $conflictParams[] = $room;
      }
      if ($group!=="") {
        $conflictSql .= " OR `class_group`=?";
        $conflictParams[] = $group;
      }
      $conflictSql .= ")";
      $conflict = $this->DB->fetchCol(
        $conflictSql." LIMIT 1", $conflictParams
      );
      $result[] = $conflict!==null
        ? ["status" => "conflicting", "message" => "Teacher has another scheduled class within the 60-minute conflict window."]
        : ["status" => "new", "message" => "New class will be scheduled."];
    }
    return $result;
  }

  // (C) DELETE CLASS
  //  $id : class id
  function del ($id) {
    $this->DB->start();
    $this->DB->delete("attendance", "`class_id`=?", [$id]);
    $this->DB->delete("attendance_corrections", "`class_id`=?", [$id]);
    $this->DB->delete("attendance_sessions", "`class_id`=?", [$id]);
    $this->DB->delete("attendance_audit", "`class_id`=?", [$id]);
    $this->DB->delete("attendance_schedule_audit", "`class_id`=?", [$id]);
    $this->DB->delete("attendance_offline_queue", "`class_id`=?", [$id]);
    $this->DB->delete("class_substitutes", "`class_id`=?", [$id]);
    $this->DB->delete("classes", "`class_id`=?", [$id]);
    $this->DB->end();
    return true;
  }

  // (D) GET CLASS
  //  $id : class id
  function get ($id) {
    $class = $this->DB->fetch(
      "SELECT cl.*, DATE_FORMAT(cl.`class_date`, '".DT_LONG."') `cd`,
              co.`course_code`, co.`course_name`, u.`user_name`, h.`holiday_name`,
              (h.`holiday_date` IS NOT NULL) AS `is_holiday`
       FROM `classes` cl 
       LEFT JOIN `courses` co USING (`course_code`)
       LEFT JOIN `users` u USING (`user_id`)
       LEFT JOIN `attendance_holidays` h ON (h.`holiday_date`=DATE(cl.`class_date`))
       WHERE cl.`class_id`=?", [$id]
    );
    if (is_array($class) && !$this->canManageClass($id)) { return null; }
    return $class;
  }

  function canManageClass ($id) {
    if (!isset($_SESSION["user"])) { return false; }
    $uid = (int)($_SESSION["user"]["user_id"] ?? 0);
    if ($_SESSION["user"]["user_level"]=="A") {
      return $this->DB->fetchCol(
        "SELECT 1 FROM `classes` WHERE `class_id`=?", [$id]
      ) !== null;
    }
    if ($_SESSION["user"]["user_level"]=="T") {
      $owned = $this->DB->fetchCol(
        "SELECT 1 FROM `classes` WHERE `class_id`=? AND `user_id`=?",
        [$id, $uid]
      );
      if ($owned !== null) { return true; }
      return $this->DB->fetchCol(
        "SELECT 1 FROM `class_substitutes`
         WHERE `class_id`=? AND `user_id`=? AND `expires_at`>NOW()",
        [$id, $uid]
      ) !== null;
    }
    return false;
  }

  private function canManageSubstitutes ($classId) {
    if (!isset($_SESSION["user"])) { return false; }
    if (($_SESSION["user"]["user_level"] ?? "")==="A") { return $this->canManageClass($classId); }
    return $this->DB->fetchCol(
      "SELECT 1 FROM `classes` WHERE `class_id`=? AND `user_id`=?",
      [$classId, $_SESSION["user"]["user_id"]]
    ) !== null;
  }

  function addSubstitute ($classId, $userId, $expiresAt) {
    if (!isset($_SESSION["user"])) {
      $this->error = "No access permission";
      return false;
    }
    if (!$this->canManageSubstitutes($classId)) {
      $this->error = "No access permission";
      return false;
    }
    if (is_string($expiresAt) && preg_match('/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}$/D', $expiresAt)) {
      $expiresAt = str_replace("T", " ", $expiresAt).":00";
    }
    $parsedExpiry = is_string($expiresAt)
      ? DateTimeImmutable::createFromFormat("!Y-m-d H:i:s", $expiresAt)
      : false;
    $class = $this->DB->fetch(
      "SELECT `class_date` FROM `classes` WHERE `class_id`=?",
      [$classId]
    );
    if (
      !$parsedExpiry || $parsedExpiry->format("Y-m-d H:i:s")!==$expiresAt ||
      !is_array($class) || $expiresAt<=date("Y-m-d H:i:s") ||
      $expiresAt>date("Y-m-d H:i:s", strtotime($class["class_date"]." +12 hours"))
    ) {
      $this->error = "Substitute access must expire in the future and no later than 12 hours after class start.";
      return false;
    }
    $user = $this->DB->fetch(
      "SELECT `user_id`, `user_level` FROM `users` WHERE `user_id`=?",
      [$userId]
    );
    if (!is_array($user) || !in_array($user["user_level"], ["A", "T"], true)) {
      $this->error = "Only teachers and admins can be assigned as substitutes.";
      return false;
    }
    $this->DB->replace(
      "class_substitutes",
      ["class_id", "user_id", "created_by", "created_at", "expires_at"],
      [$classId, $userId, $_SESSION["user"]["user_id"], date("Y-m-d H:i:s"), $expiresAt]
    );
    return true;
  }

  function delSubstitute ($classId, $userId) {
    if (!isset($_SESSION["user"])) {
      $this->error = "No access permission";
      return false;
    }
    if (!$this->canManageSubstitutes($classId)) {
      $this->error = "No access permission";
      return false;
    }
    $this->DB->delete(
      "class_substitutes",
      "`class_id`=? AND `user_id`=?",
      [$classId, $userId]
    );
    return $this->DB->lastRows > 0;
  }

  function getSubstitutes ($classId) {
    if (!isset($_SESSION["user"])) { return null; }
    if (!$this->canManageSubstitutes($classId)) {
      $this->error = "No access permission";
      return false;
    }
    return $this->DB->fetchAll(
      "SELECT s.`user_id`, u.`user_name`, u.`user_email`, s.`expires_at`
       FROM `class_substitutes` s
       JOIN `users` u USING (`user_id`)
       WHERE s.`class_id`=?
       ORDER BY u.`user_name`",
      [$classId]
    ) ?? [];
  }

  // (E) GET CLASSES
  //  $search : optional, course code
  //  $page : optional, current page number
  function getAll ($search=null, $page=null) {
    // (E1) PARTIAL SQL
    $sql = " FROM `classes` cl
            LEFT JOIN `courses` co USING (`course_code`)
            LEFT JOIN `users` u USING (`user_id`)
            LEFT JOIN `attendance_holidays` h ON (h.`holiday_date`=DATE(cl.`class_date`))";
    $data = null;
    if ($search != null) {
      $sql .= " WHERE co.`course_code` LIKE ?";
      $data = ["%$search%"];
    }

    // (E2) PAGINATION
    if ($page != null) {
      $this->Core->paginator($this->DB->fetchCol(
        "SELECT COUNT(*) $sql", $data
      ), $page);
    }

    // (E3) RESULT
    $sql .= " ORDER BY `class_date` DESC";
    if ($page != null) { $sql .= $this->Core->page["lim"]; }
    return $this->DB->fetchAll(
      "SELECT cl.*, DATE_FORMAT(cl.`class_date`, '".DT_LONG."') `cd`,
              co.`course_code`, co.`course_name`, u.`user_name`, h.`holiday_name`,
              (h.`holiday_date` IS NOT NULL) AS `is_holiday` $sql",
      $data, "class_id"
    );
  }

  // (F) GET CLASSES FOR TEACHER
  //  $uid : user id
  //  $date : date range restriction
  //  $range : -1 for all classes before $date
  //           1 for all classes after $date
  //           0 all classes on $date
  //           "" or null for no restriction
  //  $page : current page number, optional
  function getByTeacher ($uid, $date=null, $range=null, $page=null) {
    // (F1) PARTIAL SQL
    $sql = "FROM `classes` cl
            JOIN `courses` co USING (`course_code`)
            LEFT JOIN `attendance_holidays` h ON (h.`holiday_date`=DATE(cl.`class_date`))
            WHERE `user_id`=?";
    $data = [$uid];
    if ($range=="-1") {
      $sql .= " AND `class_date`<=?";
      $data[] = "$date 23:59:59";
    }
    if ($range=="1") {
      $sql .= " AND `class_date`>=?";
      $data[] = "$date 00:00:00";
    }
    if ($range=="0") {
      $sql .= " AND `class_date` BETWEEN ? AND ?";
      $data[] = "$date 00:00:00";
      $data[] = "$date 23:59:59";
    }

    // (F2) PAGINATION
    if ($page != null) {
      $this->Core->paginator(
        $this->DB->fetchCol("SELECT COUNT(*) $sql", $data), $page
      );
    }

    // (F3) GET CLASSES
    $sql = "SELECT cl.*, DATE_FORMAT(cl.`class_date`, '".DT_LONG."') `cd`,
                   co.`course_code`, co.`course_name`, h.`holiday_name`,
                   (h.`holiday_date` IS NOT NULL) AS `is_holiday`
            $sql
            ORDER BY `class_date` ASC";
    if ($page != null) { $sql .= $this->Core->page["lim"]; }

    // (F4) RESULTS
    return $this->DB->fetchAll($sql, $data, "class_id");
  }

  // (G) GET CLASSES FOR STUDENT
  //  $uid : user id
  //  $date : date range restriction
  //  $range : -1 for all classes before $date
  //           1 for all classes after $date
  //           0 all classes on $date
  //           "" or null for no restriction
  //  $page : current page number, optional
  function getByStudent ($uid, $date=null, $range=null, $page=null) {
    // (G1) PARTIAL SQL
    $sql = "WHERE cl.`course_code` IN
            (SELECT `course_code` FROM `courses_users` WHERE `user_id`=?)";
    $data = [$uid];
    if ($range=="-1") {
      $sql .= " AND `class_date`<=?";
      $data[] = "$date 23:59:59";
    }
    if ($range=="1") {
      $sql .= " AND `class_date`>=?";
      $data[] = "$date 00:00:00";
    }
    if ($range=="0") {
      $sql .= " AND `class_date` BETWEEN ? AND ?";
      $data[] = "$date 00:00:00";
      $data[] = "$date 23:59:59";
    }

    // (G2) PAGINATION
    if ($page != null) {
      $this->Core->paginator(
        $this->DB->fetchCol("SELECT COUNT(*) FROM `classes` cl $sql", $data), $page
      );
    }

    // (G3) GET CLASSES
    $sql = "SELECT cl.*, DATE_FORMAT(cl.`class_date`, '".DT_LONG."') `cd`,
                   co.`course_code`, co.`course_name`, a.`a_status`,
                 h.`holiday_name`, (h.`holiday_date` IS NOT NULL) AS `is_holiday`,
                 cr.`request_status` AS `correction_status`
          FROM `classes` cl
          LEFT JOIN `attendance` a ON (cl.`class_id`=a.`class_id` AND a.`user_id`=?)
          LEFT JOIN `courses` co ON (cl.`course_code`=co.`course_code`)
          LEFT JOIN `attendance_holidays` h ON (h.`holiday_date`=DATE(cl.`class_date`))
            LEFT JOIN `attendance_corrections` cr ON (
              cr.`class_id`=cl.`class_id` AND cr.`user_id`=? AND
              cr.`request_id`=(
                SELECT MAX(cr2.`request_id`) FROM `attendance_corrections` cr2
                WHERE cr2.`class_id`=cl.`class_id` AND cr2.`user_id`=?
              )
            )
            $sql  ORDER BY `class_date` ASC";
    if ($page != null) { $sql .= $this->Core->page["lim"]; }
    array_unshift($data, $uid, $uid, $uid);

    // (G4) RESULTS
    return $this->DB->fetchAll($sql, $data, "class_id");
  }
}