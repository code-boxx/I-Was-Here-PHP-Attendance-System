<?php
class Course {
  // (A) COUNTALL () : COUNT TOTAL NUMBER OF COURSES
  //  $search : optional search term
  function countAll ($search="") {
    $sql = "SELECT COUNT(*) `c` FROM `courses`";
    $cond = null;
    if ($search!="") {
      $sql .= " WHERE `course_code` LIKE ? OR `course_name` LIKE ?";
      $cond = ["%$search%", "%$search%"];
    }
    $c = $this->core->fetch($sql, $cond);
    return $c['c'];
  }

  // (B) GETALL () : GET ALL COURSES
  //  $search : optional search term
  //  $limit : optional limit SQL (for pagination)
  function getAll ($search="", $limit=true) {
    $sql = "SELECT * FROM `courses`";
    $cond = null;
    if ($search!="" && $search!=null) {
      $sql .= " WHERE `course_code` LIKE ? OR `course_name` LIKE ?";
      $cond = ["%$search%", "%$search%"];
    }
    if ($limit) { $sql .= $this->core->Page->limit(); }
    return $this->core->fetchAll($sql, $cond, "course_code");
  }

  // (C) GET () : GET COURSE BY CODE
  //  $code : course code
  function get ($code) {
    return $this->core->fetch(
      "SELECT * FROM `courses` WHERE `course_code`=?", [$code]
    );
  }

  // (D) SAVE () : ADD/EDIT COURSE
  //  $code : course code
  //  $name : course name
  //  $desc : course description (optional)
  //  $ocode : old course code, for edit only
  function save ($code, $name, $desc=null, $ocode=null) {
    // (D1) CHECK CODE
    if ($ocode==null || ($code!=$ocode)) {
      if ($this->get($code)!==false) {
        $this->error = "$code is already registered";
        return false;
      }
    }
    
    // (D2) ADD COURSE
    if ($ocode==null) {
      return $this->core->exec(
        "INSERT INTO `courses` (`course_code`, `course_name`, `course_desc`) VALUES (?,?,?)",
        [$code, $name, $desc]
      );
    }

    // (D3) EDIT COURSE
    else {
      // (D3A) UPDATE MAIN ENTRY
      $this->core->start();
      $pass = $this->core->exec(
        "UPDATE `courses` SET `course_code`=?, `course_name`=?, `course_desc`=? WHERE `course_code`=?",
        [$code, $name, $desc, $ocode]
      );

      // (D3B) UPDATE COURSE STUDENTS
      if ($pass && $ocode!=$code) {
        $pass = $this->core->exec(
          "UPDATE `courses_students` SET `course_code`=? WHERE `course_code`=?",
          [$code, $ocode]
        );
      }

      // (D3C) UPDATE CLASSES
      if ($pass && $ocode!=$code) {
        $pass = $this->core->exec(
          "UPDATE `classes` SET `course_code`=? WHERE `course_code`=?",
          [$code, $ocode]
        );
      }

      // (D3D) RESULT
      $this->core->end($pass);
      return $pass;
    }
  }

  // (E) DEL () : DELETE COURSE
  //  $code : course code
  function del ($code) {
    // (E1) REMOVE ATTENDANCE RECORDS
    $this->core->start();
    $pass = $this->core->exec(
      "DELETE FROM `attendence` WHERE `class_id` IN (SELECT `class_id` FROM `classes` WHERE `course_code`=?)",
      [$code]
    );
    
    // (E2) REMOVE CLASSES
    if ($pass) {
      $pass = $this->core->exec("DELETE FROM `classes` WHERE `course_code`=?", [$code]);
    }
    
    // (E3) REMOVE COURSE STUDENTS
    if ($pass) {
      $pass = $this->core->exec("DELETE FROM `courses_students` WHERE `course_code`=?", [$code]);
    }

    // (E4) REMOVE MAIN COURSE
    if ($pass) {
      $pass = $this->core->exec("DELETE FROM `courses` WHERE `course_code`=?", [$code]);
    }

    // (E5) RESULT
    $this->core->end($pass);
    return $pass;
  }
  
  // (F) STUCOUNT () : COUNT TOTAL NUMBER OF STUDENTS IN COURSE
  //  $code : course code
  function stuCount ($code) {
    $c = $this->core->fetch(
      "SELECT COUNT(*) `c` FROM `courses_students` WHERE `course_code`=?", [$code]
    );
    return $c['c'];
  }

  // (G) STUGET () : GET STUDENTS IN COURSE
  //  $code : course code
  //  $limit : optional limit SQL (for pagination)
  function stuGet ($code, $limit=true) {
    $sql = "SELECT u.* FROM `courses_students` c LEFT JOIN `users` u USING (`user_id`) WHERE c.`course_code`=?";
    $cond = [$code];
    if ($limit) { $sql .= $this->core->Page->limit(); }
    return $this->core->fetchAll($sql, $cond, "user_id");
  }

  // (H) STUADD () : ADD STUDENT TO COURSE
  // $code : course code
  // $email : user email
  function stuAdd ($code, $email) {
    // (H1) CHECKS
    $user = $this->core->fetch(
      "SELECT * FROM `users` WHERE `user_email`=? AND `user_role`='S'", [$email]
    );
    if ($user==false) {
      $this->error = "$email is not registered or not a student.";
      return false;
    }
    $check = $this->core->fetch(
      "SELECT * FROM `courses_students` WHERE `course_code`=? AND `user_id`=?",
      [$code, $user['user_id']]
    );
    if (is_array($check)) {
      $this->error = $user['user_name']." is already a student of ".$code;
      return false;
    }

    // (H2) ADD
    return $this->core->exec(
      "INSERT INTO `courses_students` (`course_code`, `user_id`) VALUES (?,?)",
      [$code, $user['user_id']]
    );
  }

  // (I) STUDEL () : REMOVE STUDENT FROM COURSE
  //  $code : course code
  //  $id : user ID
  function stuDel ($code, $id) {
    return $this->core->exec(
      "DELETE FROM `courses_students` WHERE `course_code`=? AND `user_id`=?",
      [$code, $id]
    );
  }
}