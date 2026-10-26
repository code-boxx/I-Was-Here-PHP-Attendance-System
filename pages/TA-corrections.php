<?php
$_CORE->ucheck(["A", "T"]);
$_PMETA = ["load" => [
  ["s", HOST_ASSETS."TA-corrections.js", "defer"]
]];
require PATH_PAGES . "TEMPLATE-top.php"; ?>
<h3 class="mb-3">Attendance correction requests</h3>
<p>Review student requests. A decision reason is required and is retained with the request.</p>
<div id="correction-list" aria-live="polite">Loading requests…</div>
<?php require PATH_PAGES . "TEMPLATE-bottom.php"; ?>
