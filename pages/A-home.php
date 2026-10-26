<?php
// (A) PAGE META
$_PMETA = ["load" => [
  ["s", HOST_ASSETS."CB-autocomplete.js", "defer"],
  ["s", HOST_ASSETS."A-reports.js", "defer"]
]];

// (B) HTML
require PATH_PAGES . "TEMPLATE-top.php"; ?>
<div class="d-flex flex-wrap">
  <!-- (B1) ATTENDANCE -->
  <form class="m-1 p-4 bg-white border" id="report-attend" method="post"
        target="_blank" action="<?=HOST_BASE?>report/attend"
        data-report-base="<?=HOST_BASE?>report/" onsubmit="return reports.submit()">
    <div class="fw-bold text-danger mb-2">ATTENDANCE REPORT</div>
    <div class="form-floating">
      <input type="text" class="form-control" id="attend-course">
      <label>Course Code/Name</label>
    </div>
    <input type="hidden" id="attend-code" name="code">
    <input type="hidden" id="attend-format" name="format" value="csv">
    <div class="form-floating mt-3">
      <input type="date" class="form-control" id="attend-from" name="from">
      <label for="attend-from">From date (optional)</label>
    </div>
    <div class="form-floating mt-3">
      <input type="date" class="form-control" id="attend-to" name="to">
      <label for="attend-to">To date (optional)</label>
    </div>
    <div class="d-flex gap-2 mt-3">
      <button type="submit" class="btn btn-primary" onclick="reports.format('csv')">
        Download CSV
      </button>
      <button type="submit" class="btn btn-outline-primary" onclick="reports.format('print')">
        Printable report
      </button>
    </div>
    <div class="d-flex gap-2 mt-2">
      <button type="submit" class="btn btn-outline-secondary" onclick="reports.type('trends')">
        Weekly trends CSV
      </button>
      <button type="submit" class="btn btn-outline-secondary" onclick="reports.type('followup')">
        Below-threshold follow-up CSV
      </button>
    </div>
    <small id="report-selection" class="d-block mt-2 text-secondary" role="status">
      Choose a course from the suggestions to enable report generation.
    </small>
  </form>
</div>
<?php require PATH_PAGES . "TEMPLATE-bottom.php"; ?>