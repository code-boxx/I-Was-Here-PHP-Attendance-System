<?php
// (A) GET ENTRIES
$_CORE->load("Page");
$students = $_CORE->Page->autoGet("Course", "stuCount", "stuGet");

// (B) STUDENTS LIST 
if (is_array($students)) { ?>
<table class="zebra">
 <?php foreach ($students as $id=>$s) { ?>
  <tr>
    <td>
      <strong><?=$s['user_name']?></strong><br>
      <small><?=$s['user_email']?></small>
    </td>
    <td class="right">
      <input type="button" value="Delete" onclick="stu.del(<?=$id?>)">
    </td>
  </tr>
  <?php } ?>
</table>
<?php } else { echo "<div>No students found.</div>"; }

// (C) PAGINATION
$_CORE->Page->draw("stu.goToPage", "J");