<?php
$course = $_CORE->autoCall("Courses", "get");
$events = $_CORE->autoCall("Courses", "getCourseAudit");
$labels = [
  "created" => "Created",
  "updated" => "Updated",
  "deleted" => "Deleted"
];
?>
<h3 class="mb-3">COURSE AUDIT HISTORY</h3>
<p class="text-secondary" data-course-code="<?=htmlspecialchars($course["course_code"] ?? "", ENT_QUOTES, "UTF-8")?>">
  <?=htmlspecialchars($course["course_code"] ?? "", ENT_QUOTES, "UTF-8")?> —
  <?=htmlspecialchars($course["course_name"] ?? "Deleted course", ENT_QUOTES, "UTF-8")?>
</p>
<?php if (is_array($events) && count($events)>0) {
  foreach ($events as $event) { ?>
  <div class="border p-2 mb-2">
    <strong><?=htmlspecialchars($labels[$event["event_type"]] ?? $event["event_type"], ENT_QUOTES, "UTF-8")?></strong>
    <small class="text-secondary">
      <?=htmlspecialchars($event["changed_by_name"] ?? "Deleted account", ENT_QUOTES, "UTF-8")?> ·
      <?=htmlspecialchars($event["changed_at"], ENT_QUOTES, "UTF-8")?>
    </small>
    <div><?=htmlspecialchars($event["reason"], ENT_QUOTES, "UTF-8")?></div>
    <?php if ($event["undone_at"]!==null) { ?>
      <div class="text-secondary">
        Undone by <?=htmlspecialchars($event["undone_by_name"] ?? "Deleted admin", ENT_QUOTES, "UTF-8")?>:
        <?=htmlspecialchars($event["undo_reason"] ?? "", ENT_QUOTES, "UTF-8")?>
      </div>
    <?php } elseif ($event["event_type"]==="updated") { ?>
      <button type="button" class="btn btn-outline-danger btn-sm mt-1"
              onclick="course.undoAudit(<?=$event["event_id"]?>)">Undo update</button>
    <?php } ?>
    <?php if ($event["old_values"]!==null || $event["new_values"]!==null) { ?>
      <details>
        <summary>View recorded values</summary>
        <?php if ($event["old_values"]!==null) { ?>
          <pre class="small mb-1"><?=htmlspecialchars($event["old_values"], ENT_QUOTES, "UTF-8")?></pre>
        <?php } ?>
        <?php if ($event["new_values"]!==null) { ?>
          <pre class="small mb-0"><?=htmlspecialchars($event["new_values"], ENT_QUOTES, "UTF-8")?></pre>
        <?php } ?>
      </details>
    <?php } ?>
  </div>
<?php }} else { ?>
  <p class="text-secondary">No course changes have been recorded.</p>
<?php } ?>
<button type="button" class="btn btn-danger" onclick="cb.page(1)">Back</button>
