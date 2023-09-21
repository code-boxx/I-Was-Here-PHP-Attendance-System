<?php
// (A) GET CLASSES IN COURSE
$classes = $_CORE->autoCall("Classes", "getAll");

// (B) DRAW CLASSES LIST
if (is_array($classes)) { foreach ($classes as $id=>$c) { ?>
<div class="d-flex align-items-center border p-2">
  <div class="flex-grow-1">
    <strong>[<?=$c["course_code"]?>] <?=$c["course_name"]?></strong><br>
    <small class="fw-bold"><?=$c["cd"]?></small><br>
    <small><?=$c["user_name"]?></small><br>
    <small><?=$c["class_desc"]?></small>
  </div>
  <div class="dropdown">
    <button type="button" class="btn btn-primary p-3 ico-sm icon-arrow-right" type="button" data-bs-toggle="dropdown"></button>
    <ul class="dropdown-menu dropdown-menu-dark">
      <li class="dropdown-item" onclick="classes.addEdit('<?=$id?>')">
        <i class="text-secondary ico-sm icon-pencil"></i> Edit
      </li>
      <li class="dropdown-item" onclick="attend.show(<?=$id?>)">
        <i class="text-secondary ico-sm icon-users"></i> Attendance
      </li>
      <li><a class="dropdown-item" target="_blank" href="<?=HOST_BASE?>TA/classqr?c=<?=$id?>">
        <i class="text-secondary ico-sm icon-qrcode"></i> QR Code
      </a></li>
      <li class="dropdown-item text-warning" onclick="classes.del('<?=$id?>')">
        <i class="ico-sm icon-bin2"></i> Delete
      </li>
    </ul>
  </div>
</div>
<?php }} else { echo "No classes found."; }

// (C) PAGINATION
$_CORE->load("Page");
$_CORE->Page->draw("classes.goToPage");