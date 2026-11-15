INSERT INTO `settings`
  (`setting_name`,`setting_description`,`setting_value`,`setting_group`)
VALUES
  ('SESSION_IDLE_MINUTES','Automatically expire inactive sessions after this many minutes','30',1),
  ('SESSION_ABSOLUTE_HOURS','Maximum lifetime of a session regardless of activity','12',1)
ON DUPLICATE KEY UPDATE
  `setting_description`=VALUES(`setting_description`);
