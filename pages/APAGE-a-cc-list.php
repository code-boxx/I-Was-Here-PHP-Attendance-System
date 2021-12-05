<?php
// (A) GET CLASSES IN COURSE
$classes = $_CORE->autoCall("Classes", "getAll");

// (B) DRAW CLASSES LIST
if (is_array($classes["data"])) { foreach ($classes["data"] as $id=>$c) { ?>
<div class="row p-1">
  <div class="col-7">
    <strong><?=$c["class_date"]?></strong><br>
    <small>Teacher : <?=$c["user_name"]?></small><br>
    <small><?=$c["class_desc"]?></small>
  </div>
  <div class="col text-end">
    <button class="btn btn-danger btn-sm" onclick="cc.del(<?=$id?>)">
      <span class="mi">delete</span>
    </button>
    <button class="btn btn-primary btn-sm" onclick="cca.show(<?=$id?>)">
      <span class="mi">checklist</span>
    </button>
    <button class="btn btn-primary btn-sm" onclick="cc.addEdit(<?=$id?>)">
      <span class="mi">edit</span>
    </button>
  </div>
</div>
<?php }} else { echo "No classes found."; }

// (C) PAGINATION
$_CORE->load("Page");
$_CORE->Page->draw($classes["page"], "cc.goToPage");
