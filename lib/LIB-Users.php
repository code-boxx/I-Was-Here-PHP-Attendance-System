<?php
// (A-B) PROPERTIES, SETTINGS, HELPER
// (C-D) GET USERS
// (E-H) SAVE & DELETE USER
// (I-K) VERIFY, LOGIN, LOGOUT
// (L-N) REGISTRATION, ACTIVATION
// (O-Q) USER HASH
// (R) IMPORT
class Users extends Core {
  // (A) SETTINGS
  private $hvalid = 900; // validation link good for 15 mins
  private $hlen = 12; // 24 characters validation hash
  private $loginAttempts = 5;
  private $loginWindow = 15;

  private function mfaBase32 ($value) {
    $alphabet = "ABCDEFGHIJKLMNOPQRSTUVWXYZ234567";
    $bits = "";
    foreach (str_split($value) as $char) {
      $bits .= str_pad(decbin(ord($char)), 8, "0", STR_PAD_LEFT);
    }
    $result = "";
    foreach (str_split($bits, 5) as $part) {
      if (strlen($part)<5) { $part = str_pad($part, 5, "0"); }
      $result .= $alphabet[bindec($part)];
    }
    return $result;
  }

  private function mfaCode ($secret, $time=null) {
    $alphabet = "ABCDEFGHIJKLMNOPQRSTUVWXYZ234567";
    $bits = "";
    foreach (str_split($secret) as $char) {
      $value = strpos($alphabet, strtoupper($char));
      if ($value===false) { throw new InvalidArgumentException("Invalid MFA secret."); }
      $bits .= str_pad(decbin($value), 5, "0", STR_PAD_LEFT);
    }
    $binary = "";
    foreach (str_split($bits, 8) as $part) {
      if (strlen($part)==8) { $binary .= chr(bindec($part)); }
    }
    $counter = pack("N2", 0, intdiv($time ?? time(), 30));
    $hash = hash_hmac("sha1", $counter, $binary, true);
    $offset = ord($hash[19]) & 15;
    return str_pad((string)((((ord($hash[$offset]) & 127) << 24) |
      ((ord($hash[$offset+1]) & 255) << 16) |
      ((ord($hash[$offset+2]) & 255) << 8) |
      (ord($hash[$offset+3]) & 255)) % 1000000), 6, "0", STR_PAD_LEFT);
  }

  private function mfaValid ($secret, $code) {
    if (!is_string($code) || !preg_match('/^\d{6}$/D', $code)) { return false; }
    for ($offset=-1; $offset<=1; $offset++) {
      if (hash_equals($this->mfaCode($secret, time()+($offset*30)), $code)) { return true; }
    }
    return false;
  }

  // (B) LOGIN THROTTLE
  function loginKey ($email) {
    $ip = $_SERVER["REMOTE_ADDR"] ?? "unknown";
    return hash("sha256", strtolower(trim($email)) . "|" . $ip);
  }

  function loginLocked ($key) {
    return $this->DB->fetchCol(
      "SELECT 1 FROM `login_attempts` WHERE `attempt_key`=? AND `locked_until`>NOW()",
      [$key]
    ) !== null;
  }

  function loginFailed ($key) {
    $this->DB->query(
      "INSERT INTO `login_attempts` (`attempt_key`, `failed_attempts`, `window_started`)
       VALUES (?, 1, NOW())
       ON DUPLICATE KEY UPDATE
         `locked_until`=CASE
           WHEN `window_started`<DATE_SUB(NOW(), INTERVAL ".$this->loginWindow." MINUTE) THEN NULL
           WHEN `failed_attempts`>= ".($this->loginAttempts - 1)."
             THEN DATE_ADD(NOW(), INTERVAL ".$this->loginWindow." MINUTE)
           ELSE `locked_until`
         END,
         `failed_attempts`=IF(
           `window_started`<DATE_SUB(NOW(), INTERVAL ".$this->loginWindow." MINUTE),
           1, `failed_attempts`+1
         ),
         `window_started`=IF(
           `window_started`<DATE_SUB(NOW(), INTERVAL ".$this->loginWindow." MINUTE),
           NOW(), `window_started`
         )",
      [$key]
    );
  }

