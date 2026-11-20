<?php
class Courses extends Core {
  // (A) ADD OR UPDATE COURSE
  //  $code : course code
  //  $name : course name
  //  $start : start date
  //  $end : end date
  //  $desc : course description
  //  $ocode : old course code (edit only)
  function save ($code, $name, $start, $end, $desc=null, $ocode=null, $reason=null) {
    // (A1) DATA SETUP
    if (strtotime($end) < strtotime($start)) {
      $this->error = "End date cannot be earlier than start";
      return false;
    }
    $fields = ["course_code", "course_name", "course_start", "course_end", "course_desc"];
    $data = [$code, $name, $start, $end, $desc];

    if (!isset($_SESSION["user"]["user_id"])) {
      $this->error = "An authenticated actor is required for course audit history.";
      return false;
    }
    $reason = $this->validCourseReason(
      $reason ?? ($ocode===null ? "Course created." : "Course updated.")
    );
    if ($reason===false) { return false; }

    // (A2) ADD/UPDATE COURSE
    $this->DB->start();
    try {
      $previous = $ocode===null ? null : $this->get($ocode);
      if ($ocode==null) {
        $this->DB->insert("courses", $fields, $data);
        $eventType = "created";
        $courseCode = $code;
      } else {
        $data[] = $ocode;
        $this->DB->update("courses", $fields, "`course_code`=?", $data);
        $eventType = "updated";
        $courseCode = $code;
      }
      $current = $this->get($courseCode);
      $this->auditCourseChange($courseCode, $eventType, $previous, $current, $reason);
      $this->DB->end();
    } catch (Throwable $ex) {
      if ($this->DB->pdo->inTransaction()) { $this->DB->end(false); }
      throw $ex;
    }
    return true;
  }

  // (B) IMPORT COURSE (OVERRIDES OLD ENTRY)
  //  $code : course code
  //  $name : course name
  //  $start : start date
  //  $end : end date
  //  $desc : course description
  function import ($code, $name, $start, $end, $desc=null) {
    // (B1) GET COURSE
    $course = $this->get($code);

    // (B2) UPDATE OR INSERT
    return $this->save($code, $name, $start, $end, $desc, is_array($course)?$course["course_code"]:null);
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
      $this->error = "Preview between 1 and 500 course rows.";
      return false;
    }

