<?php
// (A) ACCESS CHECK
$_PATH = explode("/", rtrim($_PATH, "/\\"));
$_PATH[0] = strtoupper($_PATH[0]);
if ($_SESS["user"]["user_role"]!=$_PATH[0]) { $_CORE->redirect(); }

// (B) RESOLVE PAGE
if (count($_PATH)==1) { $_PATH[1] = "home"; }
$_CORE->Route->load(implode("-", $_PATH) . ".php");