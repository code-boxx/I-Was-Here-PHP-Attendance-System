ALTER TABLE `attendance_schedule_audit`
  ADD COLUMN `undone_by` bigint(20) DEFAULT NULL,
  ADD COLUMN `undone_at` datetime DEFAULT NULL,
  ADD COLUMN `undo_reason` varchar(500) DEFAULT NULL;
