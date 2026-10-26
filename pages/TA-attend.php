<?php
// (A) GET CLASS + COURSE
$_CORE->load("Courses");
$class = $_CORE->autoCall("Classes", "get");
$course = $_CORE->Courses->get($class["course_code"]); ?>
<!-- (B) NAVIGATION -->
<div class="d-flex align-items-center mb-3">
  <div class="flex-grow-1">
    <h3 class="mb-0">[<?=$course["course_code"]?>] <?=$course["course_name"]?></h3>
    <small class="fw-bold"><?=$class["cd"]?></small>
  </div>
  <button type="button" class="btn btn-danger p-3 ico-sm icon-undo2"
          aria-label="Back to classes" onclick="attend.back()"></button>
</div>

<!-- (C) NOTES -->
<div class="d-flex align-items-stretch bg-white border mb-1 p-2">
  * Blue check is "present", red cross is "absent".<br>
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
    <div class="col">Not recorded: <strong id="dashboard-unrecorded">—</strong></div>
    <div class="col">Total: <strong id="dashboard-total">—</strong></div>
  </div>
  <p id="dashboard-last-checkin" class="mb-2">No QR check-in recorded.</p>
  <div>
    <strong>Not marked present</strong>
    <ul id="dashboard-not-present" class="mb-1"></ul>
  </div>
  <small id="dashboard-updated" class="text-secondary" role="status"></small>
  <p id="dashboard-error" class="text-danger mb-0" role="alert"></p>
</section>

<!-- (D) ATTENDANCE LIST -->
<div id="attend-list" class="zebra my-4"></div>
<button type="button" class="btn btn-outline-secondary" onclick="attend.audit()">
  View attendance change history
</button>
<div id="attendance-audit" class="my-3" aria-live="polite"></div>