<?php
// (A) NOT SIGNED IN
if (!isset($_SESSION["user"])) { $_CORE->redirect(); }
$_CORE->load("Notifications");
$notificationPreferences = $_CORE->Notifications->getPreferences();

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
<section class="row justify-content-center mt-4">
  <div class="col-md-10 bg-white border p-4">
    <h3>Email notifications</h3>
    <p class="text-secondary">
      Optional email notices are sent by the site's scheduled notification worker.
      You can change these preferences at any time.
    </p>
    <form onsubmit="return notificationPreferences.save()">
      <div class="form-check mb-2">
        <input class="form-check-input" type="checkbox" id="notify-attendance-warning"
               <?=$notificationPreferences["attendance_warning"] ? "checked" : ""?>>
        <label class="form-check-label" for="notify-attendance-warning">
          Attendance falls below the school threshold
        </label>
      </div>
      <div class="form-check mb-2">
        <input class="form-check-input" type="checkbox" id="notify-correction-decision"
               <?=$notificationPreferences["correction_decision"] ? "checked" : ""?>>
        <label class="form-check-label" for="notify-correction-decision">
          My attendance correction request is approved or rejected
        </label>
      </div>
      <div class="form-check mb-3">
        <input class="form-check-input" type="checkbox" id="notify-class-reminder"
               <?=$notificationPreferences["class_reminder"] ? "checked" : ""?>>
        <label class="form-check-label" for="notify-class-reminder">
          Reminder for a class scheduled within 24 hours
        </label>
      </div>
      <button type="submit" class="btn btn-primary">Save notification preferences</button>
    </form>
  </div>
</section>
<section class="row justify-content-center mt-4">
  <div class="col-md-10 bg-white border p-4">
    <h3>Active sessions</h3>
    <p class="text-secondary">Revoke access from browsers or devices you no longer use.</p>
    <div id="active-sessions" class="d-grid gap-2" role="status" aria-live="polite">
      Loading active sessions...
    </div>
  </div>
</section>
<?php if (($_SESSION["user"]["user_level"] ?? "")==="A") { ?>
<section class="row justify-content-center mt-4">
  <div class="col-md-10 bg-white border p-4">
    <h3>Administrator MFA</h3>
    <p class="text-secondary">Use an authenticator app for administrator sign-in.</p>
    <button type="button" class="btn btn-primary" onclick="mfa.start()">Set up MFA</button>
    <button type="button" class="btn btn-outline-danger" onclick="mfa.disable()">Disable MFA</button>
    <pre id="mfa-result" class="mt-3 d-none"></pre>
  </div>
</section>
<?php } ?>
<?php require PATH_PAGES . "TEMPLATE-bottom.php";