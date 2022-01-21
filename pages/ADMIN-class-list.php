<?php
// (A) GET CLASSES IN COURSE
$classes = $_CORE->autoCall("Classes", "getAll");

// (B) DRAW CLASSES LIST
if (is_array($classes["data"])) { foreach ($classes["data"] as $id=>$c) { ?>
<div class="d-flex align-items-center border p-2">
  <div class="flex-grow-1">
    <strong><?=$c["class_date"]?></strong><br>
    <small>Teacher : <?=$c["user_name"]?></small><br>
    <small><?=$c["class_desc"]?></small>
  </div>
  <div>
    <button class="btn btn-danger btn-sm mi" onclick="classes.del(<?=$id?>)">
      delete
    </button>
    <button class="btn btn-primary btn-sm mi" onclick="attend.show(<?=$id?>)">
      checklist
    </button>
    <button class="btn btn-primary btn-sm mi" onclick="classes.addEdit(<?=$id?>)">
      edit
    </button>
  </div>
</div>
<?php }} else { ?>
<div class="d-flex align-items-center border p-2">No classes found.</div>
<?php }

// (C) PAGINATION
$_CORE->load("Page");
$_CORE->Page->draw($classes["page"], "classes.goToPage");
