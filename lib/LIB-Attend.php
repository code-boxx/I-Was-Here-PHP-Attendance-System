<?php
class Attend extends Core {
  // (A) SAVE ATTENDANCE
  //  $id : class id
  //  $att : array, attendance records
  //    id => [s : status, n : notes] 
  function save ($id, $att) {
    $att = json_decode($att, true);
    $this->Core->load("Classes");
    if (!$this->Classes->canManageClass($id)) {
      $this->error = "No access permission";
      return false;
    }
    if (!is_array($att) || count($att)==0) {
      $this->error = "Attendance data is invalid.";
      return false;
    }

    $class = $this->DB->fetch(
      "SELECT `course_code` FROM `classes` WHERE `class_id`=?",
      [$id]
    );
    $records = [];
    foreach ($att as $uid=>$record) {
      if (
        !ctype_digit((string)$uid) ||
        !is_array($record) ||
        !isset($record["s"]) ||
        !in_array((string)$record["s"], ["0", "1"], true)
      ) {
        $this->error = "Attendance data is invalid.";
        return false;
      }
      $enrolled = $this->DB->fetchCol(
        "SELECT 1 FROM `courses_users` cu
         JOIN `users` u USING (`user_id`)
         WHERE cu.`course_code`=? AND cu.`user_id`=? AND u.`user_level`='U'",
        [$class["course_code"], $uid]
      );
      if ($enrolled===null) {
        $this->error = "Student is not enrolled in this course.";
        return false;
      }
      $notes = trim((string)($record["n"] ?? ""));
      $noteLength = preg_match_all('/./us', $notes, $characters);
      if ($noteLength===false || $noteLength>255) {
        $this->error = "Attendance notes must be valid UTF-8 and cannot exceed 255 characters.";
        return false;
      }
      $records[] = [$uid, (int)$record["s"], $notes==="" ? null : $notes];
    }

    $this->DB->start();
    foreach ($records as [$uid, $status, $notes]) {
      $previous = $this->DB->fetchCol(
        "SELECT `a_status` FROM `attendance` WHERE `class_id`=? AND `user_id`=? FOR UPDATE",
        [$id, $uid]
      );
      if ($previous!==null && (int)$previous!==$status && ($notes===null || $notes==="")) {
        $this->DB->end(false);
        $this->error = "Provide a reason when correcting an existing attendance record.";
        return false;
      }
      $this->DB->replace(
        "attendance",
        ["class_id", "user_id", "a_status", "a_by", "a_date", "a_notes"],
        [$id, $uid, $status, $_SESSION["user"]["user_id"], date("Y-m-d H:i:s"), $notes]
      );
      if ($previous===null || (int)$previous!==$status) {
        $this->logChange(
          $id, $uid, $previous===null ? null : (int)$previous, $status,
          $_SESSION["user"]["user_id"], $notes, "manual"
        );
      }
    }
    $this->DB->end();
    return true;
  }

