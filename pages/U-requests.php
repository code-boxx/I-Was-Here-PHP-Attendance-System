<?php
$_PMETA = ["load" => [
  ["s", HOST_ASSETS."U-corrections.js", "defer"]
]];
require PATH_PAGES . "TEMPLATE-top.php"; ?>
<h3 class="mb-3">My correction requests</h3>
<div id="my-correction-list" aria-live="polite">Loading requests…</div>
<?php require PATH_PAGES . "TEMPLATE-bottom.php"; ?>
