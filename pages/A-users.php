<?php
$_CORE->Settings->defineN("USER_ROLES", true);
$_PMETA = ["load" => [
  ["s", HOST_ASSETS."A-users.js", "defer"],
  ["s", HOST_ASSETS."A-users-import.js", "defer"]
]];
require PATH_PAGES . "TEMPLATE-top.php"; ?>
<!-- (A) HEADER -->
<h3 class="mb-3">MANAGE USERS</h3>

<!-- (B) ACTION BAR -->
<form class="d-flex align-items-stretch head border mb-3 p-2" onsubmit="return usr.search()">
  <!-- (B1) SEARCH -->
  <input type="text" id="user-search" placeholder="Search" class="form-control form-control-sm">

  <!-- (B2) SEARCH OPTION -->
  <div class="btn-group">
    <button class="btn btn-primary mi ms-1">search</button>
    <button type="button" class="btn btn-primary dropdown-toggle dropdown-toggle-split" data-bs-toggle="dropdown"></button>
    <ul class="dropdown-menu dropdown-menu-dark">
      <li class="dropdown-item"><select id="user-search-role" class="form-select">
        <option value="">All Active</option>
        <?php foreach (USER_ROLES as $code=>$role) { ?>
        <option value="<?=$code?>"><?=$role?></option>
        <?php } ?>
      </select></li>
    </ul>
  </div>

  <!-- (B3) ADD -->
  <div class="dropdown">
    <button class="btn btn-primary mi ms-1" type="button" data-bs-toggle="dropdown">
      add
    </button>
    <ul class="dropdown-menu dropdown-menu-dark">
      <li class="dropdown-item" onclick="usr.addEdit()">
        <i class="mi mi-smil">add</i> Add Single
      </li>
      <li class="dropdown-item" onclick="uimport.init()">
        <i class="mi mi-smil">upload</i> Import CSV
      </li>
    </ul>
  </div>
</form>

<!-- (C) USERS LIST -->
<div id="user-list" class="zebra my-4"></div>
<?php require PATH_PAGES . "TEMPLATE-bottom.php"; ?>