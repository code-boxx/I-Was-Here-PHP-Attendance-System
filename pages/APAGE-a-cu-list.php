<?php
// (A) GET USERS IN COURSE
$users = $_CORE->autoCall("Courses", "getUsers");

// (B) DRAW USERS LIST
$roles = ["A" => "Admin", "T" => "Teacher", "S" => "Student"];
if (is_array($users["data"])) { foreach ($users["data"] as $id=>$u) { ?>
<div class="row p-1">
  <div class="col-7">
    <strong><?=$u["user_name"]?></strong><br>
    <small><?=$roles[$u["user_role"]]?></small> |
    <small><?=$u["user_email"]?></small>
  </div>
  <div class="col text-end">
    <button class="btn btn-danger btn-sm" onclick="cu.del(<?=$id?>)">
      <span class="mi">delete</span>
    </button>
  </div>
</div>
<?php }} else { echo "No users found."; }

// (C) PAGINATION
$_CORE->load("Page");
$_CORE->Page->draw($users["page"], "cu.goToPage");
