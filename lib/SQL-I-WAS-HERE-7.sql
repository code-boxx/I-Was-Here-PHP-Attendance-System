CREATE TABLE IF NOT EXISTS `attendance_offline_queue` (
  `event_id` varchar(191) NOT NULL,
  `class_id` bigint(20) NOT NULL,
  `user_id` bigint(20) NOT NULL,
  `attendance_status` tinyint(1) NOT NULL DEFAULT 1,
  `recorded_at` datetime NOT NULL,
  `event_payload` json DEFAULT NULL,
  `status` enum('pending','synced','failed','rejected') NOT NULL DEFAULT 'pending',
  `created_at` datetime NOT NULL,
  `processed_at` datetime DEFAULT NULL,
  `reviewed_by` bigint(20) DEFAULT NULL,
  `review_note` varchar(500) DEFAULT NULL,
  `last_error` varchar(500) DEFAULT NULL,
  PRIMARY KEY (`event_id`),
  KEY `class_status` (`class_id`,`status`),
  KEY `user_status` (`user_id`,`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `class_substitutes` (
  `class_id` bigint(20) NOT NULL,
  `user_id` bigint(20) NOT NULL,
  `created_by` bigint(20) NOT NULL,
  `created_at` datetime NOT NULL,
  `expires_at` datetime NOT NULL,
  PRIMARY KEY (`class_id`,`user_id`),
  KEY `user_id` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

REPLACE INTO `settings` (`setting_name`, `setting_description`, `setting_value`, `setting_group`) VALUES
('ATTENDANCE_LATE_GRACE_MINUTES', 'Grace period before a check-in is marked late (minutes)', '10', 1);
