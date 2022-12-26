<?php
class Courses extends Core {
  // (A) ADD OR UPDATE COURSE
  //  $code : course code
  //  $name : course name
  //  $start : start date
  //  $end : end date
  //  $desc : course description
  function save ($code, $name, $start, $end, $desc=null, $id=null) {
    // (A1) DATA SETUP
    if (strtotime($end) < strtotime($start)) {
      $this->error = "End date cannot be earlier than start";
      return false;
    }
    $fields = ["course_code", "course_name", "course_start", "course_end", "course_desc"];
    $data = [$code, $name, $start, $end, $desc];

    // (A2) ADD/UPDATE COURSE
    if ($id===null) {
      $this->DB->insert("courses", $fields, $data);
    } else {
      $data[] = $id;
      $this->DB->update("courses", $fields, "`course_id`=?", $data);
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
    $this->save($code, $name, $start, $end, $desc, is_array($course)?$course["course_id"]:null);
    return true;
  }

  // (C) DELETE COURSE
  //  $id : course id
  function del ($id) {
    $this->DB->start();
    $this->DB->delete("attendance", "`course_id`=?", [$id]);
    $this->DB->delete("classes", "`course_id`=?", [$id]);
    $this->DB->delete("courses_users", "`course_id`=?", [$id]);
    $this->DB->delete("courses", "`course_id`=?", [$id]);
    $this->DB->end();
    return true;
  }

  // (D) GET COURSE
  //  $id : course id or code
  function get ($id) {
    return $this->DB->fetch(
      "SELECT * FROM `courses` WHERE `course_". (is_numeric($id)?"id":"code") ."`=?",
      [$id]
    );
  }

  // (E) SEARCH COURSE - FOR AUTOCOMPLETE USE
  //  $search : course name or code
  function autocomplete ($search) {
    $sql = "SELECT * FROM `courses` WHERE `course_code` LIKE ? OR `course_name` LIKE ? LIMIT 5";
    $data = ["%$search%", "%$search%"];
    $this->DB->query($sql, $data);
    $result = [];
    while ($row = $this->DB->stmt->fetch()) {
      $result[] = [
        "d" => "{$row["course_code"]} ({$row["course_name"]})",
        "v" => $row["course_id"]
      ];
    }
    return count($result)==0 ? null : $result;
  }

  // (F) GET ALL OR SEARCH COURSES
  //  $search : optional, course code or name
  //  $page : optional, current page number
  function getAll ($search=null, $page=null) {
    // (F1) PARITAL SQL + DATA
    $sql = "FROM `courses`";
    $data = null;
    if ($search != null) {
      $sql .= " WHERE `course_code` LIKE ? OR `course_name` LIKE ?";
      $data = ["%$search%", "%$search%"];
    }

    // (F2) PAGINATION
    if ($page != null) {
      $this->core->paginator(
        $this->DB->fetchCol("SELECT COUNT(*) $sql", $data), $page
      );
      $sql .= $this->core->page["lim"];
    }

    // (F3) RESULTS
    return $this->DB->fetchAll(
      "SELECT *, DATE_FORMAT(`course_start`, '".D_SHORT."') `sd`, DATE_FORMAT(`course_end`, '".D_SHORT."') `ed` $sql",
       $data, "course_id"
    );
  }

  // (G) ADD USER TO COURSE
  //  $cid : course id
  //  $uid : user id or email
  function addUser ($cid, $uid) {
    // (G1) VERIFY VALID USER
    $this->core->load("Users");
    $user = $this->core->Users->get($uid);
    if (!is_array($user) || $user["user_role"]=="I") {
      $this->error = "Invalid user";
      return false;
    }

    // (G2) ADD TO COURSE
    $this->DB->replace("courses_users", ["course_id", "user_id"], [$cid, $user["user_id"]]);
    return true;
  }

  // (H) DELETE USER FROM COURSE
  //  $cid : course id
  //  $uid : user id or email
  function delUser ($cid, $uid) {
    $this->DB->delete("courses_users", "`course_id`=? AND `user_id`=?", [$cid, $uid]);
    return true;
  }

  // (I) GET ALL USERS IN COURSE
  //  $id : course id
  //  $page : optional, current page number
  function getUsers ($id, $page=null) {
    // (I1) PARITAL SQL + DATA
    $sql = "FROM `courses_users` cu
            JOIN `users` u USING (`user_id`)
            WHERE cu.`course_id`=? AND u.`user_role`!='I'";
    $data = [$id];

    // (I2) PAGINATION
    if ($page != null) {
      $this->core->paginator(
        $this->DB->fetchCol("SELECT COUNT(*) $sql", $data), $page
      );
    }

    // (I3) "MAIN SQL"
    $sql .= " ORDER BY FIELD(`user_role`, 'A','T','S'), `user_name`";
    if ($page != null) { $sql .= $this->core->page["lim"]; }

    // (I4) RESULTS
    return $this->DB->fetchAll("SELECT * $sql", $data, "user_id");
  }

  // (J) GET TEACHERS IN COURSE
  //  $id : course id
  function getTeachers ($id) {
    return $this->DB->fetchAll(
      "SELECT u.`user_id`, u.`user_name`, u.`user_email`
       FROM `courses_users` c
       JOIN `users` u USING (`user_id`)
       WHERE u.`user_role` IN ('A', 'T')
       AND c.`course_id`=?
       ORDER BY `user_name` ASC",
       [$id], "user_id"
    );
  }
}