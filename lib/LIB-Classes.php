<?php
class Classes extends Core {
  // (A) ADD OR UPDATE CLASS
  //  $uid : user id (teacher-in-charge)
  //  $cid : course id
  //  $date : class date
  //  $desc : class description
  //  $id : class id (for edit only)
  function save ($uid, $cid, $date, $desc=null, $id=null) {
    // (A1) DATA SETUP
    $fields = ["user_id", "course_id", "class_date", "class_desc"];
    $data = [$uid, $cid, $date, $desc];

    // (A2) ADD/UPDATE CLASS
    if ($id===null) {
      return $this->DB->insert("classes", $fields, $data);
    } else {
      $data[] = $id;
      return $this->DB->update("classes", $fields, "`class_id`=?", $data);
    }
  }

  // (B) DELETE CLASS
  //  $id : class id
  function del ($id) {
    // (B1) DELETE ATTENDANCE
    $this->DB->start();
    $pass = $this->DB->query("DELETE FROM `attendance` WHERE `class_id`=?", [$id]);

    // (B2) DELETE CLASS
    if ($pass) {
      $pass = $this->DB->query("DELETE FROM `classes` WHERE `class_id`=?", [$id]);
    }

    // (B3) RESULT
    $this->DB->end($pass);
    return $pass;
  }

  // (C) GET CLASS
  //  $id : course id or code
  function get ($id) {
    return $this->DB->fetch(
      "SELECT * FROM `classes` WHERE `class_id`=?",
      [$id]
    );
  }

  // (D) COUNT CLASSES (FOR SEARCH & PAGINATION)
  //  $cid : course id
  function count ($cid) {
    return $this->DB->fetchCol(
      "SELECT COUNT(*) FROM `classes` WHERE `course_id`=?", [$cid]
    );
  }

  // (E) GET CLASSES FOR COURSE
  //  $cid : course id
  //  $page : optional, current page number
  function getAll ($cid, $page=1) {
    // (E1) PAGINATION
    $entries = $this->count($cid);
    if ($entries===false) { return false; }
    $pgn = $this->core->paginator($entries, $page);

    // (E2) GET CLASSES
    // "MAIN SELECT"
    $sql = "SELECT c.*, u.`user_name` FROM `classes` c
            JOIN `users` u USING (`user_id`)
            WHERE `course_id`=?
            ORDER BY `class_date` DESC";
    $data = [$cid];

    // LIMIT
    $sql .= " LIMIT {$pgn["x"]}, {$pgn["y"]}";

    // (E3) RESULTS
    $classes = $this->DB->fetchAll($sql, $data, "class_id");
    if ($classes===false) { return false; }
    return ["data" => $classes, "page" => $pgn];
  }

  // (F) SET ATTENDANCE
  //  $id : class id
  //  $cid : course id
  //  $uid : user id or email
  function attend ($id, $cid, $uid) {
    // (F1) VERIFY VALID USER
    $this->core->load("Users");
    $user = $this->core->Users->get($uid);
    if ($user===false || !is_array($user) || $user["user_role"]!="S") {
      $this->error = "Invalid user";
      return false;
    }

    // (F2) ADD ATTENDANCE
    return $this->DB->insert("attendance",
      ["class_id", "user_id", "course_id", "sign_date"],
      [$id, $user["user_id"], $cid, date("Y-m-d H:i:s")], true);
  }

  // (G) REMOVE ATTENDANCE
  //  $id : class id
  //  $uid : user id
  function absent ($id, $uid) {
    return $this->DB->query(
      "DELETE FROM `attendance` WHERE `class_id`=? AND `user_id`=?",
      [$id, $uid]
    );
  }

  // (H) GET STUDENT & ATTENDANCE IN CLASS
  //  $id : class id
  //  $cid : course id
  function getStudents ($id, $cid) {
    // (H1) GET ALL STUDENTS IN COURSE
    $sql = "SELECT u.`user_id`, u.`user_name`, u.`user_email`
            FROM `courses_users` cu
            JOIN `users` u USING (`user_id`)
            WHERE cu.`course_id`=? AND u.`user_role`='S'
            ORDER BY `user_name`";
    $data = [$cid];
    $students = $this->DB->fetchAll($sql, $data, "user_id");
    if ($students === false) { return false; }
    if (!is_array($students)) { $students = []; }

    // (H2) GET ATTENDANCE
    $sql = "SELECT u.`user_id`, u.`user_name`, u.`user_email`
            FROM `attendance` a
            JOIN `users` u USING (`user_id`)
            WHERE `class_id`=?";
    $data = [$id];
    $this->DB->query($sql, $data);
    while ($row = $this->DB->stmt->fetch()) {
      if (!isset($students[$row["user_id"]])) {
        $students[$row["user_id"]] = $row;
      }
      $students[$row["user_id"]]["a"] = 1;
    }

    // (H3) RESULTS
    return $students;
  }