  // (B) ATTENDANCE VIA QR
  //  $id : class id
  //  $token : signed, short-lived token displayed by an open teacher session
  function attendQR ($token) {
    $claims = $this->verifyQRToken($token);
    if (!is_array($claims)) {
      $this->error = "This QR code has expired or is invalid.";
      return false;
    }
    $uid = $_SESSION["user"]["user_id"] ?? 0;
    $this->DB->start();
    try {
      $session = $this->DB->fetch(
        "SELECT s.*, c.`course_code`
         FROM `attendance_sessions` s
         JOIN `classes` c USING (`class_id`)
         WHERE s.`session_id`=? AND s.`class_id`=? AND c.`course_code`=?
         AND s.`session_status`='open'
         FOR UPDATE",
        [$claims["s"], $claims["c"], $claims["o"]]
      );
      if (!is_array($session)) {
        $this->DB->end(false);
        $this->error = "This attendance session is closed.";
        return false;
      }
      $enrolled = $this->DB->fetchCol(
        "SELECT 1 FROM `courses_users` cu
         JOIN `users` u USING (`user_id`)
         WHERE cu.`course_code`=? AND cu.`user_id`=? AND u.`user_level`='U'",
        [$session["course_code"], $uid]
      );
      if ($enrolled===null) {
        $this->DB->end(false);
        $this->error = "You are not enrolled in this course.";
        return false;
      }
      $this->DB->query(
        "INSERT IGNORE INTO `attendance`
         (`class_id`,`user_id`,`a_status`,`a_by`,`a_date`,`a_notes`)
         VALUES (?,?,?,?,?,?)",
        [$claims["c"], $uid, 1, $uid, date("Y-m-d H:i:s"), "QR"]
      );
      $previous = null;
      if ($this->DB->stmt->rowCount()==0) {
        $this->DB->update(
          "attendance",
          ["a_status", "a_by", "a_date", "a_notes"],
          "`class_id`=? AND `user_id`=? AND `a_status`=0",
          [1, $uid, date("Y-m-d H:i:s"), "QR", $claims["c"], $uid]
        );
        if ($this->DB->lastRows==0) {
          $this->DB->end(false);
          $this->error = "Attendance has already been recorded.";
          return false;
        }
        $previous = 0;
      }
      $this->logChange(
        $claims["c"], $uid, $previous,
        1, $uid, "QR check-in", "qr"
      );
      $this->DB->end();
      return true;
    } catch (Throwable $ex) {
      if ($this->DB->pdo->inTransaction()) { $this->DB->end(false); }
      throw $ex;
    }
  }

  function openSession ($id) {
    $this->Core->load("Classes");
    if (!$this->Classes->canManageClass($id)) {
      $this->error = "No access permission";
      return false;
    }
    $this->DB->start();
    try {
      $class = $this->DB->fetch(
        "SELECT `class_id` FROM `classes` WHERE `class_id`=? FOR UPDATE", [$id]
      );
      if (!is_array($class)) {
        $this->DB->end(false);
        $this->error = "Invalid class.";
        return false;
      }
      $active = $this->DB->fetchCol(
        "SELECT `session_id` FROM `attendance_sessions`
         WHERE `class_id`=? AND `session_status`='open' LIMIT 1",
        [$id]
      );
      if ($active!==null) {
        $this->DB->end(false);
        $this->error = "An attendance session is already open.";
        return false;
      }
      $this->DB->insert(
        "attendance_sessions",
        ["class_id", "opened_by", "opened_at", "session_status"],
        [$id, $_SESSION["user"]["user_id"], date("Y-m-d H:i:s"), "open"]
      );
      $sessionId = (int)$this->DB->lastID;
      $this->DB->end();
      return $sessionId;
    } catch (Throwable $ex) {
      if ($this->DB->pdo->inTransaction()) { $this->DB->end(false); }
      throw $ex;
    }
  }

  function closeSession ($id) {
    $this->Core->load("Classes");
    if (!$this->Classes->canManageClass($id)) {
      $this->error = "No access permission";
      return false;
    }
    $this->DB->update(
      "attendance_sessions",
      ["session_status", "closed_at"],
      "`class_id`=? AND `session_status`='open'",
      ["closed", date("Y-m-d H:i:s"), $id]
    );
    if ($this->DB->lastRows==0) {
      $this->error = "There is no open attendance session.";
      return false;
    }
    return true;
  }

  function sessionState ($id) {
    $this->Core->load("Classes");
    if (!$this->Classes->canManageClass($id)) {
      $this->error = "No access permission";
      return false;
    }
    return $this->DB->fetch(
      "SELECT `session_id`, `opened_at`, `session_status`
       FROM `attendance_sessions`
       WHERE `class_id`=? AND `session_status`='open'
       ORDER BY `session_id` DESC LIMIT 1",
      [$id]
    ) ?: ["session_status" => "closed"];
  }

  function qrToken ($id) {
    if (JWT_SECRET==="") {
      $this->error = "QR signing is not configured.";
      return false;
    }
    $this->Core->load("Classes");
    if (!$this->Classes->canManageClass($id)) {
      $this->error = "No access permission";
      return false;
    }
    $session = $this->DB->fetch(
      "SELECT s.`session_id`, c.`course_code`
       FROM `attendance_sessions` s
       JOIN `classes` c USING (`class_id`)
       WHERE s.`class_id`=? AND s.`session_status`='open'
       ORDER BY s.`session_id` DESC LIMIT 1",
      [$id]
    );
    if (!is_array($session)) {
      $this->error = "Open an attendance session before displaying its QR code.";
      return false;
    }
    $expires = time() + 30;
    $payload = self::base64url(json_encode([
      "s" => (int)$session["session_id"], "c" => (int)$id,
      "o" => $session["course_code"], "e" => $expires
    ], JSON_THROW_ON_ERROR));
    return ["token" => $payload . "." . hash_hmac("sha256", $payload, JWT_SECRET), "expires" => $expires];
  }

