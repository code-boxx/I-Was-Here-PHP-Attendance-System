<?php
class Report extends Core {
  private function reportDate ($value, $field) {
    if ($value===null || $value==="") { return null; }
    if (!is_string($value) || !preg_match('/^\d{4}-\d{2}-\d{2}$/D', $value)) {
      throw new InvalidArgumentException("Invalid $field date.");
    }
    $date = DateTimeImmutable::createFromFormat("!Y-m-d", $value);
    if (!$date || $date->format("Y-m-d")!==$value) {
      throw new InvalidArgumentException("Invalid $field date.");
    }
    return $value;
  }

  private function validateRange ($from, $to) {
    $from = $this->reportDate($from, "start");
    $to = $this->reportDate($to, "end");
    if ($from!==null && $to!==null && $from>$to) {
      throw new InvalidArgumentException("The report start date must not be after the end date.");
    }
    return [$from, $to];
  }

  private function canViewCourseReport ($code) {
    $level = $_SESSION["user"]["user_level"] ?? "";
    if ($level=="A") { return true; }
    if ($level!="T") { return false; }
    return $this->DB->fetchCol(
      "SELECT 1 FROM `classes`
       WHERE `course_code`=? AND `user_id`=? LIMIT 1",
      [$code, $_SESSION["user"]["user_id"]]
    ) !== null;
  }

  private function attendanceThreshold () {
    $threshold = $this->DB->fetchCol(
      "SELECT `setting_value` FROM `settings` WHERE `setting_name`='ATTENDANCE_THRESHOLD'"
    );
    if (
      $threshold===null || !ctype_digit((string)$threshold) ||
      (int)$threshold<1 || (int)$threshold>100
    ) {
      throw new RuntimeException("Attendance threshold is not configured correctly.");
    }
    return (int)$threshold;
  }