  // (I) COUNT CLASSES FOR TEACHER
  //  $uid : user id
  //  $date : date range restriction
  //  $range : -1 for all classes before $date
  //           1 for all classes after $date
  //           0 all classes on $date
  //           "" or null for no restriction
  function countByTeacher ($uid, $date=null, $range=null) {
    // (I1) "MAIN" SQL
    $sql = "SELECT COUNT(*) FROM `classes` WHERE `user_id`=?";
    $data = [$uid];

    // (I2) DATE RANGE RESTRICTION
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

    // (I3) RESULT
    return $this->DB->fetchCol($sql, $data);
  }

  // (J) GET CLASSES FOR TEACHER
  //  $uid : user id
  //  $date : date range restriction
  //  $range : -1 for all classes before $date
  //           1 for all classes after $date
  //           0 all classes on $date
  //           "" or null for no restriction
  //  $page : current page number
  function getByTeacher ($uid, $date=null, $range=null, $page=1) {
    // (J1) PAGINATION
    $entries = $this->countByTeacher($uid, $date, $range);
    if ($entries===false) { return false; }
    $pgn = $this->core->paginator($entries, $page);

    // (J2) GET CLASSES
    // "MAIN SELECT"
    $sql = "SELECT cl.*, co.`course_code`, co.`course_name`
            FROM `classes` cl
            JOIN `courses` co USING (`course_id`)
            WHERE `user_id`=?";
    $data = [$uid];

    // DATE RANGE RESTRICTION
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

    // ORDER & LIMIT
    $sql .= " ORDER BY `class_date` DESC LIMIT {$pgn["x"]}, {$pgn["y"]}";

    // (J3) RESULTS
    $classes = $this->DB->fetchAll($sql, $data, "class_id");
    if ($classes===false) { return false; }
    return ["data" => $classes, "page" => $pgn];
  }

  // (K) SAVE CLASS ATTENDANCE
  //  $cid : course id
  //  $id : class id
  //  $list : present students (array or json encoded array)
  //          send empty array to indicate all absent
  function attendance ($cid, $id, $list) {
    // (K1) SORT OUT THE LIST
    if (!is_array($list)) {
      try { $list = json_decode($list); }
      catch (Exception $ex) {
        $this->error = $ex->getMessage();
        return false;
      }
    }

    // (K2) DELETE OLD ENTRIES
    $this->DB->start();
    $pass = $this->DB->query("DELETE FROM `attendance` WHERE `class_id`=?", [$id]);

    // (K3) ADD NEW ENTRIES
    if ($pass && count($list)>0) {
      $fields = ["class_id", "user_id", "course_id"];
      $data = [];
      foreach ($list as $uid) {
        $data[] = $id; $data[] = $uid; $data[] = $cid;
      }
      $pass = $this->DB->insert("attendance", $fields, $data);
    }

    // (K4) RESULTS
    $this->DB->end($pass);
    return $pass;
  }

  // (L) COUNT CLASSES FOR STUDENT
  //  $uid : user id
  //  $date : date range restriction
  //  $range : -1 for all classes before $date
  //           1 for all classes after $date
  //           0 all classes on $date
  //           "" or null for no restriction
  function countByStudent ($uid, $date=null, $range=null) {
    // (L1) "MAIN" SQL
    $sql = "SELECT COUNT(*) FROM `classes`
            WHERE `course_id` IN
            (SELECT `course_id` FROM `courses_users` WHERE `user_id`=?)";
    $data = [$uid];

    // (L2) DATE RANGE RESTRICTION
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

    // (L3) RESULT
    return $this->DB->fetchCol($sql, $data);
  }

  // (M) GET CLASSES FOR STUDENT
  //  $uid : user id
  //  $date : date range restriction
  //  $range : -1 for all classes before $date
  //           1 for all classes after $date
  //           0 all classes on $date
  //           "" or null for no restriction
  //  $page : current page number
  function getByStudent ($uid, $date=null, $range=null, $page=1) {
    // (M1) PAGINATION
    $entries = $this->countByStudent($uid, $date, $range);
    if ($entries===false) { return false; }
    $pgn = $this->core->paginator($entries, $page);

    // (M2) GET CLASSES
    // "MAIN SELECT"
    $sql = "SELECT cl.*, co.`course_code`, co.`course_name`, a.`sign_date`
            FROM `classes` cl
            LEFT JOIN `attendance` a ON (cl.`class_id`=a.`class_id` AND a.`user_id`=?)
            LEFT JOIN `courses` co ON (cl.`course_id`=co.`course_id`)
            WHERE cl.`course_id` IN
            (SELECT `course_id` FROM `courses_users` WHERE `user_id`=?)";
    $data = [$uid, $uid];

    // DATE RANGE RESTRICTION
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

    // ORDER & LIMIT
    $sql .= " ORDER BY `class_date` DESC LIMIT {$pgn["x"]}, {$pgn["y"]}";

    // (M3) RESULTS
    $classes = $this->DB->fetchAll($sql, $data, "class_id");

    if ($classes===false) { return false; }
    return ["data" => $classes, "page" => $pgn];
  }
}
