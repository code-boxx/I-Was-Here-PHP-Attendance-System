<?php
// (A) GET CLASS + COURSE
$_CORE->load("Courses");
$class = $_CORE->autoCall("Classes", "get");
$course = $_CORE->Courses->get($class["course_code"]);
$_CORE->load("Classes");
$canManageSubstitutes = in_array($_SESSION["user"]["user_level"] ?? "", ["A", "T"], true) &&
  (($_SESSION["user"]["user_level"] ?? "")==="A" ||
   (int)$_SESSION["user"]["user_id"]===(int)$class["user_id"]);
$canManageSchedule = (($_SESSION["user"]["user_level"] ?? "")==="A" ||
  (($_SESSION["user"]["user_level"] ?? "")==="T" &&
   (int)$_SESSION["user"]["user_id"]===(int)$class["user_id"]));
if ($canManageSubstitutes) {
  $substituteTeachers = $_CORE->Courses->getTeachers($class["course_code"]);
  $substitutes = $_CORE->Classes->getSubstitutes($class["class_id"]);
} ?>
<!-- (B) NAVIGATION -->
<div class="d-flex align-items-center mb-3">
  <div class="flex-grow-1">
    <h3 class="mb-0">[<?=$course["course_code"]?>] <?=$course["course_name"]?></h3>
    <small class="fw-bold"><?=$class["cd"]?></small>
  </div>
  <a class="btn btn-outline-primary me-2" target="_blank" rel="noopener"
     href="<?=HOST_BASE?>TA/projector/?id=<?=(int)$class["class_id"]?>">
    Classroom display
  </a>
  <button type="button" class="btn btn-danger p-3 ico-sm icon-undo2"
          aria-label="Back to classes" onclick="attend.back()"></button>
</div>

<?php if ($canManageSchedule) { ?>
<section class="border bg-white p-3 mb-3" aria-labelledby="schedule-control-title">
  <h4 id="schedule-control-title">Class schedule</h4>
  <?php if ($class["class_status"]==="cancelled") { ?>
  <p class="alert alert-danger mb-2">Cancelled: <?=htmlspecialchars($class["class_status_reason"] ?? "", ENT_QUOTES, "UTF-8")?></p>
  <?php } elseif (!empty($class["is_holiday"])) { ?>
  <p class="alert alert-warning mb-2">This date is a school non-instruction day: <?=htmlspecialchars($class["holiday_name"], ENT_QUOTES, "UTF-8")?>. Attendance is disabled.</p>
  <?php } ?>
  <p>Rescheduling preserves existing attendance records and closes any active QR session. A reason is recorded in the schedule history.</p>
  <div class="row g-2 align-items-end">
    <div class="col-md-4">
      <label class="form-label" for="class-reschedule-date">New date and time</label>
      <input id="class-reschedule-date" type="datetime-local" class="form-control"
             value="<?=htmlspecialchars(date("Y-m-d\TH:i", strtotime($class["class_date"])), ENT_QUOTES, "UTF-8")?>">
    </div>
    <div class="col-md-5">
      <label class="form-label" for="class-schedule-reason">Reason</label>
      <input id="class-schedule-reason" type="text" maxlength="500" class="form-control" required>
    </div>
    <div class="col-md-3 d-flex gap-2">
      <button type="button" class="btn btn-primary" onclick="attend.reschedule()">Reschedule</button>
      <?php if ($class["class_status"]!=="cancelled") { ?>
      <button type="button" class="btn btn-outline-danger" onclick="attend.cancelClass()">Cancel class</button>
      <?php } ?>
    </div>
  </div>
  <div id="class-schedule-audit" class="small mt-3" aria-live="polite"></div>
</section>
<?php } ?>

<!-- (C) NOTES -->
<div class="d-flex align-items-stretch bg-white border mb-1 p-2">
  * Choose present, absent, or excused for each student.<br>
  * Provide a note when correcting a saved status. New statuses can be saved without a note.
</div>

<section class="border bg-white p-3 mb-3" aria-labelledby="live-attendance-title">
  <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
    <h4 id="live-attendance-title" class="mb-0">Live attendance</h4>
    <span id="dashboard-session" class="badge bg-secondary">Session status unavailable</span>
  </div>
  <div class="row g-2 my-2" aria-live="polite">
    <div class="col">Present: <strong id="dashboard-present">—</strong></div>
    <div class="col">Absent: <strong id="dashboard-absent">—</strong></div>
    <div class="col">Late: <strong id="dashboard-late">—</strong></div>
    <div class="col">Excused: <strong id="dashboard-excused">—</strong></div>
    <div class="col">Not recorded: <strong id="dashboard-unrecorded">—</strong></div>
    <div class="col">Total: <strong id="dashboard-total">—</strong></div>
  </div>
  <div class="row g-2 mb-2">
    <div class="col-md-6">
      <small class="text-secondary">Session summary</small>
      <div id="dashboard-summary" class="small text-secondary">No summary available yet.</div>
    </div>
    <div class="col-md-6">
      <small class="text-secondary">Late-arrival rule</small>
      <div id="dashboard-late-rule" class="small text-secondary">Grace period unavailable.</div>
    </div>
  </div>
  <p id="dashboard-last-checkin" class="mb-2">No QR check-in recorded.</p>
  <div>
    <strong>Absent or unrecorded</strong>
    <ul id="dashboard-not-present" class="mb-1"></ul>
  </div>
  <small id="dashboard-updated" class="text-secondary" role="status"></small>
  <p id="dashboard-error" class="text-danger mb-0" role="alert"></p>