  function getStudentSummary () {
    if (($_SESSION["user"]["user_level"] ?? "")!="U") {
      $this->error = "No access permission";
      return false;
    }
    $uid = $_SESSION["user"]["user_id"];
    return $this->DB->fetchAll(
      "SELECT co.`course_code`, co.`course_name`,
              COUNT(cl.`class_id`) AS `total_classes`,
              COALESCE(SUM(CASE WHEN a.`a_status`=1 THEN 1 ELSE 0 END), 0) AS `present_classes`,
              COALESCE(SUM(CASE WHEN a.`a_status`=0 THEN 1 ELSE 0 END), 0) AS `absent_classes`
       FROM `courses_users` cu
       JOIN `courses` co USING (`course_code`)
       LEFT JOIN `classes` cl ON (cl.`course_code`=co.`course_code` AND cl.`class_date`<=NOW())
       LEFT JOIN `attendance` a ON (a.`class_id`=cl.`class_id` AND a.`user_id`=cu.`user_id`)
       WHERE cu.`user_id`=? AND EXISTS (
         SELECT 1 FROM `users` u WHERE u.`user_id`=cu.`user_id` AND u.`user_level`='U'
       )
       GROUP BY co.`course_code`, co.`course_name`
       ORDER BY co.`course_name`",
      [$uid],
      "course_code"
    );
  }

  function requestCorrection ($classId, $requestedStatus, $reason) {
    if (($_SESSION["user"]["user_level"] ?? "")!="U") {
      $this->error = "No access permission";
      return false;
    }
    if (!in_array((string)$requestedStatus, ["0", "1"], true)) {
      $this->error = "Requested attendance status is invalid.";
      return false;
    }
    $reason = trim((string)$reason);
    if ($reason==="" || !$this->textWithinLimit($reason, 500)) {
      $this->error = "Please provide a reason of no more than 500 characters.";
      return false;
    }
    $uid = $_SESSION["user"]["user_id"];
    $this->DB->start();
    try {
      $enrolled = $this->DB->fetch(
        "SELECT cl.`class_id` FROM `classes` cl
         JOIN `courses_users` cu ON (cu.`course_code`=cl.`course_code`)
         WHERE cl.`class_id`=? AND cu.`user_id`=? FOR UPDATE",
        [$classId, $uid]
      );
      if (!is_array($enrolled)) {
        $this->DB->end(false);
        $this->error = "You are not enrolled in this class.";
        return false;
      }
      $pending = $this->DB->fetchCol(
        "SELECT `request_id` FROM `attendance_corrections`
         WHERE `class_id`=? AND `user_id`=? AND `request_status`='pending'",
        [$classId, $uid]
      );
      if ($pending!==null) {
        $this->DB->end(false);
        $this->error = "You already have a pending request for this class.";
        return false;
      }
      $this->DB->insert(
        "attendance_corrections",
        ["class_id", "user_id", "requested_status", "request_reason", "submitted_at"],
        [$classId, $uid, (int)$requestedStatus, $reason, date("Y-m-d H:i:s")]
      );
      $this->DB->end();
      return true;
    } catch (Throwable $ex) {
      if ($this->DB->pdo->inTransaction()) { $this->DB->end(false); }
      throw $ex;
    }
  }

