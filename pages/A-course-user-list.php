<?php
// (A) GET USERS IN COURSE
$users = $_CORE->autoCall("Courses", "getUsers");
$_CORE->Settings->defineN("USER_ROLES", true);

// (B) DRAW USERS LIST
if (is_array($users)) { foreach ($users as $id=>$u) { ?>
<div class="d-flex align-items-center border p-2">
  <div class="flex-grow-1">
    <strong><?=$u["user_name"]?></strong><br>
    <small><?=USER_ROLES[$u["user_role"]]?></small> |
    <small><?=$u["user_email"]?></small>
  </div>
  <button class="btn btn-danger btn-sm mi" onclick="cuser.del(<?=$id?>)">
    delete
  </button>
</div>
<?php }} else { echo "No users found."; }

// (C) PAGINATION
$_CORE->load("Page");
$_CORE->Page->draw("cuser.goToPage");