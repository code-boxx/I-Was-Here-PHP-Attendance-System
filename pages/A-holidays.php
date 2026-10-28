<?php
$_CORE->ucheck("A");
$_PMETA = ["load" => [["s", HOST_ASSETS."A-holidays.js", "defer"]]];
require PATH_PAGES . "TEMPLATE-top.php"; ?>
<h3 class="mb-3">School holidays and non-instruction days</h3>
<p>Classes scheduled on these dates are excluded from attendance calculations and cannot accept check-ins. Adding a date does not delete class or attendance history.</p>

<form id="holiday-form" class="border bg-white p-3 mb-4"
      onsubmit="return holidays.save()">
  <div class="row g-2 align-items-end">
    <div class="col-md-3">
      <label class="form-label" for="holiday-date">Date</label>
      <input id="holiday-date" name="date" type="date" class="form-control" required>
    </div>
    <div class="col-md-3">
      <label class="form-label" for="holiday-name">Name</label>
      <input id="holiday-name" name="name" type="text" maxlength="255"
             class="form-control" required>
    </div>
    <div class="col-md-4">
      <label class="form-label" for="holiday-reason">Audit reason</label>
      <input id="holiday-reason" name="reason" type="text" maxlength="500"
             class="form-control" required>
    </div>
    <div class="col-md-2">
      <button class="btn btn-primary w-100" type="submit">Save date</button>
    </div>
  </div>
</form>

<h4>Configured dates</h4>
<div id="holiday-list" class="d-grid gap-2" aria-live="polite">Loading dates...</div>
<h4 class="mt-4">Calendar change history</h4>
<div id="holiday-audit" class="d-grid gap-2" aria-live="polite"></div>
<?php require PATH_PAGES . "TEMPLATE-bottom.php"; ?>
