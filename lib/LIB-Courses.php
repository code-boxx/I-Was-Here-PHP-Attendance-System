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

    // (A2) ADD/UPDATE USER
    if ($id===null) {
      return $this->DB->insert("courses", $fields, $data);
    } else {
      $data[] = $id;
      return $this->DB->update("courses", $fields, "`course_id`=?", $data);
    }
  }

  // (B) DELETE COURSE
  //  $id : course id
  function del ($id) {
    // (B1) DELETE ATTENDANCE
    $this->DB->start();
    $pass = $this->DB->query("DELETE FROM `attendance` WHERE `course_id`=?", [$id]);

    // (B2) DELETE CLASSES
    if ($pass) {
      $pass = $this->DB->query("DELETE FROM `classes` WHERE `course_id`=?", [$id]);
    }

    // (B3) DELETE COURSE USERS
    if ($pass) {
      $pass = $this->DB->query("DELETE FROM `courses_users` WHERE `course_id`=?", [$id]);
    }

    // (B4) DELETE COURSE
    if ($pass) {
      $pass = $this->DB->query("DELETE FROM `courses` WHERE `course_id`=?", [$id]);
    }

    // (B5) RESULT
    $this->DB->end($pass);
    return $pass;
  }

  // (C) GET COURSE
  //  $id : course id or code
  function get ($id) {
    return $this->DB->fetch(
      "SELECT * FROM `courses` WHERE `course_". (is_numeric($id)?"id":"code") ."`=?",
      [$id]
    );
  }

  // (D) COUNT (FOR SEARCH & PAGINATION)
  //  $search : optional, course code or name
  function count ($search=null) {
    // "MAIN SELECT"
    $sql = "SELECT COUNT(*) FROM `courses`";
    $data = null;

    // ADD SEARCH
    if ($search!=null && $search!="") {
      $sql .= " WHERE `course_code` LIKE ? OR `course_name` LIKE ?";
      $data = ["%$search%", "%$search%"];
    }
    return $this->DB->fetchCol($sql, $data);
  }

  // (E) GET ALL OR SEARCH COURSES
  //  $search : optional, user name or email
  //  $page : optional, current page number
  function getAll ($search=null, $page=1) {
    // (E1) PAGINATION
    $entries = $this->count($search);
    if ($entries===false) { return false; }
    $pgn = $this->core->paginator($entries, $page);

    // (E2) GET COURSES
    // "MAIN SELECT"
    $sql = "SELECT * FROM `courses`";
    $data = null;

    // ADD SEARCH
    if ($search!=null && $search!="") {
      $sql .= " WHERE `course_code` LIKE ? OR `course_name` LIKE ?";
      $data = ["%$search%", "%$search%"];
    }

    // LIMIT
    $sql .= " ORDER BY `course_id` DESC LIMIT {$pgn["x"]}, {$pgn["y"]}";

    // (E3) RESULTS
    $courses = $this->DB->fetchAll($sql, $data, "course_id");
    if ($courses===false) { return false; }
    return ["data" => $courses, "page" => $pgn];
  }

  // (F) ADD USER TO COURSE
  //  $cid : course id
  //  $uid : user id or email
  function addUser ($cid, $uid) {
    // (F1) VERIFY VALID USER
    $this->core->load("Users");
    $user = $this->core->Users->get($uid);
    if ($user===false || !is_array($user) || $user["user_role"]=="I") {
      $this->error = "Invalid user";
      return false;
    }

    // (F2) ADD TO COURSE
    return $this->DB->insert("courses_users",
      ["course_id", "user_id"], [$cid, $user["user_id"]], true);
  }

  // (G) DELETE USER FROM COURSE
  //  $cid : course id
  //  $uid : user id or email
  function delUser ($cid, $uid) {
    return $this->DB->query(
      "DELETE FROM `courses_users` WHERE `course_id`=? AND `user_id`=?",
      [$cid, $uid]
    );
  }

  // (H) COUNT USERS IN COURSE (FOR SEARCH & PAGINATION)
  //  $cid : course id
  function countUsers ($id) {
    $sql = "SELECT COUNT(*) FROM `courses_users` cu
            JOIN `users` u USING (`user_id`)
            WHERE cu.`course_id`=? AND u.`user_role`!='I'";
    $data = [$id];
    return $this->DB->fetchCol($sql, $data);
  }

  // (I) GET USERS IN COURSE
  //  $cid : course id
  //  $page : optional, current page number
  function getUsers ($id, $page=1) {
    // (I1) PAGINATION
    $entries = $this->countUsers($id);
    if ($entries===false) { return false; }
    $pgn = $this->core->paginator($entries, $page);

    // (I2) SQL
    // "MAIN SQL"
    $sql = "SELECT * FROM `courses_users` cu
            JOIN `users` u USING (`user_id`)
            WHERE cu.`course_id`=? AND u.`user_role`!='I'
            ORDER BY FIELD(`user_role`, 'A','T','S'), `user_name`";
    $data = [$id];

    // LIMIT
    $sql .= " LIMIT {$pgn["x"]}, {$pgn["y"]}";

    // (I3) RESULTS
    $users = $this->DB->fetchAll($sql, $data, "user_id");
    if ($users===false) { return false; }
    return ["data" => $users, "page" => $pgn];
  }

  // (J) GET TEACHERS IN COURSE
  //  $id : course id
  function getTeachers ($id) {
    return $this->DB->fetchAll(
      "SELECT u.* FROM `courses_users` c
       JOIN `users` u USING (`user_id`)
       WHERE u.`user_role` IN ('A', 'T')
       AND c.`course_id`=?
       ORDER BY `user_name` ASC",
       [$id], "user_id"
    );
  }
}
