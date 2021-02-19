<h1>IMPORT STUDENTS TO COURSE</h1>
<div class="bar">
  Create a single column CSV file with the email of students.
  Do not mass import thousands of entries at once, separate into multiple files.
</div>

<form class="standard" onsubmit="return simp.upload()">
  <label for="imp_file">CSV File</label>
  <input type="file" id="imp_file" required/>
  <input type="submit" value="Upload"/>
  <input type="button" value="Back" onclick="common.page('B')">
</form>