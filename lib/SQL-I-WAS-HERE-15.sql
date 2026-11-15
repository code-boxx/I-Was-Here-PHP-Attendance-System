CREATE TABLE IF NOT EXISTS `user_sessions` (
  `session_id` varchar(255) NOT NULL,
  `user_id` bigint(20) NOT NULL,
  `created_at` datetime NOT NULL,
  `last_seen_at` datetime NOT NULL,
  `expires_at` datetime DEFAULT NULL,
  `revoked_at` datetime DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`session_id`),
  KEY `user_active_sessions` (`user_id`,`revoked_at`,`last_seen_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
