<?php
// (A) ALREADY SIGNED IN
if (isset($_SESS["user"])) { $_CORE->redirect(); }

// (B) PART 1 - ENTER EMAIL
if (!isset($_GET["i"]) && !isset($_GET["h"])) {
$_PMETA = ["load" => [
  ["s", HOST_ASSETS."PAGE-forgot.js"]
]];
require PATH_PAGES . "TEMPLATE-top.php"; ?>
<div class="row justify-content-center">
<div class="col-md-10 bg-white border">
<div class="row">
  <div class="col-4" style="background:url('<?=HOST_ASSETS?>question.jpg') center"></div>
  <form class="col-8 p-5" onsubmit="return forgot();">
    <h3>FORGOT PASSWORD</h3>
    <p class="my-4">Enter your password below, and we will send a reset link to your email.</p>

    <div class="input-group mb-4">
      <div class="input-group-prepend">
        <span class="input-group-text mi">email</span>
      </div>
      <input type="email" id="forgot-email" class="form-control" required placeholder="Email"/>
    </div>

    <input type="submit" class="btn btn-primary mb-4" value="Reset Request"/>
    <div>
      <a href="<?=HOST_BASE?>login">Back to login</a>
    </div>
  </form>
</div>
</div>
</div>
<?php require PATH_PAGES . "TEMPLATE-bottom.php"; }

// (C) PART 2 - VALIDATION
else {
$_CORE->load("Forgot");
$pass = $_CORE->Forgot->reset($_GET["i"], $_GET["h"]);
require PATH_PAGES . "TEMPLATE-top.php"; ?>
<div class="row justify-content-center">
<div class="col-md-10 bg-white border">
<div class="row">
  <div class="col-4" style="background:url('<?=HOST_ASSETS?>question.jpg') center"></div>
  <div class="col-8 p-5">
    <h3 class="mb-4"><?=$pass?"DONE!":"ERROR"?></h3>

    <p><?php
      if ($pass) { echo "OK - New password sent to your email."; }
      else { echo $_CORE->error; }
    ?></p>

    <div>
      <a href="<?=HOST_BASE?>login">Back to login</a>
    </div>
  </div>
</div>
</div>
</div>
<?php require PATH_PAGES . "TEMPLATE-bottom.php"; } ?>
