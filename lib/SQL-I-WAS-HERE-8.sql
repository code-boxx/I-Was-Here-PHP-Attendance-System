ALTER TABLE `attendance`
  MODIFY `a_status` tinyint(1) NOT NULL;

ALTER TABLE `attendance_corrections`
  MODIFY `requested_status` tinyint(1) NOT NULL;

ALTER TABLE `attendance_audit`
  MODIFY `old_status` tinyint(1) DEFAULT NULL,
  MODIFY `new_status` tinyint(1) NOT NULL;

ALTER TABLE `classes`
  ADD COLUMN `class_status` enum('scheduled','cancelled') NOT NULL DEFAULT 'scheduled',
  ADD COLUMN `class_status_reason` varchar(500) DEFAULT NULL,
  ADD COLUMN `class_status_by` bigint(20) DEFAULT NULL,
  ADD COLUMN `class_status_at` datetime DEFAULT NULL;

CREATE TABLE IF NOT EXISTS `attendance_holidays` (
  `holiday_date` date NOT NULL,
  `holiday_name` varchar(255) NOT NULL,
  `created_by` bigint(20) NOT NULL,
  `created_at` datetime NOT NULL,
  `updated_by` bigint(20) NOT NULL,
  `updated_at` datetime NOT NULL,
  PRIMARY KEY (`holiday_date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `attendance_schedule_audit` (
  `event_id` bigint(20) NOT NULL AUTO_INCREMENT,
  `class_id` bigint(20) DEFAULT NULL,
  `holiday_date` date DEFAULT NULL,
  `event_type` varchar(32) NOT NULL,
  `old_value` varchar(255) DEFAULT NULL,
  `new_value` varchar(255) DEFAULT NULL,
  `reason` varchar(500) NOT NULL,
  `changed_by` bigint(20) NOT NULL,
  `changed_at` datetime NOT NULL,
  PRIMARY KEY (`event_id`),
  KEY `class_changed` (`class_id`,`changed_at`),
  KEY `holiday_changed` (`holiday_date`,`changed_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
