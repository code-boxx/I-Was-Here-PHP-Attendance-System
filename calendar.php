<?php
require __DIR__ . DIRECTORY_SEPARATOR . "lib" . DIRECTORY_SEPARATOR . "CORE-Go.php";

if (!isset($_SESSION["user"]["user_id"])) {
  http_response_code(401);
  header("Content-Type: text/plain; charset=utf-8");
  exit("Sign in required.");
}

$id = $_SESSION["user"]["user_id"];
$classes = $_CORE->DB->fetchAll(
  "SELECT cl.`class_id`, cl.`class_date`, cl.`class_desc`,
          co.`course_code`, co.`course_name`
   FROM `classes` cl
   JOIN `courses` co USING (`course_code`)
   WHERE cl.`class_status`='scheduled'
     AND (
       EXISTS (
         SELECT 1 FROM `courses_users` cu
         WHERE cu.`course_code`=cl.`course_code` AND cu.`user_id`=?
       )
       OR cl.`user_id`=?
     )
   ORDER BY cl.`class_date`",
  [$id, $id]
) ?? [];

$escape = function ($value) {
  return str_replace(["\\", ";", ",", "\r\n", "\r", "\n"], ["\\\\", "\;", "\,", "", "", "\\n"], (string)$value);
};
$date = function ($value, $minutes=0) {
  $time = new DateTimeImmutable($value, new DateTimeZone("UTC"));
  return $time->modify(($minutes>=0 ? "+" : "").$minutes." minutes")->format("Ymd\THis\Z");
};

header("Content-Type: text/calendar; charset=utf-8");
header("Content-Disposition: attachment; filename=\"i-was-here-classes.ics\"");
header("Cache-Control: private, no-store");
echo "BEGIN:VCALENDAR\r\nVERSION:2.0\r\nPRODID:-//I Was Here//Attendance//EN\r\n";
echo "CALSCALE:GREGORIAN\r\nMETHOD:PUBLISH\r\n";
foreach ($classes as $class) {
  $uid = "class-".$class["class_id"]."@".HOST_NAME;
  $summary = "[".$class["course_code"]."] ".$class["course_name"];
  echo "BEGIN:VEVENT\r\n";
  echo "UID:".$escape($uid)."\r\n";
  echo "DTSTAMP:".$date("now")."\r\n";
  echo "DTSTART:".$date($class["class_date"])."\r\n";
  echo "DTEND:".$date($class["class_date"], 60)."\r\n";
  echo "SUMMARY:".$escape($summary)."\r\n";
  if ((string)$class["class_desc"]!=="") {
    echo "DESCRIPTION:".$escape($class["class_desc"])."\r\n";
  }
  echo "END:VEVENT\r\n";
}
echo "END:VCALENDAR\r\n";
