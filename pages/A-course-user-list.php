<?php
// (A) GET USERS IN COURSE
$users = $_CORE->autoCall("Courses", "getUsers");

// (B) DRAW USERS LIST
if (is_array($users)) { foreach ($users as $id=>$u) { ?>
<div class="d-flex align-items-center border p-2">
  <input class="form-check-input me-3 course-user-select" type="checkbox"
         value="<?=$id?>" aria-label="Select <?=htmlspecialchars($u["user_name"], ENT_QUOTES, "UTF-8")?>">
  <div class="flex-grow-1">
    <strong><?=htmlspecialchars($u["user_name"], ENT_QUOTES, "UTF-8")?></strong><br>
    <small><?=htmlspecialchars(USR_LVL[$u["user_level"]], ENT_QUOTES, "UTF-8")?></small> |
    <small><?=htmlspecialchars($u["user_email"], ENT_QUOTES, "UTF-8")?></small>
  </div>
  <button type="button" class="btn btn-danger p-3 ico-sm icon-bin2" onclick="cuser.del(<?=$id?>)"></button>
</div>
<?php }} else { echo "No users found."; }

// (C) PAGINATION
$_CORE->load("Page");
$_CORE->Page->draw("cuser.goToPage");