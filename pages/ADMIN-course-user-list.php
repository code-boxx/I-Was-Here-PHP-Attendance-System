<?php
// (A) GET USERS IN COURSE
$users = $_CORE->autoCall("Courses", "getUsers");

// (B) DRAW USERS LIST
if (is_array($users["data"])) { foreach ($users["data"] as $id=>$u) { ?>
<div class="d-flex align-items-center p-2">
  <div class="flex-grow-1">
    <strong><?=$u["user_name"]?></strong><br>
    <small><?=USER_ROLES[$u["user_role"]]?></small> |
    <small><?=$u["user_email"]?></small>
  </div>
  <div>
    <button class="btn btn-danger btn-sm" onclick="cuser.del(<?=$id?>)">
      <span class="mi">delete</span>
    </button>
  </div>
</div>
<?php }} else { ?>
<div class="d-flex align-items-center p-2">No users found.</div>
<?php }

// (C) PAGINATION
$_CORE->load("Page");
$_CORE->Page->draw($users["page"], "cuser.goToPage");
