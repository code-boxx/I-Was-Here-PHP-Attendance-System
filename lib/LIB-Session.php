<?php
class Session extends Core {
  private $jti = null;
  // (A) COMMON COOKIE "TEMPLATE"
  private $cookie = [
    "domain" => HOST_NAME,
    "path" => "/",
    "httponly" => true,
    "expires" => 0,
    // "secure" => true,
    "samesite" => "Lax"
  ];

  // (B) CONSTRUCTOR - AUTO VALIDATE JWT COOKIE & RESTORE SESSION DATA
  function __construct ($core) {
    // (B1) INIT - CORE LINKS
    parent::__construct($core);
    if (!empty($_SERVER["HTTPS"]) && $_SERVER["HTTPS"]!="off") {
      $this->cookie["secure"] = true;
    }
    $_SESSION = [];
    $valid = false;

    // (B2) DECODE JWT COOKIE
    if (isset($_COOKIE["cbsess"])) { try {
      require PATH_LIB . "jwt/autoload.php";
      $token = Firebase\JWT\JWT::decode(
        $_COOKIE["cbsess"], new Firebase\JWT\Key(JWT_SECRET, JWT_ALGO)
      );
      $valid = is_object($token);
    } catch (Exception $e) { $valid = false; }}

    // (B3) EXPIRED? VALID ISSUER? VALID AUDIENCE?
    if ($valid) {
      $now = strtotime("now");
      $valid = $token->iss == JWT_ISSUER &&
      $token->aud == HOST_NAME &&
      $token->nbf <= $now &&
      isset($token->ver);
      if ($valid && JWT_EXPIRE!=0) {
        $valid = isset($token->exp) && $token->exp > $now;
      }
      if ($valid && isset($token->data->user)) {
        $idle = defined("SESSION_IDLE_MINUTES") ? (int)SESSION_IDLE_MINUTES : 30;
        $absolute = defined("SESSION_ABSOLUTE_HOURS") ? (int)SESSION_ABSOLUTE_HOURS : 12;
        $started = isset($token->data->session_started) ? (int)$token->data->session_started : (int)$token->iat;
        $lastActivity = isset($token->data->session_last_activity)
          ? (int)$token->data->session_last_activity : (int)$token->iat;
        $valid = ($idle<1 || $lastActivity + ($idle * 60)>$now) &&
          ($absolute<1 || $started + ($absolute * 3600)>$now);
      }
    }

    // (B4) UNPACK COOKIE DATA INTO SESSION
    if ($valid) {
      $this->jti = isset($token->jti) ? (string)$token->jti : null;
      $_SESSION = (array) $token->data;
      $_SESSION["session_version"] = (int)$token->ver;
      $_SESSION["session_jti"] = $this->jti;
      foreach ($_SESSION as $k=>$v) {
        if (is_object($v)) { $_SESSION[$k] = (array) $v; }
      }
      unset($token);
    }

    // (B5) INVALID SESSION
    if (!$valid && isset($_COOKIE["cbsess"])) {
      $this->destroy();
      throw new Exception("Invalid or expired session.");
    }

    // (B6) OK - VALID SESSION HOOK
    unset($_COOKIE["cbsess"]);
    require PATH_LIB . "HOOK-SESS-Load.php";
    if (!isset($_SESSION["csrf_token"]) || !is_string($_SESSION["csrf_token"])) {
      $_SESSION["csrf_token"] = bin2hex(random_bytes(32));
    }
    if ($valid && isset($_SESSION["user"])) {
      $now = strtotime("now");
      $_SESSION["session_started"] = (int)($_SESSION["session_started"] ?? $now);
      $_SESSION["session_last_activity"] = $now;
      $validSession = $this->DB->fetchCol(
        "SELECT 1 FROM `user_sessions`
         WHERE `session_id`=? AND `user_id`=? AND `revoked_at` IS NULL
         AND (`expires_at` IS NULL OR `expires_at`>NOW())",
        [$this->jti, $_SESSION["user"]["user_id"]]
      );
      if ($validSession===null) {
        $this->destroy();
        throw new Exception("Invalid or revoked session.");
      }
      $this->save();
    }
  }

