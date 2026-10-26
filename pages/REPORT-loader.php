<?php
// (A) ADMIN AND TEACHER REPORTS
$_CORE->ucheck(["A", "T"]);

// (B) LOAD REPORT
$_CORE->Route->path = explode("/", $_CORE->Route->path);
if (count($_CORE->Route->path)!=3) { exit("Invalid report"); }
$action = $_CORE->Route->path[1];
if (!in_array($action, ["attend", "trends", "followup"], true)) {
  exit("Invalid report");
}
$_CORE->autoCall("Report", $action);