  function loginSucceeded ($key) {
    $this->DB->delete("login_attempts", "`attempt_key`=?", [$key]);
  }

  // (C) PASSWORD CHECKER (HELPER)
  //  $password : password to check
  //  $pattern : regex pattern check (at least 8 characters, alphanumeric)
  function checker ($password, $pattern='/^(?=.*[0-9])(?=.*[A-Z]).{8,20}$/i') {
    if (preg_match($pattern, $password)) { return true; }
    else {
      $this->error = "Password must be at least 8 characters alphanumeric.";
      return false;
    }
  }

  // (C) GET USER
  //  $id : user id or email
  //  $hash : optional, also get validation hash
  function get ($id, $hash=null) {
    $sql = sprintf(
      "SELECT %s FROM `users` u%s WHERE u.`user_%s`=?",
      $hash==null ? "u.*" : "u.*, h.`hash_code`, h.`hash_time`, h.`hash_tries`",
      $hash==null ? "" : " LEFT JOIN `users_hash` h ON (u.`user_id`=h.`user_id` AND h.`hash_for`=?)",
      is_numeric($id) ? "id" : "email"
    );
    $data = $hash==null ? [$id] : [$hash, $id];
    return $this->DB->fetch($sql, $data);
  }

  // (D) GET ALL OR SEARCH USERS (ADMIN USE)
  //  $search : optional, user name or email
  //  $page : optional, current page number
  function getAll ($search=null, $page=null) {
    // (D1) PARITAL USERS SQL + DATA
    $sql = "FROM `users`";
    $data = null;
    if ($search != null) {
      $sql .= " WHERE `user_name` LIKE ? OR `user_email` LIKE ?";
      $data = ["%$search%", "%$search%"];
    }

    // (D2) PAGINATION
    if ($page != null) {
      $this->Core->paginator(
        $this->DB->fetchCol("SELECT COUNT(*) $sql", $data), $page
      );
      $sql .= $this->Core->page["lim"];
    }

    // (D3) RESULTS
    return $this->DB->fetchAll("SELECT * $sql", $data, "user_id");
  }

  // (E) ADD OR UPDATE USER (ADMIN/SECONDARY USE)
  //  $name : user name
  //  $email : user email
  //  $password : user password
  //  $lvl : user level
  //  $id : user id (for updating only)
  function save ($name, $email, $password, $lvl, $id=null) {
    // (E1) DATA SETUP + PASSWORD CHECK
    if (!$this->checker($password)) { return false; }
    $fields = ["user_name", "user_email", "user_password", "user_level"];
    $data = [$name, $email, password_hash($password, PASSWORD_DEFAULT), $lvl];

    // (E2) ADD/UPDATE USER
    if ($id===null) {
      $this->DB->insert("users", $fields, $data);
    } else {
      $this->DB->query(
        "UPDATE `users`
         SET `user_name`=?, `user_email`=?, `user_password`=?, `user_level`=?,
             `session_version`=`session_version`+1
         WHERE `user_id`=?",
        [$name, $email, $data[2], $lvl, $id]
      );
    }
    return true;
  }

  // (F) DELETE USER (ADMIN USE)
  //  $id : user id
  function del ($id) {
    $this->DB->start();
    $this->DB->delete("users", "`user_id`=?", [$id]);
    $this->DB->delete("users_hash", "`user_id`=?", [$id]);
    $this->DB->end();
    return true;
  }

  // (G) SUSPEND USER
  //  $id : user id
  function suspend ($id) {
    $this->DB->query(
      "UPDATE `users` SET `user_level`='S', `session_version`=`session_version`+1 WHERE `user_id`=?",
      [$id]
    );
  }

