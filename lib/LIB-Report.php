<?php
class Report extends Core {
  private function csvRow ($file, $values) {
    foreach ($values as &$value) {
      if (is_string($value) && preg_match('/^[\t\r ]*[=+\-@]/', $value)) {
        $value = "'" . $value;
      }
    }
    unset($value);
    fputcsv($file, $values);
  }

  // (A) ATTENDANCE REPORT
  function attend ($code) {
    // (A1) GET COURSE
    $course = $this->DB->fetch(
      "SELECT *, DATE_FORMAT(`course_start`, '".D_LONG."') `sd`, DATE_FORMAT(`course_end`, '".D_LONG."') `ed`
       FROM `courses`
       WHERE `course_code`=?",
       [$code]
    );
    if (!is_array($course)) { throw new Exception("Course not found."); }

    // (A2) OUTPUT CSV HEADER
    $filename = preg_replace('/[^A-Za-z0-9._-]/', '_', $course["course_code"]);
    header("Content-Type: text/csv; charset=utf-8");
    header("Content-Disposition: attachment; filename=\"{$filename}.csv\"");
    $f = fopen("php://output", "w");
    $this->csvRow($f, [strtoupper("[{$course["course_code"]}] {$course["course_name"]}")]);
    $this->csvRow($f, ["FROM {$course["sd"]} TO {$course["ed"]}"]);
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
    $classdate[] = "ATTENDANCE %";
    $this->csvRow($f, $classdate);
    unset($classdate); unset($i);

    // (A5) STUDENTS & ATTENDANCE, INCLUDING STUDENTS WITH NO CHECK-INS
    $this->DB->query(
      "SELECT u.`user_id`, u.`user_name`, u.`user_email`, cl.`class_id`, a.`a_status`
       FROM `courses_users` cu
       JOIN `users` u ON (cu.`user_id`=u.`user_id` AND u.`user_level`='U')
       LEFT JOIN `classes` cl ON (cu.`course_code`=cl.`course_code`)
       LEFT JOIN `attendance` a ON (a.`class_id`=cl.`class_id` AND a.`user_id`=u.`user_id`)
       WHERE cu.`course_code`=?
       ORDER BY u.`user_id`, cl.`class_date`",
       [$code]
    );
    $uid = null;
    $row = [];
    $present = 0;
    while ($r = $this->DB->stmt->fetch()) {
      if ($r["user_id"]!=$uid) {
        if ($uid!==null) {
          $row[] = count($class)==0 ? "N/A" : round($present / count($class) * 100) . "%";
          $this->csvRow($f, $row);
        }
        $row = ["{$r["user_name"]} ({$r["user_email"]})"];
        for ($i=0; $i<count($class); $i++) { $row[] = 0; }
        $uid = $r["user_id"];
        $present = 0;
      }

      if ($r["class_id"]!==null && isset($class[$r["class_id"]])) {
        $attended = (int)$r["a_status"] === 1;
        $row[$class[$r["class_id"]]] = $attended ? 1 : 0;
        if ($attended) { $present++; }
      }
    }

    if ($uid!==null) {
      $row[] = count($class)==0 ? "N/A" : round($present / count($class) * 100) . "%";
      $this->csvRow($f, $row);
    }

    // (A6) DONE
    fclose($f);
  }
}