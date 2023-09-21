<?php
// CALLED BY $_CORE->SESSION->__CONSTRUCT()
// USE THIS TO BUILD/OVERRIDE SESSION DATA WHEN UNPACKING THE JWT

/*
// EXAMPLE - LOAD USER CUSTOM SETTINGS
if (isset($_SESSION["user"])) {
  $_SESSION["settings"] = $this->DB->fetchAll(
    "SELECT * FROM `user_settings` WHERE `user_id`=?",
    [$_SESSION["user"]["user_id"]]
  );
}

// EXAMPLE - CHECK IF COUPON STILL VALID
if (isset($_SESSION["coupon"])) {
  $coupon = $this->DB->fetchAll(
    "SELECT * FROM `coupons` WHERE `coupon_id`=?",
    [$_SESSION["coupon"]]
  );
  if ($coupon["expire"] >= strtotime("now")) {
    unset($_SESSION["coupon"]);
  }
}
*/

// ADDED BY INSTALLER - LOAD USER INFO INTO SESSION
if (isset($_SESSION["user"])) {
  $user = $this->DB->fetch(
    "SELECT * FROM `users` WHERE `user_id`=?",
    [$_SESSION["user"]["user_id"]]
  );
  if (!is_array($user) || (isset($user["user_level"]) && $user["user_level"]=="S")) {
    $this->destroy();
    throw new Exception("Invalid or expired session.");
  } else {
    unset($user["user_password"]);
    $_SESSION["user"] = $user;
  }
}