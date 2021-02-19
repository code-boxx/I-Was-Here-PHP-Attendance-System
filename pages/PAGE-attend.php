<?php require PATH_PAGES . "TEMPLATE-top.php" ; ?>
<script src="<?=URL_PUBLIC?>page-attend.js"></script>
<div class="bar">
  <h1>WELCOME, <?=strtoupper($_SESSION['user']['name'])?>.</h1>
  <div>Here are your attendance records.</div>
</div>
<div id="cla-list"></div>
<?php require PATH_PAGES . "TEMPLATE-bottom.php" ;