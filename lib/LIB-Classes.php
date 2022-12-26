<?php
class Classes extends Core {
  // (A) ADD OR UPDATE CLASS
  //  $cid : course id
  //  $uid : user id (teacher in charge)
  //  $date : class date
  //  $desc : class description
  //  $id : class id (for edit only)
  function save ($cid, $uid, $date, $desc=null, $id=null) {
    // (A1) DATA SETUP
    $fields = ["course_id", "user_id", "class_date", "class_desc"];
    $data = [$cid, $uid, $date, $desc];

    // (A2) ADD/UPDATE CLASS
    if ($id==null) {
      $fields[] = "class_hash";
      $data[] = $this->core->random(12);
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
  //  $id : class id
  function get ($id) {
    return $this->DB->fetch(
      "SELECT cl.*, DATE_FORMAT(cl.`class_date`, '".DT_LONG."') `cd`, co.`course_code`, co.`course_name`, u.`user_name`
       FROM `classes` cl 
       LEFT JOIN `courses` co USING (`course_id`)
       LEFT JOIN `users` u USING (`user_id`)
       WHERE cl.`class_id`=?", [$id]
    );
  }

  // (D) GET CLASSES
  //  $search : optional, course code
  //  $page : optional, current page number
  function getAll ($search=null, $page=null) {
    // (D1) PARTIAL SQL
    $sql = " FROM `classes` cl 
            LEFT JOIN `courses` co USING (`course_id`)
            LEFT JOIN `users` u USING (`user_id`)";
    $data = null;
    if ($search != null) {
      $sql .= " WHERE co.`course_code` LIKE ?";
      $data = ["%$search%"];
    }

    // (D2) PAGINATION
    if ($page != null) {
      $this->core->paginator($this->DB->fetchCol(
        "SELECT COUNT(*) $sql", $data
      ), $page);
    }

    // (D3) RESULT
    $sql .= " ORDER BY `class_date` DESC";
    if ($page != null) { $sql .= $this->core->page["lim"]; }
    return $this->DB->fetchAll(
      "SELECT cl.*, DATE_FORMAT(cl.`class_date`, '".DT_LONG."') `cd`, co.`course_code`, co.`course_name`, u.`user_name` $sql",
      $data, "class_id"
    );
  }

  // (E) SET ATTENDANCE
  //  $id : class id
  //  $uid : user id or email
  function attend ($id, $uid) {
    // (E1) VERIFY VALID USER
    $this->core->load("Users");
    $user = $this->core->Users->get($uid);
    if (!is_array($user) || $user["user_role"]!="S") {
      $this->error = "Invalid user";
      return false;
    }

    // (E2) ADD ATTENDANCE
    $this->DB->replace("attendance",
      ["class_id", "user_id", "sign_date"],
      [$id, $user["user_id"], date("Y-m-d H:i:s")]);
    return true;
  }

  // (F) SET ATTENDANCE VIA QR
  //  $uid : user id
  //  $id : class id
  //  $hash : class hash
  function attendQR ($uid, $id, $hash) {
    // (F1) GET CLASS
    $class = $this->get($id);
    if (!is_array($class)) {
      $this->error = "Invalid class.";
      return false;
    }

    // (F2) VERIFY
    if ($class["class_hash"]!=$hash) {
      $this->error = "Invalid class.";
      return false;
    }

    // (F3) SAVE ATTENDANCE
    return $this->attend($id, $uid);
  }

  // (G) REMOVE ATTENDANCE
  //  $id : class id
  //  $uid : user id
  function absent ($id, $uid) {
    $this->DB->delete("attendance", "`class_id`=? AND `user_id`=?", [$id, $uid]);
    return true;
  }

  // (H) GET STUDENT & ATTENDANCE FOR CLASS
  //  $id : class id
  function getStudents ($id) {
    // (H1) GET COURSE ID
    $cid = $this->DB->fetchCol(
      "SELECT `course_id` FROM `classes` WHERE `class_id`=?", [$id]
    );

    // (H2) GET ALL STUDENTS FOR THE CLASS
    $sql = "SELECT u.`user_id`, u.`user_name`, u.`user_email`
            FROM `courses_users` cu
            JOIN `users` u USING (`user_id`)
            WHERE cu.`course_id`=? AND u.`user_role`='S'
            ORDER BY `user_name`";
    $data = [$cid];
    $students = $this->DB->fetchAll($sql, $data, "user_id");
    if (!is_array($students)) { $students = []; }

    // (H3) GET ATTENDANCE
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

    // (H4) RESULTS
    return $students;
  }

  // (I) SAVE CLASS ATTENDANCE
  //  $id : class id
  //  $list : present students (array or json encoded array)
  //          send empty array to indicate all absent
  function attendance ($id, $list) {
    // (I1) SORT OUT THE LIST
    if (!is_array($list)) {
      try { $list = json_decode($list); }
      catch (Exception $ex) {
        $this->error = $ex->getMessage();
        return false;
      }
    }

    // (I2) DELETE OLD ENTRIES
    $this->DB->start();
    $this->DB->delete("attendance", "`class_id`=?", [$id]);

    // (I3) ADD NEW ENTRIES
    if (count($list)>0) {
      $fields = ["class_id", "user_id"];
      $data = [];
      foreach ($list as $uid) { $data[] = $id; $data[] = $uid; }
      $this->DB->insert("attendance", $fields, $data);
    }

    // (I4) RESULTS
    $this->DB->end();
    return true;
  }

  // (J) GET CLASSES FOR TEACHER
  //  $uid : user id
  //  $date : date range restriction
  //  $range : -1 for all classes before $date
  //           1 for all classes after $date
  //           0 all classes on $date
  //           "" or null for no restriction
  //  $page : current page number, optional
  function getByTeacher ($uid, $date=null, $range=null, $page=null) {
    // (J1) PARTIAL SQL
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

    // (J2) PAGINATION
    if ($page != null) {
      $this->core->paginator(
        $this->DB->fetchCol("SELECT COUNT(*) $sql", $data), $page
      );
    }

    // (J3) GET CLASSES
    $sql = "SELECT cl.*, DATE_FORMAT(cl.`class_date`, '".DT_LONG."') `cd`, co.`course_code`, co.`course_name` $sql ORDER BY `class_date` DESC";
    if ($page != null) { $sql .= $this->core->page["lim"]; }

    // (J4) RESULTS
    return $this->DB->fetchAll($sql, $data, "class_id");
  }

  // (K) GET CLASSES FOR STUDENT
  //  $uid : user id
  //  $date : date range restriction
  //  $range : -1 for all classes before $date
  //           1 for all classes after $date
  //           0 all classes on $date
  //           "" or null for no restriction
  //  $page : current page number, optional
  function getByStudent ($uid, $date=null, $range=null, $page=null) {
    // (K1) PARTIAL SQL
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

    // (K2) PAGINATION
    if ($page != null) {
      $this->core->paginator(
        $this->DB->fetchCol("SELECT COUNT(*) FROM `classes` cl $sql", $data), $page
      );
    }

    // (K3) GET CLASSES
    $sql = "SELECT cl.*, DATE_FORMAT(cl.`class_date`, '".DT_LONG."') `cd`, co.`course_code`, co.`course_name`, a.`sign_date`
            FROM `classes` cl
            LEFT JOIN `attendance` a ON (cl.`class_id`=a.`class_id` AND a.`user_id`=?)
            LEFT JOIN `courses` co ON (cl.`course_id`=co.`course_id`)
            $sql  ORDER BY `class_date` DESC";
    if ($page != null) { $sql .= $this->core->page["lim"]; }
    array_unshift($data, $uid);

    // (K4) RESULTS
    return $this->DB->fetchAll($sql, $data, "class_id");
  }
}