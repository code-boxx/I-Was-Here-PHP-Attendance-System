<?php
// (A) GET ENTRIES
$_CORE->load("Page");
$_POST['id'] = $_SESSION['user']['id'];
$classes = $_CORE->Page->autoGet("Classes", "attendStuCount", "attendStuGet");

// (B) CLASS LIST
if (is_array($classes)) { ?>
<table class="zebra">
  <?php foreach ($classes as $id=>$c) { ?>
  <tr><td>
    <strong>[<?=$c['class_date']?>]</strong><br>
    <?=$c['course_code']?>
  </td></tr>
  <?php } ?>
</table>
<?php } else { echo "<div>No classes found.</div>"; }

// (C) PAGINATION
$_CORE->Page->draw("attend.goToPage", "J");