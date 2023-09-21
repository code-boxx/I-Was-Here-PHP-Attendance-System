<?php
class Attend extends Core {
  // (A) SAVE ATTENDANCE
  //  $id : class id
  //  $att : array, attendance records
  //    id => [s : status, n : notes] 
  function save ($id, $att) {
    $att = json_decode($att, true);
    foreach ($att as $uid=>$a) {
      $this->DB->replace("attendance",
        ["class_id", "user_id", "a_status", "a_by", "a_date", "a_notes"],
        [
          $id, $uid, $a["s"], 
          isset($_SESSION["user"]["user_id"]) ? $_SESSION["user"]["user_id"] : 0,
          date("Y-m-d H:i:s"), $a["n"]=="" ? null : $a["n"]
        ]
      );
    }
    return true;
  }

  // (B) ATTENDANCE VIA QR
  //  $id : class id
  //  $hash : class hash
  function attendQR ($id, $hash) {
    // (B1) GET CLASS
    $this->Core->load("Classes");
    $class = $this->Classes->get($id);
    if (!is_array($class)) {
      $this->error = "Invalid class.";
      return false;
    }

    // (B2) VERIFY
    if ($class["class_hash"]!=$hash) {
      $this->error = "Invalid class.";
      return false;
    }

    // (B3) SAVE ATTENDANCE
    $this->DB->replace("attendance",
      ["class_id", "user_id", "a_status", "a_by", "a_date", "a_notes"],
      [$id, $_SESSION["user"]["user_id"], 1, $_SESSION["user"]["user_id"], date("Y-m-d H:i:s"), "QR"]
    );
    return true;
  }

  // (C) GET STUDENT & ATTENDANCE FOR CLASS
  //  $id : class id
  function getStudents ($id) {
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