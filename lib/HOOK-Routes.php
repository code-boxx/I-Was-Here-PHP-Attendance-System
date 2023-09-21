<?php
// CALLED BY $_CORE->ROUTES->RESOLVE()
// USE THIS TO OVERRIDE URL PAGE ROUTES

// (A) EXACT PATH ROUTING
$routes = [
  // EXAMPLES
  // "/" => "myhome.php", // http://site.com/ > pages/myhome.php
  // "foo/" => "bar.php", // http://site.com/foo/ > pages/bar.php
];

// (B) WILDCARD PATH ROUTING
$wild = [
  "T/" => "USR-check.php",
  "A/" => "USR-check.php",
  "TA/" => "USR-check.php",
  "U/" => "USR-check.php",
  "report/" => "REPORT-loader.php"
];

// (C) MANUAL PATH OVERRIDE - LOGIN CHECK
$override = function ($path) {
  if (!isset($_SESSION["user"]) && $path!="login/" && $path!="forgot/") {
    if (isset($_POST["ajax"])) { exit("E"); }
    else { header("Location: ".HOST_BASE."login"); exit(); }
  }
  return $path;
};