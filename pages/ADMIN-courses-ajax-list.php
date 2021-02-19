<?php
// (A) GET ENTRIES
$_CORE->load("Page");
$courses = $_CORE->Page->autoGet("Course", "countAll", "getAll");

// (B) COURSES LIST
if (is_array($courses)) { ?>
<table class="zebra">
  <?php foreach ($courses as $id=>$c) { ?>
  <tr>
    <td>
      <strong>[<?=$c['course_code']?>] <?=$c['course_name']?></strong><br>
      <small><?=$c['course_desc']?></small>
    </td>
    <td class="right">
      <input type="button" value="Delete" onclick="cor.del('<?=$id?>')"/>
      <input type="button" value="Edit" onclick="cor.addEdit('<?=$id?>')"/>
      <input type="button" value="Students" onclick="stu.attach('<?=$id?>')"/>
    </td>
  </tr>
  <?php } ?>
</table>
<?php } else { echo "<div>No courses found.</div>"; }

// (C) PAGINATION
$_CORE->Page->draw("cor.goToPage", "J");