<?php require PATH_PAGES . "TEMPLATE-top.php"; ?>
<!-- (A) JAVASCRIPT -->
<link rel="stylesheet" href="<?=URL_PUBLIC?>dp-dark.css"/>
<link rel="stylesheet" href="<?=URL_PUBLIC?>time-pick-dark.css"/>
<script src="<?=URL_PUBLIC?>datepicker.js"></script>
<script src="<?=URL_PUBLIC?>time-pick.js"></script>
<script src="<?=URL_PUBLIC?>admin-classes.js"></script>

<!-- (B) NAVIGATION -->
<h1>MANAGE CLASSES</h1>
<div class="bar">
  <input type="button" value="Add" onclick="cla.addEdit()">
</div>

<!-- (C) CLASS LIST -->
<div id="cla-list"></div>
<?php require PATH_PAGES . "TEMPLATE-bottom.php"; ?>