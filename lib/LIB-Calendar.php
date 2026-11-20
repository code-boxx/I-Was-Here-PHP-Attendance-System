<?php
class Calendar extends Core {
  private function validDate ($value) {
    if (!is_string($value) || !preg_match('/^\d{4}-\d{2}-\d{2}$/D', $value)) {
      return false;
    }
    $date = DateTimeImmutable::createFromFormat("!Y-m-d", $value);
    return $date && $date->format("Y-m-d")===$value;
  }

  private function validReason ($reason) {
    $reason = trim((string)$reason);
    $length = preg_match_all('/./us', $reason, $characters);
    return $length!==false && $length>0 && $length<=500 ? $reason : false;
  }

  private function notifyAffectedClasses ($date, $event, $changedAt) {
    $classes = $this->DB->fetchAll(
      "SELECT cl.`class_id`, co.`course_code`, co.`course_name`
       FROM `classes` cl JOIN `courses` co USING (`course_code`)
       WHERE DATE(cl.`class_date`)=? AND cl.`class_status`='scheduled'",
      [$date]
    ) ?? [];
    foreach ($classes as $class) {
      $this->DB->query(
        "UPDATE `notification_outbox` SET `delivery_status`='cancelled'
         WHERE `event_key` LIKE ? AND `delivery_status`='pending'",
        ["class-reminder:".$class["class_id"].":%"]
      );
      $body = $event==="holiday_deleted"
        ? "<p>The school non-instruction date for ".htmlspecialchars($date, ENT_QUOTES | ENT_SUBSTITUTE, "UTF-8")." was removed. The class may meet as scheduled; please confirm with your teacher.</p>"
        : "<p>The class <strong>".htmlspecialchars($class["course_name"], ENT_QUOTES | ENT_SUBSTITUTE, "UTF-8")."</strong> scheduled for ".htmlspecialchars($date, ENT_QUOTES | ENT_SUBSTITUTE, "UTF-8")." will not meet because this is a school non-instruction day.</p>";
      $subject = $event==="holiday_deleted"
        ? "School calendar update: ".$class["course_code"]
        : "Class affected by school calendar: ".$class["course_code"];
      $this->DB->query(
        "INSERT IGNORE INTO `notification_outbox`
           (`user_id`,`category`,`event_key`,`recipient_email`,`subject`,`body`,`available_at`)
         SELECT u.`user_id`, 'class_reminder',
                CONCAT('calendar-change:', ?, ':', ?, ':', ?, ':', u.`user_id`),
                u.`user_email`, ?, ?, ?
         FROM `courses_users` cu
         JOIN `users` u ON (u.`user_id`=cu.`user_id` AND u.`user_level`='U')
         JOIN `notification_preferences` p ON (p.`user_id`=u.`user_id` AND p.`class_reminder`=1)
         WHERE cu.`course_code`=?",
        [$event, $class["class_id"], str_replace([" ", ":"], ["-", ""], $changedAt),
         $subject, $body, $changedAt, $class["course_code"]]
      );
    }
  }

  function getHolidays () {
    return $this->DB->fetchAll(
      "SELECT h.`holiday_date`, h.`holiday_name`, h.`updated_at`,
              u.`user_name` AS `updated_by_name`
       FROM `attendance_holidays` h
       LEFT JOIN `users` u ON (u.`user_id`=h.`updated_by`)
       ORDER BY h.`holiday_date` DESC"
    ) ?? [];
  }

  function saveHoliday ($date, $name, $reason) {
    if (!$this->validDate($date)) {
      $this->error = "Holiday date is invalid.";
      return false;
    }
    $name = trim((string)$name);
    $nameLength = preg_match_all('/./us', $name, $characters);
    $reason = $this->validReason($reason);
    if ($nameLength===false || $nameLength<1 || $nameLength>255 || $reason===false) {
      $this->error = "Provide a holiday name and an audit reason (maximum 500 characters).";
      return false;
    }

    $actor = (int)($_SESSION["user"]["user_id"] ?? 0);
    $this->DB->start();
    try {
      $old = $this->DB->fetch(
        "SELECT `holiday_name` FROM `attendance_holidays`
         WHERE `holiday_date`=? FOR UPDATE",
        [$date]
      );
      if (is_array($old)) {
        $this->DB->update(
          "attendance_holidays",
          ["holiday_name", "updated_by", "updated_at"],
          "`holiday_date`=?",
          [$name, $actor, date("Y-m-d H:i:s"), $date]
        );
      } else {
        $now = date("Y-m-d H:i:s");
        $this->DB->insert(
          "attendance_holidays",
          ["holiday_date", "holiday_name", "created_by", "created_at", "updated_by", "updated_at"],
          [$date, $name, $actor, $now, $actor, $now]
        );
      }
      $changedAt = date("Y-m-d H:i:s");
      $this->DB->insert(
        "attendance_schedule_audit",
        ["holiday_date", "event_type", "old_value", "new_value", "reason", "changed_by", "changed_at"],
        [$date, is_array($old) ? "holiday_updated" : "holiday_added",
         is_array($old) ? $old["holiday_name"] : null, $name, $reason, $actor, $changedAt]
      );
      $this->notifyAffectedClasses(
        $date, is_array($old) ? "holiday_updated" : "holiday_added", $changedAt
      );
      $this->DB->end();
      return true;
    } catch (Throwable $ex) {
      if ($this->DB->pdo->inTransaction()) { $this->DB->end(false); }
      throw $ex;
    }
  }

  function deleteHoliday ($date, $reason) {
    if (!$this->validDate($date) || ($reason=$this->validReason($reason))===false) {
      $this->error = "A valid holiday date and audit reason are required.";
      return false;
    }
    $this->DB->start();
    try {
      $holiday = $this->DB->fetch(
        "SELECT `holiday_name` FROM `attendance_holidays`
         WHERE `holiday_date`=? FOR UPDATE",
        [$date]
      );
      if (!is_array($holiday)) {
        $this->DB->end(false);
        $this->error = "Holiday was not found.";
        return false;
      }
      $this->DB->delete("attendance_holidays", "`holiday_date`=?", [$date]);
      $changedAt = date("Y-m-d H:i:s");
      $this->DB->insert(
        "attendance_schedule_audit",
        ["holiday_date", "event_type", "old_value", "new_value", "reason", "changed_by", "changed_at"],
        [$date, "holiday_deleted", $holiday["holiday_name"], null, $reason,
         $_SESSION["user"]["user_id"], $changedAt]
      );
      $this->notifyAffectedClasses($date, "holiday_deleted", $changedAt);
      $this->DB->end();
      return true;
    } catch (Throwable $ex) {
      if ($this->DB->pdo->inTransaction()) { $this->DB->end(false); }
      throw $ex;
    }
  }

  function getAudit ($date=null) {
    if ($date!==null && $date!=="" && !$this->validDate($date)) {
      $this->error = "Audit date is invalid.";
      return false;
    }
    $sql = "SELECT a.*, COALESCE(u.`user_name`, CONCAT('Deleted account #', a.`changed_by`)) AS `actor_name`
            FROM `attendance_schedule_audit` a
            LEFT JOIN `users` u ON (u.`user_id`=a.`changed_by`)";
    if ($date!==null && $date!=="") {
      return $this->DB->fetchAll(
        $sql." WHERE a.`holiday_date`=? ORDER BY a.`changed_at` DESC",
        [$date]
      ) ?? [];
    }
    return $this->DB->fetchAll($sql." ORDER BY a.`changed_at` DESC LIMIT 200") ?? [];
  }
}
