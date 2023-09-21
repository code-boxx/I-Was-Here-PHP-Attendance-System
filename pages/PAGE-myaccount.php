<?php
// (A) NOT SIGNED IN
if (!isset($_SESSION["user"])) { $_CORE->redirect(); }

// (B) PAGE META & SCRIPTS
$_PMETA = ["load" => [
  ["s", HOST_ASSETS."PAGE-myaccount.js", "defer"]
]];

// (C) HTML PAGE
require PATH_PAGES . "TEMPLATE-top.php"; ?>
<div class="row justify-content-center">
<div class="col-md-10 bg-white border">
<div class="row">
  <div class="col-4" style="background:url('<?=HOST_ASSETS?>users.webp') center;background-size:cover"></div>
  <form class="col-8 p-4" onsubmit="return save();">
    <img src="<?=HOST_ASSETS?>favicon.png" class="p-2 rounded-circle" style="width:128px;height:128px;background:#f1f1f1">
    <h3 class="my-4">MY ACCOUNT</h3>
    <div class="form-floating mb-4">
      <input type="text" id="user-name" class="form-control" required value="<?=$_SESSION["user"]["user_name"]?>">
      <label>Name</label>
    </div>

    <div class="form-floating mb-4">
      <input type="email" class="form-control" readonly value="<?=$_SESSION["user"]["user_email"]?>">
      <label>Email</label>
    </div>

    <div class="form-floating mb-4">
      <input type="password" id="user-cpass" class="form-control" required>
      <label>Current Password</label>
    </div>

    <div class="form-floating mb-1">
      <input type="password" id="user-npass" class="form-control" required>
      <label>New Password</label>
    </div>
    <div class="mb-4 text-secondary">* At least 8 alphanumeric characters.</div>

    <div class="form-floating mb-4">
      <input type="password" id="user-ncpass" class="form-control" required>
      <label>Confirm Password</label>
    </div>

    <button type="submit" class="my-1 btn btn-primary d-flex-inline">
      <i class="ico-sm icon-checkmark"></i> Save
    </button>
  </form>
</div>
</div>
</div>
<?php require PATH_PAGES . "TEMPLATE-bottom.php";