  // (C) CREATE CBSESS COOKIE
  function save () {
    // (C1) FILTER SESSION DATA TO PUT INTO COOKIE
    $data = $_SESSION;
    require PATH_LIB . "HOOK-SESS-Save.php";

    // (C2) GENERATE JWT COOKIE
    require PATH_LIB . "jwt/autoload.php";
    $now = strtotime("now");
    $version = 0;
    if (isset($_SESSION["user"]["user_id"])) {
      $version = $this->DB->fetchCol(
        "SELECT `session_version` FROM `users` WHERE `user_id`=?",
        [$_SESSION["user"]["user_id"]]
      );
      if ($version===null) { throw new Exception("Cannot create a session for an unknown user."); }
    }
    $jti = (isset($_SESSION["session_jti"]) && is_string($_SESSION["session_jti"]))
      ? $_SESSION["session_jti"] : base64_encode(random_bytes(16));
    $_SESSION["session_jti"] = $jti;
    $expiresAt = null;
    if (defined("SESSION_ABSOLUTE_HOURS") && (int)SESSION_ABSOLUTE_HOURS>0) {
      $expiresAt = date("Y-m-d H:i:s", $now + ((int)SESSION_ABSOLUTE_HOURS * 3600));
    }
    $this->DB->query(
      "INSERT INTO `user_sessions`
         (`session_id`,`user_id`,`created_at`,`last_seen_at`,`expires_at`,`ip_address`,`user_agent`)
       VALUES (?,?,?,?,?,?,?)
       ON DUPLICATE KEY UPDATE `last_seen_at`=VALUES(`last_seen_at`), `expires_at`=VALUES(`expires_at`)",
      [$jti, $_SESSION["user"]["user_id"], date("Y-m-d H:i:s", $_SESSION["session_started"] ?? $now),
       date("Y-m-d H:i:s", $now), $expiresAt, $_SERVER["REMOTE_ADDR"] ?? null,
       substr((string)($_SERVER["HTTP_USER_AGENT"] ?? ""), 0, 255)]
    );
    $token = [
      "iat" => $now, // issued at
      "nbf" => $now, // not before
      "jti" => $jti, // json token id
      "iss" => JWT_ISSUER, // issuer
      "aud" => HOST_NAME, // audience
      "ver" => (int)$version, // revoke sessions after account changes
      "data" => $data // additional data
    ];
    if (JWT_EXPIRE > 0) { $token["exp"] = $now + JWT_EXPIRE; } // expiry
    $token = Firebase\JWT\JWT::encode($token, JWT_SECRET, JWT_ALGO);
    setcookie("cbsess", $token, $this->cookie);
  }

  // (D) DESTROY SESSION + COOKIE
  function destroy () {
    if ($this->jti!==null) {
      $this->DB->query(
        "UPDATE `user_sessions` SET `revoked_at`=NOW()
         WHERE `session_id`=? AND `revoked_at` IS NULL",
        [$this->jti]
      );
    }
    // (D1) EXPIRE HTTP COOKIE
    $options = $this->cookie;
    $options["expires"] = -1;
    setcookie("cbsess", "", $options);

    // (D2) CLEAR ALL SESSION DATA
    $_SESSION = [];
  }

  function getActiveSessions () {
    if (!isset($_SESSION["user"]["user_id"])) {
      $this->error = "Sign in before viewing active sessions.";
      return false;
    }
    return $this->DB->fetchAll(
      "SELECT `session_id`,`created_at`,`last_seen_at`,`expires_at`,
              (`session_id`=?) AS `current`
       FROM `user_sessions`
       WHERE `user_id`=? AND `revoked_at` IS NULL
       AND (`expires_at` IS NULL OR `expires_at`>NOW())
       ORDER BY `last_seen_at` DESC",
      [$this->jti, $_SESSION["user"]["user_id"]]
    ) ?? [];
  }

  function revokeSession ($sessionId) {
    if (!isset($_SESSION["user"]["user_id"]) || !is_string($sessionId) || $sessionId==="") {
      $this->error = "Select a valid session.";
      return false;
    }
    $this->DB->query(
      "UPDATE `user_sessions` SET `revoked_at`=NOW()
       WHERE `session_id`=? AND `user_id`=? AND `revoked_at` IS NULL",
      [$sessionId, $_SESSION["user"]["user_id"]]
    );
    return $this->DB->lastRows>0;
  }
}