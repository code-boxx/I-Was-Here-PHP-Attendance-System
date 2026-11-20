<?php
// (A) GET COURSE
$course = $_CORE->autoCall("Courses", "get"); ?>
<!-- (B) NAVIGATION -->
<div class="d-flex align-items-center mb-3">
  <div class="flex-grow-1">
    <h3 class="mb-0">COURSE COHORT</h3>
    <small class="fw-bold">
      [<?=$course["course_code"]?>] <?=$course["course_name"]?>
    </small>
  </div>
  <button type="button" class="btn btn-danger p-3 ico-sm icon-undo2" aria-label="Back to course" onclick="cb.page(1)"></button>
</div>

<!-- (C) ADD USER TO COURSE -->
<form class="d-flex align-items-stretch head border mb-3 p-2" onsubmit="return cuser.add()">
  <input type="text" required id="course-user-add" placeholder="Student or teacher name/email" class="form-control form-control-sm" aria-label="Student or teacher name/email">
  <button type="submit" class="btn btn-primary p-3 mx-1 ico-sm icon-plus" aria-label="Enroll student or assign course teacher"></button>
  <button type="button" class="btn btn-primary p-3 ico-sm icon-upload3" aria-label="Import course users from CSV" onclick="cuser.import()"></button>
</form>

<form class="border bg-white p-3 mb-3" onsubmit="return cuser.addMany()">
  <label for="course-users-bulk" class="form-label">Bulk add users</label>
  <textarea id="course-users-bulk" class="form-control" rows="3"
            placeholder="One user email per line (up to 200)"></textarea>
  <button type="submit" class="btn btn-outline-primary mt-2">Add listed users</button>
</form>

<div class="d-flex justify-content-end mb-2">
  <button type="button" class="btn btn-danger" onclick="cuser.delSelected()">
    Remove selected users
  </button>
</div>

<form class="d-flex flex-wrap gap-2 border bg-white p-2 mb-3" onsubmit="return cuser.search()">
  <input type="search" id="course-user-search" class="form-control flex-grow-1"
         placeholder="Search roster by name or email" aria-label="Search roster by name or email">
  <select id="course-user-role" class="form-select w-auto" aria-label="Filter roster by role" onchange="cuser.filter()">
    <option value="all">All roles</option>
    <option value="teachers">Teachers</option>
    <option value="students">Students</option>
    <option value="admins">Admins</option>
  </select>
  <select id="course-user-sort" class="form-select w-auto" aria-label="Sort roster" onchange="cuser.filter()">
    <option value="name">Name A-Z</option>
    <option value="name_desc">Name Z-A</option>
    <option value="email">Email A-Z</option>
    <option value="email_desc">Email Z-A</option>
    <option value="role">Role</option>
  </select>
  <button type="submit" class="btn btn-primary ico-sm icon-search" aria-label="Search roster"></button>
</form>

<div class="d-flex flex-wrap align-items-center gap-2 mb-3">
  <label for="course-user-move-to" class="form-label mb-0">Move selected students to</label>
  <select id="course-user-move-to" class="form-select w-auto" aria-label="Destination course">
    <option value="">Choose destination course</option>
    <?php
    $courses = $_CORE->autoCall("Courses", "getAll");
    if (is_array($courses)) { foreach ($courses as $code=>$item) {
      if ($code===$course["course_code"]) { continue; } ?>
      <option value="<?=htmlspecialchars($code, ENT_QUOTES, "UTF-8")?>">
        <?=htmlspecialchars("[".$code."] ".$item["course_name"], ENT_QUOTES, "UTF-8")?>
      </option>
    <?php }} ?>
  </select>
  <button type="button" class="btn btn-outline-primary" onclick="cuser.moveSelected()">
    Move students
  </button>
</div>

<details class="border bg-white p-3 mb-3" id="course-roster-history">
  <summary class="fw-bold">Roster change history and undo</summary>
  <div id="course-user-audit" class="mt-3"></div>
</details>

<!-- (D) USERS LIST -->
<div id="course-user-list" class="zebra my-4"></div>