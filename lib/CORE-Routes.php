<?php
// (A) LOGIN CHECK
$override = function ($path) {
  global $_SESS;
  if (!isset($_SESS["user"]) && $path!="login/" && $path!="forgot/") {
    if (isset($_POST["ajax"])) { exit("E"); }
    else { header("Location: ".HOST_BASE."login"); exit(); }
  }
  return $path;
};

// (B) WILDCARD PATH ROUTING
$wild = [
  "t/" => "TSA-check.php",
  "s/" => "TSA-check.php",
  "a/" => "TSA-check.php",
  "report/" => "REPORT-loader.php"
];