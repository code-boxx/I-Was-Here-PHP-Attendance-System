ALTER TABLE `users`
  ADD COLUMN `session_version` bigint(20) unsigned NOT NULL DEFAULT 0;
