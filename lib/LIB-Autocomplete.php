<?php
class Autocomplete extends Core {
  // (A) HELPER - RUN SQL SEARCH & FORMAT RESULTS
  //  $sql : sql query string
  //  $data : parameters
  //  $n : set this column as name
  //  $v : set this column as value (optional)
  function query ($sql, $data, $n, $v=null) {
    $this->DB->query($sql . " LIMIT " . SUGGEST_LIMIT, $data);
    $res = [];
    if ($v==null) {
      while ($r = $this->DB->stmt->fetch()) {
        $res[] = ["n" => $r[$n]];
      }
    } else {
      while ($r = $this->DB->stmt->fetch()) {
        $res[] = ["n" => $r[$n], "v" => $r[$v]];
      }
    }
    return $res;
  }

  // (B) SUGGEST USER  
  function user ($search) {
    return $this->query(
      "SELECT * FROM `users` WHERE `user_name` LIKE ?",
      ["%$search%"], "user_name"
    );
  }

  // (C) SUGGEST USER EMAIL
  function userEmail ($search) {
    return $this->query(
      "SELECT * FROM `users` WHERE `user_name` LIKE ? OR `user_email` LIKE ?",
      ["%$search%", "%$search%"], "user_name", "user_email"
    );
  }

  // (D) SUGGEST COURSE  
  function course ($search) {
    return $this->query(
      "SELECT * FROM `courses` WHERE `course_code` LIKE ? OR `course_name` LIKE ?",
      ["%$search%", "%$search%"], "course_name", "course_code"
    );
  }

  // (E) "SPECIAL ENDPOINT" USED BY ADD/EDIT CLASS
  function icourse ($code) {
    $this->Core->load("Courses");
    return [
      "c" => $this->Courses->get($code),
      "t" => $this->Courses->getTeachers($code)
    ];
  }
}