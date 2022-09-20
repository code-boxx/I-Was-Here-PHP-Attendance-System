<!-- (A) NAVIGATION -->
<div class="d-flex align-items-center mb-3">
  <div class="flex-grow-1">
    <h3 class="mb-0">IMPORT USERS</h3>
    <small class="text-danger fw-bold">Existing users will be overridden!</small>
  </div>
  <button class="btn btn-danger mi" onclick="cb.page(0)">
    reply
  </button>
</div>

<!-- (B) SELECT CSV FILE -->
<div id="user-import-select">
  <div class="head border mb-2 p-2 d-flex align-items-center">
    <input type="file" class="form-control" required accept=".csv" 
    id="user-import-file" onchange="uimport.read()">
  </div>
  
  <small>
    * CSV file format - name | email | role | password<br>
    * Roles - <u>A</u>dmin, <u>T</u>eacher, <u>S</u>tudent
  </small>
</div>

<!-- (C) USERS IMPORT LIST -->
<table id="user-import-table" class="table table-striped d-none">
  <thead><tr class="table-dark">
    <th>Name</th>
    <th>Email</th>
    <th>Role</th>
    <th>Password</th>
    <th>Status</th>
  </tr></thead>
  <tbody id="user-import-list"></tbody>
<table>