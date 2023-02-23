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
  //  $password : user password
  //  $role : "A"dmin | "T"eacher | "S"tudent | "I"nactive
  //  $id : user id (for updating only)
  function save ($name, $email, $password, $role="S", $id=null) {
    // (B1) DATA SETUP + PASSWORD CHECK
    if (!$this->checker($password)) { return false; }
    $fields = ["user_name", "user_email", "user_password", "user_role"];
    $data = [$name, $email, password_hash($password, PASSWORD_DEFAULT), $role];

    // (B2) ADD/UPDATE USER
    if ($id===null) {
      $this->DB->insert("users", $fields, $data);
    } else {
      $data[] = $id;
      $this->DB->update("users", $fields, "`user_id`=?", $data);
    }
    return true;
  }

  // (C) IMPORT USER (OVERRIDES OLD ENTRY)
  //  $name : user name
  //  $email : user email
  //  $password : user password
  //  $role : "A"dmin | "T"eacher | "S"tudent | "I"nactive
  function import ($name, $email, $password, $role="S") {
    // (C1) GET USER
    $user = $this->get($email);

    // (C2) UPDATE OR INSERT
    $this->save($name, $email, $password, $role, is_array($user)?$user["user_id"]:null);
    return true;
  }

  // (D) UPDATE ACCOUNT (LIMITED SAVE)
  function update ($name, $email, $password) {
    // (D1) MUST BE SIGNED IN
    global $_SESS;
    if (!isset($_SESS["user"])) {
      $this->error = "Please sign in first";
      return false;
    }

    // (D2) UPDATE DATABASE
    $this->DB->update("users",
      ["user_name", "user_email", "user_password"],
      "`user_id`=?", [$name, $email, password_hash($password, PASSWORD_DEFAULT), $_SESS["user"]["user_id"]]
    );
    return true;
  }

  // (E) DELETE USER (NON-DESTRUCTIVE)
  //  $id : user id
  function del ($id) {
    $this->DB->update("users", ["user_role"], "`user_id`=?", ["I", $id]);
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
  function autocomplete ($search, $role=null) {
    $sql = "SELECT * FROM `users` WHERE (`user_name` LIKE ? OR `user_email` LIKE ?)";
    $data = ["%$search%", "%$search%"];
    if ($role != null) {
      $sql .= " AND `user_role`=?";
      $data[] = $role;
    }
    $sql .= " LIMIT 5";
    $this->DB->query($sql, $data);
    $result = [];
    while ($row = $this->DB->stmt->fetch()) {
      $result[] = [
        "d" => "{$row["user_name"]} ({$row["user_email"]})",
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
    $sql = "FROM `users` WHERE `user_role`";
    if ($role==null) {
      $sql .= "!=?";
      $data = ["I"];
    } else {
      $sql .= "=?";
      $data = [$role];
    }
    if ($search != null) {
      $sql .= " AND (`user_name` LIKE ? OR `user_email` LIKE ?)";
      array_push($data, "%$search%", "%$search%");
    }

    // (H2) PAGINATION
    if ($page != null) {
      $this->Core->paginator(
        $this->DB->fetchCol("SELECT COUNT(*) $sql", $data), $page
      );
      $sql .= $this->Core->page["lim"];
    }

    // (H3) RESULTS
    return $this->DB->fetchAll("SELECT * $sql", $data, "user_id");
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
    $this->Session->create();
    return true;
  }

  // (K) LOGOUT
  function logout () {
    // (K1) ALREADY SIGNED OFF
    global $_SESS;
    if (!isset($_SESS["user"])) { return true; }

    // (K2) END SESSION
    $this->Session->destroy();
    return true;
  }
}