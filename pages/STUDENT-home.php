<?php
$_PMETA = ["load" => [
  ["s", HOST_ASSETS."STUDENT-class.js", "defer"]
]];
require PATH_PAGES . "TEMPLATE-top.php"; ?>
<!-- (A) HEADER -->
<h3 class="mb-3">MY CLASSES</h3>

<!-- (B) SEARCH BAR -->
<form class="d-flex align-items-stretch bg-white border mb-3 p-3" onsubmit="return classes.search()">
  <select id="search-range" onchange="classes.stog()">
    <option value="">All</option>
    <option value="-1">Before</option>
    <option value="1">After</option>
    <option value="0">On</option>
  </select>
  <input type="date" id="search-date" class="form-control form-control-sm" disabled value="<?=date("Y-m-d")?>"/>
  <button class="btn btn-primary mi">
    search
  </button>
</form>

<!-- (C) CLASSES LIST -->
<div id="class-list" class="bg-white zebra my-4"></div>
<?php require PATH_PAGES . "TEMPLATE-bottom.php"; ?>
