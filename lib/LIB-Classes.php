<?php
class Classes extends Core {
  // (A) ADD OR UPDATE CLASS
  //  $code : course code
  //  $uid : user id (teacher in charge)
  //  $date : class date
  //  $desc : class description
  //  $id : class id (for edit only)
  function save ($code, $uid, $date, $desc=null, $id=null) {
    // (A1) DATA SETUP
    $fields = ["course_code", "user_id", "class_date", "class_desc"];
    $data = [$code, $uid, $date, $desc];

    // (A2) ADD/UPDATE CLASS
    if ($id==null) {
      $fields[] = "class_hash";
      $data[] = $this->Core->random(12);
      $this->DB->insert("classes", $fields, $data);
    } else {
      $data[] = $id;
      $this->DB->update("classes", $fields, "`class_id`=?", $data);
    }
    return true;
  }

  // (B) IMPORT CLASS
  //  $code : course code
  //  $date : class date & time
  //  $email : teacher's email
  //  $desc : description, optional
  function import ($code, $date, $email, $desc) {
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
    $this->save($course["course_code"], $teacher["user_id"], $date, $desc);
    return true;
  }

  // (C) DELETE CLASS
  //  $id : class id
  function del ($id) {
    $this->DB->start();
    $this->DB->delete("attendance", "`class_id`=?", [$id]);
    $this->DB->delete("classes", "`class_id`=?", [$id]);
    $this->DB->end();
    return true;
  }

  // (D) GET CLASS
  //  $id : class id
  function get ($id) {
    return $this->DB->fetch(
      "SELECT cl.*, DATE_FORMAT(cl.`class_date`, '".DT_LONG."') `cd`, co.`course_code`, co.`course_name`, u.`user_name`
       FROM `classes` cl 
       LEFT JOIN `courses` co USING (`course_code`)
       LEFT JOIN `users` u USING (`user_id`)
       WHERE cl.`class_id`=?", [$id]
    );
  }

  // (E) GET CLASSES
  //  $search : optional, course code
  //  $page : optional, current page number
  function getAll ($search=null, $page=null) {
    // (E1) PARTIAL SQL
    $sql = " FROM `classes` cl 
            LEFT JOIN `courses` co USING (`course_code`)
            LEFT JOIN `users` u USING (`user_id`)";
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
      "SELECT cl.*, DATE_FORMAT(cl.`class_date`, '".DT_LONG."') `cd`, co.`course_code`, co.`course_name`, u.`user_name` $sql",
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
    $sql = "SELECT cl.*, DATE_FORMAT(cl.`class_date`, '".DT_LONG."') `cd`, co.`course_code`, co.`course_name` $sql ORDER BY `class_date` ASC";
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
    $sql = "SELECT cl.*, DATE_FORMAT(cl.`class_date`, '".DT_LONG."') `cd`, co.`course_code`, co.`course_name`, a.`a_status`
            FROM `classes` cl
            LEFT JOIN `attendance` a ON (cl.`class_id`=a.`class_id` AND a.`user_id`=?)
            LEFT JOIN `courses` co ON (cl.`course_code`=co.`course_code`)
            $sql  ORDER BY `class_date` ASC";
    if ($page != null) { $sql .= $this->Core->page["lim"]; }
    array_unshift($data, $uid);

    // (G4) RESULTS
    return $this->DB->fetchAll($sql, $data, "class_id");
  }
}