<?php
$_PMETA = ["load" => [
  ["l", HOST_ASSETS."CB-selector.css"],
  ["s", HOST_ASSETS."CB-selector.js", "defer"],
  ["s", HOST_ASSETS."A-class.js", "defer"],
  ["s", HOST_ASSETS."A-class-attend.js", "defer"]
]];
require PATH_PAGES . "TEMPLATE-top.php"; ?>
<!-- (A) NAVIGATION -->
<h3 class="mb3">MANAGE CLASSES</h3>
  
<!-- (B) ACTION BAR -->
<form class="d-flex align-items-stretch head border mb-3 p-2" onsubmit="return classes.search()">
  <!-- (B1) SEARCH -->
  <input type="text" id="class-search" placeholder="Search (course code)" class="form-control form-control-sm">
  <button class="btn btn-primary mi mx-1">
    search
  </button>

  <!-- (B2) ADD CLASS -->
  <button class="btn btn-primary mi" onclick="classes.addEdit()">
    add
  </button>
</form>

<!-- (C) CLASSES LIST -->
<div id="class-list" class="zebra my-4"></div>
<?php require PATH_PAGES . "TEMPLATE-bottom.php"; ?>