  function getCorrections ($mine=false) {
    $level = $_SESSION["user"]["user_level"] ?? "";
    $uid = $_SESSION["user"]["user_id"] ?? 0;
    if ($mine || $level=="U") {
      if ($level!="U") {
        $this->error = "No access permission";
        return false;
      }
      return $this->DB->fetchAll(
        "SELECT r.*, cl.`class_date`, co.`course_code`, co.`course_name`,
                u.`user_name`, d.`user_name` AS `decider_name`
         FROM `attendance_corrections` r
         JOIN `classes` cl USING (`class_id`)
         JOIN `courses` co USING (`course_code`)
         JOIN `users` u ON (u.`user_id`=r.`user_id`)
         LEFT JOIN `users` d ON (d.`user_id`=r.`decided_by`)
         WHERE r.`user_id`=? ORDER BY r.`submitted_at` DESC",
        [$uid]
      );
    }
    if (!in_array($level, ["A", "T"], true)) {
      $this->error = "No access permission";
      return false;
    }
    $sql = "SELECT r.*, cl.`class_date`, co.`course_code`, co.`course_name`,
                   u.`user_name`, u.`user_email`
            FROM `attendance_corrections` r
            JOIN `classes` cl USING (`class_id`)
            JOIN `courses` co USING (`course_code`)
            JOIN `users` u ON (u.`user_id`=r.`user_id`)";
    $data = [];
    if ($level=="T") {
      $sql .= " WHERE cl.`user_id`=?";
      $data[] = $uid;
    }
    $sql .= " ORDER BY FIELD(r.`request_status`,'pending','approved','rejected'), r.`submitted_at` DESC";
    return $this->DB->fetchAll($sql, $data);
  }

  function decideCorrection ($requestId, $decision, $note) {
    if (!in_array(($_SESSION["user"]["user_level"] ?? ""), ["A", "T"], true)) {
      $this->error = "No access permission";
      return false;
    }
    if (!in_array($decision, ["approved", "rejected"], true)) {
      $this->error = "Correction decision is invalid.";
      return false;
    }
    $note = trim((string)$note);
    if ($note==="" || !$this->textWithinLimit($note, 500)) {
      $this->error = "Please provide a decision reason of no more than 500 characters.";
      return false;
    }
    $this->DB->start();
    try {
      $request = $this->DB->fetch(
        "SELECT * FROM `attendance_corrections` WHERE `request_id`=? FOR UPDATE",
        [$requestId]
      );
      if (!is_array($request) || $request["request_status"]!="pending") {
        $this->DB->end(false);
        $this->error = "This correction request is no longer pending.";
        return false;
      }
      $this->Core->load("Classes");
      if (!$this->Classes->canManageClass($request["class_id"])) {
        $this->DB->end(false);
        $this->error = "No access permission";
        return false;
      }
      if ($decision=="approved") {
        $enrolled = $this->DB->fetchCol(
          "SELECT 1 FROM `classes` cl
           JOIN `courses_users` cu ON (cu.`course_code`=cl.`course_code`)
           WHERE cl.`class_id`=? AND cu.`user_id`=?",
          [$request["class_id"], $request["user_id"]]
        );
        if ($enrolled===null) {
          $this->DB->end(false);
          $this->error = "The student is no longer enrolled in this course.";
          return false;
        }
        $oldStatus = $this->DB->fetchCol(
          "SELECT `a_status` FROM `attendance`
           WHERE `class_id`=? AND `user_id`=? FOR UPDATE",
          [$request["class_id"], $request["user_id"]]
        );
        $this->DB->replace(
          "attendance",
          ["class_id", "user_id", "a_status", "a_by", "a_date", "a_notes"],
          [
            $request["class_id"], $request["user_id"], $request["requested_status"],
            $_SESSION["user"]["user_id"], date("Y-m-d H:i:s"), $this->truncateText($note, 255)
          ]
        );
        $this->logChange(
          $request["class_id"], $request["user_id"],
          $oldStatus===null ? null : (int)$oldStatus,
          (int)$request["requested_status"], $_SESSION["user"]["user_id"],
          $this->truncateText("Request #$requestId: {$request["request_reason"]}; $note", 500),
          "correction"
        );
      }
      $this->DB->update(
        "attendance_corrections",
        ["request_status", "decided_by", "decided_at", "decision_note"],
        "`request_id`=?",
        [$decision, $_SESSION["user"]["user_id"], date("Y-m-d H:i:s"), $note, $requestId]
      );
      $this->DB->end();
      return true;
    } catch (Throwable $ex) {
      if ($this->DB->pdo->inTransaction()) { $this->DB->end(false); }
      throw $ex;
    }
  }

