<?php require PATH_PAGES . "TEMPLATE-top.php"; ?>
<!-- (A) JAVASCRIPT -->
<script src="<?=URL_PUBLIC?>admin-courses.js"></script>

<!-- (B) NAVIGATION -->
<h1>MANAGE COURSES</h1>
<form class="bar" onsubmit="return cor.search();">
  <input type="text" id="c-search"/>
  <input type="submit" value="Search"/>
  <input type="button" value="Add" onclick="cor.addEdit()"/>
  <input type="button" value="Import" onclick="cimp.show()"/>
</form>

<!-- (C) COURSES LIST -->
<div id="cor-list"></div>
<?php require PATH_PAGES . "TEMPLATE-bottom.php"; ?>