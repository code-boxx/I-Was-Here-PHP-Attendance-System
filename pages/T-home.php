<?php
// (A) PAGE META
$_PMETA = ["load" => [
  ["s", HOST_ASSETS."T-classes.js", "defer"],
  ["s", HOST_ASSETS."TA-attend.js", "defer"]
]];

// (B) HTML
require PATH_PAGES . "TEMPLATE-top.php"; ?>
<!-- (B1) HEADER -->
<h3 class="mb-3">MY CLASSES</h3>

<!-- (B2) SEARCH BAR -->
<form class="d-flex align-items-stretch head border mb-3 p-2" onsubmit="return classes.search()">
  <select id="search-range" class="form-select w-auto" onchange="classes.stog()">
    <option value="1">After</option>
    <option value="-1">Before</option>
    <option value="0">On</option>
    <option value="">All</option>
  </select>
  <input type="date" id="search-date" class="mx-1 form-control form-control-sm" value="<?=date("Y-m-d")?>">
  <button class="btn btn-primary p-3 ico-sm icon-search" type="submit"></button>
</form>

<!-- (B3) CLASSES LIST -->
<div id="class-list" class="zebra my-4"></div>
<?php require PATH_PAGES . "TEMPLATE-bottom.php"; ?>