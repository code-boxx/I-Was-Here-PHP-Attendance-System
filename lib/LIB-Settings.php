<?php
class Settings extends Core {
  // (A) CONSTRUCTOR - LOAD SYSTEM SETTINGS
  function __construct ($core) {
    parent::__construct($core);
    $this->defineG(1);
  }

  // (B) AUTO DEFINE BY SETTING GROUP
  //  $group : setting group
  function defineG ($group) : void {
    foreach ($this->DB->fetchKV(
      "SELECT * FROM `settings` WHERE `setting_group`=?",
      [$group], "setting_name", "setting_value"
    ) as $k=>$v) { define($k, $v); }
  }

  // (C) AUTO DEFINE BY SETTING NAME
  //  $name : setting name (string or array)
  //  $json : json decode setting value?
  function defineN ($name, $json=false) : void {
    // (C1) SQL & DATA
    $sql = "SELECT * FROM `settings` WHERE `setting_name`";
    if (is_array($name)) {
      $sql .= " IN (";
      foreach ($name as $n) { $sql .= "?,"; }
      $sql = substr($sql,0,-1) . ")";
      $data = $name;
    } else {
      $sql .= "=?";
      $data = [$name];
    }

    // (C2) GET & DEFINE
    foreach ($this->DB->fetchKV(
      $sql, $data, "setting_name", "setting_value"
    ) as $k=>$v) { define($k, ($json?json_decode($v,true):$v)); }
  }

  // (D) GET SETTINGS
  //  $group : setting group
  function getAll ($group=1) {
    return $this->DB->fetchAll("SELECT * FROM `settings` WHERE `setting_group`=?", [$group]);
  }

  // (E) SAVE SETTINGS
  //  $settings : string, json encoded array of settings in key => value
  //  $settings : array, key => value
  function save ($settings) {
    if (!is_array($settings)) {
      $settings = json_decode($settings, true);
    }
    if (!is_array($settings) || count($settings)==0) {
      $this->error = "Settings data is invalid.";
      return false;
    }
    $allowed = $this->DB->fetchKV(
      "SELECT `setting_name`, `setting_value` FROM `settings` WHERE `setting_group`=1",
      [], "setting_name", "setting_value"
    ) ?? [];
    foreach ($settings as $name=>$value) {
      if (
        !is_string($name) || !array_key_exists($name, $allowed) ||
        !is_scalar($value) || strlen((string)$value)>255
      ) {
        $this->error = "Settings data is invalid.";
        return false;
      }
      if ($name=="ATTENDANCE_THRESHOLD") {
        if (
          (!is_string($value) && !is_int($value)) ||
          !preg_match('/^[0-9]+$/D', (string)$value) ||
          (int)$value<1 || (int)$value>100
        ) {
          $this->error = "Attendance threshold must be a whole number from 1 to 100.";
          return false;
        }
      }
      if ($name=="ATTENDANCE_LATE_GRACE_MINUTES") {
        if (
          (!is_string($value) && !is_int($value)) ||
          !preg_match('/^[0-9]+$/D', (string)$value) ||
          (int)$value>1440
        ) {
          $this->error = "Late-arrival grace must be a whole number from 0 to 1440 minutes.";
          return false;
        }
      }
      if (in_array($name, ["SESSION_IDLE_MINUTES", "SESSION_ABSOLUTE_HOURS"], true)) {
        if (
          (!is_string($value) && !is_int($value)) ||
          !preg_match('/^[0-9]+$/D', (string)$value) ||
          (int)$value<1 || (int)$value>10080
        ) {
          $this->error = "Session expiry settings must be whole numbers greater than zero.";
          return false;
        }
      }
    }
    foreach ($settings as $k=>$v) {
      $this->DB->update("settings", ["setting_value"], "`setting_name`=?", [$v, $k]);
    }
    return true;
  }
}