  // (H) UPDATE ACCOUNT (LIMITED "MY ACCOUNT" USER SAVE)
  //  $name : name
  //  $cpass : current password
  //  $pass : new password
  function update ($name, $cpass, $pass) {
    // (H1) MUST BE SIGNED IN
    if (!isset($_SESSION["user"])) {
      $this->error = "Please sign in first";
      return false;
    }

    // (H2) PASSWORD STRENGTH
    if (!$this->checker($pass)) { return false; }

    // (H3) VERIFY CURRENT PASSWORD
    if (!$this->verify($_SESSION["user"]["user_email"], $cpass)) {
      return false;
    }

    // (H4) UPDATE DATABASE
    $this->DB->query(
      "UPDATE `users`
       SET `user_name`=?, `user_password`=?, `session_version`=`session_version`+1
       WHERE `user_id`=?",
      [$name, password_hash($pass, PASSWORD_DEFAULT), $_SESSION["user"]["user_id"]]
    );
    $this->Session->destroy();
    return true;
  }

  // (I) VERIFY EMAIL & PASSWORD (LOGIN OR SECURITY CHECK)
  // RETURNS USER ARRAY IF VALID, FALSE IF INVALID
  //  $email : user email
  //  $password : user password
  function verify ($email, $password) {
    // (I1) GET USER
    $user = $this->get($email, "A");
    if (!is_array($user)) {
      $this->error = "Invalid user or password.";
      return false;
    }

    // (I2) PENDING ACTIVATION
    if ($user["hash_code"]!=null) {
      $this->error = "Please activate your account first.";
      return false;
    }

    // (I3) SUSPENDED
    if ($user["user_level"]=="S") {
      $this->error = "Invalid user or password.";
      return false;
    }

    // (I4) PASSWORD CHECK
    if (!password_verify($password, $user["user_password"])) {
      $this->error = "Invalid user or password.";
      return false;
    }
    return $user;
  }

  // (J) LOGIN
  //  $email : user email
  //  $password : user password
  function login ($email, $password) {
    // (J1) ALREADY SIGNED IN
    if (isset($_SESSION["user"])) { return true; }

    // (J2) LIMIT REPEATED FAILED SIGN-IN ATTEMPTS
    $key = $this->loginKey($email);
    if ($this->loginLocked($key)) {
      $this->Core->load("Security");
      $this->Security->log("login_throttled", null, ["login_key" => $key]);
      $this->error = "Invalid user or password. Try again later.";
      return false;
    }

    // (J3) VERIFY EMAIL PASSWORD ACCOUNT
    $user = $this->verify($email, $password);
    if ($user===false) {
      $this->loginFailed($key);
      $this->Core->load("Security");
      $this->Security->log("login_failed", null, ["login_key" => $key]);
      return false;
    }
    $this->loginSucceeded($key);

    if ($user["user_level"]==="A" && !empty($user["mfa_secret"])) {
      $_SESSION["mfa_pending"] = ["user_id" => (int)$user["user_id"], "expires" => time()+300];
      $this->Session->save();
      return ["mfa_required" => true];
    }

    // (J4) SESSION START
    $_SESSION["user"] = $user;
    unset($_SESSION["user"]["user_password"]);
    unset($_SESSION["user"]["hash_code"]);
    unset($_SESSION["user"]["hash_time"]);
    $this->Session->save();
    $this->Core->load("Security");
    $this->Security->log("login_succeeded", $user["user_id"]);
    return true;
  }

  function mfaStart () {
    if (($_SESSION["user"]["user_level"] ?? "")!=="A") {
      $this->error = "Administrator access required.";
      return false;
    }
    $secret = $this->mfaBase32(random_bytes(20));
    $_SESSION["mfa_setup"] = ["secret" => $secret, "expires" => time()+600];
    $this->Session->save();
    return ["secret" => $secret, "issuer" => HOST_NAME,
      "account" => $_SESSION["user"]["user_email"],
      "uri" => "otpauth://totp/".rawurlencode(HOST_NAME.":".$_SESSION["user"]["user_email"]).
        "?secret=".$secret."&issuer=".rawurlencode(HOST_NAME)];
  }

