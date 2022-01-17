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
    if ($id==null) {
      $this->DB->insert("classes", $fields, $data);
    } else {
      $data[] = $id;
      $this->DB->update("classes", $fields, "`class_id`=?", $data);
    }
    return true;
  }

  // (B) DELETE CLASS
  //  $id : class id
  function del ($id) {
    $this->DB->start();
    $this->DB->delete("attendance", "`class_id`=?", [$id]);
    $this->DB->delete("classes", "`class_id`=?", [$id]);
    $this->DB->end();
    return true;
  }

  // (C) GET CLASS
  //  $id : course id or code
  function get ($id) {
    return $this->DB->fetch(
      "SELECT * FROM `classes` WHERE `class_id`=?", [$id]
    );
  }

  // (D) GET CLASSES FOR COURSE
  //  $cid : course id
  //  $page : optional, current page number
  function getAll ($cid, $page=null) {
    // (D1) PAGINATION
    if ($page != null) {
      $pgn = $this->core->paginator(
        $this->DB->fetchCol("SELECT COUNT(*) FROM `classes` WHERE `course_id`=?", [$cid]), $page
      );
    }

    // (D2) CLASSES SQL
    $sql = "SELECT c.*, u.`user_name` FROM `classes` c
            JOIN `users` u USING (`user_id`)
            WHERE `course_id`=?
            ORDER BY `class_date` DESC";
    $data = [$cid];
    if ($page != null) { $sql .= " LIMIT {$pgn["x"]}, {$pgn["y"]}"; }

    // (D3) RESULTS
    $classes = $this->DB->fetchAll($sql, $data, "class_id");
    return $page != null
     ? ["data" => $classes, "page" => $pgn]
     : $classes ;
  }

  // (E) SET ATTENDANCE
  //  $id : class id
  //  $cid : course id
  //  $uid : user id or email
  function attend ($id, $cid, $uid) {
    // (E1) VERIFY VALID USER
    $this->core->load("Users");
    $user = $this->core->Users->get($uid);
    if (!is_array($user) || $user["user_role"]!="S") {
      $this->error = "Invalid user";
      return false;
    }

    // (E2) ADD ATTENDANCE
    $this->DB->replace("attendance",
      ["class_id", "user_id", "course_id", "sign_date"],
      [$id, $user["user_id"], $cid, date("Y-m-d H:i:s")]);
    return true;
  }

  // (F) REMOVE ATTENDANCE
  //  $id : class id
  //  $uid : user id
  function absent ($id, $uid) {
    $this->DB->delete("attendance", "`class_id`=? AND `user_id`=?", [$id, $uid]);
    return true;
  }

  // (G) GET STUDENT & ATTENDANCE IN CLASS
  //  $id : class id
  //  $cid : course id
  function getStudents ($id, $cid) {
    // (G1) GET ALL STUDENTS IN COURSE
    $sql = "SELECT u.`user_id`, u.`user_name`, u.`user_email`
            FROM `courses_users` cu
            JOIN `users` u USING (`user_id`)
            WHERE cu.`course_id`=? AND u.`user_role`='S'
            ORDER BY `user_name`";
    $data = [$cid];
    $students = $this->DB->fetchAll($sql, $data, "user_id");
    if (!is_array($students)) { $students = []; }

    // (G2) GET ATTENDANCE
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

    // (G3) RESULTS
    return $students;
  }

  // (H) SAVE CLASS ATTENDANCE
  //  $cid : course id
  //  $id : class id
  //  $list : present students (array or json encoded array)
  //          send empty array to indicate all absent
  function attendance ($cid, $id, $list) {
    // (H1) SORT OUT THE LIST
    if (!is_array($list)) {
      try { $list = json_decode($list); }
      catch (Exception $ex) {
        $this->error = $ex->getMessage();
        return false;
      }
    }

    // (H2) DELETE OLD ENTRIES
    $this->DB->start();
    $this->DB->delete("attendance", "`class_id`=?", [$id]);

    // (H3) ADD NEW ENTRIES
    if (count($list)>0) {
      $fields = ["class_id", "user_id", "course_id"];
      $data = [];
      foreach ($list as $uid) {
        $data[] = $id; $data[] = $uid; $data[] = $cid;
      }
      $this->DB->insert("attendance", $fields, $data);
    }

    // (H4) RESULTS
    $this->DB->end();
    return true;
  }

  // (I) GET CLASSES FOR TEACHER
  //  $uid : user id
  //  $date : date range restriction
  //  $range : -1 for all classes before $date
  //           1 for all classes after $date
  //           0 all classes on $date
  //           "" or null for no restriction
  //  $page : current page number, optional
  function getByTeacher ($uid, $date=null, $range=null, $page=null) {
    // (I1) PARTIAL SQL
    $sql = "FROM `classes` cl
            JOIN `courses` co USING (`course_id`)
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

    // (I2) PAGINATION
    if ($page != null) {
      $pgn = $this->core->paginator(
        $this->DB->fetchCol("SELECT COUNT(*) $sql", $data), $page
      );
    }

    // (I3) GET CLASSES
    $sql = "SELECT cl.*, co.`course_code`, co.`course_name` $sql ORDER BY `class_date` DESC";
    if ($page != null) { $sql .= " LIMIT {$pgn["x"]}, {$pgn["y"]}"; }

    // (I4) RESULTS
    $classes = $this->DB->fetchAll($sql, $data, "class_id");
    return $page != null
     ? ["data" => $classes, "page" => $pgn]
     : $classes ;
  }

  // (J) GET CLASSES FOR STUDENT
  //  $uid : user id
  //  $date : date range restriction
  //  $range : -1 for all classes before $date
  //           1 for all classes after $date
  //           0 all classes on $date
  //           "" or null for no restriction
  //  $page : current page number, optional
  function getByStudent ($uid, $date=null, $range=null, $page=null) {
    // (J1) PARTIAL SQL
    $sql = "WHERE cl.`course_id` IN
            (SELECT `course_id` FROM `courses_users` WHERE `user_id`=?)";
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

    // (J2) PAGINATION
    if ($page != null) {
      $pgn = $this->core->paginator(
        $this->DB->fetchCol("SELECT COUNT(*) FROM `classes` cl $sql", $data), $page
      );
    }

    // (J3) GET CLASSES
    $sql = "SELECT cl.*, co.`course_code`, co.`course_name`, a.`sign_date`
            FROM `classes` cl
            LEFT JOIN `attendance` a ON (cl.`class_id`=a.`class_id` AND a.`user_id`=?)
            LEFT JOIN `courses` co ON (cl.`course_id`=co.`course_id`)
            $sql  ORDER BY `class_date` DESC";
    if ($page != null) { $sql .= " LIMIT {$pgn["x"]}, {$pgn["y"]}"; }
    array_unshift($data, $uid);

    // (J4) RESULTS
    $classes = $this->DB->fetchAll($sql, $data, "class_id");
    return $page != null
     ? ["data" => $classes, "page" => $pgn]
     : $classes ;
  }
}
