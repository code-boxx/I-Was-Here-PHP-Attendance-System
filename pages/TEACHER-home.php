<?php
$_PMETA = ["load" => [
  ["s", HOST_ASSETS."TEACHER-classes.js", "defer"],
  ["s", HOST_ASSETS."TEACHER-attend.js", "defer"]
]];
require PATH_PAGES . "TEMPLATE-top.php"; ?>
<!-- (A) NAVIGATION -->
<nav class="bg-white border mb-3">
  <!-- (A1) HEADER -->
  <div class="d-flex align-items-center p-3 pb-0">
    <h3>MY CLASSES</h3>
  </div>

  <!-- (A2) SEARCH BAR -->
  <form class="d-flex align-items-stretch p-3" onsubmit="return classes.search()">
    <select id="search-range" onchange="classes.stog()">
      <option value="">All</option>
      <option value="-1">Before</option>
      <option value="1">After</option>
      <option value="0">On</option>
    </select>
    <input type="date" id="search-date" class="form-control form-control-sm" disabled value="<?=date("Y-m-d")?>"/>
    <button class="btn btn-primary">
      <span class="mi">search</span>
    </button>
  </form>
</nav>

<!-- (B) CLASSES LIST -->
<div id="class-list" class="bg-white border zebra my-4"></div>
<?php require PATH_PAGES . "TEMPLATE-bottom.php"; ?>