  function mfaEnable ($code) {
    if (($_SESSION["user"]["user_level"] ?? "")!=="A" || !isset($_SESSION["mfa_setup"])) {
      $this->error = "Start MFA setup first.";
      return false;
    }
    $setup = $_SESSION["mfa_setup"];
    if ($setup["expires"]<time() || !$this->mfaValid($setup["secret"], $code)) {
      $this->error = "Invalid or expired MFA code.";
      return false;
    }
    $recovery = [];
    $hashes = [];
    for ($i=0; $i<8; $i++) {
      $plain = strtoupper(bin2hex(random_bytes(5)));
      $recovery[] = $plain;
      $hashes[] = password_hash($plain, PASSWORD_DEFAULT);
    }
    $this->DB->query("UPDATE `users` SET `mfa_secret`=?, `mfa_recovery`=? WHERE `user_id`=?",
      [$setup["secret"], json_encode($hashes), $_SESSION["user"]["user_id"]]);
    unset($_SESSION["mfa_setup"]);
    return ["recovery_codes" => $recovery];
  }

  function mfaDisable ($code) {
    if (($_SESSION["user"]["user_level"] ?? "")!=="A") {
      $this->error = "Administrator access required.";
      return false;
    }
    $user = $this->get($_SESSION["user"]["user_id"]);
    if (!is_array($user) || !$this->mfaValid($user["mfa_secret"], $code)) {
      $this->error = "Invalid MFA code.";
      return false;
    }
    $this->DB->query("UPDATE `users` SET `mfa_secret`=NULL, `mfa_recovery`=NULL WHERE `user_id`=?",
      [$_SESSION["user"]["user_id"]]);
    return true;
  }

  function mfaVerify ($code) {
    $pending = $_SESSION["mfa_pending"] ?? null;
    if (!is_array($pending) || $pending["expires"]<time()) {
      $this->error = "MFA verification expired.";
      return false;
    }
    $user = $this->get($pending["user_id"]);
    if (!is_array($user) || $user["user_level"]!=="A") {
      $this->error = "MFA verification failed.";
      return false;
    }
    $valid = $this->mfaValid($user["mfa_secret"], $code);
    if (!$valid && is_string($user["mfa_recovery"])) {
      $hashes = json_decode($user["mfa_recovery"], true) ?: [];
      foreach ($hashes as $index=>$hash) {
        if (password_verify($code, $hash)) {
          unset($hashes[$index]);
          $this->DB->query("UPDATE `users` SET `mfa_recovery`=? WHERE `user_id`=?",
            [json_encode(array_values($hashes)), $user["user_id"]]);
          $valid = true;
          break;
        }
      }
    }
    if (!$valid) {
      $this->error = "Invalid MFA code.";
      return false;
    }
    unset($_SESSION["mfa_pending"]);
    $_SESSION["user"] = $user;
    unset($_SESSION["user"]["user_password"], $_SESSION["user"]["mfa_secret"], $_SESSION["user"]["mfa_recovery"]);
    $this->Session->save();
    return true;
  }

  function exportMyData () {
    if (!isset($_SESSION["user"]["user_id"])) {
      $this->error = "Please sign in first.";
      return false;
    }
    $id = $_SESSION["user"]["user_id"];
    $user = $this->DB->fetch(
      "SELECT `user_id`,`user_level`,`user_name`,`user_email` FROM `users` WHERE `user_id`=?",
      [$id]
    );
    if (!is_array($user)) {
      $this->error = "Account not found.";
      return false;
    }
    $courses = $this->DB->fetchAll(
      "SELECT c.`course_code`,`course_name`,`course_desc`,`course_start`,`course_end`
       FROM `courses_users` cu JOIN `courses` c USING (`course_code`)
       WHERE cu.`user_id`=? ORDER BY c.`course_start`, c.`course_code`",
      [$id]
    ) ?? [];
    $attendance = $this->DB->fetchAll(
      "SELECT cl.`course_code`,cl.`class_date`,cl.`class_desc`,a.`a_status`,
              a.`a_date`,a.`a_notes`
       FROM `attendance` a JOIN `classes` cl USING (`class_id`)
       WHERE a.`user_id`=? ORDER BY cl.`class_date`",
      [$id]
    ) ?? [];
    $corrections = $this->DB->fetchAll(
      "SELECT cl.`course_code`,cl.`class_date`,r.`requested_status`,
              r.`request_reason`,r.`request_status`,r.`submitted_at`,
              r.`decided_at`,r.`decision_note`
       FROM `attendance_corrections` r JOIN `classes` cl USING (`class_id`)
       WHERE r.`user_id`=? ORDER BY r.`submitted_at`",
      [$id]
    ) ?? [];
    $this->Core->load("Security");
    $this->Security->log("personal_data_exported", $id);
    return ["exported_at" => date("c"), "user" => $user, "courses" => $courses,
      "attendance" => $attendance, "corrections" => $corrections];
  }

