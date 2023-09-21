<?php
// (A) ACCESS CHECK
$access = true;
$_CORE->Route->path = explode("/", rtrim($_CORE->Route->path, "/\\"));
if ($_CORE->Route->path[0]=="TA") {
  $access = in_array($_SESSION["user"]["user_level"], ["T","A"]);
} else {
  $access = $_SESSION["user"]["user_level"] == $_CORE->Route->path[0];
}

// (B) NO ACCESS
if (!$access) {
  if (isset($_POST["ajax"])) { http_response_code(405); exit(); }
  else { $_CORE->redirect(); }
}

// (C) RESOLVE PAGE
if (count($_CORE->Route->path)==1) { $_CORE->Route->path[1] = "home"; }
$_CORE->Route->load(implode("-", $_CORE->Route->path) . ".php");