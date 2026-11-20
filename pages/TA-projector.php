<?php
$classId = filter_input(INPUT_GET, "id", FILTER_VALIDATE_INT);
if (!$classId || $classId < 1) {
  http_response_code(400);
  exit("Invalid class.");
}
$_CORE->load("Classes");
$class = $_CORE->Classes->get($classId);
if (!is_array($class)) {
  http_response_code(404);
  exit("Class unavailable.");
}
$_PMETA = [
  "title" => "Classroom attendance display",
  "load" => [["s", HOST_ASSETS."TA-projector.js", "defer"]]
];
header("Cache-Control: private, no-store, max-age=0");
require PATH_PAGES . "TEMPLATE-top.php";
?>
<main class="container py-4 text-center" aria-labelledby="projector-title">
  <div class="d-flex justify-content-between align-items-center mb-4">
    <a class="btn btn-outline-secondary" href="<?=HOST_BASE?>TA/">
      Back to classes
    </a>
    <button id="projector-fullscreen" type="button" class="btn btn-outline-primary">
      Full screen
    </button>
  </div>
  <h1 id="projector-title">
    <?=htmlspecialchars($class["course_name"], ENT_QUOTES, "UTF-8")?>
  </h1>
  <p class="lead">
    <?=htmlspecialchars($class["cd"], ENT_QUOTES, "UTF-8")?>
  </p>
  <p id="projector-session" class="badge bg-secondary fs-5">Loading session…</p>
  <div class="row g-3 my-4" aria-live="polite">
    <div class="col-6 col-lg-3"><div class="border rounded bg-white p-4">
      <div class="text-secondary">Present</div><strong id="projector-present" class="display-2">—</strong>
    </div></div>
    <div class="col-6 col-lg-3"><div class="border rounded bg-white p-4">
      <div class="text-secondary">Late</div><strong id="projector-late" class="display-2">—</strong>
    </div></div>
    <div class="col-6 col-lg-3"><div class="border rounded bg-white p-4">
      <div class="text-secondary">Absent</div><strong id="projector-absent" class="display-2">—</strong>
    </div></div>
    <div class="col-6 col-lg-3"><div class="border rounded bg-white p-4">
      <div class="text-secondary">Excused</div><strong id="projector-excused" class="display-2">—</strong>
    </div></div>
    <div class="col-6 col-lg-3"><div class="border rounded bg-white p-4">
      <div class="text-secondary">Not recorded</div><strong id="projector-unrecorded" class="display-2">—</strong>
    </div></div>
  </div>
  <p id="projector-progress" class="h3"></p>
  <p id="projector-updated" class="text-secondary" role="status"></p>
  <p id="projector-error" class="text-danger" role="alert"></p>
</main>
<script>var projectorClassId = <?=json_encode($classId, JSON_THROW_ON_ERROR)?>;</script>
<?php require PATH_PAGES . "TEMPLATE-bottom.php"; ?>
