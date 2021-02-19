<!-- (A) INSTRUCTIONS -->    
<h1>IMPORT COURSES FROM FILE</h1>
<div class="bar">
  Columns in the CSV file must be in the following order : code, name, description. 
  Do not mass import thousands of entries at once, separate into multiple files.
</div>

<!-- (B) IMPORT FORM -->
<form class="standard" onsubmit="return cimp.upload()">
  <label for="imp_file">CSV File</label>
  <input type="file" id="imp_file" required/>
  <input type="submit" value="Upload"/>
  <input type="button" value="Back" onclick="common.page('A')">
</form>