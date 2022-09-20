
<?php
// (A) ADMIN ONLY
if (!isset($_SESS["user"]) || $_SESS["user"]["user_role"]!="A") {
  $_CORE->redirect();
}

// (B) LOAD REPORT
$_PATH = explode("/", $_PATH);
if (count($_PATH)!=3) { exit("Invalid report"); }
$_CORE->autoCall("Report", $_PATH[1]);