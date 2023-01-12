<!-- (A) NAVIGATION -->
<div class="d-flex align-items-center mb-3">
  <div class="flex-grow-1">
    <h3 class="mb-0">IMPORT COURSES</h3>
    <small class="text-danger fw-bold">Existing courses will be overridden!</small>
  </div>
  <button class="btn btn-danger mi" onclick="cb.page(0)">
    reply
  </button>
</div>

<!-- (B) SELECT CSV FILE -->
<div id="course-import-select">
  <div class="head border mb-2 p-2 d-flex align-items-center">
    <input type="file" class="form-control" required accept=".csv" 
    id="course-import-file" onchange="cimport.read()">
  </div>

  <small>
    * CSV Columns - Code | Name | Description | Start date | End date<br>
    * Date Format - YYYY-MM-DD<br>
    * <a href="<?=HOST_ASSETS?>0b-dummy-courses.csv">Example</a>
  </small>
</div>

<!-- (C) COURSE IMPORT LIST -->
<table id="course-import-table" class="table table-striped d-none">
  <thead><tr class="table-dark">
    <th>Code</th>
    <th>Name</th>
    <th>Description</th>
    <th>Start</th>
    <th>End</th>
    <th>Status</th>
  </tr></thead>
  <tbody id="course-import-list"></tbody>
<table>