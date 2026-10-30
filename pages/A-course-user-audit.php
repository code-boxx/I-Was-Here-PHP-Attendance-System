<?php
$events = $_CORE->autoCall("Courses", "getRosterAudit");
$labels = [
  "enrolled" => "Enrolled",
  "removed" => "Removed",
  "moved_in" => "Moved in",
  "moved_out" => "Moved out"
];
if (is_array($events) && count($events)>0) {
  foreach ($events as $event) { ?>
  <div class="d-flex flex-wrap align-items-center gap-2 border p-2">
    <div class="flex-grow-1">
      <strong><?=htmlspecialchars($labels[$event["event_type"]] ?? $event["event_type"], ENT_QUOTES, "UTF-8")?></strong>
      — <?=htmlspecialchars($event["student_name"] ?? "Deleted account", ENT_QUOTES, "UTF-8")?>
      <small>(<?=htmlspecialchars($event["student_email"] ?? "", ENT_QUOTES, "UTF-8")?>)</small><br>
      <small><?=htmlspecialchars($event["reason"], ENT_QUOTES, "UTF-8")?> ·
        <?=htmlspecialchars($event["changed_by_name"] ?? "Deleted admin", ENT_QUOTES, "UTF-8")?> ·
        <?=htmlspecialchars($event["changed_at"], ENT_QUOTES, "UTF-8")?></small>
      <?php if ($event["undone_at"]!==null) { ?>
        <br><small class="text-secondary">Undone by
          <?=htmlspecialchars($event["undone_by_name"] ?? "Deleted admin", ENT_QUOTES, "UTF-8")?>:
          <?=htmlspecialchars($event["undo_reason"] ?? "", ENT_QUOTES, "UTF-8")?>
        </small>
      <?php } ?>
    </div>
    <?php if ($event["undone_at"]===null) { ?>
      <button type="button" class="btn btn-outline-danger"
              onclick="cuser.undo(<?=$event["event_id"]?>)">Undo operation</button>
    <?php } ?>
  </div>
  <?php }
} else { ?>
  <p class="text-secondary">No roster changes have been recorded for this course.</p>
<?php } ?>
