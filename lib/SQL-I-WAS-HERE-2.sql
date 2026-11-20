CREATE TABLE IF NOT EXISTS `login_attempts` (
  `attempt_key` char(64) NOT NULL,
  `failed_attempts` int(11) NOT NULL DEFAULT 0,
  `window_started` datetime NOT NULL,
  `locked_until` datetime DEFAULT NULL,
  PRIMARY KEY (`attempt_key`),
  KEY `locked_until` (`locked_until`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
