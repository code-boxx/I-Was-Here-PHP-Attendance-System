<!-- (A) NAVIGATION -->
<div class="d-flex align-items-center mb-3">
  <div class="flex-grow-1">
    <h3 class="mb-0">IMPORT CLASSES</h3>
    <small class="text-danger fw-bold">Classes will not be overridden, duplicates will be created!</small>
  </div>
  <button class="btn btn-danger mi" onclick="cb.page(0)">
    reply
  </button>
</div>

<!-- (B) SELECT CSV FILE -->
<div id="class-import-select">
  <div class="head border mb-2 p-2 d-flex align-items-center">
    <input type="file" class="form-control" required accept=".csv" 
    id="class-import-file" onchange="cimport.read()">
  </div>

  <small>
    * CSV Columns - Course Code | Date | Teacher (email) | Description<br>
    * Date Format - YYYY-MM-DD HH:MM<br>
    * <a href="<?=HOST_ASSETS?>0d-dummy-classes.csv">Example</a>
  </small>
</div>

<!-- (C) CLASS IMPORT LIST -->
<table id="class-import-table" class="table table-striped d-none">
  <thead><tr class="table-dark">
    <th>Code</th>
    <th>Date</th>
    <th>Teacher</th>
    <th>Description</th>
    <th>Status</th>
  </tr></thead>
  <tbody id="class-import-list"></tbody>
<table>