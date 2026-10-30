CREATE TABLE IF NOT EXISTS `course_roster_audit` (
  `event_id` bigint(20) NOT NULL AUTO_INCREMENT,
  `operation_id` char(32) NOT NULL,
  `course_code` varchar(255) NOT NULL,
  `user_id` bigint(20) NOT NULL,
  `event_type` enum('enrolled','removed','moved_in','moved_out') NOT NULL,
  `reason` varchar(500) NOT NULL,
  `changed_by` bigint(20) NOT NULL,
  `changed_at` datetime NOT NULL,
  `undone_by` bigint(20) DEFAULT NULL,
  `undone_at` datetime DEFAULT NULL,
  `undo_reason` varchar(500) DEFAULT NULL,
  PRIMARY KEY (`event_id`),
  KEY `operation_id` (`operation_id`),
  KEY `course_changed` (`course_code`,`changed_at`),
  KEY `user_changed` (`user_id`,`changed_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