  function getAudit ($classId) {
    $this->Core->load("Classes");
    if (!$this->Classes->canManageClass($classId)) {
      $this->error = "No access permission";
      return false;
    }
    return $this->DB->fetchAll(
      "SELECT a.*,
              COALESCE(u.`user_name`, CONCAT('Deleted account #', a.`user_id`)) AS `student_name`,
              COALESCE(c.`user_name`, CONCAT('Deleted account #', a.`changed_by`)) AS `actor_name`
       FROM `attendance_audit` a
       LEFT JOIN `users` u ON (u.`user_id`=a.`user_id`)
       LEFT JOIN `users` c ON (c.`user_id`=a.`changed_by`)
       WHERE a.`class_id`=? ORDER BY a.`changed_at` DESC",
      [$classId]
    );
  }

  private function logChange ($classId, $userId, $oldStatus, $newStatus, $actorId, $reason, $source) {
    $this->DB->insert(
      "attendance_audit",
      ["class_id", "user_id", "old_status", "new_status", "changed_by", "change_reason", "change_source", "changed_at"],
      [$classId, $userId, $oldStatus, $newStatus, $actorId, $reason, $source, date("Y-m-d H:i:s")]
    );
  }

  private function textWithinLimit ($text, $limit) {
    $matches = preg_match_all('/./us', $text, $characters);
    return $matches!==false && $matches<=$limit;
  }

  private function truncateText ($text, $limit) {
    preg_match_all('/./us', $text, $characters);
    return implode("", array_slice($characters[0] ?? [], 0, $limit));
  }

  private function verifyQRToken ($token) {
    if (
      JWT_SECRET==="" || !is_string($token) || strlen($token)>1024 ||
      substr_count($token, ".")!=1
    ) { return null; }
    [$payload, $signature] = explode(".", $token, 2);
    $expected = hash_hmac("sha256", $payload, JWT_SECRET);
    if (!hash_equals($expected, $signature)) { return null; }
    $decoded = self::base64urlDecode($payload);
    if ($decoded===false) { return null; }
    $claims = json_decode($decoded, true);
    if (
      !is_array($claims) ||
      !isset($claims["s"], $claims["c"], $claims["o"], $claims["e"]) ||
      !is_int($claims["s"]) || !is_int($claims["c"]) || !is_int($claims["e"]) ||
      !is_string($claims["o"]) || $claims["o"]==="" ||
      $claims["s"]<1 || $claims["c"]<1 ||
      $claims["e"]<time() || $claims["e"]>time()+30
    ) { return null; }
    return $claims;
  }

  private static function base64url ($value) {
    return rtrim(strtr(base64_encode($value), "+/", "-_"), "=");
  }

  private static function base64urlDecode ($value) {
    return base64_decode(strtr($value, "-_", "+/") . str_repeat("=", (4 - strlen($value) % 4) % 4), true);
  }

  // (C) GET STUDENT & ATTENDANCE FOR CLASS
  //  $id : class id
  function getStudents ($id) {
    $this->Core->load("Classes");
    if (!$this->Classes->canManageClass($id)) {
      $this->error = "No access permission";
      return null;
    }

    // (C1) GET COURSE CODE
    $code = $this->DB->fetchCol(
      "SELECT `course_code` FROM `classes` WHERE `class_id`=?", [$id]
    );

    // (C2) GET STUDENTS + ATTENDANCE FOR THE CLASS
    $this->DB->query(
      "SELECT u.`user_id`, u.`user_name`, u.`user_email`, a.`a_status`, a.`a_notes`
       FROM `courses_users` cu
       LEFT JOIN `users` u ON (cu.`user_id`=u.`user_id`)
       LEFT JOIN `attendance` a ON (cu.`user_id`=a.`user_id` AND a.class_id=?)
       WHERE cu.`course_code`=? AND u.`user_level`='U'
       ORDER BY `user_name`",
      [$id, $code]
    );
    $students = [];
    while ($r = $this->DB->stmt->fetch()) {
      $students[$r["user_id"]] = $r;
    }

    // (C3) RESULTS
    return $students;
  }
}