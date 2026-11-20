CREATE TABLE IF NOT EXISTS `privacy_requests` (
  `request_id` bigint(20) NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) NOT NULL,
  `request_type` enum('deletion') NOT NULL,
  `request_status` enum('pending','approved','rejected') NOT NULL DEFAULT 'pending',
  `requested_at` datetime NOT NULL,
  `decided_by` bigint(20) DEFAULT NULL,
  `decided_at` datetime DEFAULT NULL,
  `decision_note` varchar(500) DEFAULT NULL,
  PRIMARY KEY (`request_id`),
  KEY `privacy_user_status` (`user_id`,`request_status`),
  KEY `privacy_status` (`request_status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
