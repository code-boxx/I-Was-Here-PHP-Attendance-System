<?php
// (A) ACCESS CHECK
$_CORE->Route->path = explode("/", rtrim($_CORE->Route->path, "/\\"));
$_CORE->Route->path[0] = strtoupper($_CORE->Route->path[0]);
if ($_SESSION["user"]["user_level"]!=$_CORE->Route->path[0]) { $_CORE->redirect(); }

// (B) RESOLVE PAGE
if (count($_CORE->Route->path)==1) { $_CORE->Route->path[1] = "home"; }
$_CORE->Route->load(implode("-", $_CORE->Route->path) . ".php");