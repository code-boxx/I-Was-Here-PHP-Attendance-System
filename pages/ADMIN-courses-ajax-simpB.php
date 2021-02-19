<?php
// (A) FILE CHECK
if (!isset($_FILES['csv'])) { exit("NO FILE UPLOADED"); }
if (strtolower(pathinfo($_FILES["csv"]["name"], PATHINFO_EXTENSION))!="csv") { exit("INVALID FILE"); }

// (B) READ ENTRIES ?>
<h1>IMPORT STUDENTS TO COURSE</h1>
<table class="zebra" id="imp_list"><?php
  $i = 0;
  if (($handle = fopen($_FILES["csv"]["tmp_name"], "r")) !== false) {
    echo "<tr><th>EMAIL</th><th>STATUS</th></tr>";
    while (($row = fgetcsv($handle, 1000, ",")) !== false) { 
      printf("<tr class='improw'>"
      . "<td id='imail%u'>%s</td>"
      . "<td id='istat%u'></td></tr>",
      $i, $row[0], $i
      );
      $i++;
    }
    fclose($handle);
  } 
  if ($i==0) { echo "<tr><td>ERROR READING FILE!</td></tr>"; }
?></table><br>

<input type="button" id="impback" value="Back" onclick="common.page('C');"/>
<?php if ($i>0) { ?>
<input type="button" id="impgo" value="Import" onclick="simp.go();"/>
<?php } ?>