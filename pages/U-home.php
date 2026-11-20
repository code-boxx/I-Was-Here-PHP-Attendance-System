<?php
// (A) PAGE META
$_PMETA = ["load" => [
  ["s", HOST_ASSETS."U-classes.js", "defer"],
  ["s", HOST_ASSETS."U-attendance.js", "defer"]
]];

// (B) HTML
$_CORE->load("Attend");
$summaries = $_CORE->Attend->getStudentSummary();
require PATH_PAGES . "TEMPLATE-top.php"; ?>
<!-- (B1) HEADER -->
<h3 class="mb-3">MY CLASSES</h3>

<section class="border bg-white p-3 mb-4" aria-labelledby="offline-queue-heading">
  <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
    <h4 id="offline-queue-heading" class="mb-0">Offline check-ins</h4>
    <button type="button" class="btn btn-outline-primary btn-sm"
            onclick="classes.syncOfflineQueue()">Retry queued check-ins</button>
  </div>
  <p class="small text-secondary mt-2 mb-2">
    Offline check-ins are saved on this device and uploaded when a connection is available.
    They remain unverified until your teacher reviews them; device timestamps are not proof of arrival.
  </p>
  <div id="offline-queue-message" class="small mb-2" role="status">Loading check-ins…</div>
  <div id="offline-queue-list" class="d-grid gap-2"
       data-user-id="<?=(int)$_SESSION["user"]["user_id"]?>"></div>
</section>

<!-- (B2) COURSE ATTENDANCE SUMMARY -->
<h4>Course attendance</h4>
<?php if (is_array($summaries)) { foreach ($summaries as $summary) {
  $total = (int)$summary["total_classes"];
  $present = (int)$summary["present_classes"];
  $counted = (int)$summary["counted_classes"];
  $excused = (int)$summary["excused_classes"];
  $percentage = $summary["attendance_percentage"];
?>
<div class="border bg-white p-3 mb-2">
  <strong>[<?=htmlspecialchars($summary["course_code"], ENT_QUOTES, "UTF-8")?>]
    <?=htmlspecialchars($summary["course_name"], ENT_QUOTES, "UTF-8")?></strong>
  <div><?=$present?> present of <?=$counted?> counted completed classes
    (<?=$percentage===null ? "No counted classes" : "$percentage%"?>);
    <?=$excused?> excused
  </div>
  <?php if ($summary["below_threshold"]) { ?>
  <p class="alert alert-warning mt-2 mb-0" role="status">
    Your attendance is below the school threshold of <?=$summary["attendance_threshold"]?>%.
    Please contact your teacher if you believe a record needs correction.
  </p>
  <?php } ?>
</div>
<?php }} elseif ($summaries===false) { ?>
<p class="alert alert-danger" role="alert">
  <?=htmlspecialchars($_CORE->Attend->error ?: "Unable to load attendance summaries.", ENT_QUOTES, "UTF-8")?>
</p>
<?php } else { ?>
<p>No enrolled courses yet.</p>
<?php } ?>

<!-- (B2) SEARCH BAR -->
<form class="d-flex align-items-stretch head border mb-3 p-2" onsubmit="return classes.search()">
  <select id="search-range" aria-label="Date filter" class="form-select w-auto" onchange="classes.stog()">
    <option value="1">After</option>
    <option value="-1">Before</option>
    <option value="0">On</option>
    <option value="">All</option>
  </select>
  <input type="date" id="search-date" aria-label="Filter date" class="mx-1 form-control form-control-sm" value="<?=date("Y-m-d")?>">
  <button class="btn btn-primary p-3 ico-sm icon-search" type="submit" aria-label="Search classes"></button>
</form>

<!-- (B3) CLASSES LIST -->
<div id="class-list" class="zebra my-4"></div>
<?php require PATH_PAGES . "TEMPLATE-bottom.php"; ?>