    $result = [];
    $seen = [];
    foreach ($rows as $index=>$row) {
      if (!is_array($row)) {
        $result[] = ["status" => "conflicting", "message" => "Invalid row data."];
        continue;
      }
      $code = trim((string)($row["code"] ?? ""));
      $name = trim((string)($row["name"] ?? ""));
      $desc = trim((string)($row["desc"] ?? ""));
      $start = trim((string)($row["start"] ?? ""));
      $end = trim((string)($row["end"] ?? ""));
      if ($code==="" || $name==="" || !$this->validImportDate($start) || !$this->validImportDate($end)) {
        $result[] = [
          "status" => "conflicting",
          "message" => "Course code, name, and valid YYYY-MM-DD dates are required."
        ];
        continue;
      }
      if ($end<$start) {
        $result[] = ["status" => "conflicting", "message" => "End date is earlier than start date."];
        continue;
      }

      $normalized = [$code, $name, $desc, $start, $end];
      $courseKey = strtolower($code);
      if (isset($seen[$courseKey])) {
        $same = $seen[$courseKey]===$normalized;
        $result[] = [
          "status" => $same ? "duplicate" : "conflicting",
          "message" => $same
            ? "Duplicate of an earlier row; it will not be imported."
            : "This course code has conflicting values in another row."
        ];
        continue;
      }
      $seen[$courseKey] = $normalized;

      $existing = $this->get($code);
      if (!is_array($existing)) {
        $result[] = ["status" => "new", "message" => "New course will be created."];
        continue;
      }
      $same = $existing["course_name"]===$name
        && (string)($existing["course_desc"] ?? "")===$desc
        && $existing["course_start"]===$start
        && $existing["course_end"]===$end;
      $result[] = $same
        ? ["status" => "unchanged", "message" => "Course already matches; no changes needed."]
        : ["status" => "changed", "message" => "Existing course will be updated."];
    }
    return $result;
  }

  private function validImportDate ($date) {
    $parsed = DateTime::createFromFormat("!Y-m-d", $date);
    return $parsed!==false && $parsed->format("Y-m-d")===$date;
  }

  function previewUserImport ($rows) {
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
      $this->error = "Preview between 1 and 500 course enrollment rows.";
      return false;
    }

    $this->Core->load("Users");
    $result = [];
    $seen = [];
    foreach ($rows as $row) {
      if (!is_array($row)) {
        $result[] = ["status" => "conflicting", "message" => "Invalid row data."];
        continue;
      }
      $code = trim((string)($row["code"] ?? ""));
      $uid = trim((string)($row["uid"] ?? ""));
      $course = $code==="" ? null : $this->get($code);
      $user = $uid==="" ? null : $this->Users->get($uid);
      if (!is_array($course) || !is_array($user) || $user["user_level"]==="S") {
        $result[] = ["status" => "conflicting", "message" => "Course or active user could not be found."];
        continue;
      }
      $key = strtolower($code).":".$user["user_id"];
      if (isset($seen[$key])) {
        $result[] = [
          "status" => "duplicate",
          "message" => "Duplicate enrollment row; it will not be imported."
        ];
        continue;
      }
      $seen[$key] = true;
      $enrolled = $this->DB->fetchCol(
        "SELECT 1 FROM `courses_users` WHERE `course_code`=? AND `user_id`=?",
        [$course["course_code"], $user["user_id"]]
      );
      $result[] = $enrolled!==null
        ? ["status" => "unchanged", "message" => "User is already enrolled."]
        : ["status" => "new", "message" => "User will be enrolled in the course."];
    }
    return $result;
  }

  // (C) DELETE COURSE
  //  $code : course code
  function del ($code, $reason=null) {
    if (!isset($_SESSION["user"]["user_id"])) {
      $this->error = "An authenticated actor is required for course audit history.";
      return false;
    }
    $reason = $this->validCourseReason($reason ?? "Course deleted.");
    if ($reason===false) { return false; }
    $course = $this->get($code);
    if (!is_array($course)) {
      $this->error = "Invalid course.";
      return false;
    }
    $this->DB->start();
    try {
      $this->DB->query("DELETE `attendance` FROM `attendance` LEFT JOIN `classes` USING (`class_id`) WHERE `course_code`=?", [$code]);
      $this->DB->query("DELETE `attendance_corrections` FROM `attendance_corrections` JOIN `classes` USING (`class_id`) WHERE `course_code`=?", [$code]);
      $this->DB->query("DELETE `attendance_sessions` FROM `attendance_sessions` JOIN `classes` USING (`class_id`) WHERE `course_code`=?", [$code]);
      $this->DB->query("DELETE `attendance_audit` FROM `attendance_audit` JOIN `classes` USING (`class_id`) WHERE `course_code`=?", [$code]);
      $this->DB->delete("classes", "`course_code`=?", [$code]);
      $this->DB->delete("courses_users", "`course_code`=?", [$code]);
      $this->DB->delete("courses", "`course_code`=?", [$code]);
      $this->auditCourseChange($code, "deleted", $course, null, $reason);
      $this->DB->end();
    } catch (Throwable $ex) {
      if ($this->DB->pdo->inTransaction()) { $this->DB->end(false); }
      throw $ex;
    }
    return true;
  }

  private function validCourseReason ($reason) {
    if (!is_string($reason)) {
      $this->error = "Provide a reason of 1 to 500 characters.";
      return false;
    }
    $reason = trim($reason);
    $length = preg_match_all('/./us', $reason, $characters);
    if ($length===false || $length===0 || $length>500) {
      $this->error = "Provide a reason of 1 to 500 characters.";
      return false;
    }
    return $reason;
  }

  private function auditCourseChange ($code, $event, $old, $new, $reason) {
    $this->DB->insert(
      "course_audit",
      ["course_code", "event_type", "old_values", "new_values", "reason", "changed_by", "changed_at"],
      [$code, $event, $old===null ? null : json_encode($old, JSON_THROW_ON_ERROR),
       $new===null ? null : json_encode($new, JSON_THROW_ON_ERROR), $reason,
       $_SESSION["user"]["user_id"], date("Y-m-d H:i:s")]
    );
  }

  function getCourseAudit ($code) {
    if (!is_array($this->get($code))) {
      $this->error = "Invalid course.";
      return false;
    }
    return $this->DB->fetchAll(
      "SELECT a.*, u.`user_name` AS `changed_by_name`,
              uu.`user_name` AS `undone_by_name`
       FROM `course_audit` a
       LEFT JOIN `users` u ON (u.`user_id`=a.`changed_by`)
       LEFT JOIN `users` uu ON (uu.`user_id`=a.`undone_by`)
       WHERE a.`course_code`=?
       ORDER BY a.`event_id` DESC LIMIT 50",
      [$code]
    ) ?? [];
  }

  function undoCourseChange ($eventId, $reason) {
    $reason = $this->validCourseReason($reason);
    if ($reason===false || !isset($_SESSION["user"]["user_id"])) {
      if (!isset($_SESSION["user"]["user_id"])) {
        $this->error = "Sign in before undoing a course change.";
      }
      return false;
    }
    if ((!is_int($eventId) && !(is_string($eventId) && ctype_digit($eventId))) || (int)$eventId<1) {
      $this->error = "Select a valid course change.";
      return false;
    }

    $this->DB->start();
    try {
      $event = $this->DB->fetch(
        "SELECT * FROM `course_audit` WHERE `event_id`=? FOR UPDATE",
        [(int)$eventId]
      );
      if (!is_array($event) || $event["event_type"]!=="updated" || $event["undone_at"]!==null) {
        $this->DB->end(false);
        $this->error = "This course change is missing, unsupported, or already undone.";
        return false;
      }
      $old = json_decode((string)$event["old_values"], true);
      $new = json_decode((string)$event["new_values"], true);
      if (!is_array($old) || !is_array($new) || ($old["course_code"] ?? null)!==($new["course_code"] ?? null)) {
        $this->DB->end(false);
        $this->error = "Course code changes cannot be undone automatically.";
        return false;
      }
      $current = $this->DB->fetch(
        "SELECT `course_code`,`course_name`,`course_start`,`course_end`,`course_desc`
         FROM `courses` WHERE `course_code`=? FOR UPDATE",
        [$new["course_code"]]
      );
      $fields = ["course_code", "course_name", "course_start", "course_end", "course_desc"];
      foreach ($fields as $field) {
        if (!is_array($current) || (string)($current[$field] ?? "")!==(string)($new[$field] ?? "")) {
          $this->DB->end(false);
          $this->error = "The course changed after this audit entry. Undo the later change first.";
          return false;
        }
      }
      $this->DB->update(
        "courses", ["course_name", "course_start", "course_end", "course_desc"],
        "`course_code`=?",
        [$old["course_name"], $old["course_start"], $old["course_end"], $old["course_desc"], $new["course_code"]]
      );
      $this->DB->update(
        "course_audit",
        ["undone_by", "undone_at", "undo_reason"],
        "`event_id`=?",
        [$_SESSION["user"]["user_id"], date("Y-m-d H:i:s"), $reason, (int)$eventId]
      );
      $this->DB->end();
    } catch (Throwable $ex) {
      if ($this->DB->pdo->inTransaction()) { $this->DB->end(false); }
      throw $ex;
    }
    return true;
  }

  // (D) GET COURSE
  //  $code : course code
  function get ($code) {
    return $this->DB->fetch(
      "SELECT * FROM `courses` WHERE `course_code`=?",
      [$code]
    );
  }

  // (E) GET ALL OR SEARCH COURSES
  //  $search : optional, course code or name
  //  $page : optional, current page number
  function getAll ($search=null, $page=null) {
    // (E1) PARITAL SQL + DATA
    $sql = "FROM `courses`";
    $data = null;
    if ($search != null) {
      $sql .= " WHERE `course_code` LIKE ? OR `course_name` LIKE ?";
      $data = ["%$search%", "%$search%"];
    }

    // (E2) PAGINATION
    if ($page != null) {
      $this->Core->paginator(
        $this->DB->fetchCol("SELECT COUNT(*) $sql", $data), $page
      );
      $sql .= $this->Core->page["lim"];
    }

    // (E3) RESULTS
    return $this->DB->fetchAll(
      "SELECT *, DATE_FORMAT(`course_start`, '".D_SHORT."') `sd`, DATE_FORMAT(`course_end`, '".D_SHORT."') `ed` $sql",
       $data, "course_code"
    );
  }

  // (F) ADD USER TO COURSE
  //  $code : course code
  //  $uid : user id or email
  function addUser ($code, $uid, $reason=null) {
    $reason = $this->validRosterReason($reason);
    if ($reason===false) { return false; }
    if (!is_array($this->get($code))) {
      $this->error = "Invalid course.";
      return false;
    }
    // (F1) VERIFY VALID USER
    $this->Core->load("Users");
    $user = $this->Users->get($uid);
    if (!is_array($user) || $user["user_level"]=="S") {
      $this->error = "Invalid user";
      return false;
    }

    // (F2) ADD TO COURSE
    $this->DB->start();
    try {
      $exists = $this->DB->fetchCol(
        "SELECT 1 FROM `courses_users` WHERE `course_code`=? AND `user_id`=? FOR UPDATE",
        [$code, $user["user_id"]]
      );
      if ($exists===null) {
        $this->DB->query(
          "INSERT INTO `courses_users` (`course_code`,`user_id`) VALUES (?,?)",
          [$code, $user["user_id"]]
        );
        $this->auditRosterChange(
          $this->rosterOperationId(), $code, $user["user_id"], "enrolled", $reason
        );
      }
      $this->DB->end();
    } catch (Throwable $ex) {
      if ($this->DB->pdo->inTransaction()) { $this->DB->end(false); }
      throw $ex;
    }
    return true;
  }

  function addUsers ($code, $users, $reason=null) {
    $reason = $this->validRosterReason($reason);
    if ($reason===false) { return false; }
    if (!is_string($users)) {
      $this->error = "Provide a newline- or comma-separated list of user emails.";
      return false;
    }
    $course = $this->get($code);
    if (!is_array($course)) {
      $this->error = "Invalid course.";
      return false;
    }
    $users = array_values(array_unique(array_filter(
      array_map("trim", preg_split('/[\r\n,]+/', $users)),
      fn($user) => $user!==""
    )));
    if (count($users)==0 || count($users)>200) {
      $this->error = "Select between 1 and 200 users.";
      return false;
    }

    $this->Core->load("Users");
    $resolved = [];
    foreach ($users as $email) {
      $user = $this->Users->get($email);
      if (!is_array($user) || $user["user_level"]=="S") {
        $this->error = "Invalid or suspended user: $email";
        return false;
      }
      $resolved[$user["user_id"]] = $email;
    }

    $added = 0;
    $operationId = $this->rosterOperationId();
    $this->DB->start();
    try {
      foreach ($resolved as $userId=>$email) {
        $exists = $this->DB->fetchCol(
          "SELECT 1 FROM `courses_users` WHERE `course_code`=? AND `user_id`=?",
          [$code, $userId]
        );
        if ($exists!==null) { continue; }
        $this->DB->query(
          "INSERT IGNORE INTO `courses_users` (`course_code`,`user_id`) VALUES (?,?)",
          [$code, $userId]
        );
        $inserted = $this->DB->stmt->rowCount();
        if ($inserted>0) {
          $this->auditRosterChange($operationId, $code, $userId, "enrolled", $reason);
          $added += $inserted;
        }
      }
      $this->DB->end();
    } catch (Throwable $ex) {
      if ($this->DB->pdo->inTransaction()) { $this->DB->end(false); }
      throw $ex;
    }
    return ["added" => $added, "already_enrolled" => count($resolved)-$added];
  }

  // (G) DELETE USER FROM COURSE
  //  $code : course code
  //  $uid : user id or email
  function delUser ($code, $uid, $reason=null) {
    return $this->delUsers($code, json_encode([(int)$uid]), $reason);
  }

  function delUsers ($code, $uids, $reason=null) {
    $reason = $this->validRosterReason($reason);
    if ($reason===false) { return false; }
    if (!is_string($uids)) {
      $this->error = "Selected users are invalid.";
      return false;
    }
    $ids = json_decode($uids, true);
    if (!is_array($ids) || count($ids)==0 || count($ids)>200) {
      $this->error = "Select between 1 and 200 users.";
      return false;
    }
    foreach ($ids as $id) {
      if (!is_int($id) && !(is_string($id) && ctype_digit($id))) {
        $this->error = "Selected users are invalid.";
        return false;
      }
    }
    $ids = array_values(array_unique(array_map("intval", $ids)));
    $course = $this->get($code);
    if (!is_array($course)) {
      $this->error = "Invalid course.";
      return false;
    }
    $placeholders = implode(",", array_fill(0, count($ids), "?"));
    $operationId = $this->rosterOperationId();
    $removed = 0;
    $this->DB->start();
    try {
      $existing = $this->DB->fetchAll(
        "SELECT `user_id` FROM `courses_users`
         WHERE `course_code`=? AND `user_id` IN ($placeholders) FOR UPDATE",
        array_merge([$code], $ids), "user_id"
      ) ?? [];
      foreach ($existing as $id=>$row) {
        $this->auditRosterChange($operationId, $code, $id, "removed", $reason);
      }
      $this->DB->query(
        "DELETE FROM `courses_users` WHERE `course_code`=? AND `user_id` IN ($placeholders)",
        array_merge([$code], $ids)
      );
      $removed = $this->DB->stmt->rowCount();
      $this->DB->end();
    } catch (Throwable $ex) {
      if ($this->DB->pdo->inTransaction()) { $this->DB->end(false); }
      throw $ex;
    }
    return ["removed" => $removed];
  }

  function moveStudents ($from, $to, $uids, $reason=null) {
    $reason = $this->validRosterReason($reason);
    if ($reason===false) { return false; }
    if (!is_string($from) || trim($from)==="" || !is_string($to) || trim($to)==="") {
      $this->error = "Choose valid source and destination courses.";
      return false;
    }
    if (!is_string($uids)) {
      $this->error = "Selected users are invalid.";
      return false;
    }
    $ids = json_decode($uids, true);
    if (!is_array($ids) || count($ids)==0 || count($ids)>200) {
      $this->error = "Select between 1 and 200 students.";
      return false;
    }
    foreach ($ids as $id) {
      if (!is_int($id) && !(is_string($id) && ctype_digit($id))) {
        $this->error = "Selected users are invalid.";
        return false;
      }
    }
    $ids = array_values(array_unique(array_map("intval", $ids)));
    if ($from===$to) {
      $this->error = "Choose a different destination course.";
      return false;
    }
    if (!is_array($this->get($from)) || !is_array($this->get($to))) {
      $this->error = "Source or destination course is invalid.";
      return false;
    }

    $placeholders = implode(",", array_fill(0, count($ids), "?"));
    $this->DB->start();
    try {
      $enrolled = $this->DB->fetchAll(
        "SELECT cu.`user_id`
         FROM `courses_users` cu
         JOIN `users` u USING (`user_id`)
         WHERE cu.`course_code`=? AND u.`user_level`='U'
         AND cu.`user_id` IN ($placeholders)
         FOR UPDATE",
        array_merge([$from], $ids),
        "user_id"
      );
      if (!is_array($enrolled) || count($enrolled)!==count($ids)) {
        $this->DB->end(false);
        $this->error = "Only students enrolled in the source course can be moved.";
        return false;
      }

      $moved = 0;
      $alreadyEnrolled = 0;
      $operationId = $this->rosterOperationId();
      foreach ($ids as $id) {
        $this->DB->query(
          "INSERT IGNORE INTO `courses_users` (`course_code`,`user_id`) VALUES (?,?)",
          [$to, $id]
        );
        $inserted = $this->DB->stmt->rowCount();
        if ($inserted===0) { $alreadyEnrolled++; }
        else {
          $this->auditRosterChange($operationId, $to, $id, "moved_in", $reason);
        }
        $this->DB->delete(
          "courses_users", "`course_code`=? AND `user_id`=?", [$from, $id]
        );
        $removed = $this->DB->lastRows;
        if ($removed>0) {
          $this->auditRosterChange($operationId, $from, $id, "moved_out", $reason);
        }
        $moved += $removed;
      }
      $this->DB->end();
    } catch (Throwable $ex) {
      if ($this->DB->pdo->inTransaction()) { $this->DB->end(false); }
      throw $ex;
    }
    return ["moved" => $moved, "already_enrolled" => $alreadyEnrolled];
  }

  private function validRosterReason ($reason) {
    if (!is_string($reason)) {
      $this->error = "Provide a reason of 1 to 500 characters.";
      return false;
    }
    $reason = trim($reason);
    $length = preg_match_all('/./us', $reason, $characters);
    if ($length===false || $length===0 || $length>500) {
      $this->error = "Provide a reason of 1 to 500 characters.";
      return false;
    }
    return $reason;
  }

  private function rosterOperationId () {
    return bin2hex(random_bytes(16));
  }

  private function auditRosterChange ($operationId, $code, $userId, $event, $reason) {
    if (!isset($_SESSION["user"]["user_id"])) {
      throw new RuntimeException("An authenticated actor is required for roster audit history.");
    }
    $this->DB->insert(
      "course_roster_audit",
      ["operation_id", "course_code", "user_id", "event_type", "reason", "changed_by", "changed_at"],
      [$operationId, $code, $userId, $event, $reason,
       $_SESSION["user"]["user_id"], date("Y-m-d H:i:s")]
    );
  }

  function getRosterAudit ($code) {
    if (!is_array($this->get($code))) {
      $this->error = "Invalid course.";
      return false;
    }
    return $this->DB->fetchAll(
      "SELECT a.*, u.`user_name` AS `changed_by_name`,
              uu.`user_name` AS `undone_by_name`, s.`user_name` AS `student_name`,
              s.`user_email` AS `student_email`
       FROM `course_roster_audit` a
       LEFT JOIN `users` u ON (u.`user_id`=a.`changed_by`)
       LEFT JOIN `users` uu ON (uu.`user_id`=a.`undone_by`)
       LEFT JOIN `users` s ON (s.`user_id`=a.`user_id`)
       WHERE a.`course_code`=?
       ORDER BY a.`event_id` DESC LIMIT 50",
      [$code]
    ) ?? [];
  }

  function undoRosterChange ($eventId, $reason) {
    $reason = $this->validRosterReason($reason);
    if ($reason===false) { return false; }
    if ((!is_int($eventId) && !(is_string($eventId) && ctype_digit($eventId))) || (int)$eventId<1) {
      $this->error = "Select a valid roster change.";
      return false;
    }
    if (!isset($_SESSION["user"]["user_id"])) {
      $this->error = "Sign in before undoing a roster change.";
      return false;
    }

    $this->DB->start();
    try {
      $selected = $this->DB->fetch(
        "SELECT * FROM `course_roster_audit` WHERE `event_id`=? FOR UPDATE",
        [(int)$eventId]
      );
      if (!is_array($selected) || $selected["undone_at"]!==null) {
        $this->DB->end(false);
        $this->error = "This roster change is missing or has already been undone.";
        return false;
      }
      $changes = $this->DB->fetchAll(
        "SELECT * FROM `course_roster_audit`
         WHERE `operation_id`=? ORDER BY `event_id` FOR UPDATE",
        [$selected["operation_id"]]
      );
      if (!is_array($changes) || count($changes)===0) {
        $this->DB->end(false);
        $this->error = "Roster change history is incomplete.";
        return false;
      }
      foreach ($changes as $change) {
        if ($change["undone_at"]!==null) {
          $this->DB->end(false);
          $this->error = "This roster operation has already been undone.";
          return false;
        }
        $later = $this->DB->fetchCol(
          "SELECT 1 FROM `course_roster_audit`
           WHERE `course_code`=? AND `user_id`=? AND `event_id`>?
           AND `operation_id`!=? AND `undone_at` IS NULL LIMIT 1",
          [$change["course_code"], $change["user_id"], $change["event_id"], $change["operation_id"]]
        );
        if ($later!==null) {
          $this->DB->end(false);
          $this->error = "A later roster change exists. Undo that change first.";
          return false;
        }
      }

      foreach ($changes as $change) {
        $courseExists = $this->DB->fetchCol(
          "SELECT 1 FROM `courses` WHERE `course_code`=?",
          [$change["course_code"]]
        );
        $userLevel = $this->DB->fetchCol(
          "SELECT `user_level` FROM `users` WHERE `user_id`=? FOR UPDATE",
          [$change["user_id"]]
        );
        if ($courseExists===null || $userLevel===null) {
          $this->DB->end(false);
          $this->error = "The course or user no longer exists. Nothing was undone.";
          return false;
        }
        $exists = $this->DB->fetchCol(
          "SELECT 1 FROM `courses_users`
           WHERE `course_code`=? AND `user_id`=? FOR UPDATE",
          [$change["course_code"], $change["user_id"]]
        )!==null;
        $shouldExist = in_array($change["event_type"], ["enrolled", "moved_in"], true);
        if ($exists!==$shouldExist) {
          $this->DB->end(false);
          $this->error = "Current enrollment differs from this change. Nothing was undone.";
          return false;
        }
        if ($shouldExist) {
          $this->DB->delete(
            "courses_users", "`course_code`=? AND `user_id`=?",
            [$change["course_code"], $change["user_id"]]
          );
        } else {
          if ($userLevel==="S") {
            $this->DB->end(false);
            $this->error = "A suspended account cannot be re-enrolled. Nothing was undone.";
            return false;
          }
          $this->DB->query(
            "INSERT INTO `courses_users` (`course_code`,`user_id`) VALUES (?,?)",
            [$change["course_code"], $change["user_id"]]
          );
        }
      }
      $this->DB->update(
        "course_roster_audit",
        ["undone_by", "undone_at", "undo_reason"],
        "`operation_id`=? AND `undone_at` IS NULL",
        [$_SESSION["user"]["user_id"], date("Y-m-d H:i:s"), $reason, $selected["operation_id"]]
      );
      $this->DB->end();
    } catch (Throwable $ex) {
      if ($this->DB->pdo->inTransaction()) { $this->DB->end(false); }
      throw $ex;
    }
    return true;
  }

  // (H) GET ALL USERS IN COURSE
  //  $code : course code
  //  $page : optional, current page number
  function getUsers ($code, $page=null, $search=null, $role="all", $sort="name") {
    // (H1) PARITAL SQL + DATA
    $sql = "FROM `courses_users` cu
            JOIN `users` u USING (`user_id`)
            WHERE cu.`course_code`=? AND u.`user_level`!='S'";
    $data = [$code];
    if (is_string($search) && trim($search)!=="") {
      $sql .= " AND (u.`user_name` LIKE ? OR u.`user_email` LIKE ?)";
      $needle = "%".trim($search)."%";
      $data[] = $needle;
      $data[] = $needle;
    }
    $roles = ["teachers" => ["T"], "students" => ["U"], "admins" => ["A"]];
    if (is_string($role) && isset($roles[$role])) {
      $roleParams = implode(",", array_fill(0, count($roles[$role]), "?"));
      $sql .= " AND u.`user_level` IN ($roleParams)";
      $data = array_merge($data, $roles[$role]);
    }

    // (H2) PAGINATION
    if ($page != null) {
      $this->Core->paginator(
        $this->DB->fetchCol("SELECT COUNT(*) $sql", $data), $page
      );
    }

    // (H3) "MAIN SQL"
    $order = [
      "name" => "u.`user_name` ASC, u.`user_id` ASC",
      "name_desc" => "u.`user_name` DESC, u.`user_id` ASC",
      "email" => "u.`user_email` ASC, u.`user_id` ASC",
      "email_desc" => "u.`user_email` DESC, u.`user_id` ASC",
      "role" => "FIELD(u.`user_level`, 'A','T','U'), u.`user_name` ASC, u.`user_id` ASC"
    ];
    $sql .= " ORDER BY ".(is_string($sort) && isset($order[$sort]) ? $order[$sort] : $order["name"]);
    if ($page != null) { $sql .= $this->Core->page["lim"]; }

    // (H4) RESULTS
    return $this->DB->fetchAll("SELECT * $sql", $data, "user_id");
  }

  // (I) GET TEACHERS IN COURSE
  //  $code : course code
  function getTeachers ($code) {
    return $this->DB->fetchAll(
      "SELECT u.`user_id`, u.`user_name`, u.`user_email`
       FROM `courses_users` c
       JOIN `users` u USING (`user_id`)
       WHERE u.`user_level` IN ('A', 'T')
       AND c.`course_code`=?
       ORDER BY `user_name` ASC",
       [$code], "user_id"
    );
  }
}