<!-- (A) JAVASCRIPT -->
<!-- (A1) COURSES -->
<script src="<?=HOST_ASSETS?>A-cs.js"></script>
<!-- (A2) COURSE-USERS -->
<script src="<?=HOST_ASSETS?>A-cu.js"></script>
<script src="<?=HOST_ASSETS?>A-cu-import.js"></script>
<!-- (A3) COURSE-CLASSES -->
<script src="<?=HOST_ASSETS?>A-cc.js"></script>
<!-- (A4) CLASS-ATTENDANCE -->
<script src="<?=HOST_ASSETS?>A-cca.js"></script>

<!-- (B) NAVIGATION -->
<!-- (B1) PAGE HEADER -->
<nav class="navbar cb-grey"><div class="container-fluid">
  <h4>Manage Courses</h4>
  <div class="d-flex">
    <button class="btn btn-primary" onclick="cs.addEdit()">
      <span class="mi">add</span>
    </button>
  </div>
</div></nav>

<!-- (B2) SEARCH BAR -->
<div class="searchBar"><form class="d-flex" onsubmit="return cs.search()">
  <input type="text" id="cs-search" placeholder="Search" class="form-control form-control-sm"/>
  <button class="btn btn-primary">
    <span class="mi">search</span>
  </button>
</form></div>

<!-- (C) COURSES LIST -->
<div id="cs-list" class="container zebra my-4"></div>
