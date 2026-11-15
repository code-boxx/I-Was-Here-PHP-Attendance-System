<?php
class Security extends Core {
  function log ($eventType, $userId=null, $details=[]) {
    if (!is_string($eventType) || $eventType==="" || strlen($eventType)>64) {
      throw new InvalidArgumentException("Invalid security event type.");
    }
    if (!is_array($details)) { $details = []; }
    try {
      $this->DB->insert(
        "security_events",
        ["event_type", "user_id", "ip_address", "user_agent", "details", "created_at"],
        [$eventType, $userId, $_SERVER["REMOTE_ADDR"] ?? null,
         substr((string)($_SERVER["HTTP_USER_AGENT"] ?? ""), 0, 255),
         json_encode($details, JSON_INVALID_UTF8_SUBSTITUTE), date("Y-m-d H:i:s")]
      );
      $retention = $this->DB->fetchCol(
        "SELECT `setting_value` FROM `settings`
         WHERE `setting_name`='SECURITY_EVENT_RETENTION_DAYS'"
      );
      $days = is_numeric($retention) ? max(30, min(3650, (int)$retention)) : 365;
      $this->DB->query(
        "DELETE FROM `security_events` WHERE `created_at`<DATE_SUB(NOW(), INTERVAL ".$days." DAY)"
      );
    } catch (PDOException $e) {
      error_log("Security event logging failed: ".$e->getMessage());
    }
  }

  function getEvents ($limit=100, $eventType="") {
    if (($_SESSION["user"]["user_level"] ?? "")!=="A") {
      $this->error = "No access permission";
      return false;
    }
    $limit = is_numeric($limit) ? max(1, min(500, (int)$limit)) : 100;
    $eventType = is_string($eventType) ? trim($eventType) : "";
    if ($eventType!=="" && !preg_match('/^[a-z0-9_]{1,64}$/D', $eventType)) {
      $this->error = "Invalid security event filter.";
      return false;
    }
    $where = "";
    $params = [];
    if ($eventType!=="") {
      $where = " WHERE e.`event_type`=?";
      $params[] = $eventType;
    }
    return $this->DB->fetchAll(
      "SELECT e.*, u.`user_name`, u.`user_email`
       FROM `security_events` e
       LEFT JOIN `users` u ON (u.`user_id`=e.`user_id`)
       ".$where." ORDER BY e.`event_id` DESC LIMIT ".$limit,
      $params
    ) ?? [];
  }
}