  // (K) LOGOUT
  function logout () {
    // (K1) REVOKE ALL EXISTING TOKENS FOR THIS ACCOUNT
    if (isset($_SESSION["user"]["user_id"])) {
      $this->DB->query(
        "UPDATE `users` SET `session_version`=`session_version`+1 WHERE `user_id`=?",
        [$_SESSION["user"]["user_id"]]
      );
    }

    // (K2) END THIS SESSION
    $this->Session->destroy();
    return true;
  }

  // (L) REGISTER USER (SIGN UP)
  //  $name : user name
  //  $email : user email
  //  $password : user password
  function register ($name, $email, $password) {
    // (L1) ALREADY SIGNED IN
    if (isset($_SESSION["user"])) {
      $this->error = "You are already signed in.";
      return false;
    }

    // (L2) CHECK USER EXIST
    if (is_array($this->get($email))) {
      $this->error = "$email is already registered.";
      return false;
    }

    // (L3) CREATE ACCOUNT + SEND ACTIVATION LINK
    $this->DB->start();
    $ok = $this->save($name, $email, $password, "U");
    if ($ok) { $ok = $this->hsend($this->DB->lastID); }
    $this->DB->end($ok);
    return $ok;
  }

  // (M) GENERATE HASH & SEND ACTIVATION LINK
  //  $id : user id or email
  function hsend ($id) {
    // (M1) ALREADY SIGNED IN
    if (isset($_SESSION["user"])) {
      $this->error = "You are already signed in.";
      return false;
    }

    // (M2) GET USER + HASH
    $user = $this->get($id, "A");
    if (!is_array($user)) {
      $this->error = "Invalid user";
      return false;
    }

    // (M3) HAS EXISTING HASH - CHECK EXPIRY
    if ($user["hash_code"]!=null) {
      $now = strtotime("now");
      $till = strtotime($user["hash_time"]) + $this->hvalid;
      if ($now < $till) {
        $this->error = "Please wait for another ".($till - $now)." seconds.";
        return false;
      }
    }

    // (M4) GENERATE RANDOM HASH
    $hash = $this->Core->random($this->hlen);
    $this->hashAdd($user["user_id"], "A", $hash);

    // (M5) SEND ACTIVATION LINK TO USER EMAIL
    $this->Core->load("Mail");
    return $this->Mail->send([
      "to" => $user["user_email"],
      "subject" => "Validate Your Email",
      "template" => PATH_PAGES . "MAIL-activate.php",
      "vars" => [
        "link" => HOST_BASE."activate?i={$user["user_id"]}&h={$hash}"
      ]
    ]);
  }

  // (N) ACTIVATE ACCOUNT
  //  $i : user id
  //  $h : hash code
  function hactivate ($i, $h) {
    // (N1) ALREADY SIGNED IN
    if (isset($_SESSION["user"])) {
      $this->error = "Already signed in";
      return false;
    }
    
    // (N2) GET USER + HASH
    $user = $this->get($i, "A");
    if (!is_array($user)) {
      $this->error = "Invalid user";
      return false;
    }
    if ($user["hash_time"]==null) {
      $this->error = "Account already active";
      return false;
    }

    // (N3) HASH CHECK
    if (strtotime("now") >= strtotime($user["hash_time"]) + $this->hvalid) {
      $this->error = "Activation link expired";
      return false;
    }
    if ($user["hash_code"]!=$h) {
      $this->error = "Invalid activation link";
      return false;
    }

    // (N4) ACTIVATE ACCOUNT
    $this->hashDel($i, "A");

    // (N5) LOGIN
    unset($user["user_password"]);
    unset($user["hash_code"]);
    unset($user["hash_time"]);
    $_SESSION["user"] = $user;
    $this->Session->save();
    return true;
  }

