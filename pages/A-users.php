<?php
$_CORE->Settings->defineN("USER_ROLES", true);
$_PMETA = ["load" => [
  ["s", HOST_ASSETS."A-users.js", "defer"],
  ["s", HOST_ASSETS."A-users-import.js", "defer"]
]];
require PATH_PAGES . "TEMPLATE-top.php"; ?>
<!-- (A) HEADER -->
<div class="d-flex align-items-center mb-3">
  <h3 class="flex-grow-1">MANAGE USERS</h3>
  <button class="btn btn-primary mx-1 mi" onclick="uimport.init()">
    upload
  </button>
  <button class="btn btn-primary mi" onclick="usr.addEdit()">
    add
  </button>
</div>

<!-- (B) SEARCH BAR -->
<form class="d-flex align-items-stretch head border mb-3 p-2" onsubmit="return usr.search()">
  <input type="text" id="user-search" placeholder="Search" class="form-control form-control-sm">
  <select id="user-search-role" class="form-select form-select-sm" style="width:130px">
    <option value="">All Active</option>
    <?php foreach (USER_ROLES as $code=>$role) { ?>
    <option value="<?=$code?>"><?=$role?></option>
    <?php } ?>
  </select>
  <button class="btn btn-primary mi mx-1">
    search
  </button>
</form>

<!-- (C) USERS LIST -->
<div id="user-list" class="zebra my-4"></div>
<?php require PATH_PAGES . "TEMPLATE-bottom.php"; ?>