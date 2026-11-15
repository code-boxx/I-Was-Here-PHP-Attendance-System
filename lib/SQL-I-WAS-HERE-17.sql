ALTER TABLE `users`
  ADD COLUMN `mfa_secret` varchar(64) DEFAULT NULL,
  ADD COLUMN `mfa_recovery` text DEFAULT NULL;
