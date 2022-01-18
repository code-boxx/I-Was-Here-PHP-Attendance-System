<?php
$_PMETA = ["load" => [
  ["s", HOST_ASSETS."PAGE-my-account.js", "defer"]
]];
require PATH_PAGES . "TEMPLATE-top.php"; ?>
<form class="bg-white border p-4" onsubmit="return save()">
  <h3 class="mb-4">MY ACCOUNT</h3>

  <div class="input-group mb-4">
    <div class="input-group-prepend">
      <span class="input-group-text mi">person</span>
    </div>
    <input type="text" class="form-control" readonly value="<?=$_SESS["user"]["user_name"]?>"/>
  </div>

  <div class="input-group mb-4">
    <div class="input-group-prepend">
      <span class="input-group-text mi">email</span>
    </div>
    <input type="email" class="form-control" readonly value="<?=$_SESS["user"]["user_email"]?>"/>
  </div>

  <div class="input-group mb-4">
    <div class="input-group-prepend">
      <span class="input-group-text mi">verified_user</span>
    </div>
    <input type="text" class="form-control" readonly value="<?=USER_ROLES[$_SESS["user"]["user_role"]]?>"/>
  </div>

  <div class="input-group mb-4">
    <div class="input-group-prepend">
      <span class="input-group-text mi">lock</span>
    </div>
    <input type="password" class="form-control" id="pass" required placeholder="Password"/>
  </div>

  <div class="input-group mb-4">
    <div class="input-group-prepend">
      <span class="input-group-text mi">lock</span>
    </div>
    <input type="password" class="form-control" id="passc" required placeholder="Confirm Password"/>
  </div>

  <input type="submit" class="col btn btn-primary btn-lg" value="Save"/>
</form>
<?php require PATH_PAGES . "TEMPLATE-bottom.php"; ?>