  // (O) HASH ADD
  //  $id : user id
  //  $for : hash for - "A"ctivation, "OTP", "P"assword reset, "GOO"gle, "NFC"
  //  $time : timestamp
  //    - null : use current time
  //    - string : specify your own
  //    - "" : don't change
  function hashAdd ($id, $for, $code, $time=null) : void {
    $fields = ["user_id", "hash_for", "hash_code"];
    $data = [$id, $for, $code];
    if ($time===null) { $fields[] = "hash_time"; $data[] = date("Y-m-d H:i:s"); }
    else if ($time!="") { $fields[] = "hash_time"; $data[] = $time; }
    $this->DB->replace("users_hash", $fields, $data);
  }

  // (P) HASH GET
  //  $id : user id
  //  $for : hash for
  function hashGet ($id, $for) {
    return $this->DB->fetch(
      "SELECT * FROM `users_hash` WHERE `user_id`=? AND `hash_for`=?",
      [$id, $for]
    );
  }

  // (Q) HASH DELETE
  //  $id : user id
  //  $for : hash for
  function hashDel ($id, $for) : void {
    $this->DB->delete(
      "users_hash", "`user_id`=? AND `hash_for`=?", [$id, $for]
    );
  }

  // (R) IMPORT USER
  //  $name : user name
  //  $email : user email
  //  $password : user password
  //  $level : user level
  function import ($name, $email, $password, $level) {
    $name = trim($name);
    $email = strtolower(trim($email));

    if ($name==="" || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
      $this->error = "A valid name and email are required.";
      return false;
    }

    // (R1) CHECK REGISTERED
    if (is_array($this->get($email))) {
      $this->error = "$email is already registered";
      return false;
    }

    // (R2) CHECK LEVEL
    if (!array_key_exists($level, USR_LVL)) {
      $this->error = "Invalid user level";
      return false;
    }

    // (R3) SAVE
    return $this->save($name, $email, $password, $level);
  }

  function previewImport ($rows) {
    if (!is_string($rows)) {
      $this->error = "Import preview rows are invalid.";
      return false;
    }
    try { $rows = json_decode($rows, true, 512, JSON_THROW_ON_ERROR); }
    catch (JsonException $ex) {
      $this->error = "Import preview rows are invalid JSON.";
      return false;
    }
    if (!is_array($rows) || count($rows)==0 || count($rows)>500) {
      $this->error = "Preview between 1 and 500 user rows.";
      return false;
    }

    $result = [];
    $seen = [];
    foreach ($rows as $row) {
      if (!is_array($row)) {
        $result[] = ["status" => "conflicting", "message" => "Invalid row data."];
        continue;
      }
      $name = trim((string)($row["name"] ?? ""));
      $email = strtolower(trim((string)($row["email"] ?? "")));
      $password = (string)($row["password"] ?? "");
      $level = (string)($row["level"] ?? "");
      if ($name==="" || !filter_var($email, FILTER_VALIDATE_EMAIL)
          || !array_key_exists($level, USR_LVL) || !$this->checker($password)) {
        $result[] = ["status" => "conflicting", "message" => "Name, valid email, password, and user level are required."];
        continue;
      }
      $normalized = [$name, $email, $password, $level];
      if (isset($seen[$email])) {
        $same = $seen[$email]===$normalized;
        $result[] = [
          "status" => $same ? "duplicate" : "conflicting",
          "message" => $same
            ? "Duplicate of an earlier row; it will not be imported."
            : "This email has conflicting values in another row."
        ];
        continue;
      }
      $seen[$email] = $normalized;

      $existing = $this->get($email);
      $result[] = is_array($existing)
        ? ["status" => "conflicting", "message" => "Email is already registered; existing accounts are not changed by CSV import."]
        : ["status" => "new", "message" => "New account will be created."];
    }
    return $result;
  }
}