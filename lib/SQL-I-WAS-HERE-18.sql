INSERT INTO `settings`
  (`setting_name`,`setting_description`,`setting_value`,`setting_group`)
VALUES
  ('SECURITY_EVENT_RETENTION_DAYS','Days to retain security events','365',1)
ON DUPLICATE KEY UPDATE `setting_description`=VALUES(`setting_description`);
