<?php
class Users extends Core {
  // (A) PASSWORD CHECKER
  //  $password : password to check
  //  $pattern : regex pattern check (at least 8 characters, alphanumeric)
  function checker ($password, $pattern='/^(?=.*[0-9])(?=.*[A-Z]).{8,20}$/i') {
    if (preg_match($pattern, $password)) { return true; }
    else {
      $this->error = "Password must be at least 8 characters alphanumeric.";
      return false;
    }
  }

  // (B) ADD OR UPDATE USER
  //  $name : user name
  //  $email : user email
  //  $role : "A"dmin | "T"eacher | "S"tudent | "I"nactive
  //  $password : user password
  //  $id : user id (for updating only)
  function save ($name, $email, $role, $password, $id=null) {
    // (B1) DATA SETUP + PASSWORD CHECK
    if (!$this->checker($password)) { return false; }
    $fields = ["user_name", "user_email", "user_role", "user_password"];
    $data = [$name, $email, $role, password_hash($password, PASSWORD_DEFAULT)];

    // (B2) ADD/UPDATE USER
    if ($id===null) {
      $this->DB->insert("users", $fields, $data);
    } else {
      $data[] = $id;
      $this->DB->update("users", $fields, "`user_id`=?", $data);
    }
    return true;
  }

  // (C) SAVE USER (ALWAYS OVERRIDE, FOR MASS IMPORT)
  //  $name : user name
  //  $email : user email
  //  $role : "A"dmin | "T"eacher | "S"tudent | "I"nactive
  //  $password : user password
  function saveO ($name, $email, $role, $password) {
    // (C1) PASSWORD CHECK
    if (!$this->checker($password)) { return false; }

    // (C2) GET USER
    $user = $this->get($email);

    // (C3) UPDATE OR INSERT
    $fields = ["user_name", "user_email", "user_role", "user_password"];
    $data = [$name, $email, $role, password_hash($password, PASSWORD_DEFAULT)];
    if (is_array($user)) {
      $data[] = $user["user_id"];
      $this->DB->update("users", $fields, "`user_id`=?", $data);
    } else {
      $this->DB->insert("users", $fields, $data);
    }
    return true;
  }

  // (D) "LIMITED SAVE" - FOR MY ACCOUNT
  function saveL ($password) {
    // (D1) MUST BE SIGNED IN
    global $_SESS;
    if (!isset($_SESS["user"])) {
      $this->error = "Please sign in first";
      return false;
    }

    // (D2) UPDATE ACCOUNT
    return $this->save(
      $_SESS["user"]["user_name"], $_SESS["user"]["user_email"],
      $_SESS["user"]["user_role"], $password, $_SESS["user"]["user_id"]
    );
  }

  // (E) DELETE USER (NON-DESTRUCTIVE)
  //  $id : user id
  function del ($id) {
    $this->DB->update("users",
      ["user_role"], "`user_id`=?", ["I", $id]
    );
    return true;
  }

  // (F) GET USER
  //  $id : user id or email
  function get ($id) {
    return $this->DB->fetch(
      "SELECT * FROM `users` WHERE `user_". (is_numeric($id)?"id":"email") ."`=?",
      [$id]
    );
  }

  // (G) SEARCH USER - FOR AUTOCOMPLETE USE
  //  $search : user name or email
  //  $role : restrict role
  function search ($search, $role=null) {
    $sql = "SELECT * FROM `users` WHERE `user_name` LIKE ? OR `user_email` LIKE ?";
    $data = ["%$search%", "%$search%"];
    $this->DB->query($sql, $data);
    $result = [];
    while ($row = $this->DB->stmt->fetch()) {
      $result[] = [
        "d" => $row["user_name"],
        "v" => $row["user_email"]
      ];
    }
    return count($result)==0 ? null : $result;
  }

  // (H) GET ALL OR SEARCH USERS
  //  $search : optional, user name or email
  //  $role : optional, restrict to this role only
  //  $page : optional, current page number
  function getAll ($search=null, $role=null, $page=null) {
    // (H1) PARITAL USERS SQL + DATA
    $sql = "FROM `users`";
    $data = null;
    if ($search != null || $role != null) {
      $sql .= " WHERE";
      $data = [];
      if ($search != null) {
        $sql .= " (`user_name` LIKE ? OR `user_email` LIKE ?)";
        array_push($data, "%$search%", "%$search%");
      }
      if ($role != null) {
        $sql .= ($search==null?"":" AND") . " `user_role`=?";
        $data[] = $role;
      }
    }

    // (H2) PAGINATION
    if ($page != null) {
      $pgn = $this->core->paginator(
        $this->DB->fetchCol("SELECT COUNT(*) $sql", $data), $page
      );
      $sql .= " LIMIT {$pgn["x"]}, {$pgn["y"]}";
    }

    // (H3) RESULTS
    $users = $this->DB->fetchAll("SELECT * $sql", $data, "user_id");
    return $page != null
     ? ["data" => $users, "page" => $pgn]
     : $users ;
  }

  // (I) VERIFY EMAIL & PASSWORD (LOGIN OR SECURITY CHECK)
  // RETURNS USER ARRAY IF VALID, FALSE IF INVALID
  //  $email : user email
  //  $password : user password
  function verify ($email, $password) {
    // (I1) GET USER
    $user = $this->get($email);
    $pass = is_array($user);

    // (I2) PASSWORD CHECK
    if ($pass) {
      $pass = password_verify($password, $user["user_password"]);
    }

    // (I3) RESULTS
    if (!$pass) {
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
    global $_SESS;
    if (isset($_SESS["user"])) { return true; }

    // (J2) VERIFY EMAIL PASSWORD
    $user = $this->verify($email, $password);
    if ($user===false) { return false; }

    // (J3) SESSION START
    $_SESS["user"] = $user;
    $this->core->Session->create();
    return true;
  }

  // (K) LOGOUT
  function logout () {
    // (K1) ALREADY SIGNED OFF
    global $_SESS;
    if (!isset($_SESS["user"])) { return true; }

    // (K2) END SESSION
    $this->core->Session->destroy();
    return true;
  }
}
