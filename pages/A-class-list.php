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
    <small><?=htmlspecialchars($c["class_desc"] ?? "", ENT_QUOTES, "UTF-8")?></small><br>
    <?php if ($c["class_status"]==="cancelled") { ?>
    <span class="badge bg-danger">Cancelled</span>
    <?php } elseif (!empty($c["is_holiday"])) { ?>
    <span class="badge bg-warning text-dark">Non-instruction day: <?=htmlspecialchars($c["holiday_name"], ENT_QUOTES, "UTF-8")?></span>
    <?php } ?>
  </div>
  <div class="dropdown">
    <button type="button" class="btn btn-primary p-3 ico-sm icon-arrow-right" aria-label="Class actions" data-bs-toggle="dropdown"></button>
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