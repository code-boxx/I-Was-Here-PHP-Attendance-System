<?php
// (A) GET USERS
$users = $_CORE->autoCall("Users", "getAll");

// (B) DRAW USERS LIST
$roles = [
  "A" => "Admin", "T" => "Teacher", "S" => "Student", "I" => "Inactive"
];
if (is_array($users["data"])) { foreach ($users["data"] as $id=>$u) { ?>
<div class="row p-1">
  <div class="col-7">
    <strong><?=$u["user_name"]?></strong><br>
    <small><?=$roles[$u["user_role"]]?></small> |
    <small><?=$u["user_email"]?></small>
  </div>
  <div class="col text-end">
    <?php if ($u["user_role"]!="I") { ?>
    <button class="btn btn-danger btn-sm" onclick="usr.del(<?=$id?>)">
      <span class="mi">delete</span>
    </button>
    <?php } ?>
    <button class="btn btn-primary btn-sm" onclick="usr.addEdit(<?=$id?>)">
      <span class="mi">edit</span>
    </button>
  </div>
</div>
<?php }} else { echo "No users found."; }

// (C) PAGINATION
$_CORE->load("Page");
$_CORE->Page->draw($users["page"], "usr.goToPage");
