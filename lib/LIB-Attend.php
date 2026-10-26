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
      preg_match_all('/./us', $notes, $characters);
      if (count($characters[0])>255) {
        $this->error = "Attendance notes cannot exceed 255 characters.";
        return false;
      }
      $records[] = [$uid, (int)$record["s"], $notes==="" ? null : $notes];
    }

    $this->DB->start();
    foreach ($records as [$uid, $status, $notes]) {
      $this->DB->replace(
        "attendance",
        ["class_id", "user_id", "a_status", "a_by", "a_date", "a_notes"],
        [$id, $uid, $status, $_SESSION["user"]["user_id"], date("Y-m-d H:i:s"), $notes]
      );
    }
    $this->DB->end();
    return true;
  }

  // (B) ATTENDANCE VIA QR
  //  $id : class id
  //  $hash : class hash
  function attendQR ($id, $hash) {
    // (B1) GET CLASS
    $class = $this->DB->fetch(
      "SELECT * FROM `classes` WHERE `class_id`=?",
      [$id]
    );
    if (!is_array($class)) {
      $this->error = "Invalid class.";
      return false;
    }

    // (B2) VERIFY
    if ($class["class_hash"]!=$hash) {
      $this->error = "Invalid class.";
      return false;
    }

    // (B3) REQUIRE COURSE ENROLLMENT AND PREVENT DUPLICATE CHECK-INS
    $uid = $_SESSION["user"]["user_id"] ?? 0;
    $enrolled = $this->DB->fetchCol(
      "SELECT 1 FROM `courses_users` cu
       JOIN `users` u USING (`user_id`)
       WHERE cu.`course_code`=? AND cu.`user_id`=? AND u.`user_level`='U'",
      [$class["course_code"], $uid]
    );
    if ($enrolled===null) {
      $this->error = "You are not enrolled in this course.";
      return false;
    }
    $existing = $this->DB->fetchCol(
      "SELECT `a_status` FROM `attendance` WHERE `class_id`=? AND `user_id`=?",
      [$id, $uid]
    );
    if ($existing!==null && (int)$existing===1) {
      $this->error = "Attendance has already been recorded.";
      return false;
    }

    // (B4) SAVE ATTENDANCE
    $this->DB->replace("attendance",
      ["class_id", "user_id", "a_status", "a_by", "a_date", "a_notes"],
      [$id, $uid, 1, $uid, date("Y-m-d H:i:s"), "QR"]
    );
    return true;
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