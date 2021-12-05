<?php
// (A) GET COURSE
$course = $_CORE->autoCall("Courses", "get");
?>

<!-- (B) NAVIGATION -->
<nav class="navbar cb-grey">
<div class="container-fluid">
  <div>
    <h4>Course Classes</h4>
    <small>
      [<?=$course["course_code"]?>] <?=$course["course_name"]?>
    </small>
  </div>
  <div class="d-flex">
    <button class="btn btn-danger" onclick="cb.page(1)">
      <span class="mi">reply</span>
    </button>
    <button class="btn btn-primary" onclick="cc.addEdit()">
      <span class="mi">add</span>
    </button>
  </div>
</div>
</nav>

<!-- (C) CLASSES LIST -->
<div id="cc-list" class="container zebra my-4"></div>
