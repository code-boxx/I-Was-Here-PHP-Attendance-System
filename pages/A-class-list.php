<?php
// (A) GET CLASSES IN COURSE
$classes = $_CORE->autoCall("Classes", "getAll");

// (B) DRAW CLASSES LIST
if (is_array($classes)) { foreach ($classes as $id=>$c) { ?>
<div class="d-flex align-items-center border p-2">
  <div class="flex-grow-1">
    <strong>[<?=$c["course_code"]?>] <?=$c["course_name"]?></strong><br>
    <small class="fw-bold"><?=$c["class_date"]?></small><br>
    <small><?=$c["user_name"]?></small><br>
    <small><?=$c["class_desc"]?></small>
  </div>
  <div class="dropdown">
    <button class="btn btn-primary btn-sm mi" type="button" data-bs-toggle="dropdown">
      more_vert
    </button>
    <ul class="dropdown-menu dropdown-menu-dark">
      <li class="dropdown-item" onclick="classes.addEdit(<?=$id?>)">
        <i class="mi mi-smol">edit</i> Edit
      </li>
      <li class="dropdown-item" onclick="attend.show(<?=$id?>)">
        <i class="mi mi-smol">checklist</i> Attendance
      </li>
      <li><a class="dropdown-item" target="_blank" href="<?=HOST_BASE?>classqr?c=<?=$id?>">
        <i class="mi mi-smol">qr_code</i> QR Code
      </a></li>
      <li class="dropdown-item text-warning" onclick="classes.del(<?=$id?>)">
        <i class="mi mi-smol">delete</i> Delete
      </li>
    </ul>
  </div>
</div>
<?php }} else { echo "No classes found."; }

// (C) PAGINATION
$_CORE->load("Page");
$_CORE->Page->draw("classes.goToPage");