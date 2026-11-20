<?php
class Notifications extends Core {
  private function userId () {
    if (!isset($_SESSION["user"]["user_id"])) {
      $this->error = "No access permission";
      return false;
    }
    return (int)$_SESSION["user"]["user_id"];
  }

  function getPreferences () {
    $userId = $this->userId();
    if ($userId===false) { return false; }
    $this->DB->query(
      "INSERT IGNORE INTO `notification_preferences` (`user_id`,`updated_at`) VALUES (?,NOW())",
      [$userId]
    );
    return $this->DB->fetch(
      "SELECT `attendance_warning`,`correction_decision`,`class_reminder`
       FROM `notification_preferences` WHERE `user_id`=?",
      [$userId]
    );
  }

  function savePreferences ($attendanceWarning, $correctionDecision, $classReminder) {
    $userId = $this->userId();
    if ($userId===false) { return false; }
    $values = [$attendanceWarning, $correctionDecision, $classReminder];
    foreach ($values as $value) {
      if (!in_array((string)$value, ["0", "1"], true)) {
        $this->error = "Notification preferences are invalid.";
        return false;
      }
    }
    $this->DB->replace(
      "notification_preferences",
      ["user_id", "attendance_warning", "correction_decision", "class_reminder", "updated_at"],
      [$userId, (int)$attendanceWarning, (int)$correctionDecision, (int)$classReminder, date("Y-m-d H:i:s")]
    );
    return true;
  }
}
