<?php
// (A) GET USERS
$_CORE->Settings->defineN("USER_ROLES", true);
$users = $_CORE->autoCall("Users", "getAll");

// (B) DRAW USERS LIST
if (is_array($users)) { foreach ($users as $id=>$u) { ?>
<div class="d-flex align-items-center border p-2">
  <div class="flex-grow-1">
    <strong><?=$u["user_name"]?></strong><br>
    <small><?=USER_ROLES[$u["user_role"]]?> | <?=$u["user_email"]?></small>
  </div>
  <div class="dropdown">
    <button class="btn btn-primary btn-sm mi" type="button" data-bs-toggle="dropdown">
      more_vert
    </button>
    <ul class="dropdown-menu dropdown-menu-dark">
      <li class="dropdown-item" onclick="usr.addEdit(<?=$id?>)">
        <i class="mi mi-smol">edit</i> Edit
      </li>
      <?php if ($u["user_role"]!="I") { ?>
      <li class="dropdown-item text-warning" onclick="usr.del(<?=$id?>)">
        <i class="mi mi-smol">delete</i> Delete
      </li>
      <?php } ?>
    </ul>
  </div>
</div>
<?php }} else { echo "No users found."; }

// (C) PAGINATION
$_CORE->load("Page");
$_CORE->Page->draw("usr.goToPage");