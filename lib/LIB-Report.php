<?php
class Report extends Core {
  // (A) ATTENDANCE REPORT
  function attend ($id) {
    // (A1) GET COURSE
    $course = $this->DB->fetch(
      "SELECT *, DATE_FORMAT(`course_start`, '".D_LONG."') `sd`, DATE_FORMAT(`course_end`, '".D_LONG."') `ed`
       FROM `courses` WHERE `course_id`=?", [$id]
    );

    // (A2) OUTPUT CSV HEADER
    header("Content-Disposition: attachment; filename={$course["course_code"]}.csv;");
    $f = fopen("php://output", "w");
    fputcsv($f, [strtoupper("[{$course["course_code"]}] {$course["course_name"]}")]);
    fputcsv($f, ["FROM {$course["sd"]} TO {$course["ed"]}"]);
    unset($course);

    // (A3) CLASSES
    $this->DB->query(
      "SELECT *, DATE_FORMAT(`class_date`, '".DT_LONG."') `cd`
       FROM `classes` WHERE `course_id`=? ORDER BY `class_date` ASC", [$id]
    );
    $class = [];
    $classdate = ["STUDENT/CLASS"];
    while ($r = $this->DB->stmt->fetch()) {
      $class[] = $r["class_id"];
      $classdate[] = $r["cd"];
    }
    fputcsv($f, $classdate);
    unset($classdate);

    // (A4) STUDENTS
    $this->DB->query(
      "SELECT cu.`user_id`, a.`class_id`, a.`sign_date`, u.`user_name`, u.`user_email`
       FROM `courses_users` cu 
       LEFT JOIN `users` u ON (cu.`user_id`=u.`user_id`)
       LEFT JOIN `attendance` a ON (cu.`user_id`=a.`user_id`)
       WHERE cu.`course_id`=? AND u.`user_level`='S'
       ORDER BY u.`user_id`, a.`class_id`", [$id]
    );
    $uid = 0; $user = null; $attended = null;
    while ($r = $this->DB->stmt->fetch()) {
      // (A4-1) OUTPUT + NEXT STUDENT
      if ($r["user_id"]!=$uid) {
        if ($uid!=0) {
          $row = ["{$user["n"]} ({$user["e"]})"];
          foreach ($class as $cid) {
            $row[] = isset($attended[$cid]) ? "1" : "" ;
          }
          fputcsv($f, $row);
          unset($row);
        }
        $uid = $r["user_id"];
        $user = ["n" => $r["user_name"], "e" => $r["user_email"]];
        $attended = [];
      }
      
      // (A4-2) COLLECT ATTENDANCE
      if ($r["class_id"]!=null) { $attended[$r["class_id"]] = $r["sign_date"]; }
    }

    // (A4-3) LAST STUDENT
    if ($uid!=0) {
      $row = ["{$user["n"]} ({$user["e"]})"];
      foreach ($class as $cid) {
        $row[] = isset($attended[$cid]) ? "1" : "" ;
      }
      fputcsv($f, $row);
      unset($row);
    }

    // (A5) DONE
    fclose($f);
  }
}