<?php
class Users extends Core {
  // (A) ADD OR UPDATE USER
  //  $name : user name
  //  $email : user email
  //  $role : "A"dmin | "T"eacher | "S"tudent | "I"nactive
  //  $password : user password
  //  $id : user id (for updating only)
  function save ($name, $email, $role, $password, $id=null) {
    // (A1) DATA SETUP
    $fields = ["user_name", "user_email", "user_role", "user_password"];
    $data = [$name, $email, $role, password_hash($password, PASSWORD_DEFAULT)];

    // (A2) ADD/UPDATE USER
    if ($id===null) {
      $this->DB->insert("users", $fields, $data);
    } else {
      $data[] = $id;
      $this->DB->update("users", $fields, "`user_id`=?", $data);
    }
    return true;
  }

  // (B) SAVE USER (ALWAYS OVERRIDE, FOR MASS IMPORT)
  //  $name : user name
  //  $email : user email
  //  $role : "A"dmin | "T"eacher | "S"tudent | "I"nactive
  //  $password : user password
  function saveO ($name, $email, $role, $password) {
    // (B1) GET USER
    $user = $this->get($email);

    // (B2) UPDATE OR INSERT
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

  // (C) DELETE USER (NON-DESTRUCTIVE)
  //  $id : user id
  function del ($id) {
    $this->DB->update("users",
      ["user_role"], "`user_id`=?", ["I", $id]
    );
    return true;
  }

  // (D) GET USER
  //  $id : user id or email
  function get ($id) {
    return $this->DB->fetch(
      "SELECT * FROM `users` WHERE `user_". (is_numeric($id)?"id":"email") ."`=?",
      [$id]
    );
  }

  // (E) GET ALL OR SEARCH USERS
  //  $search : optional, user name or email
  //  $role : optional, restrict to this role only
  //  $page : optional, current page number
  function getAll ($search=null, $role=null, $page=null) {
    // (E1) PARITAL USERS SQL + DATA
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

    // (E2) PAGINATION
    if ($page != null) {
      $pgn = $this->core->paginator(
        $this->DB->fetchCol("SELECT COUNT(*) $sql", $data), $page
      );
      $sql .= " LIMIT {$pgn["x"]}, {$pgn["y"]}";
    }

    // (E3) RESULTS
    $users = $this->DB->fetchAll("SELECT * $sql", $data, "user_id");
    return $page != null
     ? ["data" => $users, "page" => $pgn]
     : $users ;
  }

  // (F) VERIFY EMAIL & PASSWORD (LOGIN OR SECURITY CHECK)
  // RETURNS USER ARRAY IF VALID, FALSE IF INVALID
  //  $email : user email
  //  $password : user password
  function verify ($email, $password) {
    // (F1) GET USER
    $user = $this->get($email);
    $pass = is_array($user);

    // (F2) PASSWORD CHECK
    if ($pass) {
      $pass = password_verify($password, $user["user_password"]);
    }

    // (F3) RESULTS
    if (!$pass) {
      $this->error = "Invalid user or password.";
      return false;
    }
    return $user;
  }

  // (G) LOGIN
  //  $email : user email
  //  $password : user password
  function login ($email, $password) {
    // (G1) ALREADY SIGNED IN
    global $_SESS;
    if (isset($_SESS["user"])) { return true; }

    // (G2) VERIFY EMAIL PASSWORD
    $user = $this->verify($email, $password);
    if ($user===false) { return false; }

    // (G3) SESSION START
    $_SESS["user"] = $user;
    $this->core->Session->create();
    return true;
  }

  // (H) LOGOUT
  function logout () {
    // (H1) ALREADY SIGNED OFF
    global $_SESS;
    if (!isset($_SESS["user"])) { return true; }

    // (H2) END SESSION
    $this->core->Session->destroy();
    return true;
  }
}
