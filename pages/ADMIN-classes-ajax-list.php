<?php
// (A) GET ENTRIES
$_CORE->load("Page");
$classes = $_CORE->Page->autoGet("Classes", "countAll", "getAll");

// (B) CLASS LIST
if (is_array($classes)) { ?>
<table class="zebra">
  <?php foreach ($classes as $id=>$c) { ?>
  <tr>
    <td>
      <strong>[<?=$c['course_code']?>] <?=$c['class_date']?></strong><br>
      <small><?=$c['user_name']?></small><br>
      <small><?=$c['class_desc']?></small>
    </td>
    <td class="right">
      <input type="button" value="Delete" onclick="cla.del(<?=$id?>)"/>
      <input type="button" value="Edit" onclick="cla.addEdit(<?=$id?>)"/>
      <input type="button" value="Attendance" onclick="cla.attend(<?=$id?>)"/>
    </td>
  </tr>
  <?php } ?>
</table>
<?php } else { echo "<div>No classes found.</div>"; }

// (C) PAGINATION
$_CORE->Page->draw("cor.goToPage", "J");