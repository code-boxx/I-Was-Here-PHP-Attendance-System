<!-- (A) JAVASCRIPT -->
<script src="<?=HOST_ASSETS?>S-classes.js"></script>

<!-- (B) NAVIGATION -->
<!-- (B1) PAGE HEADER -->
<nav class="navbar cb-grey"><div class="container-fluid">
  <h4>My Classes</h4>
</div></nav>

<!-- (B2) SEARCH BAR -->
<div class="searchBar"><form class="d-flex" onsubmit="return classes.search()">
  <select id="s-range" onchange="classes.stog()">
    <option value="">All</option>
    <option value="-1">Before</option>
    <option value="1">After</option>
    <option value="0">On</option>
  </select>
  <input type="date" id="s-date" class="form-control form-control-sm" disabled value="<?=date("Y-m-d")?>"/>
  <button class="btn btn-primary">
    <span class="mi">search</span>
  </button>
</form></div>

<!-- (C) CLASSES LIST -->
<div id="c-list" class="container zebra my-4"></div>
