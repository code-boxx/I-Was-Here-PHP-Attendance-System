CREATE TABLE IF NOT EXISTS `attendance_sessions` (
  `session_id` bigint(20) NOT NULL AUTO_INCREMENT,
  `class_id` bigint(20) NOT NULL,
  `opened_by` bigint(20) NOT NULL,
  `opened_at` datetime NOT NULL,
  `closed_at` datetime DEFAULT NULL,
  `session_status` enum('open','closed') NOT NULL DEFAULT 'open',
  PRIMARY KEY (`session_id`),
  KEY `class_status` (`class_id`,`session_status`),
  KEY `opened_by` (`opened_by`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `attendance_corrections` (
  `request_id` bigint(20) NOT NULL AUTO_INCREMENT,
  `class_id` bigint(20) NOT NULL,
  `user_id` bigint(20) NOT NULL,
  `requested_status` tinyint(1) NOT NULL,
  `request_reason` varchar(500) NOT NULL,
  `request_status` enum('pending','approved','rejected') NOT NULL DEFAULT 'pending',
  `submitted_at` datetime NOT NULL,
  `decided_by` bigint(20) DEFAULT NULL,
  `decided_at` datetime DEFAULT NULL,
  `decision_note` varchar(500) DEFAULT NULL,
  PRIMARY KEY (`request_id`),
  KEY `class_status` (`class_id`,`request_status`),
  KEY `student_status` (`user_id`,`request_status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `attendance_audit` (
  `audit_id` bigint(20) NOT NULL AUTO_INCREMENT,
  `class_id` bigint(20) NOT NULL,
  `user_id` bigint(20) NOT NULL,
  `old_status` tinyint(1) DEFAULT NULL,
  `new_status` tinyint(1) NOT NULL,
  `changed_by` bigint(20) NOT NULL,
  `change_reason` varchar(500) DEFAULT NULL,
  `change_source` varchar(32) NOT NULL,
  `changed_at` datetime NOT NULL,
  PRIMARY KEY (`audit_id`),
  KEY `class_user` (`class_id`,`user_id`),
  KEY `changed_by` (`changed_by`),
  KEY `changed_at` (`changed_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
