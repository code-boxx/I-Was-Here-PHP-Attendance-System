CREATE TABLE IF NOT EXISTS `course_audit` (
  `event_id` bigint(20) NOT NULL AUTO_INCREMENT,
  `course_code` varchar(255) NOT NULL,
  `event_type` enum('created','updated','deleted') NOT NULL,
  `old_values` json DEFAULT NULL,
  `new_values` json DEFAULT NULL,
  `reason` varchar(500) NOT NULL,
  `changed_by` bigint(20) NOT NULL,
  `changed_at` datetime NOT NULL,
  PRIMARY KEY (`event_id`),
  KEY `course_changed` (`course_code`,`changed_at`),
  KEY `course_actor` (`changed_by`,`changed_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
