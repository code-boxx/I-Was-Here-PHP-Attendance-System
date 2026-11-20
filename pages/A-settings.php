<?php
// (A) GET ALL SETTINGS
$settings = $_CORE->Settings->getAll();

// (B) SETTINGS LIST
$_PMETA = ["load" => [["s", HOST_ASSETS."A-settings.js", "defer"]]];
require PATH_PAGES . "TEMPLATE-top.php"; ?>
<h3 class="mb-3">SYSTEM SETTINGS</h3>
<form id="set-list" onsubmit="return save()">
  <?php foreach ($settings as $o) { ?>
  <div class="form-floating mb-3">
    <input type="<?=in_array($o["setting_name"], ["ATTENDANCE_THRESHOLD", "ATTENDANCE_LATE_GRACE_MINUTES", "SECURITY_EVENT_RETENTION_DAYS"], true) ? "number" : "text"?>"
           class="form-control" required
           name="<?=htmlspecialchars($o["setting_name"], ENT_QUOTES, "UTF-8")?>"
           value="<?=htmlspecialchars($o["setting_value"], ENT_QUOTES, "UTF-8")?>"
           <?=$o["setting_name"]=="ATTENDANCE_THRESHOLD" ? 'min="1" max="100" step="1"' : ($o["setting_name"]=="ATTENDANCE_LATE_GRACE_MINUTES" ? 'min="0" max="1440" step="1"' : "")?>>
    <label><?=htmlspecialchars($o["setting_description"], ENT_QUOTES, "UTF-8")?></label>
  </div>
  <?php } ?>

  <button type="submit" class="my-1 btn btn-primary d-flex-inline">
    <i class="ico-sm icon-floppy-disk"></i> Save Settings
  </button>
</form>
<section class="bg-white border p-4 mt-4" aria-labelledby="security-events-title">
  <h3 id="security-events-title">SECURITY EVENTS</h3>
  <p class="text-secondary">Recent authentication and authorization events. Sensitive credentials are never recorded.</p>
  <div class="input-group mb-3">
    <label class="input-group-text" for="security-event-filter">Filter</label>
    <select id="security-event-filter" class="form-select">
      <option value="">All events</option>
      <option value="login_failed">Failed logins</option>
      <option value="login_throttled">Throttled logins</option>
      <option value="login_succeeded">Successful logins</option>
      <option value="csrf_rejected">CSRF failures</option>
      <option value="authorization_denied">Authorization denials</option>
      <option value="report_exported">Report exports</option>
      <option value="personal_data_exported">Personal data exports</option>
      <option value="session_revoked">Session revocations</option>
      <option value="settings_changed">Settings changes</option>
    </select>
  </div>
  <div id="security-events" class="d-grid gap-2" role="status" aria-live="polite">Loading...</div>
</section>
<section class="bg-white border p-4 mt-4" aria-labelledby="privacy-requests-title">
  <h3 id="privacy-requests-title">PRIVACY REQUESTS</h3>
  <div id="privacy-requests" class="d-grid gap-2" role="status" aria-live="polite">Loading...</div>
</section>
<?php require PATH_PAGES . "TEMPLATE-bottom.php"; ?>