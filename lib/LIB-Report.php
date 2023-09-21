<?php
class Report extends Core {
  // (A) ATTENDANCE REPORT
  function attend ($code) {
    // (A1) GET COURSE
    $course = $this->DB->fetch(
      "SELECT *, DATE_FORMAT(`course_start`, '".D_LONG."') `sd`, DATE_FORMAT(`course_end`, '".D_LONG."') `ed`
       FROM `courses`
       WHERE `course_code`=?",
       [$code]
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
       FROM `classes`
       WHERE `course_code`=?
       ORDER BY `class_date` ASC", [$code]
    );
    $class = []; $i = 1;
    $classdate = ["STUDENT/CLASS"];
    while ($r = $this->DB->stmt->fetch()) {
      $class[$r["class_id"]] = $i; $i++;
      $classdate[] = $r["cd"];
    }
    fputcsv($f, $classdate);
    unset($classdate); unset($i);

    // (A4) ASSUME "ABSENT" IF NO ATTENDANCE RECORDS ARE FOUND
    $preattend = [];
    for ($i=0; $i<count($class); $i++) { $preattend[] = 0; }

    // (A5) STUDENTS & ATTENDANCE
    $this->DB->query(
      "SELECT u.`user_name`, u.`user_email`, a.`user_id`, a.`class_id`
       FROM `attendance` a
       LEFT JOIN `users` u ON (a.`user_id`=u.`user_id`)
       LEFT JOIN `courses_users` cu  ON (a.`user_id`=cu.`user_id`)
       WHERE a.`a_status`=? AND cu.`course_code`=? AND u.`user_level`='U'
       ORDER BY u.`user_id`, a.`class_id`",
       [1, $code]
    );
    $uid = 0;
    while ($r = $this->DB->stmt->fetch()) {
      // (A5-1) NEXT STUDENT
      if ($r["user_id"]!=$uid) {
        if ($uid!=0) { fputcsv($f, $row); }
        $row = ["{$r["user_name"]} ({$r["user_email"]})"];
        $row = array_merge($row, $preattend);
        $uid = $r["user_id"];
      }

      // (A5-2) ATTENDANCE RECORD
      $row[$class[$r["class_id"]]] = 1;
    }

    // (A5-3) LAST STUDENT
    if ($uid!=0) { fputcsv($f, $row); }

    // (A6) DONE
    fclose($f);
  }
}