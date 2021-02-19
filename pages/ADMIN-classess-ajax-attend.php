<?php
// (A) GET CLASS + ATTENDANCE
$_CORE->load("Classes");
$attend = $_CORE->Classes->attendGet($_POST['id']); 
if (!is_array($attend)) { exit("ERROR - INVALID CLASS!"); }

// (B) ATTENDANCE LIST ?>
<h1>[<?=$attend['class']['course_code']?>] <?=$attend['class']['class_date']?></h1>
<?php if (isset($attend['all'])) { ?>
<table class="zebra"><?php
   foreach ($attend['all'] as $id=>$u) { ?>
  <tr><td><label>
    <input type="checkbox" id="att-<?=$id?>" 
           onchange="cla.attup(<?=$id?>)" <?=isset($u['a'])?" checked":""?>/>
    <?=$u['user_name']?>
  </label></td></tr>
  <?php } ?>
</table>
<?php } else { echo "No students found."; } ?>

<form target="_blank" action="<?=URL_ADMIN?>export-attend" method="post">
  <input type="hidden" id="attclass" name="id" value="<?=$_POST['id']?>"/>
  <input type="button" value="Back" onclick="common.page('A')"/>
  <input type="submit" value="Export CSV"/>
</form>