</section>

<section class="border bg-white p-3 mb-3" aria-labelledby="offline-review-title">
  <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
    <h4 id="offline-review-title" class="mb-0">Offline check-ins awaiting review</h4>
    <button type="button" class="btn btn-outline-primary btn-sm"
            onclick="attend.loadOfflineQueue()">Refresh queue</button>
  </div>
  <p class="small text-secondary mt-2">
    Device timestamps are unverified. Approving records the check-in at the review time and does not replace existing attendance.
  </p>
  <div id="offline-review-message" class="small mb-2" role="status"></div>
  <div id="offline-review-list" class="d-grid gap-2"></div>
</section>

<?php if ($canManageSubstitutes) { ?>
<section class="border bg-white p-3 mb-3" aria-labelledby="substitute-title">
  <h4 id="substitute-title">Temporary substitute access</h4>
  <p class="small text-secondary">
    Access is limited to this class and expires no later than 12 hours after class start.
  </p>
  <div class="row g-2 align-items-end mb-3">
    <div class="col-md-5">
      <label class="form-label" for="ta-substitute-select">Teacher</label>
      <select id="ta-substitute-select" class="form-select">
        <option value="">Select a teacher</option>
        <?php if (is_array($substituteTeachers)) { foreach ($substituteTeachers as $teacherId=>$teacher) {
          if ((int)$teacherId===(int)$class["user_id"]) { continue; } ?>
        <option value="<?=$teacherId?>"><?=htmlspecialchars($teacher["user_name"], ENT_QUOTES, "UTF-8")?> (<?=htmlspecialchars($teacher["user_email"], ENT_QUOTES, "UTF-8")?>)</option>
        <?php }} ?>
      </select>
    </div>
    <div class="col-md-5">
      <label class="form-label" for="ta-substitute-expiry">Access expires at</label>
      <input id="ta-substitute-expiry" type="datetime-local" class="form-control"
             value="<?=htmlspecialchars(date("Y-m-d\TH:i", strtotime($class["class_date"]." +2 hours")), ENT_QUOTES, "UTF-8")?>">
    </div>
    <div class="col-md-2">
      <button type="button" class="btn btn-primary w-100" onclick="attend.addSubstitute()">Assign</button>
    </div>
  </div>
  <div id="ta-substitute-list" class="d-grid gap-2">
    <?php if (is_array($substitutes)) { foreach ($substitutes as $substitute) { ?>
    <div class="d-flex justify-content-between align-items-center border p-2">
      <span><?=htmlspecialchars($substitute["user_name"], ENT_QUOTES, "UTF-8")?> (<?=htmlspecialchars($substitute["user_email"], ENT_QUOTES, "UTF-8")?>) — expires <?=htmlspecialchars($substitute["expires_at"], ENT_QUOTES, "UTF-8")?></span>
      <button type="button" class="btn btn-sm btn-outline-danger"
              onclick="attend.removeSubstitute(<?=$substitute["user_id"]?>)">Remove</button>
    </div>
    <?php }} else { ?>
    <small class="text-secondary">No substitute teachers assigned.</small>
    <?php } ?>
  </div>
</section>
<?php } ?>

<form class="d-flex flex-wrap align-items-end gap-2 border bg-white p-3 mb-3"
      method="post" target="_blank" action="<?=HOST_BASE?>report/followup">
  <input type="hidden" name="code"
         value="<?=htmlspecialchars($course["course_code"], ENT_QUOTES, "UTF-8")?>">
  <div class="form-floating">
    <input type="date" class="form-control" id="followup-from" name="from">
    <label for="followup-from">From date (optional)</label>
  </div>
  <div class="form-floating">
    <input type="date" class="form-control" id="followup-to" name="to">
    <label for="followup-to">To date (optional)</label>
  </div>
  <button type="submit" class="btn btn-outline-primary">
    Export below-threshold students
  </button>
</form>

<!-- (D) ATTENDANCE LIST -->
<div id="attend-list" class="zebra my-4"></div>
<button type="button" class="btn btn-outline-secondary" onclick="attend.audit()">
  View attendance change history
</button>
<div id="attendance-audit" class="my-3" aria-live="polite"></div>