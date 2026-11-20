CREATE TABLE IF NOT EXISTS `security_events` (
  `event_id` bigint(20) NOT NULL AUTO_INCREMENT,
  `event_type` varchar(64) NOT NULL,
  `user_id` bigint(20) DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` varchar(255) DEFAULT NULL,
  `details` json DEFAULT NULL,
  `created_at` datetime NOT NULL,
  PRIMARY KEY (`event_id`),
  KEY `security_event_type` (`event_type`,`created_at`),
  KEY `security_event_user` (`user_id`,`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
