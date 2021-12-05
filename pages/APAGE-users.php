<!-- (A) JAVASCRIPT -->
<script src="<?=HOST_ASSETS?>A-users.js"></script>
<script src="<?=HOST_ASSETS?>A-users-import.js"></script>

<!-- (B) NAVIGATION -->
<!-- (B1) PAGE HEADER -->
<nav class="navbar cb-grey"><div class="container-fluid">
  <h4>Manage Users</h4>
  <div class="d-flex">
    <label class="btn btn-primary" for="cu-import">
      <span class="mi">upload</span>
    </label>
    <input type="file" class="d-none" accept=".csv" id="cu-import" onchange="uin.init()"/>
    <button class="btn btn-primary" onclick="usr.addEdit()">
      <span class="mi">add</span>
    </button>
  </div>
</div></nav>

<!-- (B2) SEARCH BAR -->
<div class="searchBar"><form class="d-flex" onsubmit="return usr.search()">
  <select id="user-search-role" class="form-control form-control-sm" style="width:130px">
    <option value="">All Active</option>
    <option value="A">Admin</option>
    <option value="T">Teachers</option>
    <option value="S">Students</option>
    <option value="I">Inactive</option>
  </select>
  <input type="text" id="user-search" placeholder="Search" class="form-control form-control-sm"/>
  <button class="btn btn-primary">
    <span class="mi">search</span>
  </button>
</form></div>

<!-- (C) USERS LIST -->
<div id="user-list" class="container zebra my-4"></div>
