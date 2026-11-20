CREATE TABLE IF NOT EXISTS `notification_preferences` (
  `user_id` bigint(20) NOT NULL,
  `attendance_warning` tinyint(1) NOT NULL DEFAULT 0,
  `correction_decision` tinyint(1) NOT NULL DEFAULT 0,
  `class_reminder` tinyint(1) NOT NULL DEFAULT 0,
  `updated_at` datetime NOT NULL,
  PRIMARY KEY (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `notification_outbox` (
  `notification_id` bigint(20) NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) NOT NULL,
  `category` enum('attendance_warning','correction_decision','class_reminder') NOT NULL,
  `event_key` varchar(191) NOT NULL,
  `recipient_email` varchar(255) NOT NULL,
  `subject` varchar(255) NOT NULL,
  `body` text NOT NULL,
  `delivery_status` enum('pending','sending','sent','failed','cancelled') NOT NULL DEFAULT 'pending',
  `attempts` tinyint(3) unsigned NOT NULL DEFAULT 0,
  `available_at` datetime NOT NULL,
  `claimed_at` datetime DEFAULT NULL,
  `sent_at` datetime DEFAULT NULL,
  `last_error` varchar(500) DEFAULT NULL,
  PRIMARY KEY (`notification_id`),
  UNIQUE KEY `event_key` (`event_key`),
  KEY `delivery_status` (`delivery_status`,`available_at`),
  KEY `user_category` (`user_id`,`category`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

ALTER TABLE `notification_outbox`
  MODIFY `event_key` varchar(191) NOT NULL;
