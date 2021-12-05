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
      return $this->DB->insert("users", $fields, $data);
    } else {
      $data[] = $id;
      return $this->DB->update("users", $fields, "`user_id`=?", $data);
    }
  }

  // (B) SAVE USER (AUTOMATIC UPDATE IF EMAIL FOUND)
  //  $name : user name
  //  $email : user email
  //  $role : "A"dmin | "T"eacher | "S"tudent | "I"nactive
  //  $password : user password
  function saveO ($name, $email, $role, $password) {
    // (B1) GET USER
    $user = $this->get($email);
    if ($user===false) { return false; }

    // (B2) UPDATE OR INSERT
    $fields = ["user_name", "user_email", "user_role", "user_password"];
    $data = [$name, $email, $role, password_hash($password, PASSWORD_DEFAULT)];
    if (is_array($user)) {
      $data[] = $user["user_id"];
      return $this->DB->update("users", $fields, "`user_id`=?", $data);
    } else {
      return $this->DB->insert("users", $fields, $data);
    }
  }

  // (C) DELETE USER (NON-DESTRUCTIVE)
  //  $id : user id
  function del ($id) {
    return $this->DB->update("users",
      ["user_role"], "`user_id`=?", ["I", $id]
    );
  }

  // (D) GET USER
  //  $id : user id or email
  function get ($id) {
    return $this->DB->fetch(
      "SELECT * FROM `users` WHERE `user_". (is_numeric($id)?"id":"email") ."`=?",
      [$id]
    );
  }

  // (E) COUNT (FOR SEARCH & PAGINATION)
  //  $search : optional, user name or email
  //  $role : optional, user role
  function count ($search=null, $role=null) {
    // "MAIN SELECT"
    $sql = "SELECT COUNT(*) FROM `users` WHERE `user_role` ";
    $data = null;

    // RESTRICT BY ROLE
    if ($role==null || $role=="") { $sql .= "!='I'"; }
    else { $sql .= "=?"; $data = [$role]; }

    // ADD SEARCH
    if ($search!=null && $search!="") {
      $sql .= " AND (`user_name` LIKE ? OR `user_email` LIKE ?)";
      if ($data===null) { $data = ["%$search%", "%$search%"]; }
      else { $data[] = "%$search%"; $data[] = "%$search%"; }
    }
    return $this->DB->fetchCol($sql, $data);
  }

  // (F) GET ALL OR SEARCH USERS
  //  $search : optional, user name or email
  //  $role : optional, user role
  //  $page : optional, current page number
  function getAll ($search=null, $role=null, $page=1) {
    // (F1) PAGINATION
    $entries = $this->count($search, $role);
    if ($entries===false) { return false; }
    $pgn = $this->core->paginator($entries, $page);

    // (F2) GET USERS
    // "MAIN SELECT"
    $sql = "SELECT `user_id`, `user_name`, `user_email`, `user_role`
            FROM `users` WHERE `user_role` ";
    $data = null;

    // RESTRICT BY ROLE
    if ($role==null || $role=="") { $sql .= "!='I'"; }
    else { $sql .= "=?"; $data = [$role]; }

    // ADD SEARCH
    if ($search!=null && $search!="") {
      $sql .= " AND (`user_name` LIKE ? OR `user_email` LIKE ?)";
      if ($data===null) { $data = ["%$search%", "%$search%"]; }
      else { $data[] = "%$search%"; $data[] = "%$search%"; }
    }

    // LIMIT
    $sql .= " LIMIT {$pgn["x"]}, {$pgn["y"]}";

    // (F3) RESULTS
    $users = $this->DB->fetchAll($sql, $data, "user_id");
    if ($users===false) { return false; }
    return ["data" => $users, "page" => $pgn];
  }

  // (G) VERIFY EMAIL & PASSWORD (LOGIN OR SECURITY CHECK)
  // RETURNS USER ARRAY IF VALID, FALSE IF INVALID
  //  $email : user email
  //  $password : user password
  function verify ($email, $password) {
    // (G1) GET USER
    $user = $this->get($email);
    if ($user===false) { return false; }
    $pass = is_array($user);
    if ($pass) { $pass = $user["user_role"]!="I"; }

    // (G2) PASSWORD CHECK
    if ($pass) {
      $pass = password_verify($password, $user["user_password"]);
    }

    // (G3) RESULTS
    if (!$pass) {
      $this->error = "Invalid user or password.";
      return false;
    }
    return $user;
  }

  // (H) LOGIN - JWT COOKIE
  //  $email : user email
  //  $password : user password
  function inJWT ($email, $password) {
    // (H1) ALREADY SIGNED IN
    $this->core->load("JWT");
    if ($this->core->JWT->verify(false)) { return true; }

    // (H2) VERIFY EMAIL PASSWORD
    $user = $this->verify($email, $password);
    if ($user===false) { return false; }

    // (H3) GENERATE TOKEN
    $this->core->JWT->create(["user_id" => $user["user_id"]]);
    return true;
  }
}
