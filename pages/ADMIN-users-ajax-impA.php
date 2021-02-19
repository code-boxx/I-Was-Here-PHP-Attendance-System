<!-- (A) INSTRUCTIONS -->
<h1>IMPORT USERS FROM FILE</h1>
<div class="bar">
  Columns in the CSV file must be in the following order : name, email, password. 
  Do not mass import thousands of entries at once, separate into multiple files.
</div>

<!-- (B) IMPORT FORM -->
<form class="standard" onsubmit="return uimp.upload()">
  <label for="imp_role">Role</label>
  <select id="imp_role">
    <option value="S">Students</option>
    <option value="T">Teachers</option>
  </select>
  <label for="imp_file">CSV File</label>
  <input type="file" id="imp_file" accept=".csv" required/>
  <input type="submit" value="Upload"/>
  <input type="button" value="Back" onclick="common.page('A')"/>
</form>