<?php
// (A) ADMIN ONLY
$_CORE->ucheck("A");

// (B) LOAD REPORT
$_CORE->Route->path = explode("/", $_CORE->Route->path);
if (count($_CORE->Route->path)!=3) { exit("Invalid report"); }
$_CORE->autoCall("Report", $_CORE->Route->path[1]);