<?php
class Classes {
  // (A) COUNTALL () : COUNT TOTAL NUMBER OF CLASSES
  //  $id : teacher user ID
  function countAll ($id=null) {
    $sql = "SELECT COUNT(*) `c` FROM `classes`";
    $cond = null;
    if ($id!==null) {
      $sql .= " WHERE `user_id`=?";
      $cond = [$id];
    }
    $c = $this->core->fetch($sql, $cond);
    return $c['c'];
  }

  // (B) GETALL () : GET ALL CLASSES
  //  $id : teacher user ID
  //  $limit : optional limit SQL (for pagination)
  function getAll ($id=null, $limit=true) {
    $sql = "SELECT c.*, u.`user_name` FROM `classes` c LEFT JOIN `users` u USING (`user_id`)";
    $cond = null;
    if ($id!==null) {
      $sql .= " WHERE c.`user_id`=?";
      $cond = [$id];
    }
    $sql .= " ORDER BY c.`class_date` DESC";
    if ($limit) { $sql .= $this->core->Page->limit(); }
    return $this->core->fetchAll($sql, $cond, "class_id");
  }

  // (C) GET () : GET CLASS BY ID
  //  $id : class ID
  function get ($id) {
    return $this->core->fetch(
      "SELECT * FROM `classes` WHERE `class_id`=?", [$id]
    );
  }

  // (D) SAVE () : ADD/EDIT CLASS
  //  $code : course code
  //  $id : user ID, teacher-in-charge
  //  $date : date and time
  //  $desc : description, optional
  //  $cid : class ID, for edit only
  function save ($code, $id, $date, $desc, $cid=null) {
    // (D1) ADD CLASS
    if ($cid===null) {
      $sql = "INSERT INTO `classes` (`course_code`, `user_id`, `class_date`, `class_desc`) VALUES (?,?,?,?)";
      $cond = [$code, $id, $date, $desc];
    }

    // (D2) EDIT CLASS
    else {
      $sql = "UPDATE `classes` SET `course_code`=?, `user_id`=?, `class_date`=?, `class_desc`=? WHERE `class_id`=?";
      $cond = [$code, $id, $date, $desc, $cid];
    }

    // (D3) GO!
    return $this->core->exec($sql, $cond);
  }

  // (E) DEL () : DELETE CLASS
  //  $id : class ID
  function del ($id) {
    // (E1) REMOVE ATTENDANCE RECORDS
    $this->core->start();
    $sql = "DELETE FROM `attendence` WHERE `class_id`=?";
    $pass = $this->core->exec($sql, [$id]);

    // (E2) REMOVE CLASS
    if ($pass) {
      $sql = "DELETE FROM `classes` WHERE `class_id`=?";
      $pass = $this->core->exec($sql, [$id]);
    }

    // (E3) RESULT
    $this->core->end($pass);
    return $pass;
  }

  // (F) ATTENDGET() : GET CLASS ATTENDANCE
  //  $id : class ID
  function attendGet ($id) {
    // (F1) GET THE CLASS
    $class = $this->get($id);
    if (!is_array($class)) {
      $this->error = "Invalid class";
      return false;
    }

    // (F2) GET ALL STUDENTS
    $all = $this->core->fetchAll(
      "SELECT u.* FROM `courses_students` c LEFT JOIN `users` u USING (`user_id`) WHERE `course_code`=? ORDER BY u.`user_name` ASC",
      [$class['course_code']], "user_id"
    );

    // (F3) GET CURRENT ATTENDANCE
    $sql = "SELECT `user_id` FROM `attendence` WHERE `class_id`=?";
    $this->stmt = $this->pdo->prepare($sql);
    $this->stmt->execute([$id]);
    while ($row = $this->stmt->fetch()) { $all[$row['user_id']]['a'] = 1; }
    
    // (F4) RESULTS
    return [
      "class" => $class,
      "all" => $all
    ];
  }

  // (G) ATTENDSAVE () : UPDATE ATTENDANCE
  //  $cid : class ID
  //  $uid : user ID
  //  $attend : 1 or 0, yes or no
  //  $time : assume now if null
  function attendSave ($cid, $uid, $attend, $time=null) {
    if ($attend) {
      $sql = "REPLACE INTO `attendence` (`class_id`, `user_id`, `sign_date`) VALUES (?,?,?)";
      if ($time===null) { $time = date("Y-m-d H:i:s"); }
      $cond = [$cid, $uid, $time];
    } else {
      $sql = "DELETE FROM `attendence` WHERE `class_id`=? AND `user_id`=?";
      $cond = [$cid, $uid];
    }
    return $this->core->exec($sql, $cond);
  }
  
  // (H) ATTENDSTUCOUNT () : COUNT STUDENT ATTENDENCE RECORDS
  //  $id : student ID
  function attendStuCount ($id) {
    $c = $this->core->fetch(
      "SELECT COUNT(*) c FROM `attendence` WHERE `user_id`=?", [$id]
    );
    return $c['c'];
  }
  
  // (I) ATTENDSTUGET () : GET STUDENT ATTENDANCE
  //  $id : student ID
  //  $limit : optional limit SQL (for pagination)
  function attendStuGet ($id, $limit=true) {
    $sql = "SELECT * FROM `attendence` a LEFT JOIN `classes` c USING (`class_id`)"
         . " WHERE a.`user_id`=?"
         . " ORDER BY c.`class_date` DESC";
    if ($limit) { $sql .= $this->core->Page->limit(); }
    return $this->core->fetchAll($sql, [$id]);
  }
}