  private function html ($value) {
    return htmlspecialchars((string)$value, ENT_QUOTES | ENT_SUBSTITUTE, "UTF-8");
  }

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
  function attend ($code, $from=null, $to=null, $format="csv") {
    if (!is_string($code) || trim($code)==="") {
      throw new InvalidArgumentException("Select a valid course.");
    }
    [$from, $to] = $this->validateRange($from, $to);
    if (!in_array($format, ["csv", "print"], true)) {
      throw new InvalidArgumentException("Invalid report format.");
    }

    // (A1) GET COURSE
    $course = $this->DB->fetch(
      "SELECT *, DATE_FORMAT(`course_start`, '".D_LONG."') `sd`, DATE_FORMAT(`course_end`, '".D_LONG."') `ed`
       FROM `courses`
       WHERE `course_code`=?",
       [$code]
    );
    if (!is_array($course)) { throw new Exception("Course not found."); }
    if (!$this->canViewCourseReport($code)) {
      throw new RuntimeException("No access permission");
    }

    // (A2) GET CLASSES IN THE OPTIONAL DATE RANGE
    $sql = "SELECT `class_id`, DATE_FORMAT(`class_date`, '".DT_LONG."') `cd`
            FROM `classes` WHERE `course_code`=?";
    $params = [$code];
    if ($from!==null) {
      $sql .= " AND `class_date`>=?";
      $params[] = "$from 00:00:00";
    }
    if ($to!==null) {
      $sql .= " AND `class_date`<DATE_ADD(?, INTERVAL 1 DAY)";
      $params[] = $to;
    }
    $sql .= " ORDER BY `class_date` ASC";
    $this->DB->query($sql, $params);
    $class = []; $i = 1;
    $classdate = ["STUDENT/CLASS"];
    while ($r = $this->DB->stmt->fetch()) {
      $class[$r["class_id"]] = $i;
      $classdate[] = $r["cd"];
      $i++;
    }
    $classdate[] = "ATTENDANCE %";

    // (A3) OUTPUT THE REPORT HEADER
    $filename = preg_replace('/[^A-Za-z0-9._-]/', '_', $course["course_code"]);
    if ($format=="csv") {
      header("Content-Type: text/csv; charset=utf-8");
      header("Content-Disposition: attachment; filename=\"{$filename}.csv\"");
      $f = fopen("php://output", "w");
      $this->csvRow($f, [strtoupper("[{$course["course_code"]}] {$course["course_name"]}")]);
      $rangeFrom = $from ?? $course["sd"];
      $rangeTo = $to ?? $course["ed"];
      $this->csvRow($f, ["FROM $rangeFrom TO $rangeTo"]);
      $this->csvRow($f, $classdate);
    } else {
      header("Content-Type: text/html; charset=utf-8");
      $rangeFrom = $from ?? $course["sd"];
      $rangeTo = $to ?? $course["ed"];
      echo '<!doctype html><html lang="en"><head><meta charset="utf-8">';
      echo '<meta name="viewport" content="width=device-width, initial-scale=1">';
      echo '<title>Attendance report - '.$this->html($course["course_code"]).'</title>';
      echo '<style>body{font:16px system-ui,sans-serif;margin:2rem;color:#222}table{border-collapse:collapse;width:100%;font-size:.9rem}th,td{border:1px solid #999;padding:.4rem;text-align:left}th{background:#eee}.actions{margin-bottom:1rem}@media print{.actions{display:none}body{margin:0}}</style>';
      echo '</head><body><div class="actions"><button onclick="window.print()">Print report</button></div>';
      echo '<h1>Attendance report</h1><h2>['.$this->html($course["course_code"]).'] '.$this->html($course["course_name"]).'</h2>';
      echo '<p>Period: '.$this->html($rangeFrom).' to '.$this->html($rangeTo).'</p>';
      echo '<table><thead><tr>';
      foreach ($classdate as $heading) { echo '<th>'.$this->html($heading).'</th>'; }
      echo '</tr></thead><tbody>';
    }
    unset($course, $classdate, $i);

    // (A4) STUDENTS & ATTENDANCE, INCLUDING STUDENTS WITH NO CHECK-INS
    $sql = "SELECT u.`user_id`, u.`user_name`, u.`user_email`, cl.`class_id`, a.`a_status`
            FROM `courses_users` cu
            JOIN `users` u ON (cu.`user_id`=u.`user_id` AND u.`user_level`='U')
            LEFT JOIN `classes` cl ON (cu.`course_code`=cl.`course_code`";
    $joinParams = [];
    if ($from!==null) {
      $sql .= " AND cl.`class_date`>=?";
      $joinParams[] = "$from 00:00:00";
    }
    if ($to!==null) {
      $sql .= " AND cl.`class_date`<DATE_ADD(?, INTERVAL 1 DAY)";
      $joinParams[] = $to;
    }
    $sql .= ")
            LEFT JOIN `attendance` a ON (a.`class_id`=cl.`class_id` AND a.`user_id`=u.`user_id`)
            WHERE cu.`course_code`=?
            ORDER BY u.`user_id`, cl.`class_date`";
    $this->DB->query($sql, array_merge($joinParams, [$code]));
    $uid = null;
    $row = [];
    $present = 0;
    while ($r = $this->DB->stmt->fetch()) {
      if ($r["user_id"]!=$uid) {
        if ($uid!==null) {
          $row[] = count($class)==0 ? "N/A" : round($present / count($class) * 100) . "%";
          $this->reportRow($format, $f ?? null, $row);
        }
        $row = ["{$r["user_name"]} ({$r["user_email"]})"];
        for ($i=0; $i<count($class); $i++) { $row[] = null; }
        $uid = $r["user_id"];
        $present = 0;
      }

      if ($r["class_id"]!==null && isset($class[$r["class_id"]])) {
        $attended = (int)$r["a_status"] === 1;
        $row[$class[$r["class_id"]]] = $r["a_status"]===null ? null : ($attended ? 1 : 0);
        if ($attended) { $present++; }
      }
    }

    if ($uid!==null) {
      $row[] = count($class)==0 ? "N/A" : round($present / count($class) * 100) . "%";
      $this->reportRow($format, $f ?? null, $row);
    }

    if ($format=="csv") { fclose($f); }
    else { echo '</tbody></table></body></html>'; }
  }

  function trends ($code, $from=null, $to=null) {
    if (!is_string($code) || trim($code)==="") {
      throw new InvalidArgumentException("Select a valid course.");
    }
    [$from, $to] = $this->validateRange($from, $to);
    $course = $this->DB->fetch(
      "SELECT `course_code`, `course_name` FROM `courses` WHERE `course_code`=?",
      [$code]
    );
    if (!is_array($course)) { throw new Exception("Course not found."); }
    if (!$this->canViewCourseReport($code)) {
      throw new RuntimeException("No access permission");
    }

    $sql = "SELECT DATE_FORMAT(cl.`class_date`, '%x-W%v') AS `week`,
                   DATE_FORMAT(MIN(cl.`class_date`), '%Y-%m-%d') AS `week_start`,
                   COUNT(cl.`class_id`) AS `total`,
                   COALESCE(SUM(a.`a_status`=1), 0) AS `present`,
                   COALESCE(SUM(cl.`class_id` IS NOT NULL AND (a.`user_id` IS NULL OR a.`a_status`=0)), 0) AS `not_present`
            FROM `classes` cl
            JOIN `courses_users` cu ON (cu.`course_code`=cl.`course_code`)
            JOIN `users` u ON (u.`user_id`=cu.`user_id` AND u.`user_level`='U')
            LEFT JOIN `attendance` a ON (a.`class_id`=cl.`class_id` AND a.`user_id`=u.`user_id`)
            WHERE cl.`course_code`=? AND cl.`class_date`<=NOW()";
    $params = [$code];
    if ($from!==null) {
      $sql .= " AND cl.`class_date`>=?";
      $params[] = "$from 00:00:00";
    }
    if ($to!==null) {
      $sql .= " AND cl.`class_date`<DATE_ADD(?, INTERVAL 1 DAY)";
      $params[] = $to;
    }
    $sql .= " GROUP BY YEARWEEK(cl.`class_date`, 3) ORDER BY MIN(cl.`class_date`)";
    $this->DB->query($sql, $params);

    $filename = preg_replace('/[^A-Za-z0-9._-]/', '_', $code);
    header("Content-Type: text/csv; charset=utf-8");
    header("Content-Disposition: attachment; filename=\"{$filename}-trends.csv\"");
    $file = fopen("php://output", "w");
    $this->csvRow($file, ["WEEKLY ATTENDANCE TREND", "[{$course["course_code"]}] {$course["course_name"]}"]);
    $this->csvRow($file, ["Week", "Week starting", "Present", "Absent or unrecorded", "Possible check-ins", "Attendance %"]);
    while ($row = $this->DB->stmt->fetch()) {
      $total = (int)$row["total"];
      $present = (int)$row["present"];
      $this->csvRow($file, [
        $row["week"], $row["week_start"], $present, (int)$row["not_present"], $total,
        $total===0 ? "N/A" : round(100 * $present / $total) . "%"
      ]);
    }
    fclose($file);
  }

  function followup ($code, $from=null, $to=null) {
    if (!is_string($code) || trim($code)==="") {
      throw new InvalidArgumentException("Select a valid course.");
    }
    [$from, $to] = $this->validateRange($from, $to);
    $course = $this->DB->fetch(
      "SELECT `course_code`, `course_name` FROM `courses` WHERE `course_code`=?",
      [$code]
    );
    if (!is_array($course)) { throw new Exception("Course not found."); }
    if (!$this->canViewCourseReport($code)) {
      throw new RuntimeException("No access permission");
    }
    $threshold = $this->attendanceThreshold();

    $sql = "SELECT u.`user_name`, u.`user_email`,
                   COUNT(cl.`class_id`) AS `total`,
                   COALESCE(SUM(a.`a_status`=1), 0) AS `present`,
                   COALESCE(SUM(cl.`class_id` IS NOT NULL AND (a.`user_id` IS NULL OR a.`a_status`=0)), 0) AS `missed`
            FROM `courses_users` cu
            JOIN `users` u ON (u.`user_id`=cu.`user_id` AND u.`user_level`='U')
            LEFT JOIN `classes` cl ON (cl.`course_code`=cu.`course_code`";
    $joinParams = [];
    if ($from!==null) {
      $sql .= " AND cl.`class_date`>=?";
      $joinParams[] = "$from 00:00:00";
    }
    if ($to!==null) {
      $sql .= " AND cl.`class_date`<DATE_ADD(?, INTERVAL 1 DAY)";
      $joinParams[] = $to;
    }
    $sql .= " AND cl.`class_date`<=NOW())
            LEFT JOIN `attendance` a ON (a.`class_id`=cl.`class_id` AND a.`user_id`=u.`user_id`)
            WHERE cu.`course_code`=?
            GROUP BY u.`user_id`, u.`user_name`, u.`user_email`
            ORDER BY u.`user_name`";
    $this->DB->query($sql, array_merge($joinParams, [$code]));

    $filename = preg_replace('/[^A-Za-z0-9._-]/', '_', $code);
    header("Content-Type: text/csv; charset=utf-8");
    header("Content-Disposition: attachment; filename=\"{$filename}-follow-up.csv\"");
    $file = fopen("php://output", "w");
    $this->csvRow($file, ["STUDENT ATTENDANCE FOLLOW-UP", "[{$course["course_code"]}] {$course["course_name"]}"]);
    $this->csvRow($file, ["Below threshold", "$threshold%", "From", $from ?? "All dates", "To", $to ?? "All dates"]);
    $this->csvRow($file, ["Student", "Email", "Present", "Absent or unrecorded", "Completed classes", "Attendance %"]);
    while ($row = $this->DB->stmt->fetch()) {
      $total = (int)$row["total"];
      if ($total===0) { continue; }
      $present = (int)$row["present"];
      $percentage = (int)round(100 * $present / $total);
      if ($percentage >= $threshold) { continue; }
      $this->csvRow($file, [
        $row["user_name"], $row["user_email"], $present, $total - $present,
        $total, "$percentage%"
      ]);
    }
    fclose($file);
  }

  private function reportRow ($format, $file, $row) {
    if ($format=="csv") {
      foreach ($row as &$value) {
        if ($value===null) { $value = 0; }
      }
      unset($value);
      $this->csvRow($file, $row);
      return;
    }
    echo '<tr>';
    foreach ($row as $index=>$value) {
      if ($index>0 && $index<count($row)-1) {
        $value = $value===null ? "Not recorded" : ((int)$value===1 ? "Present" : "Absent");
      }
      echo '<td>'.$this->html($value).'</td>';
    }
    echo '</tr>';
  }
}