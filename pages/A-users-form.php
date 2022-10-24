<?php
// (A) GET USER
$_CORE->Settings->defineN("USER_ROLES", true);
$edit = isset($_POST["id"]) && $_POST["id"]!="";
if ($edit) { $user = $_CORE->autoCall("Users", "get"); }

// (B) USER FORM ?>
<h3 class="mb-3"><?=$edit?"EDIT":"ADD"?> USER</h3>
<form onsubmit="return usr.save()">
  <input type="hidden" id="user_id" value="<?=isset($user)?$user["user_id"]:""?>">

  <div class="bg-white border p-4 mb-3">
    <div class="input-group mb-3">
      <div class="input-group-prepend">
        <span class="input-group-text mi">person</span>
      </div>
      <input type="text" class="form-control" id="user_name" required value="<?=isset($user)?$user["user_name"]:""?>" placeholder="Name">
    </div>

    <div class="input-group mb-3">
      <div class="input-group-prepend">
        <span class="input-group-text mi">email</span>
      </div>
      <input type="email" class="form-control" id="user_email" required value="<?=isset($user)?$user["user_email"]:""?>" placeholder="Email">
    </div>

    <div class="input-group mb-3">
      <div class="input-group-prepend">
        <span class="input-group-text mi">verified_user</span>
      </div>
      <select class="form-select" id="user_role"><?php
        foreach (USER_ROLES as $k=>$v) {
          printf("<option %svalue='%s'>%s</option>",
          ($edit && $k==$user["user_role"] ? "selected  " : ""), $k, $v
        );
      }
      ?></select>
    </div>

    <div class="input-group">
      <div class="input-group-prepend">
        <span class="input-group-text mi">lock</span>
      </div>
      <input type="password" class="form-control" id="user_password" required placeholder="Password">
    </div>
    <div class="mt-2 text-secondary">* At least 8 characters alphanumeric.</div>
  </div>

  <input type="button" class="col btn btn-danger" value="Back" onclick="cb.page(0)">
  <input type="submit" class="col btn btn-primary" value="Save">
</form>