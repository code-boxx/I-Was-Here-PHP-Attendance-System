<!-- (A) NAVIGATION -->
<div class="d-flex align-items-center mb-3">
  <h3 class="flex-grow-1">IMPORT USERS INTO COURSE</h3>
  <div>
    <button class="btn btn-danger mi" onclick="cb.page(1)">
      reply
    </button>
  </div>
</div>

<!-- (B) SELECT CSV FILE -->
<div id="user-import-select">
  <div class="head border mb-2 p-2 d-flex align-items-center">
    <input type="file" class="form-control" required accept=".csv" 
    id="user-import-file" onchange="uimport.read()">
  </div>

  <small>
    * CSV file should only have one column of emails
  </small>
</div>

<!-- (C) USERS IMPORT LIST -->
<table id="user-import-table" class="table table-striped d-none">
  <thead><tr class="table-dark">
    <th>Email</th>
    <th>Status</th>
  </tr></thead>
  <tbody id="user-import-list"></tbody>
<table>